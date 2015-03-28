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

class MessageController extends Controller
{
    public function index(){

    }

    //获取所有消息
    //返回：按照时间倒序返回
    public function getAllMessage($userId){
        $userId = I('userId');

        if (empty($userId)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = D('SpbcnMessage');
        $result = $model->getAllMessage($userId);
        //TODO数据库操作失败校验
//        if ($result==null) {
//            echo json_encode(array(
//                'status'=> 'FAILURE',
//                'reason'=>'数据库操作失败',
//                'data'=>'',
//            ));
//        } else {
//        }
        if($result == null){
            $result = '';
        }
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result
        ));
    }

    //获取所有未读消息
    //返回：按照时间倒序返回
    public function getHaventReadMeassage($userId){

        $userId = I('userId');

        if (empty($userId)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = D('SpbcnMessage');
        $result = $model->getHaventReadMeassage($userId);

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

    //发送私信消息
    public function sendMessage($fromUid, $toUid, $content)
    {
        $fromUid = I('fromUid');
        $toUid = I('toUid');
        $content = I('content');
        $type = 3;

        if (empty($fromUid) || empty($toUid) || empty($content)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }


        //添加到数据库
        $model = D('SpbcnMessage');
        $result = $model->sendMessageWithoutCheckSelf($toUid, $content, '', '', $fromUid, $type);

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
                'data' => $model->getAllMessage($toUid)
            ));
        }

    }

    //将所有消息设置为已读
    public function setAllReaded($userId){

        $userId = I('userId');

        if (empty($userId)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = D('SpbcnMessage');
        $result = $model->setAllReaded($userId);

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result
        ));
    }

    //将某一条消息设置为已读
    public function readMessage($messageId)
    {
        $messageId = I('messageId');

        if (empty($messageId)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = D('SpbcnMessage');
        $result = $model->readMessage($messageId);

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

    //获取发出的所有消息
    //返回：按照时间倒序返回
    public function getAllMessageSended($userId){
        $userId = I('userId');

        if (empty($userId)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        $model = D('SpbcnMessage');
        $result = $model->getAllMessageSended($userId);
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