<?php
/**
 * 国际转运打包模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Orders extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('3,4');
    }

    /** 编辑查看 */
    public function detail()
    {
        $orderSn = get('order_sn', 'text', '');

        $map['order_sn']   = $orderSn;
        $map['del_status'] = 0;

        $orders = table('Orders')->where($map)->field('id,order_sn,message')->find();
        if (!$orders) {
            $this->appReturn(array('status' => true, 'msg' => '订单信息不存在'));
        }

        $goodsList = table('OrdersPackage')->where('order_sn', $orders['order_sn'])->find('array');
        $logistics = dao('Logistics', 'fastgo')->detail($orderSn);

        $data['goodsList'] = $goodsList ? $goodsList : array();
        $data['logistics'] = $logistics ? $logistics : array();
        $data['orders']    = $orders ? $orders : array();

        $this->appReturn(array('status' => true, 'msg' => '操作成功', 'data' => $data));
    }
}
