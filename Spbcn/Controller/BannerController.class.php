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

class BannerController extends Controller
{
    public function index(){
        
    }

    public function addBanner()
    {
        $uploaddir = './Uploads/spbcn/banner/';
        $userfile = $uploaddir.$_FILES['bannerfile']['name'];
        if(!is_dir($uploaddir)){
            if(!mkdir($uploaddir,0777,true)){
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'创建文件夹失败',
                    'data'=>'',
                ));
                return;
            }
        }
        if ($_FILES["file"]["error"] > 0)
        {
            if(APP_DEBUG){
                echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
            }
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'上传文件失败',
                'data'=>'',
            ));
            return;

        } else if (move_uploaded_file($_FILES['bannerfile']['tmp_name'], $userfile)) {
            $bannerModule = M('spbcn_banner');
            $data = array(
                'picurl' => substr($userfile,1),
                'dl_url' => I('dl_url'),
                'create_time' => NOW_TIME,
            );
            if(I('picurl')){
                $data['picurl'] = I('picurl');
            }
            $insertId = $bannerModule -> add($data);
            if($insertId){
                $result = $bannerModule->where('bannerid='.$insertId)->find();
                echo json_encode(array(
                    'status'=> 'SUCCESS',
                    'reason'=>'',
                    'data'=>$result,
                ));
            } else {
                echo json_encode(array(
                    'status'=> 'FAILURE',
                    'reason'=>'数据库插入失败',
                    'data'=>'',
                ));
            }
        } else {
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'移动文件失败',
                'data'=>'',
            ));
        }

    }

    public function updateBanner()
    {

    }

    public function getBanner()
    {
        $bannerModule = M('spbcn_banner');
        $data = $bannerModule -> order('bannerid desc') -> select();
        $resultData = array();
        if($data){
            $resultData['status'] = 'SUCCESS';
            $resultData['date'] = date('Y-m-d',NOW_TIME);
            $resultData['data'] = $data;
        } else {
            //无banner
            $resultData['status'] = 'NONE';
            $resultData['date'] = date('Y-m-d',NOW_TIME);
            $resultData['data'] = $data;
        }
        echo json_encode($resultData);
    }

    public function deleteBanner()
    {

    }


}