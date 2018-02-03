<?php
/**
 * 支付回调处理
 */
namespace app\fastgo\app\controller\v1\pay;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class PayNotify extends Init
{

    public function main()
    {
        $callBackType = get('call_back', 'intval', 0); //调用支付接口类型

        $result = dao('PayNotify')->main($callBackType);
    }
}
