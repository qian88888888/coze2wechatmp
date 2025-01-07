<?php

namespace app\api\controller\robot;




use app\common\model\robot\Document as Documents;
use think\Db;

class Document extends Base
{

    /**
     * 新增文档
     */
    public function insert()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $param = $this->request->post();

        $chatId = isset($param['id']) ? $param['id'] : 0;
        $content = isset($param['data']) ? $param['data'] : '';
        $title = isset($param['title']) ? $param['title'] : '未命名文档';
        if (!$chatId) {
            $this->response(2007);
        }

        if(!trim($content)){
            $this->response(4001);
        }

        try {
            $data['user_id'] = $this->uid;
            $data['chat_id'] = $chatId;
            $data['title'] = $title;
            $data['content'] = $content;
            $data['update_time'] = $data['create_time'] = time();
            $res = Documents::insert($data);
            if(!$res){
                $this->response(4002);
            }
            $data['id'] = Documents::getLastInsID();
        } catch (\Exception $e) {
            $this->response(9999,[],'服务器错误:'.$e->getMessage());
        }

        $this->response(0, $data);
    }

    /**
     * 文档内容详情
     */
    public function getDocumentInfo(){
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->get('id');
        if (!$id) {
            $this->response(4003);
        }
        $model = new Documents();
        $res = $model->getInfo($id,$this->uid);
        $data['title'] = $res['title'];
        $data['list'] = $res['content'];
        $this->response(0, $data);
    }

    public function update(){
        if (!$this->uid) {
            $this->response(1003);
        }

        $param = $this->request->post();

        $documentId = isset($param['id']) ? $param['id'] : 0;
        $content = isset($param['data']) ? $param['data'] : '';
        $title = isset($param['title']) ? $param['title'] : '未命名文档';

        if (!$documentId) {
            $this->response(8001);
        }

        if(!trim($content) || (mb_strlen($content)<20) || (mb_strlen($content)>3000)){
            $this->response(8001);
        }

        $data['title'] = $title;
        $data['content'] = $content;
        $data['update_time'] = time();

        $model = new Documents();
        $model->saveDocument($documentId,$this->uid,$data);
        $this->response();
    }

    public function list(){
        if (!$this->uid) {
            $this->response(1003);
        }


        $map['user_id'] = $this->uid;
        $map['status'] = 1;
        $list = Db::name('robot_document')->where($map)->limit(50)->order('update_time desc')->select();

        $data = [];

        foreach ($list as $k=>$v){
            $data[$k]['id'] = $v['id'];
            $data[$k]['title'] = $v['title'];
            $data[$k]['date'] = $this->_getDocumentDate($v['update_time']);
            $data[$k]['nums'] = mb_strlen($v['content']);
        }

        $this->response(0, $data);
    }

    private function _getDocumentDate($targetTime)
    {
        if(!$targetTime){
            return '10天前';
        }

        $todayLast   = strtotime(date('Y-m-d 23:59:59'));
        $agoTimeTrue = time() - $targetTime;
        $agoTime     = $todayLast - $targetTime;
        $agoDay      = floor($agoTime / 86400);

        if ($agoTimeTrue < 60) {
            $result = '刚刚';
        } elseif ($agoTimeTrue < 3600) {
            $result = (ceil($agoTimeTrue / 60)) . '分钟前';
        } elseif ($agoTimeTrue < 3600 * 3) {
            $result = (ceil($agoTimeTrue / 3600)) . '小时前';
        } elseif ($agoDay == 0) {
            $result = '今天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 1) {
            $result = '昨天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 2) {
            $result = '前天 ' . date('H:i', $targetTime);
        } elseif ($agoDay > 2 && $agoDay <= 10) {
            $result = $agoDay . '天前 ' . date('H:i', $targetTime);
        } else {
            $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
            $result = date($format, $targetTime);
        }
        return $result;

    }

    public function delete(){
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id', 0);


        $data['status'] = 0;
        $data['update_time'] = time();
        $res = DB::name('robot_document')->where(['id'=>$id,'user_id'=>$this->uid])->update($data);
        if (!$res) {
            $this->response(2001);
        }
        $this->response();
    }





}