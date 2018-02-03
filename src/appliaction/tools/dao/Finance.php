<?php
/**
 * 财务模块
 */
namespace app\tools\dao;

class Finance
{

    /** 直接财务记录 */
    public function addPay($param = array(), $isPay = 0)
    {

        if (!$param['money'] || !$param['type'] || !$param['order_sn']) {
            return array('status' => false, 'msg' => '财务参数错误');
        }

        $data['type']     = $param['type'];
        $data['money']    = $param['money'];
        $data['order_sn'] = $param['order_sn'];
        $data['created']  = TIME;
        $data['is_pay']   = $isPay;
        $data['unit']     = $param['unit'];
        $data['title']    = $param['title'];
        $data['uid']      = $param['uid'];
        $data['pay_type'] = $param['pay_type'];
        $data['pay_sn']   = dao('Orders')->createOrderSn();

        $result = table('FinanceLog')->add($data);
        if (!$result) {
            dao('Log')->error(1, '财务记录插入异常,请立即查明原因');
            return array('status' => false, 'msg' => '财务记录,执行失败');
        }

        return true;
    }

    //增加财务记录
    public function add($type = 0, $money = 0, $orderSn = '', $isPay = 0, $title = '', $unit = 'CNY')
    {
        if (!$money || !$type) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data['type']     = $type;
        $data['money']    = $money;
        $data['order_sn'] = $orderSn;
        $data['created']  = TIME;
        $data['is_pay']   = $isPay;
        $data['unit']     = $unit;
        $data['title']    = $title;

        $result = table('FinanceLog')->add($data);
        if (!$result) {
            dao('Log')->error(1, '财务记录插入异常,请立即查明原因');
            return false;
        }

        return true;
    }

    /**
     * 支付确认
     * @date   2017-10-13T11:51:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $map [description]
     * @return [type]                        [description]
     */
    public function pay($map, $payMoney = 0, $unit = '')
    {
        if (!$map) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['is_pay'] = 0;

        $finance = table('FinanceLog')->where($map)->field('id,money,unit')->find('');
        if (!$finance) {
            return array('status' => false, 'msg' => '财务信息不存在');
        }

        if ($payMoney && $payMoney < $finance['money']) {
            table('FinanceLog')->where('id', $finance['id'])->save('issue_status', 1);
            return array('status' => false, 'msg' => '支付金额不一致');
        }

        if ($unit && $finance['unit'] != $unit) {
            table('FinanceLog')->where('id', $finance['id'])->save('issue_status', 2);
            return array('status' => false, 'msg' => '支付币种不一致');
        }

        $data              = array();
        $data['is_pay']    = 1;
        $data['pay_money'] = $payMoney;

        $result = table('FinanceLog')->where('id', $finance['id'])->save('is_pay', 1);
        if (!$result) {
            return array('status' => false, 'msg' => '修改失败');
        }

        return array('status' => true, 'msg' => '操作成功');

    }

    /** 待支付状态下 检测支付金额/币种是否一致 */
    public function checkPrice($type = 0, $param)
    {
        if (!$param['order_sn'] || !$param['price'] || !$type) {
            return array('status' => false, 'msg' => '支付异常');
        }

        $map           = array();
        $map['pay_sn'] = $param['pay_sn'];
        $map['type']   = $type;
        $map['is_pay'] = 0;

        $finance = table('FinanceLog')->where($map)->field('id,price,money,unit')->find();
        if (!$finance) {
            return array('status' => false, 'msg' => '支付信息不存在');
        }

        if ($finance['money'] - $param['price'] > 0.1 && $param['price']) {
            return array('status' => false, 'msg' => '支付金额不一致');
        }

        if (isset($param['unit'])) {
            if ($param['unit'] != $finance['unit']) {
                return array('status' => false, 'msg' => '支付货币种类不一致');
            }
        }

        $data              = array();
        $data['pay_money'] = $param['price'];
        $data['is_pay']    = 1;

        $result = table('FinanceLog')->where('id', $finance['id'])->save($data);
        if (!$result) {
            return array('status' => false, 'msg' => '支付状态异常,请通知管理员');
        }

        return array('status' => true, 'msg' => '检验通过');
    }

}
