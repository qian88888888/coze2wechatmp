<?php

namespace app\api\controller\robot;

use app\common\model\robot\Banner;


class Index extends Base
{
    /**
     * 小程序轮播图列表
     */
    public function getBanner()
    {
        $model = new Banner();
        $map['status'] = 1;
        $banner = $model->getList($map);
        $data['swiper'] = $banner;
        $this->response(0, $data);
    }



    /**
     * 获取应用信息
     */
    public function getChannelInfo()
    {
        $data['name'] = $this->channel['name'];
        $data['desc'] = $this->channel['desc'];
        $data['stream'] = (int)$this->channel['stream'];
        $data['showStream'] = (int)$this->channel['show_stream'] ? true : false;
        $this->response(0, $data);
    }


}
