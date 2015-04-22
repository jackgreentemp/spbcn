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

class ShareController extends Controller
{
    public function index(){
        $userid = I('userid');
        query_user($userid);
    }

    //添加share，不带##识别活动
    public function addShareItem()
    {
        $userid = I('userid');
        $nickname = query_user('username',$userid);
        $content = I('content');
        $location = I('location');
        $createtime = I('createtime');
        $longitude = I('longitude');//经度
        $latitude = I('latitude');//纬度

        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $pic_upload = C('PICTURE_UPLOAD');
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

        foreach($info as $key => $value){
            $pic[$key]['id'] = $value['id'];
//            $pic[$key]['pichurl'] = $value['path'];//大图
//            $pic[$key]['picurl'] = mGetThumbImageById($value['id'],100,100);
        }
//        dump($pic);
        $data = array(
            'userid' => $userid,
            'pic' => serialize($pic),
            'nickname' => $nickname,
            'content' => $content,
            'location' => $location,
            'createtime' => $createtime,
            'create_time' => NOW_TIME,
            'longitude' => $longitude,
            'latitude' => $latitude
        );
        $share = M('Spbcn_share');
        $result = $share->create($data);
        if(!$result){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'写入数据库失败',
                'data'=>'',
            ));
            return false;
        }
        $shareItem_id = $share->add();
        //发布消息

//        dump($share->where('id='.$shareItem_id)->find());
        $shareData = $this->getShareItemById($shareItem_id);
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $shareData,
        ));
    }

    //添加share，带##识别活动
    public function addShareItemWithActivity()
    {
        $userid = I('userid');
        $nickname = query_user('username',$userid);
        $content = I('content');
        $location = I('location');
        $createtime = I('createtime');
        $longitude = I('longitude');
        $latitude = I('latitude');

        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $pic_upload = C('PICTURE_UPLOAD');
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

        foreach($info as $key => $value){
            $pic[$key]['id'] = $value['id'];
//            $pic[$key]['pichurl'] = $value['path'];//大图
//            $pic[$key]['picurl'] = mGetThumbImageById($value['id'],100,100);
        }
//        dump($pic);
        $data = array(
            'userid' => $userid,
            'pic' => serialize($pic),
            'nickname' => $nickname,
            'content' => $content,
            'location' => $location,
            'createtime' => $createtime,
            'create_time' => NOW_TIME,
            'longitude' => $longitude,
            'latitude' => $latitude
        );
        $share = M('Spbcn_share');
        $result = $share->create($data);
        if(!$result){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'写入数据库失败',
                'data'=>'',
            ));
            return false;
        }
        $shareItem_id = $share->add();

        //处理content
        $startIndex = strpos($content,'#');
        $endIndex = strrpos($content,'#');
        if($startIndex!==false && $endIndex!==false){
            $activityContent = substr($content,$startIndex+1,$endIndex-$startIndex-1);
            if(!empty($activityContent)){
                $activityDetailModel = M('Spbcn_activitydetail');
                $map['content'] = $activityContent;
                $activityInfo = $activityDetailModel -> where($map) -> find();
                if($activityInfo){
                    //活动已经存在
                    $activityListModel = M('Spbcn_activitylist');
                    $activityListData = array(
                        'shareid' => $shareItem_id,
                        'activityid' => $activityInfo['id'],
                        'create_time' => NOW_TIME
                    );
                    $activityListModel->add($activityListData);
                } else {
                    //活动不存在，新建
                    $activityDetailData = array(
                        'content' => $activityContent,
                        'create_time' => NOW_TIME
                    );
                    $activityDetailId = $activityDetailModel->add($activityDetailData);
                    $activityListModel = M('Spbcn_activitylist');
                    $activityListData = array(
                        'shareid' => $shareItem_id,
                        'activityid' => $activityDetailId,
                        'create_time' => NOW_TIME
                    );
                    $activityListModel->add($activityListData);
                }

            }
        }

