<?php
/**
 * 支付回调处理
 */
namespace app\tools\dao;

class PayCallBack
{
    public function main($payType, $param)
    {
        switch ($payType) {
            case 2: //充值处理
                $this->execute_2($payType, $param);
                break;
            case 3: //本地直邮处理
            case 4: //本地直邮处理
                $this->execute_2($payType, $param);
                break;

            default:
                # code...
                break;
        }
    }

    /** 支付成功操作 */
    public function execute_2($payType, $param)
    {
        $map            = array();
        $map['type']    = $payType;
        $map['pay_sn']  = $param['pay_sn'];
        $map['is_lock'] = 0;

        $finance = table('FinanceLog')->where($map)->field('id,is_pay,money,unit')->find();
        if (!$isPay) {
            return array('status' => false, 'msg' => '尚未支付');
        }

        //开启事务
        table('User')->startTrans();

        //增加用户余额
        $data = array();
        if ($finance['unit'] != 'CNY') {
            $data['money_' . strtolower($finance['unit'])] = array('add', $finance['money']);
        } else {
            $data['money'] = array('add', $finance['money']);
        }

        $map        = array();
        $map['uid'] = $param['uid'];
        $result     = table('User')->where($map)->save($data);
        if (!$result) {
            table('User')->rollback();
            return array('status' => false, 'msg' => '余额充值失败,请联系管理员处理');
        }

        //锁定财务表
        $result = table('FinanceLog')->where('id', $finance['id'])->save('is_lock', 1);
        if (!$result) {
            table('User')->rollback();
            return array('status' => false, 'msg' => '余额充值执行失败,请联系管理员处理');
        }

        table('User')->commit();
        return array('status' => true, 'msg' => '充值成功');
    }

    /** 本地直邮支付成功后续操作 */
    public function execute_3($payType, $param)
    {
        $map            = array();
        $map['type']    = $payType;
        $map['pay_sn']  = $param['pay_sn'];
        $map['is_lock'] = 0;

        $finance = table('FinanceLog')->where($map)->field('id,is_pay,order_sn,money,unit')->find();
        if (!$isPay) {
            return array('status' => false, 'msg' => '尚未支付');
        }

        $uid     = $finance['uid'];
        $orderSn = $finance['order_sn'];

        //查询订单信息
        $orders = table('Orders')->where('order_sn', $orderSn)->find();

        //开启事务
        table('Orders')->startTrans();

        //更订单支付状态
        $data                 = array();
        $data['is_pay']       = 1;
        $data['order_status'] = 3;
        $result               = table('Orders')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            return array('status' => false, 'msg' => '订单最终结算异常,请联系管理员处理');
        }

        //更改抵扣卷状态
        if ($orders['coupon_id']) {
            $data             = array();
            $data['use_time'] = TIME;
            $data['order_sn'] = $orderSn;

            $result = table('CouponLog')->where('id', $orders['coupon_id'])->save($data);
            if (!$result) {
                table('Orders')->rollback();
                return array('status' => false, 'msg' => '抵扣卷消费失败,请联系管理员处理');
            }
        }

        //更改用户余额
        if ($orders['balance_price']) {
            $data = array();
            if ($orders['unit'] == 'CNY') {
                $data['moeny'] = array('less', $orders['balance_price']);
            } else {
                $data['moeny_' . $orders['unit']] = array('less', $orders['balance_price']);
            }

            $result = table('User')->where('uid', $uid)->save($data);
            if (!$result) {
                table('Orders')->rollback();
                return array('status' => false, 'msg' => '余额结算失败,请联系管理员处理');
            }
        }

        //更改积分
        if ($orders['use_integral']) {
            $result = dao('Integral')->addTemp($uid, $orderSn . '订单支付', -$orders['use_integral']);
            if (!$result['status']) {
                table('Orders')->rollback();
                return $result;
            }
        }

        //锁定财务表
        $result = table('FinanceLog')->where('id', $finance['id'])->save('is_lock', 1);
        if (!$result) {
            table('Orders')->rollback();
            return array('status' => false, 'msg' => '余额充值执行失败,请联系管理员处理');
        }

        table('Orders')->commit();
        return array('status' => true, 'msg' => '充值成功');
    }
}
