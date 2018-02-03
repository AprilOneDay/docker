<?php
/**
 * 订单模块
 */
namespace app\fastgo\tools\dao;

class Orders
{
    /** 创建运单号 */
    public function createOrderSn()
    {
        $url    = 'http://47.100.5.66/api/outside/getmailnos/1';
        $result = file_get_contents($url);
        $result = json_decode($result, true);
        if ($result['code'] == '0000') {
            return $result['result'];
        }

        return false;
    }

    /** fastgo默认发货地址 */
    public function fastgoAddress()
    {
        $data = array(
            'name'    => 'Fastgo',
            'mobile'  => '61731032888',
            'address' => 'UNIT 4 & 5,  33 STOCKWELL PLACE, ARCHERFIELD, QUEENSLAND  4108',
        );

        return $data;
    }

    /**
     * 订单列表
     * @date   2018-01-08T15:13:33+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid      [description]
     * @param  [type]                   $param    [description]
     * @param  integer                  $pageNo   [description]
     * @param  integer                  $pageSize [description]
     * @return [type]                             [description]
     */
    public function getList($uid, $param, $pageNo = 1, $pageSize = 10)
    {
        $ot = table('Orders')->tableName();
        $lt = table('Logistics')->tableName();

        switch ($param['status']) {
            case '0': //待入库
                $ordersLogType = '1,2,3,4,5,6,9';
                break;
            case '1': //预报包裹入库
                $ordersLogType = '7,10';
                break;
            case '2': //已出库/出库包裹
                $ordersLogType = '8,15';
                break;
            case '3': //问题包裹
                $ordersLogType = '16,17,18,19,20,21,22,23';
                break;
            case '4': //包裹预报/本地直邮
                $ordersLogType = '1,2,9';
                break;
            case '5': //待揽收
                return $this->getList_5($uid, $param, $pageNo, $pageSize);
                break;
            case '6': //待报价
                $ordersLogType = '11,12';
                break;
            case '7': //代付款
                $ordersLogType = '13';
                break;
            case '8': //已付款
                $ordersLogType = '14';
                break;
            case '9': //退件
                $ordersLogType = '18,17,20';
                break;
            case '10': //国内派送
                $ordersLogType = '24';
                break;
            default:
                $ordersLogType = '0';
                break;
        }

        if ($param['start_time'] || $param['end_time']) {
            $map[$lt . '.created'] = array('between', $param['start_time'], $param['end_time']);
        }

        $map[$lt . '.type']       = array('in', $param['type']);
        $map[$ot . '.uid']        = $uid;
        $map[$ot . '.del_status'] = 0;

        //合并搜索
        if ($param['keyword']) {
            $map['_string'] = "concat($lt.logistics_mobile,$lt.logistics_name,$lt.logistics_code) like '%$param[keyword]%'";
        }

        $field = "lt.type,lt.order_sn,lt.outbound_transport_sn,lt.outbound_transport_id,lt.logistics_name,lt.logistics_address,lt.issue_album,lt.user_ablum,ot.created";

        $list = dao('OrdersLog')->getOrdersList($map, $ordersLogType, $pageNo, $pageSize, $field, 'logistics');

        foreach ($list as $key => $value) {
            $tips = '无运单号';

            //状态文案
            $map             = array();
            $map['order_sn'] = $value['order_sn'];
            $map['is_new']   = 1;
            $ordersLog       = table('OrdersLog')->where($map)->find();

            //获取订单type
            $list[$key]['status_type'] = (int) $ordersLog['type'];
            $list[$key]['title']       = $value['order_sn'];

            if ($ordersLog['type'] == 1 || $ordersLog['type'] == 9) {
                $list[$key]['title'] = $tips;
            }

            $list[$key]['package_status'] = 1;

            $orders          = table('Orders')->where('order_sn', $value['order_sn'])->field('acount_original,is_pay')->find();
            $order['status'] = $ordersLog == 2 ? 1 : 0; //判断是否打包完成

            $goodsList = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            //判断状态
            $list[$key]['issue_status'] = $this->changeIssueStatus($ordersLog);

            //如果包裹商品有不存在的 则状态为 false
            foreach ($goodsList as $k => $v) {
                if (!$v['status']) {
                    $list[$key]['package_status'] = 0;
                }
            }

            //获取转运公司信息
            $list[$key]['logisticsCompany'] = array();
            if ($value['outbound_transport_id']) {
                $logisticsCompany   = dao('Category')->getName($value['outbound_transport_id'], $this->lg);
                $logisticsCompanyNC = dao('Category')->getBname($value['outbound_transport_id']);

                $list[$key]['logisticsCompany'] = array('name' => $logisticsCompany, 'bname' => $logisticsCompanyNC, 'transport_sn' => $value['transport_sn']);
            }

            //是否上传包裹照
            $list[$key]['is_update_ablum'] = $value['user_ablum'] ? 1 : 0;
            //是否上传问题处理图片
            $list[$key]['is_issue_album'] = $value['issue_album'] ? 1 : 0;

            $statusCopy = $this->ordersStatusCopy($ordersLog);

            $list[$key]['status_copy']      = $statusCopy['msg_copy'];
            $list[$key]['status_time_copy'] = $statusCopy['time_copy'];

            $list[$key]['goodsList'] = $goodsList ? $goodsList : array();
            $list[$key]['orders']    = $orders;
        }

        $data['list'] = $list ? $list : array();

        return $data;
    }

