<?php

namespace app\api\controller\robot;

use app\common\library\Upload;
use app\common\model\robot\Vip;
use app\common\model\robot\Channelmodel;
use think\helper\Str;
use Workerman\Timer;
use Workerman\Connection\TcpConnection;
use app\api\library\robot\WeChat;
use app\common\model\robot\Assistant;
use app\common\model\robot\Creation;
use app\common\model\robot\Dialog;
use app\common\model\robot\Channel;
use app\common\model\robot\Session;
use app\common\model\robot\User;
use app\api\library\robot\Tts;

use think\Db;
use think\Request;

class Chat extends Base
{

    /**
     * 创作
     */
    public function create()
    {
        if(!$this->uid) {
            $this->response(1003);
        }
        $param = $this->request->post();

        $file = isset($param['fileList']) ? $param['fileList'] : '';

        $platform = isset($param['platform']) ? $param['platform'] : 'wx';
        $assistantId = isset($param['assistant_id']) ? $param['assistant_id'] : 0;
        if (!$assistantId) {
            $this->response(2001);
        }

        $map['a.status'] = 1;
        $assistantId && $map['a.id'] = $assistantId;

        $assistant =  Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.keywords,a.template,a.coze_id,a.type')
            ->where($map)
            ->find();

        if ($assistant) {
            $assistant['keywords'] = json_decode($assistant['keywords'], true);
        }

        if (!$assistant) {
            $this->response(2003);
        }

        $input = isset($param['input']) ? $param['input'] : [];
        $prompt = isset($param['text']) ? $param['text'] : '';

        $input = json_decode($input, true);

        if (!$input) {
            $this->response(2002);
        }
        $replace = [];
        $image = '';

        if(!$prompt){
            foreach ($input as $k => $v) {
                if($v['type'] == 'list'){
                    continue;
                }

                if($v['type'] == 'file'){
                    if(!$file){
                        $this->response(9999, [], '请输入' . $v['title']);
                    }
                    continue;
                }

                if (!$v['tag']) {
                    $this->response(2004);
                }
                if ($v['require'] && !$v['val']) {
                    $this->response(9999, [], '请输入' . $v['title']);
                }
                $v['val'] = trim($v['val']);
                if (($v['type'] == 'slider')) {
                    $v['val'] = (int)$v['val'];
                    if ($v['val'] > 1000) {
                        $v['val'] = 1000;
                    }
                }

                if($v['type'] == 'image'){
                    $image = $v['val'];
                }

                $replace['[[' . $v['tag'] . ']]'] = $v['val'];
            }

            $prompt = str_replace(array_keys($replace), array_values($replace), $assistant['template']);
        }



        if (!$this->_checkPrompt($prompt)) {
            $this->response(2005);
        }

        $stream = isset($param['stream']) ? $param['stream'] : $this->channel['stream'];

        if(in_array($assistant['type'],[2,3])){
            $stream = 1;
        }
        if($file){
            $stream = 2;
        }


        $limit = $this->_limit();
        if (!$limit) {
            $this->response(2009);
        }

        if ($stream == 1) {
            try {
                $promptParam['prompt'] = $prompt;
                $promptParam['coze_id'] = $assistant['coze_id'];
                $promptParam['image'] = $image;
                $messageData = $this->_create($promptParam);

                if (($messageData['code'] != 0) || (!$messageData['data'])) {
                    $this->response(2008);
                }

                $messageData['upload_img'] = $image;
                $chatId = $this->_insertCreation($prompt, $param['input'], $messageData, $assistant, $stream, $platform);

                if (!$chatId) {
                    $this->response(2010);
                }

                $response['chat_id'] = $chatId;
                $this->_setTodayNums('day_limit');
                $this->_setLimit();

            } catch (\Exception $e) {
                $this->response(9999,[],'生成失败，服务器错误：'.$e->getMessage());
            }

        }

        if ($stream == 2) {
            if($file){
                $fileArr = json_decode($file,true);
                foreach ($fileArr as $k=>$v){
                    $path = parse_url($v, PHP_URL_PATH);
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    if(!in_array($extension,['pdf','doc','txt','docx'])){
                        $this->response(9001);
                    }
                }
            }
            $messageData['upload_img'] = $image;
            $messageData['file'] = $file;
            $chatId = $this->_insertCreation($prompt, $param['input'], $messageData, $assistant, $stream, $platform);
            if (!$chatId) {
                $this->response(2008);
            }
            $response['chat_id'] = $chatId;
        }

        $response['stream'] = $stream;
        $this->response(0, $response);

    }

