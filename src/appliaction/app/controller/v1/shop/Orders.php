<?php
/**
 * 订单信息模块管理
 */
namespace app\app\controller\v1\shop;

use app\app\controller;

class Orders extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkShop();
        $this->checkIde();
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

        $map['seller_uid'] = $this->uid;
        $map['del_seller'] = 0;
        if ($orderStatus) {
            $map['order_status'] = $orderStatus;
        }

        if ($type) {
            $map['type'] = $type;
        }

        $list = table('Orders')->where($map)->field('id,order_sn,message,seller_message,status,order_status,acount_original,acount')->limit($offer, $pageSize)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            $goods = table('OrdersCar')->where('order_sn', $value['order_sn'])->field('title,ascription,goods_id,thumb,price_original,price,produce_time,mileage,start_time,end_time')->find('array');
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

        $map['seller_uid'] = $this->uid;
        $map['order_sn']   = $orderSn;

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

        $map['seller_uid'] = $this->uid;
        $map['order_sn']   = $orderSn;
        $map['status']     = 0;

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
     * 卖家删除已拒绝订单
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

        $map['seller_uid'] = $this->uid;
        $map['order_sn']   = $orderSn;
        $map['status']     = 3;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $result = table('Orders')->where('id', $id)->save('del_seller', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作完成'));
    }

    /**
     * 卖家拒绝预约
     * @date   2017-09-22T17:12:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function refuseTime()
    {
        $orderSn       = post('order_sn', 'text', '');
        $startTime     = post('start_time', 'intval', 0);
        $endTime       = post('end_time', 'intval', 0);
        $sellerMessage = post('seller_message', 'text', '');

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['seller_uid'] = $this->uid;
        $map['order_sn']   = $orderSn;
        $map['status']     = 0;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        //另选时间
        if ($startTime && $endTime) {
            if (date('Y-m-d', $startTime) != date('Y-m-d', $endTime)) {
                $this->appReturn(array('status' => false, 'msg' => '预约超过一天了'));
            }

            $dataInfo['start_time'] = $startTime;
            $dataInfo['end_time']   = $endTime;

            $data['status']         = 2;
            $data['seller_message'] = $sellerMessage;
            $date['status_time']    = TIME;

            $result = table('Orders')->where('id', $id)->save($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '执行失败'));
            }

            $result = table('OrdersCar')->where('order_sn', $orderSn)->save($dataInfo);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '修改时间执行失败'));
            }

        }
        //直接拒绝
        else {
            $data['status']         = 3;
            $data['seller_message'] = $sellerMessage;

            $result = table('Orders')->where('id', $id)->save($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '执行失败'));
            }
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

    /**
     * 卖家关闭订单
     */
    public function close()
    {
        $orderSn = post('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['seller_uid']   = $this->uid;
        $map['order_sn']     = $orderSn;
        $map['status']       = 1;
        $map['order_status'] = 2;

        $id = table('Orders')->where($map)->field('id')->find('one');
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '可操作信息不存在'));
        }

        $data['close_time'] = TIME;
        $date['status']     = 2;

        $result = table('Orders')->where('id', $id)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->appReturn(array('msg' => '操作完成'));
    }

    /**
     * 完成订单
     * @date   2017-09-27T15:20:59+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function success()
    {
        if (IS_POST) {
            $orderSn      = post('order_sn', 'text', '');
            $price        = post('price', 'float', 0);
            $couponId     = post('use_coupon_id', 'intval', 0);
            $giftCouponId = post('send_coupon_id', 'intval', 0);

            if (!$orderSn) {
                $this->appReturn(array('status' => false, 'msg' => '订单编号错误'));
            }

            if (!$price) {
                $this->appReturn(array('status' => false, 'msg' => '输入实付金额'));
            }

            if (!$orderSn) {
                $this->appReturn(array('status' => false, 'msg' => '订单编号错误'));
            }
            $map['order_sn']     = $orderSn;
            $map['seller_uid']   = $this->uid;
            $map['status']       = 1;
            $map['order_status'] = 2;

            $orders = table('Orders')->where($map)->field('acount,acount_original,uid,type')->find();

            if (!$orders) {
                $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
            }

            if ($orders['type'] == 1) {
                $type       = 23;
                $ordersData = table('OrdersCar')->where('order_sn', $orderSn)->field('goods_id')->find('one');
            } else {
                $ordersData = table('OrdersService')->where('order_sn', $orderSn)->field('type,goods_id')->find('one');
                $type       = $ordersData['type'];
            }
            $canUseCouponList = dao('Coupon')->canUseCouponList($orders['uid'], $orders['acount_original']);
            if ($canUseCouponList) {
                $couponIdArray = $canUseCouponList[$this->uid][$type];
            }

            $couponLog = table('CouponLog')->tableName();
            $coupon    = table('Coupon')->tableName();

            $couponMap[$couponLog . '.id'] = array('in', $couponIdArray);
            $couponList                    = dao('Coupon')->lists($couponMap);

            //print_r($couponIdArray);die;

            //使用抵扣卷
            if ($couponId) {
                if (!in_array($couponId, $couponIdArray)) {
                    $this->appReturn(array('status' => false, 'msg' => '抵扣卷不存在'));
                }
                $couponDetail = dao('Coupon')->logDetail($couponId);
                if ($couponDetail) {
                    if ($couponDetail['type'] == 1) {
                        $data['acount'] = $price - $couponDetail['less'];
                    } elseif ($couponDetail['type'] == 2) {
                        $data['acount'] = $price * $couponDetail['discount'];
                    }
                    //优惠金额
                    $data['coupon_price'] = $price - $data['acount'];
                }
            } else {
                $data['acount'] = $price;
            }

            if (!$data['acount']) {
                $this->appReturn(array('status' => false, 'msg' => '实付价格不能为0'));
            }

            $data['success_time'] = TIME;
            $data['order_status'] = 3;

            table('Orders')->startTrans();
            //保存订单信息
            $result = table('Orders')->where('order_sn', $orderSn)->save($data);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '操作失败'));
            }

            //修改抵扣卷信息
            if ($couponId) {
                $dataCoupon['use_time'] = TIME;
                $dataCoupon['order_sn'] = $orderSn;

                $result = table('CouponLog')->where('id', $couponId)->save($dataCoupon);
                if (!$result) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '抵扣卷保存失败,请重新尝试'));
                }
            }

            //赠送抵扣卷
            if ($giftCouponId) {

                $mapCoupon['uid']           = $this->uid;
                $mapCoupon['id']            = $giftCouponId;
                $mapCoupon['start_time']    = array('<=', TIME);
                $mapCoupon['end_time']      = array('>=', TIME);
                $mapCoupon['remainder_num'] = array('>', 0);

                $isCoupon = table('Coupon')->where($mapCoupon)->field('id')->find('One');
                if (!$isCoupon) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '赠送抵扣卷不存在'));
                }

                $dataSendCoupon['created']  = TIME;
                $dataSendCoupon['type']     = 1;
                $dataSendCoupon['order_sn'] = $orderSn;
                $dataSendCoupon['uid']      = $orders['uid'];
                $dataSendCoupon['value']    = $giftCouponId;

                $result = table('Gift')->add($dataSendCoupon);
                if (!$result) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '抵扣卷赠送失败', 'sql' => table('Gift')->getSql()));
                }

                //修改商户抵扣卷记录
                $dataUserCoupon['remainder_num'] = array('less', 1);
                $dataUserCoupon['version']       = array('add', 1);

                $resultCoupon = table('Coupon')->where(array('id' => $coupon['id'], 'version' => $coupon['version']))->save($dataUserCoupon);
                if (!$resultCoupon) {
                    table('User')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '抵扣卷库存异常,请稍后尝试'));
                }

            }

            table('Orders')->commit();
            $this->appReturn(array('msg' => '操作完成'));
        }
        //显示完成信息
        else {
            $orderSn  = get('order_sn', 'text', '');
            $price    = get('price', 'float', 0);
            $couponId = get('use_coupon_id', 'intval', 0);

            if (!$orderSn) {
                $this->appReturn(array('status' => false, 'msg' => '订单编号错误'));
            }

            $map['order_sn']     = $orderSn;
            $map['seller_uid']   = $this->uid;
            $map['status']       = 1;
            $map['order_status'] = 2;

            //获取可用抵扣卷列表
            $orders = table('Orders')->where($map)->field('acount,acount_original,uid,type')->find();
            if (!$orders) {
                $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
            }

            if ($orders['type'] == 1) {
                $type = 23;
            } else {
                $type = table('OrdersService')->where('order_sn', $orderSn)->field('type')->find('one');
            }
            $canUseCouponList = dao('Coupon')->canUseCouponList($orders['uid'], $orders['acount_original']);
            if ($canUseCouponList) {
                $couponIdArray = $canUseCouponList[$this->uid][$type];
            }

            $couponLog = table('CouponLog')->tableName();
            $coupon    = table('Coupon')->tableName();

            $couponMap[$couponLog . '.id'] = array('in', $couponIdArray);
            $couponList                    = dao('Coupon')->lists($couponMap);

            $data['use_coupon_id'] = $couponId;
            $data['price']         = $data['acount']         = $price ? $price : $orders['acount_original'];
            $data['coupon_list']   = $couponList;

            //使用抵扣卷
            if ($couponId && in_array($couponId, $couponIdArray)) {
                $couponDetail = dao('Coupon')->logDetail($couponId);
                if ($couponDetail) {
                    if ($couponDetail['type'] == 1) {
                        $data['acount'] = $data['price'] - $couponDetail['less'];
                    } elseif ($couponDetail['type'] == 2) {
                        $data['acount'] = $data['price'] * $couponDetail['discount'];
                    }
                }
            }

            $this->appReturn(array('data' => $data));
        }

    }

    /**
     * 新增临时订单
     * @date   2017-09-28T09:32:50+0800
     * @author ChenMingjiang
     */
    public function add()
    {
        $dataContent['start_time'] = post('start_time', 'intval', 0);
        $dataContent['end_time']   = post('end_time', 'intval', 0);

        $id     = post('id', 'intval', 0);
        $origin = post('origin', 'intval', 0);
        $acount = post('price', 'intval', 0);

        $message = post('message', 'text', '');

        $version = APP_VERSION;

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!$dataContent['start_time']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写开始预约时间'));
        }

        if (!$dataContent['end_time']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写结束预约时间'));
        }

        if (date('Y-m-d', $dataContent['start_time']) != date('Y-m-d', $dataContent['end_time'])) {
            $this->appReturn(array('status' => false, 'msg' => '预约超过一天了'));
        }

        $map['goods_id']   = $id;
        $map['start_time'] = $dataContent['start_time'];
        $map['end_time']   = $dataContent['end_time'];

        $is = table('OrdersCar')->where($map)->field('id')->find('one');
        if ($is) {
            $this->appReturn(array('status' => false, 'msg' => '请选择其他时间段，该时间已有预约了'));
        }

        $dataInfo = dao('orders')->getAddAttachedInfo(1, $id, $dataContent);

        if (!$dataInfo) {
            $this->appReturn(array('status' => false, 'msg' => 'dataInfo参数错误'));
        }

        //sellserUid => 商品lists
        foreach ($dataInfo as $key => $value) {
            $orderSn = dao('Orders')->createOrderSn();

            $data['is_temp']         = 1;
            $data['uid']             = $uid;
            $data['type']            = $type;
            $data['status']          = 1;
            $data['order_status']    = 3;
            $data['origin']          = $origin;
            $data['version']         = $version;
            $data['seller_uid']      = $key;
            $data['order_sn']        = $orderSn;
            $data['message']         = $message;
            $data['acount_original'] = $value['data']['acount_original'];
            $data['acount']          = $acount;
            $data['coupon_price']    = $couponPrice;
            $data['fare_price']      = $farePrice;
            $data['created']         = $data['success_time']         = $data['pass_time']         = TIME;

            table('Orders')->startTrans();
            $orderId = table('Orders')->add($data);

            if (!$orderId) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '保存订单失败'));
            }

            foreach ($value['list'] as $k => $v) {
                $goodsInfo             = $v;
                $goodsInfo['order_sn'] = $orderSn;

                $result = table('OrdersCar')->add($goodsInfo);
                if (!$result) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '保存附属信息有误'));
                }

            }

        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

}