//        dump($share->where('id='.$shareItem_id)->find());
        $shareData = $this->getShareItemById($shareItem_id);
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $shareData,
        ));
    }

    public function getShareItemById($shareItem_id){
        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
        $share = M('Spbcn_share');
        $shareData = $share->where('id='.$shareItem_id)->find();
        if(!$shareData){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'查询失败',
                'data'=>'',
            ));
            return false;
        }
        $shareData['pic'] = unserialize($shareData['pic']);



        foreach($shareData['pic'] as $key => $value){
            $picpath = M('Picture') -> where('id='.$value['id']) -> field('path') -> find();
            $picdata[$key]['index'] = $key+1;
            $picdata[$key]['pichurl'] = $pathinfo.$picpath['path'];//大图
//            $picdata[$key]['picurl'] = mGetThumbImageById($value['id'],100,100);
            $image->open('.'.$picpath['path']);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
            $temp = pathinfo($picpath['path']);
            $thumbPath = $temp['dirname'].'/'.$temp['filename'].'_thumb.jpg';
            $image->thumb(300, 600)->save('.'.$thumbPath);
            $picdata[$key]['picurl'] = $pathinfo.$thumbPath;
            $picdata[$key]['width'] = $image->width();
            $picdata[$key]['height'] = $image->height();
        }
