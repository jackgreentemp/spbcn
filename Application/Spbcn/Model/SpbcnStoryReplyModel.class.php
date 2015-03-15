<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-8
 * Time: PM4:14
 */

namespace Spbcn\Model;

use Think\Model;

class SpbcnStoryReplyModel extends Model
{
    protected $_validate = array(
        array('content', '1,40000', '内容长度不合法', self::EXISTS_VALIDATE, 'length'),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME),
        array('status', '1', self::MODEL_INSERT),
    );

    public function addReply($post_id, $content, $userid)
    {
        //新增一条回复
        $data = array('uid' => $userid, 'story_id' => $post_id, 'parse' => 0, 'content' => $content);
        $data = $this->create($data);
        if (!$data) return false;
        $result = $this->add($data);

        //增加帖子的回复数
        D('SpbcnStory')->where(array('id' => $post_id))->setInc('commencenum');

        //返回结果
        return $result;
    }

    private function sendReplyMessage($uid, $post_id, $content,$reply_id)
    {
        $limit = 10;
        $map['status']=1;
        $map['post_id']=$post_id;
        $count = D('ForumPostReply')->where($map)->count();
        $pageCount = ceil($count / $limit);
        //增加微博的评论数量
        $user = query_user(array('nickname', 'space_url'), $uid);
        $post = D('ForumPost')->find($post_id);
        $title = $user['nickname'] . '回复了您的帖子。';
        $content = '回复内容：' . mb_substr(op_t($content), 0, 20);
        $url = U('Forum/Index/detail', array('id' => $post_id,'page'=>$pageCount)).'#'.$reply_id;
        $from_uid = $uid;
        D('SpbcnMessage')->sendMessage($post['uid'], $content, $title, $url, $from_uid, 2, null, 'reply', $post_id,$reply_id);

        return $url;
    }

    public function getReplyList($map,$order,$page,$limit){
         $replyList = S('post_replylist_'.$map['post_id']);
         if($replyList == null){
            $replyList = D('ForumPostReply')->where($map)->order($order)->select();
            foreach ($replyList as &$reply) {
                $reply['user'] = query_user(array('avatar128', 'nickname', 'space_url', 'icons_html','rank_link'), $reply['uid']);
                $reply['lzl_count'] = D('forum_lzl_reply')->where('is_del=0 and to_f_reply_id=' . $reply['id'])->count();
            }
            unset($reply);
            S('post_replylist_'.$map['post_id'],$replyList,60);
        }
        $replyList = getPage($replyList,$limit,$page);
        return $replyList;
    }

    public function getReply($storyId){
        $where['story_id'] = $storyId;
        $where['status'] = 1;
        $replyList = $this->where($where)->order('id desc')->select();
        return $replyList;
    }

    public function delPostReply($id){
        $reply = D('SpbcnStoryReply')->where('id='.$id)->find();
        $data['status']=0;
        $res = $this->where('id='.$id)->save($data);
        D('SpbcnStory')->where(array('id' => $reply['post_id']))->setDec('reply_count');
        return $res;
    }


}