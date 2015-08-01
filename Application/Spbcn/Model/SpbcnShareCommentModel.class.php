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

class SpbcnShareCommentModel extends Model
{

    protected $_validate = array(
        array('content', '1,99999', '内容不能为空', self::EXISTS_VALIDATE, 'length'),
        array('content', '0,500', '内容太长', self::EXISTS_VALIDATE, 'length'),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', '1', self::MODEL_INSERT),
    );

    public function addComment($uid, $shareid, $content, $atid){
        //写入数据库
        $data = array('uid' => $uid, 'content' => $content, 'share_id' => $shareid, 'atid' => $atid);
        $data = $this->create($data);
        if (!$data) return false;
        $comment_id = $this->add($data);

        //增加share评论数量
        M('Spbcn_share')->where(array('id' => $shareid))->setInc('commencenum');

        $result = $this -> getCommentById($comment_id);

        return $result;
    }

    public function getCommentById($commentId){
        return $this->where(array('id'=>$commentId))->find();
    }

    public function getCommentByShareId($shareid){
        return $this->where(array('share_id'=>$shareid, 'status'=>1)) -> select();
    }

    public function deleteComment($commentId){

        //获取微博编号
        $comment = D('SpbcnShareComment')->find($commentId);
        $shareid = $comment['share_id'];

        //将评论标记为已经删除
        $this->where(array('id' => $commentId))->setField('status', -1);

        //减少微博的评论数量
        D('SpbcnShare')->where(array('id' => $shareid))->setDec('commencenum');

        return true;

    }

} 