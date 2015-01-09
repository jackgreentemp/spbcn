<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Spbcn\Model;
use Think\Model;
use Think\Upload;

/**
 * 头像模型
 */

class AvatarModel extends Model{

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    public function getAvatar($uid) {
        //查询数据库
        $map = array('uid'=>$uid, 'status'=>1, 'is_temp'=>0);
        $avatar = $this->where($map)->find();
        //返回结果
        return $avatar['path'];
    }

    public function saveAvatar($uid, $path) {
        //删除旧头像和临时头像
        $this->removeAvatar($uid);

        //保存新头像
        $data = array(
            'uid'=>$uid,
            'path'=>$path,
            'status'=>1,
            'is_temp'=>0,
        );
        $data = $this->create($data);
        return $this->add($data);
    }

    public function removeAvatar($uid) {
        //TODO 删除头像文件
        //删除数据库记录
        $map = array('uid'=>$uid,'is_temp'=>0);
        return $this->where($map)->delete();
    }



}
