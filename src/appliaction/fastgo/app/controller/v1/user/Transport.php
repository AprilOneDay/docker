<?php
/**
 * 商家会员相关
 */
namespace app\fastgo\app\controller\v1\user;

use app\fastgo\app\controller\v1\Init;

class Transport extends Init
{

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 设置默认中转地址 */
    public function defaultTransfer()
    {

        $trans_id = post('id', 'text', '');
        if (!$trans_id) {
            $this->appReturn(array('status' => false, 'msg' => '请选择需要设为默认的中转地址'));
        }

        $result = table('user')->where('id', $uid)->save(array('default_transfer' => $trans_id));
        $this->appReturn(array('msg' => '操作成功'));

    }

    /** 编辑包裹 */
    public function editPackagePost()
    {
        $orderSn     = post('order_sn', 'text', '');
        $warehouseId = post('warehouse_id', 'text', '');
        $message     = post('message', 'text', '');

        $goodsArray = post('goods', 'json');

        $storageTransportSn = post('storage_transport_sn', 'text', '');
        $storageTransportId = post('storage_transport_id', 'intval', 0);

        $volumeWeight = post('volume_weight', 'float', 0);

        $type = 5;

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '请上传订单编号'));
        }

        if (!$volumeWeight) {
            $this->appReturn(array('status' => false, 'msg' => '请输入预估重量'));
        }

        if (!$storageTransportSn) {
            $this->appReturn(array('status' => false, 'msg' => '请填写转运单号'));
        }

        if (!$storageTransportId) {
            $this->appReturn(array('status' => false, 'msg' => '请选择转运物流公司'));
        }

        if (!$warehouseId) {
            $this->appReturn(array('status' => false, 'msg' => '仓库地区ID错误'));
        }

        if (!$goodsArray || !is_array($goodsArray)) {
            $this->appReturn(array('status' => false, 'msg' => '请添加商品信息'));
        }

        if ($this->getMaxDim($goodsArray) != 2) {
            $this->appReturn(array('status' => false, 'msg' => 'goods结构只能有两层', 'data' => $goodsArray));
        }

        if (count($goodsArray) > 7) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '最多只能创建7个商品'));
        }

        //查询物流信息
        $map             = array();
        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;
        $logistics       = table('Logistics')->where($map)->field('id,order_sn,warehouse_id')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '物流信息不存在'));
        }

        $warehouseInfo = table('WarehouseInfo')->where('category_id', $warehouseId)->find();
        if (!$warehouseInfo) {
            $this->appReturn(array('status' => false, 'msg' => '库房信息不存在'));
        }

        //获取fasto默认发货地址
        $sender = $warehouseInfo;
        //$sender = dao('orders', 'fastgo')->fastgoAddress();

        if (!$sender) {
            $this->appReturn(array('status' => false, 'msg' => '发货人信息不存在'));
        }

        //保存订单信息
        $data            = array();
        $data['message'] = $message;

        table('Orders')->startTrans();

        $result = table('Orders')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '保存订单失败'));
        }

        //删除原商品信息
        $result = table('OrdersPackage')->where('order_sn', $orderSn)->delete();
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '商品信息保存失败了呢'));
        }

        //保存商品信息
        foreach ($goodsArray as $key => $value) {

            if (!$value['name']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品名称'));
            }

            if (!$value['num']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品数量'));
            }

            if (!$value['brand']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品品牌'));
            }

            if (!$value['price']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写正确的商品单价'));
            }

            $value['order_sn'] = $orderSn;
            $value['account']  = $value['price'] * $value['num'];
            $value['status']   = 1;

            $result = table('OrdersPackage')->add($value);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '保存商品信息失败'));
            }
        }

        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '创建物流信息失败'));
        }

        //保存物流信息
        $data                         = array();
        $data['volume_weight']        = $volumeWeight;
        $data['storage_transport_sn'] = $storageTransportSn;
        $data['storage_transport_id'] = $storageTransportId;

        $data['name']    = $sender['name'] . '|' . $this->uid;
        $data['mobile']  = $sender['mobile'];
        $data['address'] = $sender['address'];

        $result = table('Logistics')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '物流信息保存失败'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '保存成功'));
    }

    /** 添加预报包裹 */
    public function addPackage()
    {
        $warehouseId = post('warehouse_id', 'text', '');
        $message     = post('message', 'text', '');

        $goodsArray = post('goods', 'json');

        $storageTransportSn = post('storage_transport_sn', 'text', '');
        $storageTransportId = post('storage_transport_id', 'intval', 0);

        $volumeWeight = post('volume_weight', 'float', 0);

        $type = 5;

        if (!$volumeWeight) {
            $this->appReturn(array('status' => false, 'msg' => '请输入预估重量'));
        }

        if (!$storageTransportSn) {
            $this->appReturn(array('status' => false, 'msg' => '请填写转运单号'));
        }

        if (!$storageTransportId) {
            $this->appReturn(array('status' => false, 'msg' => '请选择转运物流公司'));
        }

        if (!$warehouseId) {
            $this->appReturn(array('status' => false, 'msg' => '仓库地区ID错误'));
        }

        if (!$goodsArray || !is_array($goodsArray)) {
            $this->appReturn(array('status' => false, 'msg' => '请添加商品信息'));
        }

        if ($this->getMaxDim($goodsArray) != 2) {
            $this->appReturn(array('status' => false, 'msg' => 'goods结构只能有两层', 'data' => $goodsArray));
        }

        if (count($goodsArray) > 7) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '最多只能创建7个商品'));
        }

        $warehouseInfo = table('WarehouseInfo')->where('category_id', $warehouseId)->find();
        if (!$warehouseInfo) {
            $this->appReturn(array('status' => false, 'msg' => '库房信息不存在'));
        }

        //获取fasto默认发货地址
        $sender = $warehouseInfo;
        //$sender = dao('orders', 'fastgo')->fastgoAddress();

        if (!$sender) {
            $this->appReturn(array('status' => false, 'msg' => '发货人信息不存在'));
        }

        //$this->appReturn(array('status' => false, 'msg' => '断点测试信息', 'data' => $sender));

        //创建临时订单号
        $result = dao('FastgoApi', 'fastgo')->createOrderSn($this->uid, $this->group, $warehouseId);
        if (!$result['status']) {
            $this->appReturn($result);
        }
        $orderSn = $result['data'];

        //货币单位
        $map            = array();
        $map['bname_2'] = $warehouseId;
        $unit           = table('Category')->where($map)->field('bname')->find('one');
        if (!$unit) {
            $this->appReturn(array('status' => false, 'msg' => '货币单位异常'));
        }

        //保存订单信息
        $data             = array();
        $data['order_sn'] = $orderSn;
        $data['type']     = $type;
        $data['uid']      = $this->uid;
        $data['message']  = $message;
        $data['unit']     = $unit;
        $data['origin']   = $this->origin;
        $data['created']  = TIME;

        table('Orders')->startTrans();

        $result = table('Orders')->add($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '创建订单失败'));
        }

        //保存商品信息
        foreach ($goodsArray as $key => $value) {

            if (array_diff(array_keys($value), array('name', 'spec', 'num', 'price', 'category', 'brand', 'warehouse_id'))) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '商品参数错误了'));
            }

            if (!$value['name']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品名称'));
            }

            if (!$value['spec']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品规格'));
            }

            if (!$value['num']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品数量'));
            }

            if (!$value['brand']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写商品品牌'));
            }

            if (!$value['price']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写正确的商品单价'));
            }

            $value['order_sn'] = $orderSn;
            $value['account']  = $value['price'] * $value['num'];
            $value['status']   = 1;

            $result = table('OrdersPackage')->add($value);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '保存商品信息失败'));
            }
        }

        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '创建物流信息失败'));
        }

        //保存物流信息
        $data                         = array();
        $data['type']                 = 2;
        $data['volume_weight']        = $volumeWeight;
        $data['uid']                  = $this->uid;
        $data['order_sn']             = $orderSn;
        $data['storage_transport_sn'] = $storageTransportSn;
        $data['storage_transport_id'] = $storageTransportId;
        $data['warehouse_id']         = $warehouseId;
        $data['created']              = TIME;

        $data['name']    = $sender['name'] . '|' . $this->uid;
        $data['mobile']  = $sender['mobile'];
        $data['address'] = $sender['address'];

        $result = table('Logistics')->add($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '物流信息保存失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSn, 9);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 获取可合并订单信息 */
    public function getSuccessList()
    {
        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $map[$lt . '.type']       = 2;
        $map[$ot . '.uid']        = $this->uid;
        $map[$ot . '.del_status'] = 0;

        $field = "$lt.order_sn,$lt.name,$lt.warehouse_id,$lt.created";
        $list  = dao('OrdersLog')->getOrdersList($map, 10, 0, 999, $field, 'logistics');

        foreach ($list as $key => $value) {

            $value['title']     = '无运单号';
            $value['goodsList'] = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            $value['status_time_copy'] = date('Y-m-d H:i', $value['created']);

            $depotCopy = dao('Depot', 'fastgo')->getName($value['warehouse_id'], $this->lg);

            $tmpList[$city]['id']      = $value['warehouse_id'];
            $tmpList[$city]['value']   = $depotCopy;
            $tmpList[$city]['child'][] = $value;
        }

        $tmpList = array_values($tmpList);

        $data['list'] = $tmpList ? $tmpList : array();

        $this->appReturn(array('data' => $data));

    }

    /** 创建运单初始化 */
    public function addOrders()
    {
        $orderSn = get('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => true, 'msg' => '参数错误'));
        }

        $map['order_sn'] = array('in', $orderSn);

        $list = table('OrdersPackage')->where($map)->find('array');

        $map             = array();
        $map['order_sn'] = array('in', $orderSn);
        $volumeWeight    = table('Logistics')->where($map)->field('SUM(volume_weight) AS volume_weight')->find('one');

        $data['list']          = $list ? $list : array();
        $data['volume_weight'] = (int) $volumeWeight;
        $data['orders_num']    = stripos($orderSn, ',') !== false ? count(explode(',', $orderSn)) : 1;

        $this->appReturn(array('data' => $data));
    }

    /** 创建运单 */
    public function addOrdersPost()
    {
        $sign            = post('type', 'intval', 1);
        $ordersNum       = post('orders_num', 'intval', 1);
        $aegisTotalPrice = post('aegis_total_price', 'float', 0);
        $aegisPrice      = post('aegis_price', 'float', 0);
        $tax             = post('tax', 'float', 0);
        $volumeWeight    = post('volume_weight', 'float', 0);
        $shipAddressId   = post('ship_address_id', 'intval', 0);
        $channel         = post('channel_id', 'intval', 0);
        $declaredPrice   = post('declared_price', 'float', 0);

        $message     = post('message', 'text', '');
        $orderSnText = post('order_sn', 'text', '');

        $orders = post('orders', 'json'); //箱子数据
        $vat    = post('vat', 'json'); //增值服务

        if (!$sign) {
            $this->appReturn(array('status' => false, 'msg' => '请选择合原/分原/原箱'));
        }

        if (!$orderSnText) {
            $this->appReturn(array('status' => false, 'msg' => '请上传订单编号'));
        }

        if (!$volumeWeight) {
            $this->appReturn(array('status' => false, 'msg' => '请输入预估重量'));
        }

        if ($ordersNum != count($orders) && $ordersNum > 1) {
            $this->appReturn(array('status' => false, 'msg' => '分箱信息不一致'));
        }

        if ($this->getMaxDim($vat) != 1) {
            $this->appReturn(array('status' => false, 'msg' => 'vat结构只可存在一层'));
        }

        //预处理商品订单信息
        $orders = $this->checkOrders($orders, $ordersNum, $orderSnText);
        //$this->appReturn(array('status' => false, 'msg' => '断点测试信息', 'data' => $orders));

        //保存订单
        foreach ($orders['list'] as $key => $value) {

            //创建运单号
            $result = dao('FastgoApi', 'fastgo')->createOrderSn($this->uid, $this->group, $orders['warehouseInfo']['category_id']);
            if (!$result['status']) {
                $this->appReturn($result);
            }
            $orderSn = $result['data'];

            $map            = array();
            $map['bname_2'] = $orders['warehouseInfo']['category_id'];
            $unit           = table('Category')->where($map)->field('bname')->find('one');
            if (!$unit) {
                $this->appReturn(array('status' => false, 'msg' => '货币单位异常'));
            }

            //保存订单信息 直接进入等待计算价格界面状态
            $data                    = array();
            $data['order_sn']        = $orderSn;
            $data['type']            = 5;
            $data['uid']             = $this->uid;
            $data['message']         = $message;
            $data['unit']            = $unit;
            $data['origin']          = $this->origin;
            $data['created']         = TIME;
            $data['tax']             = $tax;
            $data['acount_original'] = $value['orders_price'];

            table('Orders')->startTrans();

            $result = table('Orders')->add($data);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '创建订单失败'));
            }

            //记录所有订单编号
            $addOrderSnArray[] = $orderSn;

            //保存物流信息
            $data                            = array();
            $data['logistics_name']          = $value['address']['name'];
            $data['logistics_mobile']        = $value['address']['mobile'];
            $data['logistics_country']       = $value['address']['country'];
            $data['logistics_province']      = $value['address']['province'];
            $data['logistics_city']          = $value['address']['city'];
            $data['logistics_area']          = $value['address']['area'];
            $data['logistics_zip_code']      = $value['address']['zip_code'];
            $data['logistics_address']       = $value['address']['address'];
            $data['logistics_code']          = $value['address']['code'];
            $data['logistics_back_code']     = $value['address']['back_code'];
            $data['logistics_positive_code'] = $value['address']['positive_code'];

            $data['name']    = $orders['sender']['name'] . '|' . $this->uid;
            $data['mobile']  = $orders['sender']['mobile'];
            $data['address'] = $orders['sender']['address'];

            $data['type']              = 2;
            $data['sign']              = $sign;
            $data['ship_address_id']   = $shipAddressId;
            $data['channel_id']        = $channel;
            $data['declared_price']    = $declaredPrice;
            $data['aegis_total_price'] = $aegisTotalPrice;
            $data['aegis_price']       = $aegisPrice;
            $data['vat']               = json_encode($vat);
            $data['volume_weight']     = $volumeWeight;
            $data['address_id']        = $value['address']['id'];
            $data['outbound_company']  = $value['outbound_company'];
            $data['uid']               = $this->uid;
            $data['order_sn']          = $orderSn;
            $data['warehouse_id']      = $orders['warehouseInfo']['category_id'];
            $data['created']           = TIME;

            $result = table('Logistics')->add($data);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '创建物流信息失败'));
            }

            //保存商品信息
            foreach ($value['goods'] as $k => $v) {

                $v['order_sn'] = $orderSn;

                $result = table('OrdersPackage')->add($v);
                if (!$result) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '保存商品信息失败'));
                }
            }

        }

        //删除预报包裹信息
        $map             = array();
        $map['uid']      = $this->uid;
        $map['order_sn'] = array('in', $orderSnText);

        $result = table('Orders')->where($map)->save('del_status', 1);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '预报包裹处理失败'));
        }

        //创建合并订单编号
        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;
        $orderSnArray = array_merge($addOrderSnArray, $orderSnArray);
        $mergeSn      = dao('Orders')->createOrderSn();

        $map             = array();
        $map['order_sn'] = array('in', $orderSnArray);

        $result = table('Orders')->where($map)->save('merge_sn', $mergeSn);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '合并单号创建失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $addOrderSnArray, 11);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        table('Orders')->commit();

        $this->appReturn(array('status' => true, 'msg' => '操作成功', 'data' => $data));

    }

    private function getMaxDim($vDim)
    {
        if (!is_array($vDim)) {
            return 0;
        } else {
            $max1 = 0;
            foreach ($vDim as $item1) {
                $t1 = $this->getmaxdim($item1);
                if ($t1 > $max1) {
                    $max1 = $t1;
                }

            }
            return $max1 + 1;
        }
    }

    /** 预处理商品信息 */
    public function checkOrders($orders, $ordersNum, $orderSnText)
    {
        if ($this->getMaxDim($orders) != 4) {
            $this->appReturn(array('status' => false, 'msg' => 'orders结构只可存在四层'));
        }

        //预先处理数据
        foreach ($orders as $key => $value) {

            if (!$value['outbound_company']) {
                $this->appReturn(array('status' => false, 'msg' => '请选择转运渠道'));
            }

            //收货人信息
            if (!$value['address_id']) {
                $this->appReturn(array('status' => false, 'msg' => '请选择收货地址'));
            }

            $map = array();

            $map['uid'] = $this->uid;
            $map['id']  = $value['address_id'];
            $address    = table('UserAddress')->where($map)->find();
            if (!$address) {
                $this->appReturn(array('status' => false, 'msg' => '订单信息不存在'));
            }

            if ($address['type'] != 2) {
                $this->appReturn(array('status' => false, 'msg' => '请选择国际转运收货地址信息'));
            }

            $value['address'] = $address;

            //商品处理
            if ($this->getMaxDim($value['goods']) != 2) {
                $this->appReturn(array('status' => false, 'msg' => 'goods结构只能有两层'));
            }

            foreach ($value['goods'] as $k => $v) {

                //物流信息
                $map             = array();
                $map['uid']      = $this->uid;
                $map['order_sn'] = $v['order_sn'];
                $map['type']     = 2;

                //物流信息
                if (!$logistics) {
                    $logistics = table('Logistics')->where($map)->find();
                    if (!$logistics) {
                        $this->appReturn(array('status' => false, 'msg' => '预报包裹信息不存在'));
                    }

                    $orderStatus = dao('OrdersLog')->getNewStatus($v['order_sn']);
                    if ($orderStatus != 10) {
                        $this->appReturn(array('status' => false, 'msg' => '包裹状态异常'));
                    }
                }

                if (!$warehouseInfo) {
                    //获取仓库信息
                    $warehouseInfo = table('WarehouseInfo')->where('category_id', $logistics['warehouse_id'])->find();
                    if (!$warehouseInfo) {
                        $this->appReturn(array('status' => false, 'msg' => '库房信息不存在'));
                    }

                    //检测仓库地址是否一致
                    $map                 = array();
                    $map['order_sn']     = array('in', $orderSnText);
                    $map['type']         = 2;
                    $map['uid']          = $this->uid;
                    $map['warehouse_id'] = $logistics['warehouse_id'];
                    $warehouseCount      = table('Logistics')->where($map)->count();

                    if ($warehouseCount != count(explode(',', $orderSnText))) {
                        $this->appReturn(array('status' => false, 'msg' => '订单中包含有两个或以上仓库信息', 'data' => $warehouseCount));
                    }

                }

                //发件人信息处理
                if (!$sender) {
                    if ($shipAddressId) {
                        $map        = array();
                        $map['uid'] = $this->uid;
                        $map['id']  = $shipAddressId;

                        $sender = table('UserAddress')->where($map)->find();
                    } else {
                        //获取fasto默认发货地址
                        $sender = $warehouseInfo;
                        //$sender = dao('orders', 'fastgo')->fastgoAddress();
                    }

                    if (!$sender) {
                        $this->appReturn(array('status' => false, 'msg' => '发货人信息不存在'));
                    }
                }

                //商品信息处理
                $map             = array();
                $map['id']       = $v['id'];
                $map['order_sn'] = $v['order_sn'];

                $goods[$v['id']] = table('ordersPackage')->where($map)->find();
                if (!$goods[$v['id']]) {
                    $this->appReturn(array('status' => false, 'msg' => '商品信息不存在'));
                }

                //保存商品信息
                $value['goods'][$k] = array_merge($goods[$v['id']], $v);
                unset($value['goods'][$k]['id']);

                //当前商品填写数量
                $newGoodsNum[$v['id']] = isset($newGoodsNum[$v['id']]) ? $newGoodsNum[$v['id']] + $v['num'] : $v['num'];

                //商品总数量
                isset($goodsNum[$v['id']]) ?: $goodsNum[$v['id']] = $goods[$v['id']]['num'];
            }

            //保存信息数据
            $orders[$key] = $value;
        }

        //检测数量是否一致
        foreach ($goodsNum as $key => $value) {
            if ($value != $newGoodsNum[$key]) {
                $this->appReturn(array('status' => false, 'msg' => $goods[$v['id']]['name'] . '数量不一致', 'data' => array($goodsNum, $newGoodsNum)));
            }
        }

        $data = array(
            'list'          => $orders,
            'warehouseInfo' => $warehouseInfo,
            'sender'        => $sender,
        );

        return $data;
    }
}
