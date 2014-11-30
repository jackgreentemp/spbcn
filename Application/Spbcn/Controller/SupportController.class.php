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

class SupportController extends Controller
{
    public function index(){
    }

    //对share点赞
    public function doSupport(){
        $appname = 'Spbcn';
        $table = 'share';
        $row = I('shareid');
        $userid = I('userid');
        if(!$userid){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'userid为空',
                'data'=>'',
            ));
            return;
        }
        $support['appname'] = $appname;
        $support['table'] = $table;
        $support['row'] = $row;
        $support['uid'] = $userid;

        if (D('Support')->where($support)->count()) {
            //已点赞那么取消点赞
            D('Support')->where($support)->delete();

            if(M('Spbcn_share')->where('id='.$row)->setDec('upnum')){
                echo json_encode(array(
                    'status'=> 'SUCCESS',
                    'reason'=>'',
                    'data'=> array(
                        'supportnum' => M('Spbcn_share')->where('id='.$row)->getField('upnum')
                    ),
                ));
            } else{
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'更新点赞数量失败',
                    'data'=>'',
                ));
            }
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {
//                $lastSupportNum = M('Spbcn_share')->where('id='.$row)->getField('upnum');
//                $newSupportNum = $lastSupportNum + 1;
//                if(M('Spbcn_share')->where('id='.$row)->setField('upnum',$newSupportNum)){
                if(M('Spbcn_share')->where('id='.$row)->setInc('upnum')){
                    echo json_encode(array(
                        'status'=> 'SUCCESS',
                        'reason'=>'',
                        'data'=> array(
                            'supportnum' => M('Spbcn_share')->where('id='.$row)->getField('upnum')
                        ),
                    ));
                } else{
                    echo json_encode(array(
                        'status'=> 'FAILURE',
                        'reason'=>'更新点赞数量失败',
                        'data'=>'',
                    ));
                }
            } else {
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'写入数据库失败',
                    'data'=>'',
                ));
            }

        }

    }

    //对故事点赞
    public function addStorySupport(){
        $appname = 'Spbcn';
        $table = 'story';
        $row = I('storyid');
        $userid = I('userid');
        if(!$userid){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'userid为空',
                'data'=>'',
            ));
            return;
        }
        $support['appname'] = $appname;
        $support['table'] = $table;
        $support['row'] = $row;
        $support['uid'] = $userid;

        if (D('Support')->where($support)->count()) {
            //已点赞那么取消点赞
            D('Support')->where($support)->delete();

            if(M('Spbcn_story')->where('id='.$row)->setDec('upnum')){
                echo json_encode(array(
                    'status'=> 'SUCCESS',
                    'reason'=>'',
                    'data'=> array(
                        'supportnum' => M('Spbcn_story')->where('id='.$row)->getField('upnum')
                    ),
                ));
            } else{
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'更新点赞数量失败',
                    'data'=>'',
                ));
            }
        } else {
            $support['create_time'] = time();
            if (D('Support')->add($support)) {
//                $lastSupportNum = M('Spbcn_share')->where('id='.$row)->getField('upnum');
//                $newSupportNum = $lastSupportNum + 1;
//                if(M('Spbcn_share')->where('id='.$row)->setField('upnum',$newSupportNum)){
                if(M('Spbcn_story')->where('id='.$row)->setInc('upnum')){
                    echo json_encode(array(
                        'status'=> 'SUCCESS',
                        'reason'=>'',
                        'data'=> array(
                            'supportnum' => M('Spbcn_story')->where('id='.$row)->getField('upnum')
                        ),
                    ));
                } else{
                    echo json_encode(array(
                        'status'=> 'FAILURE',
                        'reason'=>'更新点赞数量失败',
                        'data'=>'',
                    ));
                }
            } else {
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'写入数据库失败',
                    'data'=>'',
                ));
            }

        }

    }

}