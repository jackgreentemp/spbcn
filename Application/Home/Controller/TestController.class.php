<?php

namespace Home\Controller;

use User\Api\UserApi;

require_once APP_PATH . 'User/Conf/config.php';
class TestController extends HomeController{


    public function registerTest()
    {
//        $url = U('User/spbcnRegister');
//        $data = array(
//            'username' => '',
//            'nickname' => '',
//            '$password' =>
//        );
//        $result = $this->sendFile($data, $emrUrl);
        $this -> display('registerTest');
    }

    public function loginTest(){
        $this -> display('loginTest');
    }

    public function sendFile($post_data, $emrUrl){

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
//	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
//	curl_setopt($ch, CURLOPT_PROXY, 'proxy.cmcc:8080');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($uid));

        curl_setopt($ch, CURLOPT_HEADER, false);
//启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_URL, $emrUrl);
        $temp= curl_exec($ch);
        if($temp){
            print_r($temp);
            return $temp;
        }else{
            $info = curl_error($ch);
//    echo $info;
            print_r($info);
            return $info;
        }
        curl_close($ch);
    }

}