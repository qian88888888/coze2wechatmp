<?php

namespace app\common\model\robot;

use think\Model;


class Document extends Model
{

    protected $name = 'robot_document';

    public function getInfo($id,$uid){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $map['status'] = 1;
        $document = $this->where($map)->find();
        return $document;
    }

    public function saveDocument($id,$uid,$data){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $res = $this->where($map)->update($data);
        return $res;
    }

}
