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

class StoryController extends Controller
{
    public function index(){
        
    }

    public function uploadPicture(){

        $userid = I('userid');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $pic_upload = C('STORY_PICTURE_UPLOAD');
        $pic_upload['subName'] = $userid.'/'.date('Y-m-d');
        $info = $Picture->upload(
            $_FILES,
            $pic_upload,
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器
//        dump($info);
        if(!$info){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'上传文件失败',
                'data'=>'',
            ));
            return false;
        }

        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
        foreach($info as $key => $value){
//            $pic[$key]['index'] = $key + 1;
//            $pic[$key]['url'] = $pathinfo.$value['path'];
            $pic[] = $pathinfo.$value['path'];
        }

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $pic,
        ));

    }

    public function addStory(){

        $userid = I('userid');
        $title = I('title');
        $content = I('content');
        $model = D('SpbcnStory');
        $result = $model->addStory($userid,$title,$content);

        if($result){
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result,
            ));
        } else {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库插入失败',
                'data'=>'',
            ));
        }
    }

    public function getStoryById($id){

        $model = D('SpbcnStory');
        $result = $model->getStoryById($id);
        if($result){
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result,
            ));
        } else {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库查询失败',
                'data'=>'',
            ));
        }

    }

    public function getStory(){
        $model = D('SpbcnStory');
        $result = $model->getStory();
        if($result){
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result,
            ));
        } else {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'数据库查询失败',
                'data'=>'',
            ));
        }

    }

    public function addReply(){
        $post_id = I('storyid');
        $content = I('content');
        $userid = I('userid');

        if (empty($post_id) || empty($content) || empty($userid)) {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '参数不能为空',
                'data' => ''
            ));
            return false;
        }

        //添加到数据库
        $model = D('SpbcnStoryReply');
        $result = $model->addReply($post_id, $content, $userid);
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
                'data' => D('SpbcnStoryReply')->where('id='.$result)->find(),
            ));
        }

    }

    public function delReply()
    {
        $id = I('id');
        $res = D('SpbcnStoryReply')->delPostReply($id);
        if (!$res) {
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
                'data' => '',
            ));
        }
    }

    public function getReply(){
        $storyId = I('storyid');

        $res = D('SpbcnStoryReply')->getReply($storyId);
        if (!$res) {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason'=>'无评论',
                'date' => date('Y-m-d'),
                'data'=>'',
            ));
        } else {
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $res
            ));
        }
    }

}