<?php
/**
 * 物流模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Logistics extends Init
{
    /** 物流详情 */
    public function index()
    {
        $type    = get('type', 'intval', 1);
        $orderSn = get('order_sn', 'text');
        $isPush  = get('is_push', 'intval', 0);
        switch ($type) {
            case '1':
                $result = dao('FastgoApi', 'fastgo')->getFastgoRoute($orderSn);
                break;
            case '2':
                $result = dao('FastgoApi', 'fastgo')->getLogisticsRoute($orderSn);
                break;
            default:
                $this->appReturn(array('status' => false, 'msg' => 'type参数错误'));
                break;
        }

        if ($isPush == 1 && $type == 1) {
            $this->push($orderSn);
        }

        $this->appReturn($result);
    }

    /** 包裹详情 */
    public function detail()
    {
        $mobile  = get('mobile', 'text', '');
        $code    = get('code', 'text', '');
        $orderSn = get('order_sn', 'text', '');

        if (!$mobile) {
            $this->appReturn(array('status' => false, 'msg' => '请输入收货人电话'));
        }

        if (!$code) {
            $this->appReturn(array('status' => false, 'msg' => '请输入身份证'));
        }

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '运单号错误'));
        }

        $map['order_sn']         = $orderSn;
        $map['logistics_mobile'] = $mobile;
        $map['logistics_code']   = $code;
        $map['del_status']       = 0;

        $isLogistics = table('Logistics')->where($map)->field('id')->find('one');
        if (!$isLogistics) {
            $this->appReturn(array('status' => false, 'msg' => '暂无相关信息'));
        }

        $map = array();

        $map['order_sn'] = $orderSn;
        $orders          = table('Orders')->where($map)->field('id,order_sn,message')->find();
        $goodsList       = table('OrdersPackage')->where('order_sn', $orders['order_sn'])->find('array');
        $logistics       = dao('Logistics', 'fastgo')->detail($orderSn);

        $data['goodsList'] = $goodsList;
        $data['logistics'] = $logistics;
        $data['orders']    = $orders;

        $this->appReturn(array('status' => true, 'msg' => '操作成功', 'data' => $data));

    }

    /** 推送物流信息 */
    private function push($orderSn)
    {
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请先登录', 'code' => 501));
        }

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '请输入运单号'));
        }

    }

}
