<?php
/**
 * 物流模块模块
 */
namespace app\fastgo\tools\dao;

class FastgoApi
{
    private static $baseUrl = 'http://47.100.5.66';
    private static $header  = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization:fastgokey MmJiYzZmZjEtODM3OS00YWE0LTlmMWEtNWE0MTc1NDNhZmIzLDM4ZTc5ZmNjLTlmZjMtNDYxNS04MTJiLTc1MDNkYjA3MjUyNA==',
    );

    public function __construct()
    {
        set_time_limit(0);
    }

    //http://47.100.5.66/api/express/getsites
    /** 同步更新总部/国家/处理中心/网点 */
    public function updateSite()
    {
        $url = self::$baseUrl . '/api/express/getsites';

        $data = response($url, 'GET', array(), self::$header);

        if ($data['code'] != 0000 || !isset($data['code'])) {
            return array('status' => false, 'msg' => '数据获取失败');
        }

        $response = &$data['result'][0];

        //更新总部
        $data            = array();
        $data['name']    = $data['name_en']    = $data['name_jp']    = $response['name'];
        $data['bname']   = $response['alias'];
        $data['bname_2'] = $response['siteno'];

        //更新总部
        $result = table('Category')->where('id', 743)->save($data);
        if (!$result) {
            return array('status' => false, 'msg' => '更新总部失败');
        }

        //print_r($response['items']);die;

        //更新中心
        foreach ($response['items'] as $k => $v) {

            $data             = array();
            $data['name']     = $data['name_en']     = $data['name_jp']     = $v['name'];
            $data['bname']    = $v['unit'];
            $data['bname_2']  = $v['siteno'];
            $data['parentid'] = 743;

            $oneId = table('Category')->where('bname_2', $v['siteno'])->field('id')->find('one');

            if ($oneId) {
                $result = table('Category')->where('id', $oneId)->save($data);
            } else {
                $oneId = table('Category')->add($data);
            }

            if (!$oneId || !$result) {
                return array('status' => false, 'msg' => '更新国家失败');
            }

            //更新仓库信息
            $fastgoAddress       = dao('Orders', 'fastgo')->fastgoAddress();
            $data                = array();
            $data['mobile']      = $v['tel'] != '' ? $v['tel'] : $fastgoAddress['mobile'];
            $data['address']     = $v['address'] != '' ? $v['address'] : $fastgoAddress['address'];
            $data['name']        = $v['leader'] != '' ? $v['leader'] : $fastgoAddress['name'];
            $data['category_id'] = $v['siteno'];

            $depotId = table('WarehouseInfo')->where('category_id', $v['siteno'])->field('id')->find('one');
            if ($depotId) {
                table('WarehouseInfo')->where('id', $depotId)->save($data);
            } else {
                table('WarehouseInfo')->add($data);
            }

            //更新广告图分类
            $map          = array();
            $map['title'] = $v['name'];

            $bannerId = table('Banner')->where($map)->field('id')->find('one');
            if (!$bannerId) {
                $result = table('Banner')->add(array('title' => $v['name']));
                if (!$result) {
                    return array('status' => false, 'msg' => '广告图更新失败');
                }
            }

            //更新网点
            foreach ($v['items'] as $kk => $vv) {

                $data             = array();
                $data['name']     = $data['name_en']     = $data['name_jp']     = $vv['name'];
                $data['bname']    = $vv['alias'];
                $data['bname_2']  = $vv['siteno'];
                $data['parentid'] = $oneId;

                $twoId = table('Category')->where('bname_2', $vv['siteno'])->field('id')->find('one');
                if ($twoId) {
                    table('Category')->where('id', $twoId)->save($data);
                } else {
                    $twoId = table('Category')->add($data);
                }

                if (!$twoId || !$result) {
                    return array('status' => false, 'msg' => '更新处理中心失败');
                }

            }
        }

        return array('status' => true, 'msg' => '更新成功');
    }

    /**
     * 申请运单号
     * @date   2018-01-18T15:21:09+0800
     * @author ChenMingjiang
     * @param  integer                  $userType [1普通用户 2网点用户]
     * @param  string                   $cid      [所属处理中心]
     * @param  string                   $shopUid  [网点uid]
     * @return [type]                             [description]
     */
    public function createOrderSn($shopUid = '', $userType = 1, $cid = '')
    {
        if (!in_array($userType, array(1, 2))) {
            return array('status' => false, 'msg' => '创建运单号,用户参数错误');
        }

        //获取处理中心下的直营网点Uid
        if ($userType == 1) {
            $map = array();

            $map['cid']  = $cid;
            $map['sign'] = 2;
            $shopUid     = table('UserShop')->where('city_id', $cid)->field('uid')->find('one');

            if (!$shopUid) {
                return array('status' => false, 'msg' => '未查询到相关直营店铺');
            }
        }

        //查询是否有多余运单号
        $map           = array();
        $map['uid']    = $shopUid;
        $map['is_use'] = 0;

        $count = table('PondOrders')->where($map)->count();

        //如果剩余运单号少于500个者申请新的运单号
        if ($count < 500) {
            $url  = self::$baseUrl . '/api/express/getmailnos/500';
            $data = response($url, 'GET', array(), self::$header);
            if ($data['code'] != 0000 || !isset($data['code'])) {
                return array('status' => false, 'msg' => '申请新的运单号失败了');
            }

            //保存新运单号
            $orderSnArray = explode(',', $data['result']);
            $data         = array();
            foreach ($orderSnArray as $key => $value) {
                $data[] = array('uid' => $shopUid, 'order_sn' => $value, 'created' => TIME);
            }

            $result = table('PondOrders')->addAll($data);
        }

        //取出一个新订单
        //查询是否有多余运单号
        $map           = array();
        $map['uid']    = $shopUid;
        $map['is_use'] = 0;
        $pond          = table('PondOrders')->where($map)->field('id,order_sn')->find();
        if (!$pond) {
            return array('status' => false, 'msg' => '获取运单号失败');
        }

        //关闭该订单
        $result = table('PondOrders')->where('id', $pond['id'])->save('is_use', 1);
        if (!$result) {
            return array('status' => false, 'msg' => '订单关闭异常了');
        }

        return array('status' => true, 'msg' => '创建成功', 'data' => $pond['order_sn']);
    }

    /** fasto物流查询 */
    public function getFastgoRoute($orderSn)
    {
        if (!$orderSn) {
            return array('status' => false, 'msg' => '请输入运单号');
        }

        $url  = self::$baseUrl . '/api/express/getroute/' . $orderSn;
        $data = response($url, 'GET', array(), self::$header);

        if ($data['code'] != 0000 || !isset($data['code'])) {
            return array('status' => false, 'msg' => '暂无相关记录');
        }

        $data['result']['deliverystatus'] = (int) $data['result']['deliverystatus'];

        return array('status' => true, 'msg' => '数据获取成功', 'data' => $data['result']);
    }

    /** 物流公司查询 */
    public function getLogisticsRoute($orderSn, $company = '')
    {
        if (!$orderSn) {
            return array('status' => false, 'msg' => '请输入物流单号');
        }

        $url  = self::$baseUrl . '/api/express/searchroute?no=' . $orderSn . '&type=' . $company;
        $data = response($url, 'GET', array(), self::$header);

        if ($data['code'] != 0000 || !isset($data['code'])) {
            $data['list'] = array();
            return array('status' => true, 'msg' => '暂无相关记录', 'data' => $data);
        }

        $data['result']['deliverystatus'] = (int) $data['result']['deliverystatus'];

        return array('status' => true, 'msg' => '数据获取成功', 'data' => $data['result']);
    }

    /** 获取预估价格 */
    public function getLogisticsPrice($country, $weight, $debug = false)
    {
        if (!$country || !$weight) {
            return array('status' => false, 'msg' => '预估价格参数错误');
        }

        $url = self::$baseUrl . '/api/express/calculatetransfee';

        $param['country'] = $country;
        $param['weight']  = $weight * 100; //单位g

        $result = response($url, 'POST', json_encode($param), self::$header);
        if ($debug) {
            print_r('——————————Url:' . $url . PHP_EOL);
            print_r($result);
            die;
        }

        if ($result['code'] != 0000 || !isset($result['code'])) {
            $data['list'] = array();
            return array('status' => true, 'msg' => '暂无相关记录', 'data' => $data);
        }

        $data['list'] = $result['result'];

        return array('status' => true, 'msg' => '数据获取成功', 'data' => $data);

    }

    /** 增加订单 */
    public function addOrders($orderSn)
    {
        if (!$orderSn) {
            return array('status' => false, 'msg' => '请输入物流单号');
        }

        $url = self::$baseUrl . '/api/express/createorder';

        $orders        = table('Orders')->where('order_sn', $orderSn)->find();
        $logistics     = table('Logistics')->where('order_sn', $orderSn)->find();
        $ordersPackage = table('ordersPackage')->where('order_sn', $orderSn)->find('array');

        foreach ($ordersPackage as $key => $value) {
            $goods[$key]['GoodName']    = $value['name'];
            $goods[$key]['GoodBrand']   = $value['brand'];
            $goods[$key]['GoodSpec']    = $value['spec'];
            $goods[$key]['Count']       = $value['num'];
            $goods[$key]['SinglePrice'] = $value['price'];
            $goods[$key]['TotalPrice']  = $value['account'];
            $goods[$key]['Unit']        = $orders['unit'];
        }

        $data['FastgoId'] = $orderSn;

        $data['SendDate']        = date('Y-m-d H:i:s', $orders['created']);
        $data['SenderName']      = $logistics['name'];
        $data['SenderPhone']     = $logistics['mobile'];
        $data['SenderAddress']   = $logistics['address'];
        $data['ReceiverName']    = $logistics['logistics_name'];
        $data['ReceiverPhone']   = $logistics['logistics_mobile'];
        $data['ReceiverCardNo']  = $logistics['logistics_code'];
        $data['Province']        = $logistics['logistics_province'];
        $data['City']            = $logistics['logistics_city'];
        $data['Region']          = $logistics['logistics_area'];
        $data['PostCode']        = $logistics['logistics_zip_code'];
        $data['ReceiverAddress'] = $logistics['logistics_address'];
        $data['Length']          = $logistics['length'];
        $data['Breadth']         = $logistics['breadth'];
        $data['Height']          = $logistics['height'];
        $data['Volume']          = $logistics['volume'];
        $data['VolumeWeight']    = $logistics['volume_weight'];
        $data['RealWeight']      = $logistics['real_weight'];
        $data['FeeWeight']       = $logistics['fee_weight'];
        $data['DeclarePrice']    = $logistics['declared_price'];
        $data['InsurancePrice']  = $logistics['aegis_price'];

        $data['TransportFee']      = $orders['acount_original'];
        $data['PackageTax']        = $orders['tax'];
        $data['PackageTotalPrice'] = 0;
        $data['ProxyTax']          = 0.00;
        $data['PackType']          = '';
        $data['GoodsType']         = '食品';

        $data['Goods'] = $goods;

        $result = response($url, 'POST', json_encode($data), self::$header);

        if ($result['code'] != 0000 || !isset($result['code'])) {
            $data['list'] = array();
            return array('status' => false, 'msg' => '同步失败');
        } elseif ($result['code'] == 0003 || $result['code'] == 0006) {
            return array('status' => true, 'msg' => '已推送过');
        }

        return array('status' => true, 'msg' => '同步成功');

    }

    /**
     * [本地直邮状态更改]
     * @date   2018-01-24T17:39:07+0800
     * @author ChenMingjiang
     * @param  [type]                   $param [description]
     * @param  integer                  $type  [01：到件，02：揽件，03：入口，06：上板]
     * @return [type]                          [description]
     */
    public function updateOrders($param, $type = 0)
    {
        $url = self::$baseUrl . '/api/express/pushbillstatus/' . $type;

        $data['BillId']     = $param['order_sn'];
        $data['AddedOn']    = date('Y-m-d H:i:s', TIME);
        $data['ScanSite']   = '';
        $data['ScanSiteNo'] = $param['cid'];
        $data['ScanUser']   = $param['nickname'];

        $result = response($url, 'POST', json_encode($data), self::$header);

        if ($result['code'] != 0000 || !isset($result['code'])) {
            $data['list'] = array();
            return array('status' => false, 'msg' => '同步状态失败');
        }

        return array('status' => true, 'msg' => '同步状态成功');

    }

}
