<?php
/**
 * 订单列表
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Orders extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 订单列表 */
    public function lists()
    {
        $param['type']   = get('type', 'text', '1,2');
        $param['status'] = get('status', 'text', '');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $param['keyword']    = get('keyword', 'text', '');
        $param['start_time'] = get('start_time', 'text', '');
        $param['end_time']   = get('end_time', 'text', '');

        $data = dao('Orders', 'fastgo')->getList($this->uid, $param, $pageNo, $pageSize);

        $data['param'] = $param;

        $this->appReturn(array('data' => $data));
    }

    /** 编辑查看 */
    public function detail()
    {
        $orderSn = get('order_sn', 'text', '');

        $map['order_sn']   = $orderSn;
        $map['uid']        = $this->uid;
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

    /** 本地直邮包裹点击完成 */
    public function ordersPackageFinish()
    {
        $orderSn = post('order_sn', 'text', '');

        $map['order_sn'] = $orderSn;
        $map['uid']      = $this->uid;
        $map['type']     = 4;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $logistics = table('Logistics')->where('order_sn', $orderSn)->field('user_ablum')->find();
        if (!$logistics['user_ablum']) {
            $this->appReturn(array('status' => false, 'msg' => '请先拍照上传'));
        }

        $result = table('Orders')->where('id', $id)->save('status', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSn, 2);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 用户上传物流包裹照片 */
    public function logisticsUpdateUserAblum()
    {
        $orderSn   = post('order_sn', 'text', '');
        $userAblum = files('user_ablum');

        if (!$userAblum) {
            $this->appReturn(array('status' => false, 'msg' => '请上传照片'));
        }

        $map             = array();
        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;

        $orders = table('OrdersPackage')->where(array('order_sn' => $orderSn))->field('status')->order('status asc')->find();

        if (!$orders['status']) {
            $this->appReturn(array('status' => false, 'msg' => '请先在采购明细中，点击完成采购'));
        }

        $map             = array();
        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;

        $logistics = table('Logistics')->where('order_sn', $orderSn)->field('user_ablum,id')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $data['user_ablum'] = $this->appUpload($userAblum, '', 'logistics');

        $result = table('Logistics')->where('id', $logistics['id'])->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 申请退件 */
    public function applyOrdersBack()
    {
        $orderSn = post('order_sn', 'text', '');

        $map['order_sn'] = $orderSn;
        $map['uid']      = $this->uid;

        $status = dao('OrdersLog')->getNewStatus($orderSn);

        if (!in_array($status, array(1, 2, 3, 4, 5, 6))) {
            $this->appReturn(array('status' => false, 'msg' => '订单不可申请'));
        }

        //更新订单操作
        $result = dao('OrdersLog')->add($this->uid, $orderSn, 18);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败了'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 用户删除订单 */
    public function del()
    {
        $orderSn = post('order_sn', 'text', '');

        $map['order_sn'] = $orderSn;
        $map['uid']      = $this->uid;

        $id = table('Logistics')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $status = dao('OrdersLog')->getNewStatus($orderSn);
        if (!in_array($status, array(1, 9))) {
            $this->appReturn(array('status' => false, 'msg' => '包裹不可删除'));
        }

        $data['del_uid']    = 1;
        $data['del_status'] = 1;

        $result = table('Orders')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }

    /** 订单问题处理 */
    public function ordersDeal()
    {
        $orderSn = post('order_sn', 'text', '');
        $status  = post('status', 'intval', 0);

        if (!$status || !$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!in_array($status, array(18, 1, 23))) {
            $this->appReturn(array('status' => false, 'msg' => 'status参数错误'));
        }

        //更新完成
        if ($status == 1) {
            $map             = array();
            $map['order_sn'] = $orderSn;
            $map['is_new']   = 0;
            $map['type']     = array('not in', '16,17,18,19,20,21,22,23');

            $id = table('OrdersLog')->where($map)->field('id')->order('created desc')->find('one');
            if (!$id) {
                $this->appReturn(array('status' => false, 'msg' => '尚未获取到正常状态'));
            }

            $result = table('OrdersLog')->where('order_sn', $orderSn)->save('is_new', 0);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '更新状态失败了'));
            }

            $result = table('OrdersLog')->where('id', $id)->save('is_new', 1);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '更新状态失败'));
            }

        } else {
            //更新订单操作
            $result = dao('OrdersLog')->add($this->uid, $orderSn, $status);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '操作失败了'));
            }
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 订单上传处理照片 */
    public function ordersDealAlbum()
    {
        $orderSn    = post('order_sn', 'text', '');
        $issueAlbum = files('issue_album');

        if (!$orderSn || !$issueAlbum) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;

        $logistics = table('Logistics')->where($map)->field('id')->find();

        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '运单不存在'));
        }

        $status = dao('OrdersLog')->getNewStatus($orderSn);
        if ($status != 23) {
            $this->appReturn(array('status' => false, 'msg' => '不需要上传照片'));
        }

        $data['issue_time']  = TIME;
        $data['issue_album'] = $this->appUpload($issueAlbum, '', 'fastgo');

        if (!$data['issue_album']) {
            $this->appReturn(array('status' => false, 'msg' => '请上传图片'));
        }

        $result = table('Logistics')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }

}
