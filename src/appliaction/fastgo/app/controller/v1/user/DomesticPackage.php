<?php
/**
 * 国内直邮
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class DomesticPackage extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 创建包裹 */
    public function saveOrder()
    {

        $warehouseId = post('warehouse_id', 'text', 0);
        $addressId   = post('address_id', 'text', '');

        $isBuySafe = post('is_buy_safe', 'intval', 0);
        $message   = post('message', 'text', '');

        $shipAddressId = post('ship_address_id', 'intval');
        $goodsArray    = post('goods', 'json');

        $type = post('type', 'intval', 4);

        if (!$warehouseId) {
            $this->appReturn(array('status' => false, 'msg' => '仓库ID错误'));
        }

        if (!$addressId) {
            $this->appReturn(array('status' => false, 'msg' => '请选择收件人地址'));
        }

        if (!$goodsArray || !is_array($goodsArray)) {
            $this->appReturn(array('status' => false, 'msg' => '请添加商品信息'));
        }

        if (getMaxDim($goodsArray) != 2) {
            $this->appReturn(array('status' => false, 'msg' => 'goods结构只能有两层', 'data' => $goodsArray));
        }

        $map        = array();
        $map['uid'] = $this->uid;
        $map['id']  = $addressId;

        $address = table('UserAddress')->where($map)->find();
        if (!$address) {
            $this->appReturn(array('status' => false, 'msg' => '地址信息不存在'));
        }

        if (!$address['back_code'] || !$address['positive_code']) {
            $this->appReturn(array('status' => false, 'msg' => '请上传收货人身份证照片'));
        }

        $warehouseInfo = table('WarehouseInfo')->where('category_id', $warehouseId)->find();
        if (!$warehouseInfo) {
            $this->appReturn(array('status' => false, 'msg' => '库房信息不存在'));
        }

        if ($shipAddressId) {
            $map        = array();
            $map['uid'] = $this->uid;
            $map['id']  = $shipAddressId;

            $sender = table('UserAddress')->where($map)->find();
        } else {
            $sender = $warehouseInfo;
        }

        if (!$sender) {
            $this->appReturn(array('status' => false, 'msg' => '发货人信息不存在'));
        }

        //创建临时订单号
        //$orderSn = dao('Orders')->createOrderSn();
        $result = dao('FastgoApi', 'fastgo')->createOrderSn($this->uid, $this->group, $warehouseId);
        if (!$result['status']) {
            $this->appReturn($result);
        }
        $orderSn = $result['data'];

        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '运单号创建失败,请联系管理员处理'));
        }

        $unit = table('Category')->where('bname_2', $warehouseId)->field('bname')->find('one');
        if (!$unit) {
            $this->appReturn(array('status' => false, 'msg' => '订单货币单位异常'));
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

        if (count($goodsArray) > 7) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '最多只能创建7个商品'));
        }

        //保存商品信息
        $volumeWeight = 0;
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

            $value['warehouse_id'] = (int) $value['warehouse_id'];
            $value['order_sn']     = $orderSn;
            $value['account']      = $value['price'] * $value['num'];
            $value['status']       = $value['warehouse_id'] > 0 ? 1 : 0;

            //减少小仓库商品库存
            if ($value['warehouse_id']) {
                $result = table('UserWarehouse')->where('id', $value['warehouse_id'])->save(array('num' => array('less', $value['num'])));
            }

            //包裹重总量
            $volumeWeight += $value['spec'] * $value['num'];

            $result = table('OrdersPackage')->add($value);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '保存商品信息失败'));
            }
        }

        //保存物流信息
        $data                            = array();
        $data['logistics_name']          = $address['name'];
        $data['logistics_mobile']        = $address['mobile'];
        $data['logistics_country']       = $address['country'];
        $data['logistics_province']      = $address['province'];
        $data['logistics_city']          = $address['city'];
        $data['logistics_area']          = $address['area'];
        $data['logistics_zip_code']      = $address['zip_code'];
        $data['logistics_address']       = $address['address'];
        $data['logistics_code']          = $address['code'];
        $data['logistics_back_code']     = $address['back_code'];
        $data['logistics_positive_code'] = $address['positive_code'];

        $data['name']    = $sender['name'];
        $data['mobile']  = $sender['mobile'];
        $data['address'] = $sender['address'];

        $data['volume_weight'] = $volumeWeight;
        $data['address_id']    = $addressId;
        $data['uid']           = $this->uid;
        $data['order_sn']      = $orderSn;
        $data['is_buy_safe']   = $isBuySafe;
        $data['warehouse_id']  = $warehouseId;
        $data['created']       = TIME;

        $result = table('Logistics')->add($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '创建物流信息失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSn, 1);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 编辑查看 */
    public function editOrder()
    {
        $orderSn = get('order_sn', 'text', '');

        $map['order_sn'] = $orderSn;
        $map['uid']      = $this->uid;

        $orders = table('Orders')->where($map)->field('id,order_sn,message')->find();

        $data['orders']    = $orders;
        $data['goodsList'] = table('OrdersPackage')->where('order_sn', $orders['order_sn'])->find('array');
        $data['logistics'] = table('Logistics')->where('order_sn', $orders['order_sn'])->find();

        $data['logistics']['logistics_back_code']     = $this->appImg($data['logistics']['logistics_back_code'], 'code');
        $data['logistics']['logistics_positive_code'] = $this->appImg($data['logistics']['logistics_positive_code'], 'code');

        $data['logistics']['user_ablum']    = $this->appImg($data['logistics']['user_ablum'], 'logistics');
        $data['logistics']['console_ablum'] = $this->appImg($data['logistics']['console_ablum'], 'logistics');

        $this->appReturn(array('status' => true, 'msg' => '操作成功', 'data' => $data));
    }

    /** 编辑提交 */
    public function editOrderPost()
    {

        $addressId = post('address_id', 'text', '');

        $isBuySafe = post('is_buy_safe', 'intval', 0);
        $message   = post('message', 'text', '');

        $sender     = post('sender', 'json');
        $goodsArray = post('goods', 'json');

        $shipAddressId = post('ship_address_id', 'intval');

        $orderSn = post('order_sn', 'text', '');

        //查询物流信息
        $map             = array();
        $map['uid']      = $this->uid;
        $map['order_sn'] = $orderSn;

        $logistics = table('Logistics')->where($map)->field('id,order_sn,warehouse_id')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '物流信息不存在'));
        }

        if (!$addressId) {
            $this->appReturn(array('status' => false, 'msg' => '请选择收件人地址'));
        }

        if (!$goodsArray || !is_array($goodsArray)) {
            $this->appReturn(array('status' => false, 'msg' => '请添加商品信息'));
        }

        $warehouseId = $logistics['warehouse_id'];

        $map        = array();
        $map['uid'] = $this->uid;
        $map['id']  = $addressId;
        $address    = table('UserAddress')->where($map)->find();
        if (!$address) {
            $this->appReturn(array('status' => false, 'msg' => '地址信息不存在'));
        }

        if (!$address['back_code'] || !$address['positive_code']) {
            $this->appReturn(array('status' => false, 'msg' => '请上传收货人身份证照片'));
        }

        if ($shipAddressId) {
            $map        = array();
            $map['uid'] = $this->uid;
            $map['id']  = $shipAddressId;

            $sender = table('UserAddress')->where($map)->find();
        } else {
            //$sender = $warehouseInfo;
            //获取fasto默认发货地址
            $sender = dao('orders', 'fastgo')->fastgoAddress();
        }

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
            $this->appReturn(array('status' => false, 'msg' => '订单保存失败'));
        }

        //还原商品库存
        $ordersPackageList = table('OrdersPackage')->where('order_sn', $orderSn)->find('array');
        foreach ($ordersPackageList as $key => $value) {
            if ($value['warehouse_id']) {
                $result = table('UserWarehouse')->where('id', $value['warehouse_id'])->save(array('num' => array('add', $value['num'])));
                if (!$result) {
                    table('Orders')->rollback();
                    $this->appReturn(array('status' => false, 'msg' => '库存还原失败了'));
                }
            }
        }

        //删除原商品信息
        $result = table('OrdersPackage')->where('order_sn', $orderSn)->delete();
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '商品信息操作失败了呢'));
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
                $this->appReturn(array('status' => false, 'msg' => '请填写商品数量'));
            }

            if (!$value['price']) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '请填写正确的商品单价'));
            }

            $value['warehouse_id'] = (int) $value['warehouse_id'];
            $value['order_sn']     = $orderSn;
            $value['account']      = $value['price'] * $value['num'];
            $value['status']       = $value['warehouse_id'] > 0 ? 1 : 0;

            //减少小仓库商品库存
            if ($value['warehouse_id']) {
                $result = table('warehouse')->where('id', $value['warehouse_id'])->save(array('num' => array('less', $value['num'])));
            }

            $volumeWeight += $value['spec'];

            $result = table('OrdersPackage')->add($value);
            if (!$result) {
                table('Orders')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '保存商品信息失败'));
            }
        }

        //保存物流信息
        $data                       = array();
        $data['logistics_name']     = $address['name'];
        $data['logistics_mobile']   = $address['mobile'];
        $data['logistics_country']  = $address['country'];
        $data['logistics_province'] = $address['province'];
        $data['logistics_city']     = $address['city'];
        $data['logistics_area']     = $address['area'];
        $data['logistics_zip_code'] = $address['zip_code'];
        $data['logistics_address']  = $address['address'];
        $data['logistics_code']     = $address['code'];

        $data['name']    = $sender['name'];
        $data['mobile']  = $sender['mobile'];
        $data['address'] = $sender['address'];

        $data['volume_weight'] = $volumeWeight;
        $data['address_id']    = $addressId;
        $data['is_buy_safe']   = $isBuySafe;

        $result = table('Logistics')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '物流信息保存失败'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 发货列表展示 */
    public function ship()
    {

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $map[$ot . '.uid']        = $this->uid;
        $map[$ot . '.status']     = 1;
        $map[$ot . '.del_status'] = 0;
        $map[$lt . '.type']       = 1;

        $field = "$ot.order_sn,$ot.uid,$ot.created,$lt.logistics_name,$lt.logistics_mobile,$lt.logistics_address";
        $list  = dao('OrdersLog')->getOrdersList($map, 2, 0, 99, $field, array('logistics'));

        foreach ($list as $key => $value) {
            $list[$key]['title']     = '无运单号';
            $list[$key]['goodsList'] = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            $list[$key]['status_time_copy'] = date('Y-m-d H:i', $value['created']);
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 将包裹挂载到网点下 */
    public function shipPost()
    {
        $orderSnText = post('order_sn', 'text', '');
        $sellerUid   = post('seller_uid', 'intval', 0);

        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;

        if (!$orderSnText || !$sellerUid) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        //批量检测处理
        foreach ($orderSnArray as $key => $value) {
            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 2) {
                $this->appReturn(array('status' => false, 'msg' => '存在不可操作运单'));
            }
        }

        //更改订单状态
        $map             = array();
        $map['order_sn'] = array('in', $orderSnText);

        //创建合并订单号
        $mergeSn = dao('Orders')->createOrderSn();
        if (!$mergeSn) {
            $this->appReturn(array('status' => false, 'msg' => '创建合并订单号失败'));
        }

        $data['seller_uid'] = $sellerUid;
        $data['merge_sn']   = $mergeSn;

        table('Orders')->startTrans();
        $result = table('Orders')->where($map)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单执行失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSnArray, 3);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 合并订单查看 */
    public function mergeSnOrdersList()
    {
        $mergeSn = get('merge_sn', 'text', '');

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        $map                      = array();
        $map[$ot . '.uid']        = $this->uid;
        $map[$ot . '.merge_sn']   = $mergeSn;
        $map[$ot . '.del_status'] = 0;
        $map[$lt . '.type']       = 1;

        $field = "$ot.order_sn,$ot.uid,$ot.created,$lt.logistics_name,$lt.logistics_mobile,$lt.logistics_address";
        $list  = table('Orders')->join($lt, "$ot.order_sn = $lt.order_sn")->where($map)->field($field)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['title']     = '无运单号';
            $list[$key]['goodsList'] = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            $status                         = dao('OrdersLog')->getNewStatus($value['order_sn']);
            $list[$key]['status_copy']      = $status != 4 ? '待揽收' : '已揽收';
            $list[$key]['status_time_copy'] = date('Y-m-d H:i', $value['created']);
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 增加合并包裹 */
    public function addMergeSnOrders()
    {

        $mergeSn     = post('merge_sn', 'text', '');
        $orderSnText = post('order_sn', 'text', '');
        $sellerUid   = post('seller_uid', 'text', '');

        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;

        if (!$mergeSn || !$orderSnText || !$sellerUid) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        //批量处理
        foreach ($orderSnArray as $key => $value) {
            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 2) {
                $this->appReturn(array('status' => false, 'msg' => '存在不可操作运单'));
            }
        }

        //增加合并订单号
        $data['seller_uid'] = $sellerUid;
        $data['merge_sn']   = $mergeSn;

        $map             = array();
        $map['order_sn'] = array('in', $orderSnText);

        $result = table('Orders')->where($map)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        //订单操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSnArray, 3);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '订单状态严重异常'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }

    /** 删除合并订单 */
    public function delMergeSnOrders()
    {

        $mergeSn     = post('merge_sn', 'text', '');
        $orderSnText = post('order_sn', 'text', '');

        $orderSnArray = strpos($orderSnText, ',') !== false ? explode(',', $orderSnText) : (array) $orderSnText;

        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        //批量处理
        foreach ($orderSnArray as $key => $value) {
            $status = dao('OrdersLog')->getNewStatus($value);
            if ($status != 3) {
                $this->appReturn(array('status' => false, 'msg' => '存在不可操作运单'));
            }

        }

        //取消合并订单号
        $map             = array();
        $map['merge_sn'] = $mergeSn;
        $map['order_sn'] = array('in', $orderSnText);

        $data               = array();
        $data['seller_uid'] = 0;
        $data['merge_sn']   = '';

        table('Orders')->startTrans();
        $result = table('Orders')->where($map)->save($data);
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '取消合并订单,执行失败'));
        }

        //删除选择网点
        $map             = array();
        $map['order_sn'] = array('in', $orderSnText);
        $map['type']     = 3;
        $result          = table('OrdersLog')->where($map)->delete();
        if (!$result) {
            table('Orders')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '修改状态失败'));
        }

        table('Orders')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }

}
