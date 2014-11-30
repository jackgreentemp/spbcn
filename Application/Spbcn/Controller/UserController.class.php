<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM9:14
 */

namespace Spbcn\Controller;

use Think\Controller;
use Think\Hook;
use Think\Exception;
use Common\Exception\ApiException;
use User\Api\UserApi;

class UserController extends Controller
{
    public function index(){
        $userid = I('userid');
        query_user($userid);
    }

    public function weiboSyncLogin()
    {
        $accessToken = I('accesstoken');
        $username = I('username');
        $usid = I('usid');
        $type = 'SINA';

        $userInfo['type'] = $type;
        $userInfo['name'] = $username;
        $userInfo['nick'] = $username;
        $userInfo['head'] = '';
        $userInfo['sex'] = 0;

        if ($info1 = D('sync_login')->where("`type_uid`='" . $usid . "' AND type='" . $type . "'")->find()) {
            $user = D('UcenterMember')->where("id=" . $info1 ['uid'])->find();
            if (empty ($user)) {
                D('sync_login')->where("type_uid=" . $usid . " AND type='" . $type . "'")->delete();
                //已经绑定过，执行登录操作，设置token
            } else {
                $uid = $info1 ['uid'];
            }
        } else {
            $Api = new UserApi();
            //usercenter表新增数据
            $uid = $Api->addSyncData();
            //member表新增数据
            D('Home/Member')->addSyncData($uid, $userInfo);

            // 记录数据到sync_login表中
            $this->addSyncLoginData($uid, $accessToken, $usid, $type, $usid);
        }

        $this->loginWithoutpwd($uid,$username);
    }

    public function weixinSyncLogin(){
        $accessToken = I('accesstoken');
        $username = I('username');
        $usid = I('usid');
        $type = 'QQ';

        $userInfo['type'] = $type;
        $userInfo['name'] = $username;
        $userInfo['nick'] = $username;
        $userInfo['head'] = '';
        $userInfo['sex'] = 0;

        if ($info1 = D('sync_login')->where("`type_uid`='" . $usid . "' AND type='" . $type . "'")->find()) {
            $user = D('UcenterMember')->where("id=" . $info1 ['uid'])->find();
            if (empty ($user)) {
                D('sync_login')->where("type_uid=" . $usid . " AND type='" . $type . "'")->delete();
                //已经绑定过，执行登录操作，设置token
            } else {
                $uid = $info1 ['uid'];
            }
        } else {
            $Api = new UserApi();
            //usercenter表新增数据
            $uid = $Api->addSyncData();
            //member表新增数据
            D('Home/Member')->addSyncData($uid, $userInfo);

            // 记录数据到sync_login表中
            $this->addSyncLoginData($uid, $accessToken, $usid, $type, $usid);
        }

        $this->loginWithoutpwd($uid,$username);

    }

    /**
     * 利用uid登录
     * @param $uid
     * autor:xjw129xjt
     */
    protected function loginWithoutpwd($uid, $username)
    {
        if (0 < $uid) { //UC登录成功
            /* 登录用户 */
            $Member = D('Home/Member');
            if ($Member->login($uid, false)) { //登录用户
                //TODO:跳转到登录前页面
                $user = query_user(array('uid', 'username','avatar64'));
                $user['nickname'] = $username;
                $user['avatar'] = 'http://'.$_SERVER['SERVER_NAME'].$user['avatar64'];
                echo json_encode(array(
                    'status'=> 'SUCCESS',
                    'reason'=> '',
                    'data'=>$user
                ));
            } else {
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=> $Member->getError(),
                    'data'=>'',
                ));
            }
        }
    }

    /**
     * 增加sync_login表中数据
     * @param $uid
     * @param $token
     * @param $openID
     * @param $type
     * @param $oauth_token_secret
     * @return mixed
     * autor:xjw129xjt
     */
    protected function addSyncLoginData($uid, $token, $openID, $type, $oauth_token_secret)
    {
        $data['uid'] = $uid;
        $data['type_uid'] = $openID;
        $data['oauth_token'] = $token;
        $data['oauth_token_secret'] = $oauth_token_secret;
        $data['type'] = $type;
        $res = D('sync_login')->add($data);
        return $res;
    }

}