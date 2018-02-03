<?php
/**
 * 国际转运入库模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class IntStorage extends Init
{

    private $cid;

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');

        $this->cid = dao('User')->getInfo($this->uid, 'cid');
    }

    /** 待入库列表 */
    public function lists()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $orderSn = get('order_sn', 'text', '');

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map                        = array();
        $map[$lt . '.type']         = 2;
        $map[$lt . '.warehouse_id'] = $this->cid;
        $map[$lt . '.del_status']   = 0;

        if ($orderSn) {
            $map[$lt . '.order_sn'] = $orderSn;
        }

        $field = "$lt.storage_transport_sn,$lt.order_sn,$lt.storage_transport_id,$lt.name,$lt.volume_weight,$lt.console_ablum";
        $list  = dao('OrdersLog')->getOrdersList($map, 9, $pageNo, $pageSize, $field, 'logistics');

        foreach ($list as $key => $value) {
            $list[$key]['num']                    = (int) table('OrdersPackage')->where('order_sn', $value['order_sn'])->field('sum(num) as num')->find('one');
            $list[$key]['storage_transport_type'] = dao('Category')->getBname($value['storage_transport_id']);
            $list[$key]['is_ablum']               = $value['console_ablum'] != '' ? 1 : 0;
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 详情列表 */
    public function detail()
    {
        $orderSn = get('order_sn', 'text', '');
        $list    = table('OrdersPackage')->where('order_sn', $orderSn)->find('array');

        $data['kg'] = 0;
        foreach ($list as $key => $value) {
            $data['kg'] += $value['spec'];
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 操作状态 上传图片 */
    public function update()
    {
        $status  = post('status', 'intval', 0);
        $orderSn = post('order_sn', 'text', '');

        $consoleAblum = files('console_ablum');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '运单号错误'));
        }

        if ($status == '' && !$consoleAblum) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
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
        if ($ordersStatus != 9) {
            $this->appReturn(array('status' => false, 'msg' => '信息不可操作'));
        }

        //上传照片
        if ($consoleAblum) {
            $data                  = array();
            $data['console_ablum'] = $this->appUpload($consoleAblum, '', 'fastgo');
            $result                = table('Logistics')->where('order_sn', $orderSn)->save($data);
        }

        if ($status) {
            if (!$logistics['console_ablum'] && !$data['console_ablum']) {
                $this->appReturn(array('status' => false, 'msg' => '请先上传图片'));
            }

            $result = table('Orders')->where('order_sn', $orderSn)->save('status', 1);
            if ($result) {
                //订单操作记录
                $result = dao('OrdersLog')->add($this->uid, $orderSn, 10);
                if (!$result) {
                    $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
                }

                //发送入库通知
                $sendData             = array();
                $sendData['order_sn'] = $orderSn;

                dao('Message')->send($logistics['uid'], 'user_logistics_3', $sendData, array(), 0, 3);

            }
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }
}
