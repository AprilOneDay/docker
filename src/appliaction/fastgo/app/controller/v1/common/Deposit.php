<?php
/**
 * 充值接口
 */
namespace app\fastgo\app\controller\v1\common;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Deposit extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 支付明细 */
    public function index()
    {
        $map           = array();
        $map['uid']    = $this->uid;
        $map['is_pay'] = 1;

        $list = table('FinanceLog')->where($map)->field('type,title,pay_money,created')->find('array');

        foreach ($list as $key => $value) {
            if ($type == 2) {
                $list[$key]['pay_money'] = '+' . $value['pay_money'];
            } else {
                $list[$key]['pay_money'] = '-' . $value['pay_money'];
            }
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 创建支付 */
    public function add()
    {
        $param['type']     = 2;
        $param['pay_type'] = post('pay_type', 'intval', 1);
        $param['money']    = post('money', 'floatval', 0);
        $param['unit']     = post('unit', 'text', 'CNY');
        $param['order_sn'] = dao('Orders')->createOrderSn();
        $param['title']    = '充值';
        $param['uid']      = $this->uid;

        $result = dao('Finance')->addPay($param);
        if (isset($result['status'])) {
            $this->appReturn(array('status' => false, 'msg' => $result['msg']));
        }

        if ($param['unit'] == 'CNY') {
            $result = dao('PayDeal')->main($param, 1, 'http://www.baidu.com');
        } elseif ($param['unit'] == 'AUD') {
            $result = dao('PayDeal')->main($param, 2, 'http://www.baidu.com');
        }

        $this->appReturn($result);
    }
}
