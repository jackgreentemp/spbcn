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

class SpbcnShareModel extends Model
{
    public function getAllSupporterUids($shareId){

        $data['row'] = $shareId;
        $data['table'] = 'share';
        $followerUidArray = M('Support')->where($data)->order('uid')->getField('uid',true);
        return $followerUidArray;
    }
} 