<?php
namespace app\api\library\robot;

use think\Db;
use think\Cache;
use think\Env;
use think\Exception;
use think\Log;
use ReflectionFunction;

class Coze extends Base implements Channel{


    public function chatStream($param,$options,$connection=''){
        $session = isset($param['sessionId']) ? $param['sessionId'] : 0;

        if(!$param['coze_id']){
            $result['code'] = '-1';
            $result['data'] = '';
            $result['tokens'] = 0;
            $result['msg'] = '未知错误，生成失败';
            return $result;
        }
        $token = Env::get('apitoken.token');

        $sessionId = $this->_getSession($session,$param['coze_id']);

        $url = 'https://api.coze.cn/v3/chat';
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ];
        $sessionId && $url .= '?conversation_id='.$sessionId;

        $params['stream'] = true;
        $params['bot_id'] = $param['coze_id'];
        $params['user_id'] = (string)$param['user_id'];

        if($param['image']){
            $contentObj = [
                ['type'=>'text','text'=>$param['contents'][0]['content']],
                ['type'=>'image','file_url'=>$param['image']],
            ];
            $params['additional_messages'][] = ['role'=>'user','content_type'=>'object_string','content'=>json_encode($contentObj)];
        }else{
            foreach ($param['contents'] as $v){
                $params['additional_messages'][] = ['role'=>$v['role'],'content_type'=>'text','content'=>$v['content']];
            }
        }


        $callback = function($oCurl, $data) use($param,$connection) {

            static $result = [];
            static $str = '';
            try {
                Log::record('curl-response-stream-coze:'.var_export($data,true));
                $result['code'] = 0;
                $result['tokens'] = 0;
                $result['msg'] = '成功';
                $result['model'] = $param['model'];

                $res = $this->_parseData($data);

                if(isset($res['message']) && $res['message'] == 'error'){
                    $result['code'] = -1;
                    $result['msg'] = $res['message'];
                    $result['data'] = '';
                    return 0;
                }

                if(isset($res['content'])){
                    $str .= $res['content'];
                }
                $result['data'] = $str;

                $result['tokens'] = $res['token'];

                $connection->send(json_encode($result));

            }catch (Exception $exception){
                $result['code'] = -1;
                $result['msg'] = '未知错误';
                $result['tokens'] = 0;
                $result['data'] = $str;
                $result['model'] = $param['model'];
                return 0;
            }
            return strlen($data);
        };

        $this->_curl($url,1,json_encode($params,JSON_UNESCAPED_UNICODE),$header,$callback);

        $func = new ReflectionFunction($callback);
        $data = $func->getStaticVariables();

        return $data['result'];

    }

    public function chat($param,$options){
        $result['model'] = $param['model'];
        $session = isset($param['sessionId']) ? $param['sessionId'] : 0;

        if(!$param['coze_id']){
            $result['code'] = '-1';
            $result['data'] = '';
            $result['tokens'] = 0;
            $result['msg'] = '未知错误，生成失败';
            return $result;
        }

        $sessionId = $this->_getSession($session,$param['coze_id']);
        $token = Env::get('apitoken.token');

        $url = 'https://api.coze.cn/v3/chat';
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ];
        $sessionId && $url .= '?conversation_id='.$sessionId;

        $params['stream'] = false;
        $params['bot_id'] = $param['coze_id'];
        $params['user_id'] = (string)$param['user_id'];

        if($param['image']){
            $contentObj = [
                ['type'=>'text','text'=>$param['contents'][0]['content']],
                ['type'=>'image','file_url'=>$param['image']],
            ];
            $params['additional_messages'][] = ['role'=>'user','content_type'=>'object_string','content'=>json_encode($contentObj)];
        }else{
            foreach ($param['contents'] as $v){
                $params['additional_messages'][] = ['role'=>$v['role'],'content_type'=>'text','content'=>$v['content']];
            }
        }
        $res = $this->_curl($url,1,json_encode($params,JSON_UNESCAPED_UNICODE),$header);
        $data = json_decode($res,true);

        $message = '';
        for($i=0;$i<180;$i++){
            $status = $this->_getMessageInfo($data['data']['conversation_id'],$data['data']['id']);
            if($status['status'] == 'completed'){
                $message = $this->_getMessage($data['data']['conversation_id'],$data['data']['id']);
                break;
            }elseif($status['status'] == 'created' || $status['status'] == 'in_progress'){
                sleep(1);
                continue;
            }else{
                break;
            }
        }

        if(!$message){
            $result['code'] = '-1';
            $result['data'] = '';
            $result['tokens'] = 0;
            $result['msg'] = '未知错误，生成失败';
            return $result;
        }

        $result['data'] = $message;
        $result['code'] = 0;
        $result['msg'] = '成功';
        $result['tokens'] = $status['token'];
        return $result;
    }



    private function _getSession($id=0,$appid){

        $cacheKey = 'coze_session'.$id.$appid;
        $session =  Cache::get($cacheKey);
        if($session && $id) {
            return $session;
        }
        $token = Env::get('apitoken.token');

        $url = 'https://api.coze.cn/v1/conversation/create';
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ];

        $res = $this->_curl($url,1,[],$header);
        $data = json_decode($res,true);
        $session = $data['data']['id'];
        Cache::set($cacheKey,$session,86400);
        return $session;
    }

    private function _parseData($data){
        $responseArr = explode("\n", $data);
        $content = '';
        $event = '';
        $message = '';
        static $token = 0;

        foreach($responseArr as $k=>$v){
            if(!$v){
                continue;
            }

            if(strpos($v,'event:') === 0){
                $event = substr($v,6);
            }

            if(strpos($v,'data:') === 0){

                if($event == 'conversation.message.delta'){
                    $res = json_decode(substr($v,5),true);
                    $content .= $res['content'];
                }elseif($event == 'conversation.chat.completed'){
                    $res = json_decode(substr($v,5),true);
                    $token = isset($res['usage']['token_count']) ? $res['usage']['token_count'] : 0;
                }elseif($event == 'conversation.chat.failed' || $event == 'error'){
                    $message = 'error';
                }

            }
        }


        return ['content'=>$content,'message'=>$message,'token'=>$token];
    }

    private function _getMessageInfo($conversationId,$chatId){
        $url = 'https://api.coze.cn/v3/chat/retrieve';
        $token = Env::get('apitoken.token');

        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ];

        $params['conversation_id'] = $conversationId;
        $params['chat_id'] = $chatId;

        $url .= "?" . http_build_query($params);
        $res = $this->_curl($url,0,$params,$header);
        $res = json_decode($res,true);
        $info['status'] = isset($res['data']['status']) ? $res['data']['status'] : '';
        $info['token'] = isset($res['data']['usage']['token_count']) ? $res['data']['usage']['token_count'] : '';
        return $info;
    }



    private function _getMessage($conversationId,$chatId){
        $url = 'https://api.coze.cn/v3/chat/message/list';
        $token = Env::get('apitoken.token');
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ];

        $params['conversation_id'] = $conversationId;
        $params['chat_id'] = $chatId;

        $url .= "?" . http_build_query($params);
        $res = $this->_curl($url,0,$params,$header);
        $res = json_decode($res,true);
        $message = '';

        foreach ($res['data'] as $key => $value) {
            if($value['type'] == 'answer'){
                $message .= $value['content']."\n";
            }
        }

        return $message;
    }

}