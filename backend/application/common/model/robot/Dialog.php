<?php

namespace app\common\model\robot;

use app\api\model\Channel;
use think\Model;


class Dialog extends Model
{

    protected $name = 'robot_dialog';


    public function getChannelIdList()
    {
        $map['status'] = 1;
        $field = ['id','name'];
        $channel = new Channel();
        $list = $channel->getList($map,$field);
        $statusList = [];
        foreach ($list as $k=>$v){
            $statusList[$v['id']] = $v['name'];
        }
        return $statusList;
    }

    public function getInfo($id,$uid){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $creation = $this->where($map)->find()->toArray();
        return $creation;
    }

    public function saveCreation($id,$uid,$data){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $res = $this->where($map)->update($data);
        return $res;
    }

    public function getList($map,$page,$pageSize){
        $count = $this->where($map)->count();
        $totalPage = ceil($count/$pageSize);

        $list = [];

        $data = $this
            ->alias('a')
            ->field('a.id,a.content question,a.msg answer,a.rate,a.speech_url,a.speech_time')
            ->order('id desc')
            ->limit(($page-1)*$pageSize,$pageSize)
            ->where($map)
            ->select();

        foreach ($data as $k=>$v){
            $data[$k]['speech_url'] = $v['speech_url'] ? cdnurl($v['speech_url']) : '';
        }

        $list['data'] = array_reverse($data);
        $list['totalPage'] = $totalPage;
        return $list;
    }

}
