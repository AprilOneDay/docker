<?php
/**
 * 支付同步通知处理
 */
namespace app\fastgo\app\controller\v1\pay;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class PayReturn extends Init
{
    public function main()
    {
        $callBack = get('call_back', 'intval', 0);

        switch ($callBack) {
            case '1':

                break;

            default:
                # code...
                break;
        }
    }
}
