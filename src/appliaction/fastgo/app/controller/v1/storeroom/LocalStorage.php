<?php
/**
 * 本地直邮入库模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class LocalStorage extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');
    }

    public function lists()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $cid = dao('User')->getInfo($this->uid, 'cid');

        //获取所属处理中心的店铺
        $map         = array();
        $map['type'] = 2;
        $map['cid']  = $cid;

        $userArray = table('User')->where($map)->field('uid')->find('one', true);

        //获取完成揽收的任务
        $map           = array();
        $map['uid']    = array('in', $userArray);
        $map['status'] = array('>=', 1);

        $list = table('Material')->where($map)->order('status asc')->find('array');
        foreach ($list as $key => $value) {
            $shop = table('UserShop')->where('uid', $value['uid'])->field('name,mobile')->find();

            $list[$key]['shop']       = $shop;
            $list[$key]['success_sn'] = explode(',', $value['success_sn']);
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));

    }

    /**
     * 入库操作
     * @date   2018-01-11T10:30:44+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function update()
    {
        $materialSn  = post('material_sn', 'text', '');
        $orderSnText = post('order_sn', 'text', '');

        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;

        if (!$materialSn || !$orderSnText) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        //预处理
        foreach ($orderSnArray as $key => $value) {
            $map                = array();
            $map['material_sn'] = $materialSn;
            $map['success_sn']  = array('instr', $value);

            $is = table('Material')->where($map)->field('id')->find();
            if (!$is) {
                $this->appReturn(array('status' => false, 'msg' => '运单号:' . $value . '不属于该任务'));
            }

            $map               = array();
            $map['order_sn']   = $value;
            $map['del_status'] = 0;

            $field     = 'uid,order_sn,id';
            $logistics = table('Logistics')->where($map)->field($field)->find();
            if (!$logistics) {
                $this->appReturn(array('status' => false, 'msg' => '编号：' . $value . '不存在'));
            }

            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 6) {
                $this->appReturn(array('status' => false, 'msg' => $value . '不可操作'));
            }

            $successLogistics[] = $logistics;
        }

        //修改任务状态
        $data                = array();
        $data['status']      = 2;
        $data['console_uid'] = $this->uid;

        $map                = array();
        $map['material_sn'] = $materialSn;
        $map['status']      = 1;

        table('Material')->startTrans();
        $result = table('Material')->where($map)->save($data);
        if (!$result) {
            table('Material')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '任务操作失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSnArray, 7);
        if (!$result) {
            table('Material')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        //推送站内信
        foreach ($successLogistics as $key => $value) {
            $sendData['order_sn'] = $value['order_sn'];
            dao('Message')->send($value['uid'], 'user_logistics_3', $sendData, array(), 0, 2);
        }

        //推送Fastgo系统
        foreach ($successLogistics as $key => $value) {

            $user = dao('User')->getInfo($value['uid'], 'username,cid');

            $pushData             = array();
            $pushData['order_sn'] = $value['order_sn'];
            $pushData['nickname'] = $user['username'];
            $pushData['cid']      = $user['cid'];

            dao('FastgoApi', 'fastgo')->updateOrders($pushData, 03);
        }

        table('Material')->commit();
        $this->appReturn(array('msg' => '入库操作完成'));
    }
}
