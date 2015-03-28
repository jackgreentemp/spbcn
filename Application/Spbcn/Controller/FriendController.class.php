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

class FriendController extends Controller
{
    public function index(){

    }

    public function addFriend($uid, $fuid, $note)
    {
        if (empty($uid) || empty($fuid) || empty($note)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $data['uid'] = $uid;
        $data['fuid'] = $fuid;

        if(M('SpbcnFriend')-> where($data)->find()){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'已关注该用户',
                'data'=>'',
            ));
            return false;
        }

        $data['note'] = $note;
        $data['create_time'] = time();
        $data['status'] = 0;
        $model = M('SpbcnFriend');
        $result = $model -> add($data);

        if (!$result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            //发送消息 好友请求消息type为4
            $username = M('Member')->where('uid='.$uid)->getField('nickname');
            D('SpbcnMessage') -> sendMessageWithoutCheckSelf($fuid,$username.'关注了您','','',$uid,4);
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => ''
            ));
        }
    }

    public function deleteFriend($uid, $fuid)
    {
        if (empty($uid) || empty($fuid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $data['uid'] = $uid;
        $data['fuid'] = $fuid;
        $model = M('SpbcnFriend');

        $result = $model -> where($data)->delete();

        if (!$result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => ''
            ));
        }
    }

    public function getFirendRequirement($uid){
        if (empty($uid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $data['fuid'] = $uid;
        $model = M('SpbcnFriend');

        $result = $model -> where($data) -> order('create_time desc')->select();

        if (!$result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result
            ));
        }

    }

    public function confirmRequirement($id, $uid){
        if (empty($id) || empty($uid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = M('SpbcnFriend');
        $data['id'] = $id;

        $idRaw = $model -> where($data) ->getField('fuid');

        if($uid!=$idRaw){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '用户id不匹配',
                'data' => ''
            ));
            return false;
        }

        $fuid = $model -> where($data) ->getField('uid');

        //建立反向好友关系
        //TODO 反向好友关系是否重复验证
        $friendData['uid'] = $uid;
        $friendData['fuid'] = $fuid;
        $friendData['create_time'] = time();
        $friendData['note'] = '';
        $friendData['status'] = 1;

        $result1 = $model -> add($friendData);

        $result = $model->where($id)->setField('status',1);

        if (!$result1 || !result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => ''
            ));
        }

    }

    public function getAllFirends($uid){

    }

    //获取所有关注自己的用户信息
    public function getAllFollowers($uid){
        if (empty($uid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

//        $model = M('SpbcnFriend');
        $data['fuid'] = $uid;

//        $followerUidArray = $model->where($data)->order('uid')->getField('uid',true);
        $followerUidArray = D('SpbcnFriend')->getAllFollowerUids($uid);

        //TODO 将获取用户信息的功能写成公共方法
        $avatarController = new AvatarController();

        foreach($followerUidArray as $val){
            $avatarInfo = $avatarController->getAvtarInfo($val);
            $subResult['userid'] = $val;
            $subResult['username'] = M('Member')->where('uid='.$val)->getField('nickname');
            $subResult['useravatar']= $avatarInfo['avataSma'];
            $result[]=$subResult;
        }

        if (!$result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result
            ));
        }

    }

    //获取关注列表
    public function getFollowList($uid){
        if (empty($uid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $data['uid'] = $uid;

        $followerUidArray = D('SpbcnFriend')->getAllFollowedUids($uid);

        //TODO 将获取用户信息的功能写成公共方法
        $avatarController = new AvatarController();

        foreach($followerUidArray as $val){
            $avatarInfo = $avatarController->getAvtarInfo($val);
            $subResult['userid'] = $val;
            $subResult['username'] = M('Member')->where('uid='.$val)->getField('nickname');
            $subResult['useravatar']= $avatarInfo['avataSma'];
            $result[]=$subResult;
        }

        if (!$result) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库操作失败',
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result
            ));
        }

    }


}