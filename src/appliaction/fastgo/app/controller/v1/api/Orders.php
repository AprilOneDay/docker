<?php
/**
 * 订单状态更变
 */
namespace app\fastgo\app\controller\v1\api;

use app\app\controller;
use app\fastgo\app\controller\v1\ApiInit;

class Orders extends ApiInit
{
    public function update()
    {
        $orderSn = post('order_sn', 'text');

        $isChangePrice = post('is_change_price', 'intval', 0);

        $status = post('status', 'text', 0);

        $logistics['volume_weight']         = post('volume_weight', 'text', '');
        $logistics['real_weight']           = post('real_weight', 'text', '');
        $logistics['fee_weight']            = post('fee_weight', 'text', '');
        $logistics['batch_sn']              = post('batch_sn', 'text', '');
        $logistics['position_sn']           = post('position_sn', 'text', '');
        $logistics['outbound_transport_sn'] = post('outbound_transport_sn', 'text', '');

        $orders['acount_original'] = post('acount_original', 'text', '');

        $fieldCopy = array(
            'status'          => '问题处理状态',
            'volume_weight'   => '体积重量',
            'real_weight'     => '实际重量',
            'fee_weight'      => '计费重量',
            'batch_sn'        => '批次编号',
            'position_sn'     => '包裹位置编号',
            'acount_original' => '运单最终价格',
        );

        if ($status) {
            $result = dao('OrdersLog')->add(0, $orderSn, $status);
            if (!$result) {
                $this->apiReturn(array('status' => false, 'msg' => '状态更新失败'));
            }
        }

        if (!$orderSn) {
            $this->apiReturn(array('status' => false, 'msg' => '未获取当订单编号'));
        }

        $isOrders = table('Orders')->where('order_sn', $orderSn)->field('id')->find();
        if (!$isOrders) {
            $this->apiReturn(array('status' => false, 'msg' => '订单信息不存在'));
        }

        $updateCopy = '';

        //更改订单金额 进入待支付状态
        if ($isChangePrice) {
            $data = array();
            foreach ($orders as $key => $value) {
                if ($value != '') {
                    $data[$key] = $value;
                    $updateCopy .= $fieldCopy[$key] . ' ';

                    if ($key == 'acount_original') {
                        $data['account'] = $data['acount_original'];
                    }
                }
            }

            $map             = array();
            $map['order_sn'] = $orderSn;
            $map['is_pay']   = 0;

            $isOrders = table('Orders')->where($map)->field('id')->find();
            if (!$isOrders) {
                $this->apiReturn(array('status' => false, 'msg' => '更新支付金额信订单息异常'));
            }

            $result = table('Orders')->where('order_sn', $orderSn)->save($data);
            if (!$result) {
                $this->apiReturn(array('status' => false, 'msg' => '订单价格更新失败'));
            }
        }

        //更改物流信息参数
        $data = array();
        foreach ($logistics as $key => $value) {
            if ($value != '') {
                $data[$key] = $value;
                $updateCopy .= $fieldCopy[$key] . ' ';
            }
        }

        $result = table('Logistics')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            $this->apiReturn(array('status' => false, 'msg' => '订单物流更新失败'));
        }

        $this->apiReturn(array('msg' => '成功更新字段：' . $updateCopy));
    }
}