//        dump($picdata);
        $val = $shareData;
        $val['pic'] = $picdata;
        $val['preview'] = mb_substr($val['content'],0,20,'utf-8');
        //TODO:添加fanlist
        $supportController = new SupportController();
        $val['fanlist'] = $supportController->getShareSupporterInfo($shareItem_id);
        return $val;
    }

    public function getShareItem(){
        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
        $share = M('Spbcn_share');

        //wb 2014-11-14
        $userid = I('userid');
        $support['appname'] = 'Spbcn';;
        $support['table'] = 'share';
        $support['uid'] = $userid;

        //分页
        $start = intval(I('startIndex'));
        $length = intval(I('length'));

        if($start>=0 && $length > 0){
            $shareData = $share -> order('id desc') -> limit($start, $length) -> select();
        } else {
            $shareData = $share -> order('id desc') -> select();
        }

        if(!$shareData){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'查询结果为空',
                'data'=>'',
            ));
            return false;
        }

        $supportController = new SupportController();

        $avatarController = new AvatarController();

        foreach($shareData as $key0 => $value0){
            $value0['pic'] = unserialize($value0['pic']);

            //wb 2014-11-14
            //查询该share是否点过赞
            $support['row'] = $value0['id'];
            if (D('Support')->where($support)->count()) {
                $value0['supported'] = 1;
            } else{
                $value0['supported'] = 0;
            }

            $picdata = array();

            foreach($value0['pic'] as $key => $value){
                $picpath = M('Picture') -> where('id='.$value['id']) -> field('path') -> find();
                $picdata[$key]['index'] = $key+1;
                $picdata[$key]['pichurl'] = $pathinfo.$picpath['path'];//大图
                $temp = pathinfo($picpath['path']);
                $thumbPath = $temp['dirname'].'/'.$temp['filename'].'_thumb.jpg';
                $image->open('.'.$thumbPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $picdata[$key]['picurl'] = $pathinfo.$thumbPath;
                $picdata[$key]['width'] = $image->width();
                $picdata[$key]['height'] = $image->height();
            }
            $val = $value0;
            $val['pic'] = $picdata;
            $val['preview'] = mb_substr($val['content'],0,20,'utf-8');
            //TODO:添加fanlist

            $val['fanlist'] = $supportController->getShareSupporterInfo($value0['id']);

            $avatarInfo = $avatarController->getAvtarInfo($val['userid']);
            $val['useravatar'] = $avatarInfo['avataMid'];

            $result[] = $val;
        }
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));

    }

    public function getShareItemBySupportNum(){
        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
        $share = M('Spbcn_share');

        //分页
        $start = intval(I('startIndex'));
        $length = intval(I('length'));

        if($start>=0 && $length > 0){
            $shareData = $share -> order('upnum desc') -> limit($start, $length) -> select();
        } else {
            $shareData = $share -> order('upnum desc') -> select();
        }

        //wb 2014-11-14
        $userid = I('userid');
        $support['appname'] = 'Spbcn';;
        $support['table'] = 'share';
        $support['uid'] = $userid;

        if(!$shareData){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'查询结果为空',
                'data'=>'',
            ));
            return false;
        }

        $supportController = new SupportController();

        $avatarController = new AvatarController();

        foreach($shareData as $key0 => $value0){
            $value0['pic'] = unserialize($value0['pic']);

            //wb 2014-11-14
            //查询该share是否点过赞
            $support['row'] = $value0['id'];
            if (D('Support')->where($support)->count()) {
                $value0['supported'] = 1;
            } else{
                $value0['supported'] = 0;
            }

            $picdata = array();

            foreach($value0['pic'] as $key => $value){
                $picpath = M('Picture') -> where('id='.$value['id']) -> field('path') -> find();
                $picdata[$key]['index'] = $key+1;
                $picdata[$key]['pichurl'] = $pathinfo.$picpath['path'];//大图
                $temp = pathinfo($picpath['path']);
                $thumbPath = $temp['dirname'].'/'.$temp['filename'].'_thumb.jpg';
                $image->open('.'.$thumbPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $picdata[$key]['picurl'] = $pathinfo.$thumbPath;
                $picdata[$key]['width'] = $image->width();
                $picdata[$key]['height'] = $image->height();
            }
            $val = $value0;
            $val['pic'] = $picdata;
            $val['preview'] = mb_substr($val['content'],0,20,'utf-8');
            //TODO:添加fanlist

            $val['fanlist'] = $supportController->getShareSupporterInfo($value0['id']);

            $avatarInfo = $avatarController->getAvtarInfo($val['userid']);
            $val['useravatar'] = $avatarInfo['avataMid'];

            $result[] = $val;
        }
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));

    }

    //获取活动列表
    public function getShareItemByActivityName(){
        $activityName = I('activityname');

        //wb 2014-11-14
        $userid = I('userid');
        $support['appname'] = 'Spbcn';;
        $support['table'] = 'share';
        $support['uid'] = $userid;

        //分页
        $start = intval(I('startIndex'));
        $length = intval(I('length'));

        if($activityName){
            $activityDetailModel = M('Spbcn_activitydetail');
            $map['content'] = $activityName;
            $activityId = $activityDetailModel -> where($map) -> getField('id');

            $activityListModel = M('Spbcn_activitylist');
            $map2['activityid'] = $activityId;
            $shareIds = $activityListModel -> where($map2) -> field('shareid') -> select();
            foreach($shareIds as $keys => $vals){
                $shareIdTemp[] = $vals['shareid'];
            }

            $map1['id'] = array('in',$shareIdTemp);
            $image = new \Think\Image();
            $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
            $share = M('Spbcn_share');
//            $shareData = $share -> where($map1) -> select();

            if($start>=0 && $length > 0){
                $shareData = $share -> where($map1) -> limit($start, $length) -> select();
            } else {
                $shareData = $share -> where($map1) -> select();
            }

            if(!$shareData){
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'查询结果为空',
                    'data'=>'',
                ));
                return false;
            }

            $supportController = new SupportController();
            $avatarController = new AvatarController();

            foreach($shareData as $key0 => $value0){
                $value0['pic'] = unserialize($value0['pic']);

                //wb 2014-11-14
                $support['row'] = $value0['id'];
                if (D('Support')->where($support)->count()) {
                    $value0['supported'] = 1;
                } else{
                    $value0['supported'] = 0;
                }

                $picdata = array();

                foreach($value0['pic'] as $key => $value){
                    $picpath = M('Picture') -> where('id='.$value['id']) -> field('path') -> find();
                    $picdata[$key]['index'] = $key+1;
                    $picdata[$key]['pichurl'] = $pathinfo.$picpath['path'];//大图
                    $temp = pathinfo($picpath['path']);
                    $thumbPath = $temp['dirname'].'/'.$temp['filename'].'_thumb.jpg';
                    $image->open('.'.$thumbPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                    $picdata[$key]['picurl'] = $pathinfo.$thumbPath;
                    $picdata[$key]['width'] = $image->width();
                    $picdata[$key]['height'] = $image->height();
                }
                $val = $value0;
                $val['pic'] = $picdata;
                $val['preview'] = mb_substr($val['content'],0,20,'utf-8');
                //TODO:添加fanlist
                $val['fanlist'] = $supportController->getShareSupporterInfo($value0['id']);

                $avatarInfo = $avatarController->getAvtarInfo($val['userid']);
                $val['useravatar'] = $avatarInfo['avataMid'];

                $result[] = $val;
            }
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'date' => date('Y-m-d'),
                'data' => $result,
            ));
        }
    }

    //查询附近 根据距离查询share
    /*
     * 输入：
     * userid 用户id
     * longitude 经度
     * latitude 纬度
     * */
    public function getShareItemByDIstence()
    {
        $image = new \Think\Image();
        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
        $share = M('Spbcn_share');
        $lon = I('longitude');
        $lat = I('latitude');


        //wb 2014-11-14
        $userid = I('userid');

        $support['appname'] = 'Spbcn';;
        $support['table'] = 'share';
        $support['uid'] = $userid;

        //分页
        $start = intval(I('startIndex'));
        $length = intval(I('length'));

        if($start>=0 && $length > 0){
            $shareData = $share -> where('latitude > '.$lat.'-1 and latitude < '.$lat.'+1 and longitude > '.$lon.'-1 and longitude < '.$lon.'+1') -> order('ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lon.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380') -> limit($start, $length) -> select();
        } else {
            $shareData = $share -> where('latitude > '.$lat.'-1 and latitude < '.$lat.'+1 and longitude > '.$lon.'-1 and longitude < '.$lon.'+1') -> order('ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lon.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380') -> select();
        }

        //网络上的查询语句
        //$sql='select * from users_location where latitude > '.$lat.'-1 and latitude < '.$lat.'+1 and longitude > '.$lon.'-1 and longitude < '.$lon.'+1 order by ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lon.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 asc limit 10';

        //thinkphp的查询语句 按照距离排序
        //http://blog.csdn.net/hustpzb/article/details/7688993
//        $shareData = $share -> where('latitude > '.$lat.'-1 and latitude < '.$lat.'+1 and longitude > '.$lon.'-1 and longitude < '.$lon.'+1') -> order('ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lon.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380') -> select();

//        if(!$shareData){
//            echo json_encode(array(
//                'status'=> 'FAILURE',
//                'reason'=>'查询失败',
//                'data'=>'',
//            ));
//            return false;
//        }

        $supportController = new SupportController();

        $avatarController = new AvatarController();

        foreach($shareData as $key0 => $value0){
            $value0['pic'] = unserialize($value0['pic']);

            //wb 2014-11-14
            //查询该share是否点过赞
            $support['row'] = $value0['id'];
            if (D('Support')->where($support)->count()) {
                $value0['supported'] = 1;
            } else{
                $value0['supported'] = 0;
            }

            $picdata = array();

            foreach($value0['pic'] as $key => $value){
                $picpath = M('Picture') -> where('id='.$value['id']) -> field('path') -> find();
                $picdata[$key]['index'] = $key+1;
                $picdata[$key]['pichurl'] = $pathinfo.$picpath['path'];//大图
                $temp = pathinfo($picpath['path']);
                $thumbPath = $temp['dirname'].'/'.$temp['filename'].'_thumb.jpg';
                $image->open('.'.$thumbPath);// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $picdata[$key]['picurl'] = $pathinfo.$thumbPath;
                $picdata[$key]['width'] = $image->width();
                $picdata[$key]['height'] = $image->height();
            }
            $val = $value0;
            $val['pic'] = $picdata;
            $val['preview'] = mb_substr($val['content'],0,20,'utf-8');
            //TODO:添加fanlist

            $val['fanlist'] = $supportController->getShareSupporterInfo($value0['id']);

            $avatarInfo = $avatarController->getAvtarInfo($val['userid']);
            $val['useravatar'] = $avatarInfo['avataMid'];

            $val['distance'] = $this -> distanceBetween($lat, $lon, $value0['latitude'], $value0['longitude']);

            $result[] = $val;
        }
        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));
    }

    /**
     * 计算两个坐标之间的距离(米)
     * @param float $fP1Lat 起点(纬度)
     * @param float $fP1Lon 起点(经度)
     * @param float $fP2Lat 终点(纬度)
     * @param float $fP2Lon 终点(经度)
     * @return int
     */
    function distanceBetween($fP1Lat, $fP1Lon, $fP2Lat, $fP2Lon){
        $fEARTH_RADIUS = 6378137;
        //角度换算成弧度
        $fRadLon1 = deg2rad($fP1Lon);
        $fRadLon2 = deg2rad($fP2Lon);
        $fRadLat1 = deg2rad($fP1Lat);
        $fRadLat2 = deg2rad($fP2Lat);
        //计算经纬度的差值
        $fD1 = abs($fRadLat1 - $fRadLat2);
        $fD2 = abs($fRadLon1 - $fRadLon2);
        //距离计算
        $fP = pow(sin($fD1/2), 2) +
            cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2/2), 2);
        return intval($fEARTH_RADIUS * 2 * asin(sqrt($fP)) + 0.5);
    }

}