<?php
/**
 * 会员模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class User extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('3,4');
    }

    public function index()
    {

        $user = dao('User')->getInfo($this->uid, 'real_name,mobile,cid');

        $user['warehouse_copy'] = dao('Depot', 'fastgo')->getName($user['cid'], $this->lg);

        $data = $user;

        $this->appReturn(array('data' => $data));
    }

}
