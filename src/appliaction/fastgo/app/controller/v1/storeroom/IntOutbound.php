<?php
/**
 * 国际转运出库模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class IntOutbound extends Init
{
    private $cid;

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');

        $this->cid = dao('User')->getInfo($this->uid, 'cid');
    }

    /** 待出库运单号 */
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map                        = array();
        $map[$lt . '.type']         = 2;
        $map[$lt . '.warehouse_id'] = $this->cid;
        $map[$lt . '.del_status']   = 0;
        $map[$ot . '.is_pay']       = 1;

        $field = "$lt.order_sn,$lt.console_ablum,$lt.sign,$ot.merge_sn,$lt.outbound_transport_sn,$lt.fee_weight";
        $list  = dao('OrdersLog')->getOrdersList($map, 14, $pageNo, $pageSize, $field, 'logistics');

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 操作状态 上传图片 填写重量 出库完成 */
    public function update()
    {
        $status              = post('status', 'intval', 0);
        $orderSn             = post('order_sn', 'text', '');
        $feeWeight           = post('fee_weight', 'text', '');
        $outboundTransportSn = post('outbound_transport_sn', 'text', '');

        $outboundAblum = files('outbound_ablum');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '运单号错误'));
        }

        $map                 = array();
        $map['order_sn']     = $orderSn;
        $map['warehouse_id'] = $this->cid;
        $map['type']         = 2;
        $map['del_status']   = 0;

        $logistics = table('Logistics')->where($map)->field('uid,id,outbound_ablum,fee_weight,outbound_transport_sn')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $ordersStatus = dao('OrdersLog')->getNewStatus($orderSn);
        if ($ordersStatus != 14) {
            $this->appReturn(array('status' => false, 'msg' => '信息不可操作'));
        }

        $data                          = array();
        $data['outbound_transport_sn'] = $outboundTransportSn;
        $data['fee_weight']            = $feeWeight;

        if ($outboundAblum) {
            $data['outbound_ablum'] = $this->appUpload($outboundAblum, '', 'fastgo');
        }

        $result = table('Logistics')->where('order_sn', $orderSn)->save($data);

        if ($status) {
            if (!$logistics['fee_weight']) {
                $this->appReturn(array('status' => false, 'msg' => '请先填写重量'));
            }

            if (!$logistics['outbound_transport_sn']) {
                $this->appReturn(array('status' => false, 'msg' => '请先上传运单号'));
            }

            if (!$logistics['outbound_ablum']) {
                $this->appReturn(array('status' => false, 'msg' => '请上传拍照'));
            }

            //订单操作记录
            $result = dao('OrdersLog')->add($this->uid, $orderSn, 15);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
            }

            //发送出库通知
            $sendData             = array();
            $sendData['order_sn'] = $orderSn;

            dao('Message')->send($logistics['uid'], 'user_logistics_4', $sendData, array(), 0, 3);

        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }
}
