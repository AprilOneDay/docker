<?php
/**
 * 支付前预处理
 */
namespace app\tools\dao;

class PayDeal
{
    /**
     * 统一规划预处理接口
     * @date   2018-01-15T15:25:26+0800
     * @author ChenMingjiang
     * @param  [type]                   $data      [支付相关查询条件]
     * @param  [type]                   $payMatch  [支付接口调用类型]
     * @param  [type]                   $returnUrl [支付完成通知地址]
     * @return [type]                              [description]
     */
    public function main($data, $payMatch, $returnUrl)
    {

        switch ($data['type']) {
            case '2': //充值
                $result = $this->pay_2($data);
                break;
            case '3': //本地直邮支付
            case '4': //国际转运支付
                $result = $this->pay_3($data);
                break;
            default:
                # code...
                break;
        }

        //返回初步检测错误信息
        if (isset($result['status'])) {
            return $result;
        }

        //debug
        //print_r($data);
        //print_r($result);

        //检测财务数据与实际支付信息是否一致
        $data = array_merge($data, $result);

        $result = $this->checkPaySn($data);
        if (!$result['status']) {
            return $result;
        }

        //执行支付接口
        $result = dao('Pay')->main($result['data'], $payMatch, $returnUrl);

        return $result;
    }

    //最终比较
    public function checkPaySn($param)
    {
        $map             = array();
        $map['type']     = $param['type'];
        $map['order_sn'] = $param['order_sn'];
        $map['is_pay']   = 0;

        $finance = table('FinanceLog')->where($map)->field('uid,pay_sn,money,unit,title,pay_type')->order('id desc')->find();
        if (!$finance) {
            return array('status' => false, 'msg' => '财务信息不存在');
        }

        if ($finance['money'] != $param['money']) {
            return array('status' => false, 'msg' => '财务记录金额与支付金额不一致');
        }

        if (isset($param['unit'])) {
            if ($param['unit'] != $param['unit']) {
                return array('status' => false, 'msg' => '财务记录金额与支付币种不一致');
            }
        }

        $data['uid']      = $finance['uid'];
        $data['money']    = $finance['money'];
        $data['pay_sn']   = $finance['pay_sn'];
        $data['unit']     = $finance['unit'];
        $data['title']    = $finance['title'];
        $data['pay_type'] = $finance['pay_type'];

        return array('data' => $data, 'status' => true);
    }

    //充值支付预处理
    public function pay_2($param)
    {
        $map             = array();
        $map['type']     = $param['type'];
        $map['order_sn'] = $param['order_sn'];
        $map['is_pay']   = 0;

        $finance = table('FinanceLog')->where($map)->field('uid,pay_sn,money,unit,title,pay_type')->find();

        $data['uid']      = $finance['uid'];
        $data['money']    = $finance['money'];
        $data['pay_sn']   = $finance['pay_sn'];
        $data['unit']     = $finance['unit'];
        $data['title']    = $finance['title'];
        $data['pay_type'] = $param['pay_type'];

        return $data;
    }

    //本地直邮预处理
    public function pay_3($param)
    {
        $map             = array();
        $map['type']     = array('in', '4,5');
        $map['order_sn'] = $param['order_sn'];

        $orders = table('Orders')->where($map)->field('acount')->find();

        if (!$orders) {
            return array('status' => false, 'msg' => '支付订单信息不存在');
        }

        $data['money'] = $orders['acount'];

        return $data;
    }
}
