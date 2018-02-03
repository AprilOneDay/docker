<?php
/**
 * 会员积分相关
 */
namespace app\fastgo\app\controller\v1\common;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserIntegral extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 会员积分明细 */
    public function index()
    {
        $map      = array();
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $list = dao('Integral')->getList($this->uid, $this->lg, $pageNo, $pageSize);

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }
}
