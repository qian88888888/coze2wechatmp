<?php

namespace app\common\model\robot;

use think\Model;

class Speech extends Model
{
    protected $name = 'robot_speech';

    public function createSpeech($uid,$url,$time=0)
    {
        $data['user_id'] = $uid;
        $data['url'] = $url;
        $data['time'] = $time;
        $data['create_time'] = time();
        $this->insert($data);
        $data['id'] = $this->getLastInsID();
        return $data;
    }

    public function getSpeechInfo($uid,$id=0){
        $map['user_id'] = $uid;
        $id && $map['id'] = $id;
        $speech = $this->where($map)->find();
        if($speech){
            $speech = $speech->toArray();
        }
        return $speech;
    }

}
