<?php
/**
 * 账户相关模块
 */
namespace app\fastgo\app\controller\v1\api;

use app\app\controller;
use app\fastgo\app\controller\v1\ApiInit;

class Signature extends ApiInit
{
    /** 创建签名 */
    public function createSignature()
    {

        $rand      = rand(10000, 99999);
        $time      = TIME;
        $signature = md5($this->config['secret'] . $time . $rand);

        $data['signature']  = $signature;
        $data['random_str'] = $rand;
        $data['sign_time']  = TIME;

        $this->apiReturn(array('data' => $data));
    }
}
