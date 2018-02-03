<?php
/**
 * 国际转运打包模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class IntBale extends Init
{
    private $cid;

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');

        $this->cid = dao('User')->getInfo($this->uid, 'cid');
    }

    public function index()
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

        $field = "$lt.order_sn,$lt.console_ablum,$lt.sign,$lt.real_weight,$ot.merge_sn";
        $list  = dao('OrdersLog')->getOrdersList($map, 11, $pageNo, $pageSize, $field, 'logistics');

        foreach ($list as $key => $value) {
            $list[$key]['is_ablum'] = $value['console_ablum'] != '' ? 1 : 0;

            $map             = array();
            $map['order_sn'] = array('!=', $value['order_sn']);
            $map['merge_sn'] = $value['merge_sn'];
            $orderSnArray    = table('Orders')->where($map)->field('order_sn')->find('one', true);

            $map             = array();
            $map['order_sn'] = array('in', $orderSnArray);
            $logistics       = table('Logistics')->where($map)->field('order_sn,storage_transport_sn,position_sn')->find('array');

            $list[$key]['logistics'] = $logistics ? $logistics : array();
            $list[$key]['is_ablum']  = $value['console_ablum'] != '' ? 1 : 0;
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 操作状态 上传图片 填写重量 打包完成 */
    public function update()
    {
        $status     = post('status', 'intval', 0);
        $orderSn    = post('order_sn', 'text', '');
        $realweight = post('real_weight', 'text', '');

        $consoleAblum = files('console_ablum');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '运单号错误'));
        }

        $map                 = array();
        $map['order_sn']     = $orderSn;
        $map['warehouse_id'] = $this->cid;
        $map['type']         = 2;
        $map['del_status']   = 0;

        $logistics = table('Logistics')->where($map)->field('uid,id,console_ablum')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $ordersStatus = dao('OrdersLog')->getNewStatus($orderSn);
        if ($ordersStatus != 11) {
            $this->appReturn(array('status' => false, 'msg' => '信息不可操作'));
        }

        $data                = array();
        $data['real_weight'] = $realweight;
        if ($consoleAblum) {
            $data['console_ablum'] = $this->appUpload($consoleAblum, '', 'fastgo');
        }

        //保存物流信息
        if ($consoleAblum || $realweight) {
            $result = table('Logistics')->where('order_sn', $orderSn)->save($data);
        }

        //保存状态
        if ($status) {
            if (!$logistics['real_weight'] && !$realweight) {
                $this->appReturn(array('status' => false, 'msg' => '请先填写重量'));
            }

            if (!$logistics['console_ablum'] && !$data['console_ablum']) {
                $this->appReturn(array('status' => false, 'msg' => '请先上传图片'));
            }

            if ($result) {
                //订单操作记录
                $result = dao('OrdersLog')->add($this->uid, $orderSn, 12);
                if (!$result) {
                    $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
                }
            }
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }
}
