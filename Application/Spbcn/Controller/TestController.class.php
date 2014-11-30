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

class TestController extends Controller
{
    public function index(){
        $this -> display();
    }

    public function testAddBanner(){
        $this->display('addBanner');
    }

    public function testGetBanner(){
        $this->redirect('Banner/getBanner');
    }

    public function testAddShareItem(){
        $this->display('addShareItem');
    }

    public function testAddShareItemWithActivity(){
        $this->display('addShareItemWithActivity');
    }

    public function getShareItemByActivityName(){
        $this->display('getShareItemByActivityName');
    }

    public function testWeixinLogin()
    {
        $this->display('weixinLogin');
    }

    public function testWeiboLogin(){
        $this->display('weiboLogin');
    }

    public function testSupport(){
        $this->display('support');
    }

    public function testAddStory(){
        $this->display('addStory');
    }




}