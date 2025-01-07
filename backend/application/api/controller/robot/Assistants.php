<?php

namespace app\api\controller\robot;

use app\common\model\robot\Assistant;


use think\Db;

class Assistants extends Base
{

    /**
     * 助手搜索
     */
    public function search()
    {
        $model = new Assistant();
        $title = $this->request->get('title');
        if(!$title){
            $this->response(0, []);
        }
        $assistantList = $model->getAssistantListByChannel( 0,$title);
        $this->response(0, $assistantList);
    }

    /**
     * 热门搜索
     */
    public function hotSearch()
    {
        $model = new Assistant();
        $assistantList = $model->getAssistantListByChannel( 0,'',true);
        $this->response(0, $assistantList);
    }

    /**
     * 助手详情
     */
    public function info()
    {
        $id = $this->request->get('assistant_id');


        $map['a.status'] = 1;
        $id && $map['a.id'] = $id;

        $assistantInfo = Db::name('robot_assistant')
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.keywords,a.template,a.type')
            ->order('a.weigh desc')
            ->where($map)
            ->find();

        if ($assistantInfo) {
            $assistantInfo['keywords'] = json_decode($assistantInfo['keywords'], true);
        }

        if(!$assistantInfo){
            $this->response(3001);
        }

        $like = $this->userLikeList();
        $assistantInfo['like'] = in_array($assistantInfo['id'],(array)$like) ? 1 : 0;

        $this->response(0, $assistantInfo);
    }

    /**
     * 收藏助手
     */
    public function like(){
        if(!$this->uid){
            $this->response(1003);
        }

        $id = $this->request->get('assistant_id');
        $type = $this->request->get('type');

        if(!$id){
            $this->response(3001);
        }


        $likeList = $this->userLikeList();

        if(!$likeList){
            $likeList = [];
        }

        $likeList = array_diff($likeList, [$id]);

        if($type == 0){
            $this->userLikeList($likeList);

            $this->response();
        }

        array_unshift($likeList, $id);
        $likeList = array_unique($likeList);
        $likeList = array_splice($likeList,0,20);

        $this->userLikeList($likeList);
        $this->response();
    }

}
