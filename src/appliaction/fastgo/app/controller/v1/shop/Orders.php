<?php
/**
 * 订单相关处理
 */
namespace app\fastgo\app\controller\v1\shop;

use app\fastgo\app\controller\v1\Init;

class Orders extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual(2);
    }

    /**
     * 合并订单查看
     * @date   2018-01-08T16:15:21+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function mergeSnOrdersList()
    {
        $mergeSn = get('merge_sn', 'text', '');

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $map[$ot . '.type']       = 4;
        $map[$ot . '.seller_uid'] = $this->uid;
        $map[$ot . '.status']     = 1;
        $map[$ot . '.merge_sn']   = $mergeSn;
        $map[$ot . '.del_status'] = 0;

        $field = "$ot.order_sn,$ot.uid,$ot.created,$lt.logistics_name,$lt.logistics_mobile,$lt.logistics_address,$ot.seller_uid";

        $list = table('Orders')->join($lt, "$ot.order_sn = $lt.order_sn")->where($map)->field($field)->find('array');
        foreach ($list as $key => $value) {

            $list[$key]['title']     = '无运单号';
            $list[$key]['goodsList'] = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            $status = dao('OrdersLog')->getNewStatus($orderSn);

            $list[$key]['is_status']   = 1;
            $list[$key]['status_copy'] = '待揽收';

            if ($status != 4 && !in_array($status, array(16, 17, 18, 19, 20, 21, 22, 23))) {
                $list[$key]['status_copy'] = '已揽收';
                $list[$key]['is_status']   = 0;
            } else {
                $list[$key]['status_copy'] = '问题件';
                $list[$key]['is_status']   = 0;
            }

        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 商家手动揽收操作
     * @date   2018-01-08T17:39:23+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeLogisticsPackage_2()
    {
        $sellerUid   = post('seller_uid', 'intval', 0);
        $orderSnText = post('order_sn', 'text', '');

        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;

        if (!$orderSnArray || !$sellerUid) {
            $this->appReturn(array('status' => false, 'msg' => '手动揽收参数错误'));
        }

        $shopName = dao('User')->getShopName($this->uid);
        $user     = dao('User')->getInfo($this->uid, 'username,nickname,cid');

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        //批量处理
        foreach ($orderSnArray as $key => $value) {

            $map               = array();
            $map['order_sn']   = $value;
            $map['type']       = 4;
            $map['seller_uid'] = $sellerUid;
            $map['del_status'] = 0;

            $logistics = table('Orders')->field("uid,order_sn,type")->where($map)->find();

            if (!$logistics) {
                $this->appReturn(array('status' => false, 'msg' => $value . '该运单不存在'));
            }

            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 3) {
                $this->appReturn(array('status' => false, 'msg' => $value . '该运单不可操作'));
            }

            $logisticsArray[] = $logistics;

        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSnArray, 4);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        //给用户发送系统消息
        foreach ($logisticsArray as $key => $value) {
            $sendData['shop_name'] = $shopName;
            $sendData['order_sn']  = $value['order_sn'];
            dao('Message')->send($value['uid'], 'user_logistics_2', $sendData, array(), 0, $value['type']);

            //推送FastGo系统
            $pushData['order_sn'] = $value['order_sn'];
            $pushData['nickname'] = $user['username'];
            $pushData['cid']      = $user['cid'];
            dao('FastgoApi', 'fastgo')->updateOrders($pushData, 01);
        }

        $this->appReturn(array('msg' => '揽收成功'));
    }
}
