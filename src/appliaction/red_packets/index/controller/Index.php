<?php
namespace app\red_packets\index\controller;

use app\tools\vendor\weixin\Jssdk;
use denha;

class Index extends denha\Controller
{
    public function index()
    {
        $this->show();
    }

    public function share()
    {
        $jssdk    = new Jssdk();
        $weixinJs = $jssdk->getSignPackage();

        $this->assign('weixinJs', $weixinJs);
        $this->show();
    }
}
