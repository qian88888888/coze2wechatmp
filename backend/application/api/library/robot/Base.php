<?php
namespace app\api\library\robot;
use think\Cache;
use think\Log;
class Base {

    protected function _curl($url,$post=0,$param=[],$header=[],$callback=[],$timeout=120){

        $oCurl = curl_init();
        Log::record('curl-request:'.$url.var_export($param,true));
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        if($post){
            curl_setopt($oCurl, CURLOPT_POST, 1); //post提交方式
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        if ($header) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        }
        if($callback){
            curl_setopt($oCurl, CURLOPT_WRITEFUNCTION,
                $callback
            );
        }

        $re = curl_exec($oCurl);
        Log::record('curl-response:'.var_export($re,true));
        curl_close($oCurl);
        return $re;
    }

    protected function _getBaiduToken($options){
        $appId = $options['appkey'];
        $appSecret = $options['appsecret'];

        $cacheKey = 'BAIDU_ACCESS_TOKEN';
        $token =  Cache::get($cacheKey);
        if($token){
            return $token;
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$appId.'&client_secret='.$appSecret;
        $header = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $res = $this->_curl($url,1,[],$header);
        $data = json_decode($res,true);
        $token = $data['access_token'];
        Cache::set($cacheKey,$token,3600*7);
        return $token;
    }


}