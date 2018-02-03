<?php
/**
 * 账户相关模块
 */
namespace app\fastgo\app\controller\v1\cli;

use app\app\controller;
use app\fastgo\app\controller\v1\CliInit;

class Orders extends CliInit
{
    /** 创建运单推送 */
    public function add()
    {
        $map['is_pull']    = 0;
        $map['del_status'] = 0;

        $list = table('Logistics')->where($map)->limit(0, 999)->field('order_sn')->find('one', true);
        foreach ($list as $key => $value) {
            $result = dao('FastgoApi', 'fastgo')->addOrders($value);
            if ($result['status']) {
                $result = table('Logistics')->where('order_sn', $value)->save('is_pull', 1);
            }

        }

        die('success');
    }
}
