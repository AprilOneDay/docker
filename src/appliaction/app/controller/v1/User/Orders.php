<?php
/**
 * 订单信息模块管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Orders extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 交易管理
     * @date   2017-09-22T15:27:23+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $type        = get('type', 'intval', 1);
        $orderStatus = get('order_status', 'intval', 0);
        $pageNo      = get('pageNo', 'intval', 1);
        $pageSize    = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['uid']     = $this->uid;
        $map['del_uid'] = 0;
        if ($orderStatus) {
            $map['order_status'] = $orderStatus;
        }
        $map['type'] = $type;

        $list = table('Orders')->where($map)->field('id,order_sn,message,seller_message,status,order_status,acount_original,acount')->limit($offer, $pageSize)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            switch ($type) {
                case '1':
                    $goods = table('OrdersCar')->where('order_sn', $value['order_sn'])->field('title,ascription,goods_id,thumb,price_original,price,produce_time,mileage,start_time,end_time')->find('array');
                    foreach ($goods as $k => $v) {
                        $goods[$k]['price_original'] = dao('Number')->price($v['price_original']);
                        $goods[$k]['price']          = dao('Number')->price($v['price']);
                        $goods[$k]['mileage']        = $v['mileage'] . '万公里';
                        $goods[$k]['thumb']          = $this->appImg($v['thumb'], 'car');
                        $goods[$k]['produce_time']   = $v['produce_time'] . '年';
                        $goods[$k]['time']           = date('Y-m-d H:i', $v['start_time']) . '-' . date('H:i', $v['end_time']);
                    }
                    break;
                case '2':
                    $goods = table('OrdersService')->where('order_sn', $value['order_sn'])->field('title,goods_id,thumb,price_original,price,mileage,start_time,end_time,vin,brand,style,produce_time,buy_time')->find('array');
                    foreach ($goods as $k => $v) {
                        $goods[$k]['price_original'] = dao('Number')->price($v['price_original']);
                        $goods[$k]['price']          = dao('Number')->price($v['price']);
                        $goods[$k]['mileage']        = $v['mileage'] . '万公里';
                        $goods[$k]['thumb']          = $this->appImg($v['thumb'], 'car');
                        $goods[$k]['produce_time']   = $v['produce_time'] . '年';
                        $goods[$k]['time']           = date('Y-m-d H:i', $v['start_time']) . '-' . date('H:i', $v['end_time']);
                    }
                    break;
                default:
                    # code...
                    break;
            }
            $list[$key]['goods'] = $goods;
        }

        $data = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    public function detail()
    {
        $orderSn = get('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;

        $result = dao('Orders')->detail($map);
        if (!$result['status']) {
            $this->appReturn($result);
        }

        $data = $result['data'];

        foreach ($data['goods'] as $key => $value) {
            $data['goods'][$key]['thumb'] = $this->appImg($value['thumb'], 'car');
        }

        $this->appReturn(array('data' => $data));
    }

    /**
     * 买家同意预约时间
     * @date   2017-09-22T16:27:41+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function agreeTime()
    {
        $orderSn = post('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;
        $map['status']   = 2;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $data['status']       = 1;
        $data['pass_time']    = TIME;
        $data['order_status'] = 2;

        $result = table('Orders')->where('id', $id)->save($data);

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作完成'));
    }

    /**
     * 买家删除已拒绝订单
     * @date   2017-09-22T16:41:34+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function del()
    {
        $orderSn = post('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;
        $map['status']   = 3;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $result = table('Orders')->where('id', $id)->save('del_uid', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作完成'));
    }

    /**
     * 确认商户订单
     * @date   2017-09-27T16:21:32+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function success()
    {

        $orderSn = post('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']          = $this->uid;
        $map['order_sn']     = $orderSn;
        $map['order_status'] = 3;
        $map['status']       = 1;

        $orders = table('Orders')->where($map)->field('type,seller_uid')->find();
        if (!$orders) {
            $this->appReturn(array('status' => false, 'msg' => '订单信息不存在'));
        }

        if ($orders['type'] == 1) {
            $ordersData = table('OrdersCar')->where('order_sn', $orderSn)->field('ascription')->find();
            if ($ordersData['ascription'] == 1) {
                $this->appReturn(array('status' => false, 'msg' => '个人订单无需确认'));
            }
        }

        //改为待评价
        $data['order_status'] = 4;
        table('Orders')->startTrans();
        $result = table('Orders')->where(array('order_sn' => $orderSn))->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '订单操作失败'));
        }

        //领取抵扣卷
        $gift = table('Gift')->where(array('order_sn' => $orderSn, 'status' => 0))->find();
        if ($gift['type'] == 1) {
            $result = dao('Coupon')->send($this->uid, $gift['id'], $gift['value']);
            if (!$result['status']) {
                table('Orders')->rollback();
                $this->appReturn($result);
            }
        }
        table('Orders')->commit();
        $this->appReturn(array('msg' => '操作完成'));
    }

    /**
     * 发表评价
     * @date   2017-09-27T16:46:20+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function comment()
    {
        $orderSn = post('order_sn', 'text', '');
        $score   = post('score', 'intval', 0);

        $content = post('content', 'text', '');

        $ablum = files('ablum');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if ($score > 50 || $score < 0) {
            $this->appReturn(array('status' => false, 'msg' => '评分最高5星,最低0星'));
        }

        $dataContent['ablum']    = $this->appUpload($ablum, '', 'comment');
        $dataContent['order_sn'] = $orderSn;

        $map['uid']          = $this->uid;
        $map['order_sn']     = $orderSn;
        $map['order_status'] = 4;
        $map['status']       = 1;

        $orders = table('Orders')->where($map)->field('type,seller_uid')->find();
        if (!$orders) {
            $this->appReturn(array('status' => false, 'msg' => '订单信息不存在'));
        }

        if ($orders['type'] == 1) {
            $commentType = 2;
            $ordersData  = table('OrdersCar')->where('order_sn', $orderSn)->field('goods_id')->find('one');
        } else {
            $commentType = 3;
            $ordersData  = table('OrdersService')->where('order_sn', $orderSn)->field('goods_id')->find('one');
        }

        $is = table('Comment')->where(array('order_sn' => $orderSn, 'uid' => $this->uid))->field('id')->find();
        if ($is) {
            $this->appReturn(array('status' => false, 'msg' => '订单已评价'));
        }

        table('Orders')->startTrans();
        //增加评价信息
        $result = dao('Comment')->add($this->uid, $commentType, $ordersData['goods_id'], $content, $dataContent, $orders['seller_uid']);
        if ($result['status']) {
            //订单改为已评价
            $resultOrders = table('Orders')->where(array('order_sn' => $orderSn))->save('order_status', 5);
            if (!$resultOrders) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '订单信息更新失败'));
            }

            //增加店铺打分
            $data['score']   = $score;
            $data['type']    = 1;
            $data['value']   = $orders['seller_uid'];
            $data['created'] = TIME;
            $data['uid']     = $this->uid;

            $resultScore = table('Score')->add($data);
            if (!$resultScore) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '增加评分失败'));
            }
        }

        table('Orders')->commit();
        $this->appReturn($result);
    }
}