    /**
     * 查看创作
     */
    public function getChat()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $param = $this->request->post();

        $chatId = isset($param['chatId']) ? $param['chatId'] : 0;
        if (!$chatId) {
            $this->response(2007);
        }

        $map['id'] = $chatId;
        $map['user_id'] = $this->uid;
        $creation = Db::name('robot_creation')->where($map)->find();
        if (!$creation) {
            $this->response(2008);
        }

        unset($map);
        $map['a.status'] = 1;
        $creation['assistant_id'] && $map['a.id'] = $creation['assistant_id'];

        $assistant =  Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.keywords,a.template,a.type,a.format')
            ->where($map)
            ->find();

        if ($assistant) {
            $assistant['keywords'] = json_decode($assistant['keywords'], true);
        }


        $data['format'] = $assistant['format'];
        $data['msg'] = $creation['msg'];

        $pattern = '/https?:\/\/[^\s]+/';
        $result = preg_replace($pattern, '', $creation['content']);

        $data['content'] = $result ? $result : $assistant['name'];
        $data['assistant'] = $assistant['name'];
        $data['type'] = $assistant['type'];
        $data['img'] = cdnurl($assistant['icon']);
        $data['source_url'] = $creation['source_url'] ? cdnurl($creation['source_url']) : '';
        $data['upload_img'] = $creation['upload_img'] ? cdnurl($creation['upload_img']) : '';
        $this->response(0, $data);
    }

    /**
     * 流式输出
     */
    public function getChatStream($worker)
    {

        $worker->onMessage = function (TcpConnection $connection, $msg) {
            $connection->lastMessageTime = time();
            parse_str(urldecode($msg), $arr);
            $token = $arr['token'];
            $chatId = $arr['chatId'];
            $type = $arr['type'];
            $uid = $this->_checkToken($token);
            if (!$uid) {
                $data = $this->_getResponseData(1003);
                $connection->send($data);
                exit;
            }

            if (!$chatId) {
                $data = $this->_getResponseData(2007);
                $connection->send($data);
                exit;
            }

            switch ($type){
                case 'creation':
                    $creationModel = new Creation();
                    break;
                default:
                    $creationModel = new Creation();
                    break;
            }

            $creation = $creationModel->getInfo($chatId, $uid);
            if($creation['upload_img']){
                $connection->send(json_encode(['code'=>0,'upload_img'=>$creation['upload_img']]));
            }
            if (!$creation) {
                $data = $this->_getResponseData(2008);
                $connection->send($data);
                exit;
            }

            $channelModel = new Channel();
            $this->channel = $channelModel->getInfo(21);
            $this->uid = $uid;

            if (!$this->channel) {
                $data = $this->_getResponseData(-2);
                $connection->send($data);
                exit;
            }

            $this->_setTodayNums('day_limit');
            $this->_setLimit();




            $promptParam['prompt'] = $creation['content'];


            $sessionId = 0;
            $assistant =  Db::name('robot_assistant')->where(['id'=>$creation['assistant_id']])->find();
            $promptParam['coze_id'] = $assistant['coze_id'];
            $promptParam['image'] = $creation['upload_img'];



            $res = $this->_create($promptParam,2,$connection,$sessionId);

            if (($res['code'] != 0) || (!$res['data'])) {
                $data = $this->_getResponseData(2008);
                $connection->send($data);
                exit;
            }

            $update['time'] = isset($res['time']) ? $res['time'] : 0;
            $update['model'] = isset($res['model']) ? $res['model'] : 0;
            $update['model_id'] = isset($res['model_id']) ? $res['model_id'] : 0;
            $update['tokens'] = isset($res['tokens']) ? $res['tokens'] : 0;
            $update['msg'] = isset($res['data']) ? $res['data'] : '';
            $update['update_time'] = time();
            $update['status'] = 1;
            $creationModel->saveCreation($chatId, $uid, $update);

            if(isset($creation['session_id'])){
                $session['title'] = mb_substr($creation['content'],0,20);
                $session['desc'] = isset($res['data']) ? mb_substr($res['data'],0,20) : '';
                $session['update_time'] = time();
                $model = new Session();
                $model->saveSession($this->uid,$creation['session_id'],$session);
            }


            $connection->close();
        };


        $worker->onWorkerStart = function ($worker) {
            Timer::add(10, function () use ($worker) {
                $time_now = time();
                foreach ($worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > 15) {
                        $connection->close();
                    }
                }
            });

        };


        $worker->onClose = function (TcpConnection $connection) {
            echo "connection closed\n";
        };


        $worker->onConnect = function (TcpConnection $connection) {
            $connection->id = rand(100000, 999999);
            echo "new connection " . "\n";
        };
    }

    /**
     * pc端会话列表
     */
    public function getSession()
    {
        if (!$this->uid) {
            $this->response(0, []);
        }
        $model = new Session();
        $session = $model->getListByUser($this->uid);
        $this->response(0, $session);
    }

    /**
     * pc端修改会话名称
     */
    public function saveSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id');
        $title = $this->request->post('title');
        $title = mb_substr(trim($title), 0, 10);
        if (!$id || !$title) {
            $this->response(2004);
        }

        $data['desc'] = $title;
        $data['updatetime'] = time();
        $model = new Session();
        $res = $model->saveSession($this->uid, $id, $data);
        if (!$res) {
            $this->response(2003);
        }

        $session['title'] = $title;
        $this->response(0, $session);
    }

    /**
     * pc端删除会话
     */
    public function deleteSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id', 0);

        $data['status'] = 0;
        $data['updatetime'] = time();
        $model = new Session();
        $res = $model->saveSession($this->uid, $id, $data);
        if (!$res) {
            $this->response(2003);
        }
        $this->response(0);
    }


    /**
     * pc端对话列表
     */
    public function getSessionInfo()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $sessionId = $this->request->get('id');
        if (!$sessionId) {
            $this->response(2001);
        }
        $model = new Creation();
        $data = $model->getList($this->uid, $sessionId);
        foreach ($data as $k => $v) {
            $question = '';
            $jsonData = json_decode($v['question'], true);
            if (!$jsonData) {
                $data[$k]['question'] = $question;
                continue;
            }
            foreach ($jsonData as $key => $value) {
                $question .= $value['title'] . ': ' . $value['val'] . "\n";
            }
            $data[$k]['question'] = $question;
        }
        $this->response(0, $data);
    }



    /**
     * 小程序生成记录
     */
    public function getCreation()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $model = new Creation();
        $data = $model->getList($this->uid);
        foreach ($data as $k => $v) {
            $assistantName = $v['assistant_name'];
            $question = '';
            $jsonData = json_decode($v['question'], true);
            if (!$jsonData) {
                $data[$k]['question'] = $assistantName;
                continue;
            }
            foreach ($jsonData as $key => $value) {
                if($v['type'] == 1 && isset($value['val']) &&!is_array($value['val'])  && $value['val']){
                    $question .= $value['title'] . ': ' . $value['val'] . "\n";
                }else{
                    $question = $assistantName."\n";
                }

            }
            $data[$k]['question'] = $question;
            $data[$k]['anwser'] = $v['anwser'] ? (mb_substr($v['anwser'], 0, 50) . "...") : '';
            $data[$k]['assistant'] = $assistantName;
        }
        $this->response(0, $data);
    }

    /**
     * pc端创建会话
     */
    public function createSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('assistant_id');
        if (!$id) {
            $this->response(2001);
        }
        $data = [];
        $sessionModel = new Session();
        $res = $sessionModel->createSession($this->uid, $id);
        if ($res['id']) {
            $model = new Assistant();
            $assistant = $model->getAssistantInfoByChannel($this->channel['id'], $id);
            $data['id'] = $res['id'];
            $data['assistant'] = $assistant['name'] ?: "";
        }
        $this->response(0, $data);
    }

    /**
     * 对话评分
     */
    public function score()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $id = $this->request->post('id');
        $score = $this->request->post('score');
        if (!$id || !$score) {
            $this->response(2001);
        }

        $data['rate'] = $score;
        $model = new Creation();
        $model->saveCreation($id, $this->uid, $data);

        $this->response(0);
    }

    public function uploadImage(){
        if (!$this->uid) {
            $this->response(1003);
        }
        $file = $this->request->file('file');

        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
            $url = cdnurl($attachment->url, true);
            $this->response(0,['url'=>$url]);
        }catch (\Exception $e){
            $this->response(-6);
        }

    }

    public function uploadFile(){
        if (!$this->uid) {
            $this->response(1003);
        }
        $file = $this->request->file('file');

        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
            $url = cdnurl($attachment->url, true);



            $add['assistant'] = $this->request->post('assistant_id');
            $add['uid'] = $this->uid;
            $add['file_path'] = $url;
            $add['createtime'] = time();


            $this->response(0,['url'=>$url]);
        }catch (\Exception $e){
            $this->response(-6);
        }

    }


}
