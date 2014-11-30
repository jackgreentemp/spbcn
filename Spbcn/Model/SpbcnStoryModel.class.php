<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM9:01
 */

namespace Spbcn\Model;

use Think\Model;

class SpbcnStoryModel extends Model
{
//    protected $_validate = array(
//        array('userid','require','必须！'),
//    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    public function addStory($userid, $title='', $content='')
    {
        if(!$userid) return false;
        //写入数据库
        $data = array('userid'=>$userid, 'title' => $title, 'content'=>$content);
        $data = $this->create($data);
        if(!$data) return false;
        $story_id = $this->add($data);

        //返回故事编号
        return $this->getStoryById($story_id);
    }

    public function getStoryById($story_id){

        $where['id'] = $story_id;

        $data = $this->where($where)->find();

        return $data;

    }

    public function getStory()
    {
        $data = $this->order('id desc')->select();
        return $data;
    }
}