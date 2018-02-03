<?php
/**
 * 本地直邮揽件模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Recipient extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('3');
    }

    /** 获取揽件列表 */
    public function lists()
    {
        $keyword = get('keyword', 'text', '');

        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        $map               = array();
        $map['assign_uid'] = $this->uid;
        $map['apply_time'] = array('between', $beginToday, $endToday);

        if ($keyword) {
            $map             = array();
            $map['nickname'] = array('instr', $keyword);

            $shopUid = table('UserShop')->where($map)->field('uid')->find('one', true);
            if ($shopUid) {
                $map['uid'] = array('in', $shopUid);
            }
        }

        $list = table('Material')->where($map)->find('array');
        foreach ($list as $key => $value) {
            $shop      = table('UserShop')->where('uid', $value['uid'])->field('name')->find();
            $goodsList = table('MaterialGoods')->where('material_sn', $value['material_sn'])->find('array');
            if (!$goodsList) {
                $goodsList = dao('Material', 'fastgo')->stockList($value['uid']);
            }

            $list[$key]['goodsList'] = $goodsList ? $goodsList : array();
            $list[$key]['shop']      = $shop;

        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 完成揽件 */
    public function update()
    {
        $materialSn      = post('material_sn', 'text', '');
        $recipientSnText = post('recipient_sn', 'text', '');
        $backSnText      = post('back_sn', 'text', '');
        $materialGoods   = post('material', 'json');

        $signAlbum = files('sign_album');

        $recipientSnArray = strpos($recipientSnText, ',') !== false ? explode(',', $recipientSnText) : (array) $recipientSnText;
        $backSnArray      = strpos($backSnText, ',') !== false ? explode(',', $backSnText) : (array) $backSnText;

        if (!$recipientSnText || !$materialSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!$signAlbum) {
            $this->appReturn(array('status' => false, 'msg' => '请完成签字'));
        }

        //检测任务分配状态
        $map                = array();
        $map['material_sn'] = $materialSn;
        $map['status']      = 0;
        $map['assign_uid']  = $this->uid;

        $material = table('Material')->where($map)->field('uid')->find();
        if (!$material) {
            $this->appReturn(array('status' => false, 'msg' => '任务异常'));
        }

        //预检测揽件信息
        foreach ($recipientSnArray as $key => $value) {
            $map               = array();
            $map['order_sn']   = $value;
            $map['del_status'] = 0;

            $logistics = table('Logistics')->where($map)->field('uid,order_sn,id')->find();
            if (!$logistics) {
                $this->appReturn(array('status' => false, 'msg' => '编号：' . $value . '不存在'));
            }

            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 4) {
                $this->appReturn(array('status' => false, 'msg' => $value . '不可操作'));
            }

            $successLogistics[] = $logistics;
        }

        table('Logistics')->startTrans();
        //更新任务状态
        $data                = array();
        $data['sign_album']  = $this->appUpload($signAlbum, '', 'fastgo');
        $data['status']      = 1;
        $data['success_num'] = count($recipientSnArray);
        $data['success_sn']  = $recipientSnText;

        $result = table('Material')->where('material_sn', $materialSn)->save($data);
        if (!$result) {
            table('Logistics')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '任务执行失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $recipientSnArray, 6);
        if (!$result) {
            table('Logistics')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        table('Logistics')->commit();

        //发送状态推送
        foreach ($successLogistics as $key => $value) {

            $user = dao('User')->getInfo($value['uid'], 'username,cid');

            $pushData             = array();
            $pushData['order_sn'] = $value['order_sn'];
            $pushData['nickname'] = $user['username'];
            $pushData['cid']      = $user['cid'];

            dao('FastgoApi', 'fastgo')->updateOrders($pushData, 02);
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 获取订单状态 */
    public function status()
    {
        $orderSn = get('order_sn', 'text', '');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map               = array();
        $map['order_sn']   = $orderSn;
        $map['del_status'] = 0;

        $logistics = table('Logistics')->where($map)->field('id')->find();

        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '物流信息不存在'));
        }

        $status = dao('OrdersLog')->getNewStatus($orderSn);

        $data['order_sn'] = $orderSn;
        if ($status == 18) {
            $data['status'] = 2;
        } elseif ($status == 4 || $status == 5) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        $this->appReturn(array('data' => $data));
    }

}
