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

class AvatarController extends Controller
{
    public function index(){
        $userid = I('userid');
        query_user($userid);
    }

    //上传头像
    public function addAvatar()
    {
        $userid = I('userid');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $pic_upload = C('AVATAR_PICTURE_UPLOAD');
        $pic_upload['subName'] = $userid.'/'.date('Y-m-d');
        $info = $Picture->upload(
            $_FILES,
            $pic_upload,
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器
        if(!$info){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'上传文件失败',
                'data'=>'',
            ));
            return false;
        }
        //$path = $info[0]['path'];

        foreach($info as $key => $value){
            $path = $value['path'];
        }

        $avatarModel = D('Avatar');
        $avatarModel -> saveAvatar($userid,$path);
        //TODO:获取头像信息
        $this->getAvatar($userid);
    }


    //查询头像
    public function getAvatar($userid)
    {
        $userid = I('userid');
        $avatarModel = D('Avatar');
        $avatarPath = $avatarModel->getAvatar($userid);

        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;

        if($avatarPath){
            $image->open('.'.$avatarPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
            $temp = pathinfo($avatarPath);
            $thumbPath1 = $temp['dirname'].'/'.$temp['filename'].'_128_128.jpg';
            if(!file_exists($thumbPath1))
                $image->thumb(128, 128)->save('.'.$thumbPath1);
            $thumbPath2 = $temp['dirname'].'/'.$temp['filename'].'_64_64.jpg';
            if(!file_exists($thumbPath2))
                $image->thumb(64, 64)->save('.'.$thumbPath2);
            $thumbPath3 = $temp['dirname'].'/'.$temp['filename'].'_32_32.jpg';
            if(!file_exists($thumbPath3))
                $image->thumb(32, 32)->save('.'.$thumbPath3);
            $result['avataBig'] = $pathinfo.$thumbPath1;
            $result['avataMid'] = $pathinfo.$thumbPath2;
            $result['avataSma'] = $pathinfo.$thumbPath3;
        } else {
            $result['avataBig'] = $pathinfo.'/Addons/Avatar/default_128_128.jpg';
            $result['avataMid'] = $pathinfo.'/Addons/Avatar/default_64_64.jpg';
            $result['avataSma'] = $pathinfo.'/Addons/Avatar/default_32_32.jpg';
        }
        $result['userid'] = $userid;
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result
        ));
    }

    //更新头像
    public function updateAvatar()
    {
        $userid = I('userid');
        $this->deleteAvatarInfo($userid);

        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $pic_upload = C('AVATAR_PICTURE_UPLOAD');
        $pic_upload['subName'] = $userid.'/'.date('Y-m-d');
        $info = $Picture->upload(
            $_FILES,
            $pic_upload,
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器
        if(!$info){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'上传文件失败',
                'data'=>'',
            ));
            return false;
        }
        $path = $info[0]['path'];
        $avatarModel = D('Avatar');
        $avatarModel -> saveAvatar($userid,$path);
        //TODO:获取头像信息
        $this->getAvatar($userid);

    }

    //删除头像
    public function deleteAvatar($userid){
        $userid = I('userid');
        $avatarModel = D('Avatar');
        $avatarPath = $avatarModel->getAvatar($userid);
        if($avatarPath){
            $this -> delDir('./Uploads/spbcn/avatar/'.$userid.'/');
            $avatarModel->removeAvatar($userid);
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d')
            ));
        }else{
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason' => '无头像',
                'date' => date('Y-m-d')
            ));
        }
    }

    public function delDir( $dir )
    {
        //先删除目录下的所有文件：
        $dh = opendir( $dir );
        while ( $file = readdir( $dh ) ) {
            if ( $file != "." && $file != ".." ) {
                $fullpath = $dir . "/" . $file;
                if ( !is_dir( $fullpath ) ) {
                    unlink( $fullpath );
                } else {
                    $this-> delDir( $fullpath );
                }
            }
        }
        closedir( $dh );
        //删除当前文件夹：
        return rmdir( $dir );
    }

    //查询头像，用于share等模块返回头像信息
    public function getAvtarInfo($userid){
        $userid = $userid;
        $avatarModel = D('Avatar');
        $avatarPath = $avatarModel->getAvatar($userid);

        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;

        if($avatarPath){
            $image->open('.'.$avatarPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
            $temp = pathinfo($avatarPath);
            $thumbPath1 = $temp['dirname'].'/'.$temp['filename'].'_128_128.jpg';
            if(!file_exists($thumbPath1))
                $image->thumb(128, 128)->save('.'.$thumbPath1);
            $thumbPath2 = $temp['dirname'].'/'.$temp['filename'].'_64_64.jpg';
            if(!file_exists($thumbPath2))
                $image->thumb(64, 64)->save('.'.$thumbPath2);
            $thumbPath3 = $temp['dirname'].'/'.$temp['filename'].'_32_32.jpg';
            if(!file_exists($thumbPath3))
                $image->thumb(32, 32)->save('.'.$thumbPath3);
            $result['avataBig'] = $pathinfo.$thumbPath1;
            $result['avataMid'] = $pathinfo.$thumbPath2;
            $result['avataSma'] = $pathinfo.$thumbPath3;
        } else {
            $result['avataBig'] = $pathinfo.'/Addons/Avatar/default_128_128.jpg';
            $result['avataMid'] = $pathinfo.'/Addons/Avatar/default_64_64.jpg';
            $result['avataSma'] = $pathinfo.'/Addons/Avatar/default_32_32.jpg';
        }

        return $result;
    }

    //删除头像
    public function deleteAvatarInfo($userid){
        $userid = I('userid');
        $avatarModel = D('Avatar');
        $avatarPath = $avatarModel->getAvatar($userid);
        if($avatarPath){
            $this -> delDir('./Uploads/spbcn/avatar/'.$userid.'/');
            $avatarModel->removeAvatar($userid);
        }else{
            return false;
        }
    }



}

