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

class ActivityController extends Controller
{
    public function index(){
    }

    //获取活动列表
    public function getAllActivity(){
        $activityDetailModel = M('Spbcn_activitydetail');
        $activityInfo=$activityDetailModel->select();
        if($activityInfo){
            echo json_encode(array(
                'status'=> 'SUCCESS',
                'reason' => '',
                'data' => $activityInfo,
            ));
        }
    }

    //根据活动名称查询属于该活动的share

    //


}