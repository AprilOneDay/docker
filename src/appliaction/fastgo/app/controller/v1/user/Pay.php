<?php
/**
 * 订单列表
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Pay extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /**
     * 订单支付展示
     * @date   2018-01-17T16:31:45+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function payOrders()
    {
        $param['order_sn']    = get('order_sn', 'text');
        $param['coupon_id']   = get('coupon_id', 'intval', 0);
        $param['unit']        = get('unit', 'floatval', 0);
        $param['is_CNY']      = get('is_CNY', 'intval', 0);
        $param['is_AUD']      = get('is_AUD', 'intval', 0);
        $param['is_integral'] = get('is_integral', 'intval', 0);

        $result = $this->checkShow($param);

        $this->appReturn($result);
    }

    /**
     * 订单支付提交
     * @date   2018-01-17T16:31:45+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function payOrdersPost()
    {
        $param['order_sn']    = post('order_sn', 'text');
        $param['coupon_id']   = post('coupon_id', 'intval', 0);
        $param['unit']        = post('unit', 'floatval', 0);
        $param['is_CNY']      = post('is_CNY', 'intval', 0);
        $param['is_AUD']      = post('is_AUD', 'intval', 0);
        $param['is_integral'] = post('is_integral', 'intval', 0);

        $result  = $this->checkShow($param);
        $tmpData = $result['data'];

        $data['acount']         = $tmpData['acount'];
        $data['coupon_price']   = $tmpData['userPirce']['coupon_price'];
        $data['balance_price']  = $tmpData['userPirce']['use_money'];
        $data['integral_price'] = $tmpData['userPirce']['use_integral_price'];
        $data['use_integral']   = $tmpData['userPirce']['use_integral'];

        $result = table('Orders')->where('order_sn', $param['order_sn'])->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '订单信息更新失败'));
        }

        //直接抵扣余额
        if ($data['acount'] == 0 && $data['use_money']) {
            //生成财务记录
            $params['type']     = $tmpData['orders']['type'] == 4 ? 3 : 4;
            $params['money']    = $data['acount'];
            $params['unit']     = $param['unit'];
            $params['order_sn'] = $param['order_sn'];
            $params['pay_type'] = 0;
            $params['title']    = '余额支付';
            $params['uid']      = $this->uid;
            $params['is_pay']   = 1;

            table('Orders')->startTrans();
            $result = dao('Finance')->addPay($params);
            if (!$result['status']) {
                table('Orders')->rollback();
                $this->appReturn($result);
            }

            //扣除用户余额
            $data         = array();
            $money        = $orders['unit'] == 'CNY' ? 'money' : 'money_aud';
            $data[$money] = array('less', $orders['account']);
            $result       = table('User')->where('uid', $uid)->save($data);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '余额抵扣失败'));
            }

            //更改订单状态
            $data           = array();
            $data['is_pay'] = 1;

            $result = table('Orders')->where('order_sn', $orderSn)->save($data);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '订单状态更改失败'));
            }

            //生成订单操作记录
            if ($tmpData['orders']['type'] == 5) {
                $result = dao('OrdersLog')->add($this->uid, $orderSn, 14);
            }

            table('Orders')->commit();
            $this->appReturn(array('status' => true, 'msg' => '支付成功'));
        } else {
            //生成财务订单
            $params['type']     = $tmpData['orders']['type'] == 4 ? 3 : 4;
            $params['money']    = $data['acount'];
            $params['unit']     = $param['unit'];
            $params['order_sn'] = $param['order_sn'];
            $params['pay_type'] = 1;
            $params['title']    = '线上支付';
            $params['uid']      = $this->uid;

            $result = dao('Finance')->addPay($params);
            if (isset($result['status'])) {
                $this->appReturn(array('status' => false, 'msg' => $result['msg']));
            }

            if ($params['unit'] == 'CNY') {
                $result = dao('PayDeal')->main($params, 1, 'http://www.baidu.com');
            } elseif ($params['unit'] == 'AUD') {
                $result = dao('PayDeal')->main($params, 2, 'http://www.baidu.com');
            }

        }

        $this->appReturn($result);
    }

    /** 支付信息检测 */
    private function checkShow($param)
    {
        $uid = $this->uid;

        $map['order_sn']   = $param['order_sn'];
        $map['is_pay']     = 0;
        $map['del_status'] = 0;

        $orders = table('Orders')->where($map)->field('type,uid,unit,acount,acount_original')->find();
        if (!$orders) {
            $this->appReturn(array('status' => false, 'msg' => '订单信息不存在呢'));
        }

        if ($orders['unit'] != $param['unit']) {
            $this->appReturn(array('status' => false, 'msg' => '该订单只能使用' . $orders['unit'] . '货币'));
        }

        if ($orders['type'] == 5 && $orders['uid'] != $this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '支付订单异常'));
        }

        if ($orders['type'] == 4 && $orders['uid'] != $this->uid && $orders['seller_uid'] != $this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '支付订单异常'));
        }

        if ($param['is_CNY'] && $param['unit'] != 'CNY') {
            $this->appReturn(array('status' => false, 'msg' => '澳币支付不可使用人民币余额'));
        }

        $couponPrice      = 0; //优惠卷金额
        $useMoney         = 0; //使用余额
        $useIntegral      = 0; //使用积分
        $useIntegralPrice = 0; //积分抵扣金额
        $acount           = $orders['acount_original']; //实际成交金额

        if ($param['is_AUD'] && $param['unit'] != 'AUD') {
            $this->appReturn(array('status' => false, 'msg' => '人民币支付不可使用澳币余额'));
        }

        $user = dao('User')->getInfo($this->uid, 'moeny,moeny_aud,integral');

        //获取积分规则
        $integralRule = dao('Param')->getValue(5);
        $integralRule = explode(':', trim($integralRule));

        //使用积分抵扣
        if ($param['is_integral']) {

            if (count($integralRule) != 2) {
                $this->appReturn(array('status' => false, 'msg' => '积分抵扣暂不可用，请及时联系管理员修复'));
            }

            if ($user['integral'] < $integralRule[0]) {
                $this->appReturn(array('status' => false, 'msg' => '积分不足不可使用'));
            }

            $useIntegral      = $integralRule[0];
            $useIntegralPrice = $acount - $acount * $integralRule[1];
            $acount           = $acount - $useIntegralPrice;
        }

        //使用人民币余额
        if ($param['is_CNY']) {
            $acount = max($acount - $user['moeny'], 0);
            if ($acount == 0) {
                $useMoney = $acount;
            } else {
                $useMoney = $user['moeny'];
            }
        }

        //使用澳币余额
        if ($param['is_AUD']) {
            $acount = max($acount - $user['moeny_aud'], 0);
            if ($acount == 0) {
                $useMoney = $acount;
            } else {
                $useMoney = $user['moeny_aud'];
            }
        }

        //获取可用抵扣卷
        $couponList = dao('Coupon')->canUseCouponList($this->uid, $orders['acount']);

        //使用抵扣券
        if ($param['coupon_id']) {
            if (!in_array($param['coupon_id'], $couponIdArray)) {
                $this->appReturn(array('status' => false, 'msg' => '使用的抵扣券不存在'));
            }
            $couponDetail = dao('Coupon')->logDetail($couponId);
            if ($couponDetail) {
                if ($couponDetail['type'] == 1) {
                    $couponPrice = $acount - $couponDetail['less'];
                } elseif ($couponDetail['type'] == 2) {
                    $couponPrice = $acount * $couponDetail['discount'] / 10;
                }

                $acount = $acount - $couponPrice;
            }
        }

        $data['couponList']    = $couponList;
        $data['orders']        = $orders;
        $data['user']          = $user;
        $data['get_integral']  = 0;
        $data['integral_copy'] = $integralRule[0] . '积分享' . ($integralRule[1] * 10) . '折优惠';
        $data['acount']        = sprintf('%0.2f', $acount);

        $data['param'] = $param;

        $data['userPirce'] = array(
            'coupon_price'       => $couponPrice,
            'use_money'          => $useMoney,
            'use_integral'       => $useIntegral,
            'use_integral_price' => $useIntegralPrice,
            'min_integral_rule'  => $integralRule[0],
        );

        return array('status' => true, 'msg' => '获取参数成功', 'data' => $data);
    }
}
