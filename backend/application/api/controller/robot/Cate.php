<?php

namespace app\api\controller\robot;

use app\common\model\robot\Cate as cates;
use app\common\model\robot\Assistant;


use think\Db;

class Cate extends Base
{

    private $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Assistant();
    }

    /**
     * 分类列表
     */
    public function cateList()
    {
        $levelId = $this->request->get('level_id');
        $map['a.status'] = 1;
        $map['a.level_id'] = $levelId;
        $cates = Db::name('robot_cate')
            ->alias('a')
            ->field('a.id,a.name,1 as type')
            ->order('a.weigh desc')
            ->where($map)
            ->select();


        $cate = [
            ['id'=>0,'name' => '☆','type'=>2],
            ['id'=>0,'name' => '热门','type'=>3],
        ];
        $cates = array_merge((array)$cate,(array)$cates);
        $this->response(0, $cates);
    }

    /**
     * 助手详情
     */
    public function assistantInfo()
    {
        $id = $this->request->get('assistant_id');
        $assistantInfo = $this->model->getAssistantInfoByChannel($this->channel['id'], $id);
        $assistantInfo['stream']['show'] = (int)$this->channel['show_stream'];
        $assistantInfo['stream']['default'] = $this->channel['stream_default'] ? true : false;
        $this->response(0, $assistantInfo);
    }

    /**
     * 助手列表按分类查询
     */
    public function assistantList()
    {
        $cateId = $this->request->get('cate_id');
        $levelId = $this->request->get('level_id');
        $map['status'] = 1;
        $map['cate_id'] = $cateId;
        $map['level_id'] = $levelId;
        $assistantList = Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.appid,a.apppath,a.hot')
            ->order('weigh desc')
            ->where($map)
            ->limit(100)
            ->select();
        $this->response(0, $assistantList);
    }

    /**
     * 所有助手列表按分类分组
     */
    public function cateAssistant()
    {
        $levelId = $this->request->get('levelId',8);



        $map['a.status'] = 1;
        $map['b.status'] = 1;
        $map['b.level_id'] = $levelId;
        $res = Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,b.name cate,a.appid,a.apppath,a.cate_id,a.show_type')
            ->join('robot_cate b', 'a.cate_id = b.id')
            ->order('a.weigh desc,b.weigh desc')
            ->where($map)
            ->select();
        $data = [];
        foreach ($res as $k => $v) {
            $data[$v['cate_id']]['id'] = $v['cate_id'];
            $data[$v['cate_id']]['name'] = $v['cate'];
            $data[$v['cate_id']]['data'][] = [
                'id' => $v['id'],
                'name' => mb_strlen($v['name'])>6 ? mb_substr($v['name'],0,5).'...' : $v['name'],
                'icon' => $v['icon'],
                'desc' => $v['desc'],
                'appid' => $v['appid'] ?: '',
                'apppath' => $v['apppath'] ?: '',
                'show_type' => $v['show_type'] ?: '',
            ];

        }
        $cateAssistant = array_values($data);
        $this->response(0, $cateAssistant);
    }

    /**
     * 我的常用
     */
    public function myAssistant()
    {
        $uid = $this->uid;
        if (!$uid) {
            $this->response(0, []);
        }
        $data = $this->model->getAssistantByUser($uid);
        $this->response(0, $data);
    }


    /**
     * 收藏助手列表
     */
    public function likeCateAssistant()
    {
        $like = $this->userLikeList();
        if(!$like){
            $this->response();
        }

        $map['status'] = 1;
        $assistantList = Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.appid,a.apppath,a.hot')
            ->order('weigh desc')
            ->where($map)
            ->limit(100)
            ->select();


        $list = array_column($assistantList, null, 'id');
        $res = [];
        foreach ($like as $k=>$v){
            isset($list[$v]) && $res[] = $list[$v];
        }
        $this->response(0, $res);
    }

    public function levelList(){
        $map['a.status'] = 1;
        $cates = Db::name('robot_level')
            ->alias('a')
            ->field('a.id,a.name label,a.info')
            ->order('a.weigh desc')
            ->where($map)
            ->select();
        $cates = collection($cates)->toArray();
        foreach ($cates as $k=>$v){
            $cates[$k]['short_name'] = $v['label'];
            $cates[$k]['label'] = $v['label'] . '' . $v['info'];
        }
        $this->response(0, $cates);
    }

    /**
     * 热门助手列表
     */
    public function hotCateAssistant()
    {

        $map['a.status'] = 1;
        $map['b.status'] = 1;
        $levelId = $this->request->get('level_id');
        $map['a.level_id'] = $levelId;
        $res = Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,b.name cate,a.appid,a.apppath,a.cate_id')
            ->join('robot_cate b', 'a.cate_id = b.id')
            ->order('b.weigh desc,a.weigh desc,a.id asc')
            ->where($map)
            ->select();
        $data = [];
        foreach ($res as $k => $v) {
            $data[$v['cate_id']]['id'] = $v['cate_id'];
            $data[$v['cate_id']]['name'] = $v['cate'];
            $data[$v['cate_id']]['data'][] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'icon' => $v['icon'],
                'desc' => $v['desc'],
                'appid' => $v['appid'] ?: '',
                'apppath' => $v['apppath'] ?: '',
            ];

        }
        $cateAssistant = array_values($data);

        foreach ($cateAssistant as $k=>$v){
            $cateAssistant[$k]['data'] = array_splice($v['data'],0,4);

        }
        $this->response(0, $cateAssistant);
    }


}