    //待揽收合并订单
    public function getList_5($uid, $param, $pageNo, $pageSize = 99)
    {
        $ot  = table('Orders')->tableName();
        $lt  = table('Logistics')->tableName();
        $olt = table('OrdersLog')->tableName();

        $map[$lt . '.type']       = 1;
        $map[$ot . '.seller_uid'] = $uid;
        $map[$ot . '.del_status'] = 0;
        $map[$ot . '.merge_sn']   = array('!=', '');
        $map[$olt . '.type']      = array('in', '3,4,5');
        $map[$olt . '.is_new']    = 1;

        $field = "$lt.logistics_name,GROUP_CONCAT($lt.order_sn) as order_sn,merge_sn,count($lt.order_sn) as num,$lt.uid,$lt.logistics_mobile as mobile";

        $list = table('Orders')->join($lt, "$lt.order_sn = $ot.order_sn", 'right')->join($olt, "$olt.order_sn = $ot.order_sn", 'left')->where($map)->group($ot . '.merge_sn')->field($field)->find('array');

        $data['list'] = $list ? $list : array();

        return $data;
    }

    public function changeIssueStatus($param)
    {
        $status = $param['type'];

        if (in_array($status, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15))) {
            $status = 1;
        } else {
            $status = $status;
        }

        return $status;
    }

    /** 获取订单状态文案 */
    private function ordersStatusCopy($data)
    {
        $type = $data['type'];
        $time = $data['created'];

        $type = $data['type'];
        $time = $data['created'];

        $time = date('Y-m-d H:i:s', $time);

        switch ($type) {
            case '1':
            case '2':
            case '3':
                $msgCopy = '预报包裹';
                break;
            case '4':
                $msgCopy = '包裹已到件';
                break;
            case '5':
                $msgCopy = '网点已揽收';
                break;
            case '6':
                $msgCopy = '揽货员已揽件';
                break;
            case '7':
                $msgCopy = '包裹已入库';
                break;
            case '8':
                $msgCopy = '包裹已出库';
                break;
            case '9':
                $msgCopy = '预报包裹';
                break;
            case '10':
                $msgCopy = '包裹已入库';
                break;
            case '11':
                $msgCopy = '等待打包';
                break;
            case '12':
                $msgCopy = '等待报价';
                break;
            case '13':
                $msgCopy = '代付款';
                break;
            case '14':
                $msgCopy = '待出库';
                break;
            case '15':
                $msgCopy = '已出库';
                break;
            case '16':
                $msgCopy = '退件完成';
                break;
            case '17':
                $msgCopy = '始发地退件-超标';
                break;
            case '18':
                $msgCopy = '始发地退件';
                break;
            case '19':
                $msgCopy = '海关/商检查验';
                break;
            case '20':
                $msgCopy = '拒收/退件';
                break;
            case '21':
                $msgCopy = '时未更新';
                break;
            case '22':
                $msgCopy = '快递柜/驿站超时未取';
                break;
            case '23':
                $msgCopy = '破损/丢件';
                break;
            case '24':
                $msgCopy = '国内派件';
                break;
            default:
                # code...
                break;
        }

        return array('msg_copy' => $msgCopy, 'time_copy' => $time);
    }

    /** 余额自动扣款 */
    public function autoPay($uid, $orderSn)
    {
        $user = dao('User')->getInfo($uid, 'is_auto_moeny,money,money_aud');
        if (!$user['is_auto_moeny']) {
            return false;
        }

        $map               = array();
        $map['order_sn']   = $orderSn;
        $map['is_pay']     = 0;
        $map['del_status'] = 0;

        $orders = table('Orders')->where($map)->field('type,order_sn,acount,acount_original,unit')->find();
        if (!$orders) {
            return false;
        }

        if ($orders['uid'] != $uid) {
            return false;
        }

        if ($orders['unit'] == 'CNY' && $user['money'] < $orders['acount']) {
            return false;
        }

        if ($orders['unit'] == 'AUD' && $user['money_aud'] < $orders['acount']) {
            return false;
        }

        //生成财务记录
        $params['type']     = $orders['type'] == 4 ? 3 : 4;
        $params['money']    = $orders['acount'];
        $params['unit']     = $orders['unit'];
        $params['order_sn'] = $orders['order_sn'];
        $params['pay_type'] = 0;
        $params['is_pay']   = 1;
        $params['title']    = '余额支付';
        $params['uid']      = $uid;

        $result = dao('Finance')->addPay($params);
        if (!$result['status']) {
            return false;
        }

        //扣除用户余额
        $data         = array();
        $money        = $orders['unit'] == 'CNY' ? 'money' : 'money_aud';
        $data[$money] = array('less', $orders['account']);
        $result       = table('User')->where('uid', $uid)->save($data);

        //国际运单生成订单操作记录
        if ($orders['type'] == 5) {
            $result = dao('OrdersLog')->add($uid, $orderSn, 14);
            if (!$result) {
                return false;
            }
        }

        //更改订单状态
        $data           = array();
        $data['is_pay'] = 1;

        $result = table('Orders')->where('order_sn', $orderSn)->save($data);
        if (!$result) {
            return false;
        }

        return true;
    }
}
