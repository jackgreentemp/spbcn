<?php
/**
 * 所属项目 OnePlus.
 * 开发者: 想天
 * 创建日期: 3/13/14
 * 创建时间: 7:41 PM
 * 版权所有 想天工作室(www.ourstu.com)
 */

namespace Spbcn\Model;

use Think\Model;

class SpbcnFriendModel extends Model
{
    public function getAllFollowerUids($uid){

        $data['fuid'] = $uid;
        $followerUidArray = $this->where($data)->order('uid')->getField('uid',true);
        return $followerUidArray;

    }

    public function getAllFollowedUids($uid){

        $data['uid'] = $uid;
        $followerUidArray = $this->where($data)->order('uid')->getField('fuid',true);
        return $followerUidArray;

    }
} 