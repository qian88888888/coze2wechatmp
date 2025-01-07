<?php

namespace app\common\model\robot;

use think\Db;
use think\Model;

class Session extends Model
{
    // 表名
    protected $name = 'robot_session';

    public function getListByUser($uid,$title='')
    {
        $map['a.status'] = 1;
        $map['a.user_id'] = $uid;
        $title && $map['a.title'] = $title;
        $session = $this
            ->alias('a')
            ->field('a.id,a.title,a.desc')
            ->order('a.update_time desc')
            ->where($map)
            ->limit(20)
            ->select();

        return $session;
    }


    public function saveSession($uid, $id, $data)
    {
        $id && $map['id'] = $id;
        $map['user_id'] = $uid;
        $res = $this->where($map)->update($data);
        return $res;
    }

    public function getSessionInfo($uid,$id=0){
        $map['user_id'] = $uid;
        $map['status'] = 1;
        $id && $map['id'] = $id;
        $session = $this->where($map)->order('update_time desc')->find();
        if($session){
            $session = $session->toArray();
        }
        return $session;
    }

    public function createSession($uid,$title='')
    {


        $data['user_id'] = $uid;
        $data['status'] = 1;
        $title && $data['title'] = $title;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $this->insert($data);
        $data['id'] = $this->getLastInsID();
        return $data;
    }

}
