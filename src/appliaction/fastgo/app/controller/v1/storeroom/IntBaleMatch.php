<?php
/**
 * 国际转运打包匹配模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class IntBaleMatch extends Init
{
    public function scan()
    {
        $mergeSn = get('merge_sn', 'text', '');
        $orderSn = get('order_sn', 'text', '');

        if (!$mergeSn || !$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map             = array();
        $map['merge_sn'] = $mergeSn;

        $orderSnArray = table('Orders')->field('order_sn')->where($map)->find('one', true);

        if (!in_array($orderSn, $orderSnArray)) {
            $this->appReturn(array('status' => false, 'msg' => '匹配失败了'));
        }

        $this->appReturn(array('status' => true, 'msg' => '匹配成功'));
    }
}
