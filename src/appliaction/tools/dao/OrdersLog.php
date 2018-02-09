<?php
/**
 * 文章相关信息
 */
namespace app\tools\dao;

class OrdersLog
{
    private static $table;

    public function add($uid, $orderSn, $type)
    {
        if (!$uid || !$orderSn || !$type) {
            return false;
        }

        $orderSnArray = is_array($orderSn) ? $orderSn : (array) $orderSn;

        foreach ($orderSnArray as $key => $value) {
            if ($value) {
                $data[] = array(
                    'uid'      => $uid,
                    'order_sn' => $value,
                    'type'     => $type,
                    'created'  => TIME,
                    'is_new'   => 1,
                );
            }
        }

        $map             = array();
        $map['order_sn'] = array('in', $orderSn);
        $map['is_new']   = 1;
        $result          = table('OrdersLog')->where($map)->save('is_new', 0);

        $result = table('OrdersLog')->addAll($data);
        if (!$result) {
            return false;
        }

        return true;

    }

    /** 获取订单最新状态 */
    public function getNewStatus($orderSn)
    {
        $map             = array();
        $map['order_sn'] = $orderSn;
        $map['is_new']   = 1;

        $status = (int) table('OrdersLog')->where($map)->field('type')->find('one');

        return $status;
    }

    /** 获取订单详情 */
    public function getOrdersList($mapValue, $type = 0, $pageNo, $pageSize, $field = '*', $tableArray = array())
    {
        //预处理异常订单状态
        $this->repairFieldIsNew();

        $offer = max($pageNo - 1) * $pageSize;

        $ot  = table('Orders')->tableName();
        $lt  = table('Logistics')->tableName();
        $olt = table('OrdersLog')->tableName();

        $map = array();
        foreach ($mapValue as $key => $value) {
            if (stripos('ot.', $key) === 0) {
                $key = str_replace('ot', $ot, $key);
            } elseif (stripos('lt.', $key) === 0) {
                $key = str_replace('lt', $lt, $key);
            }

            $map[$key] = $value;
        }

        $map[$olt . '.is_new'] = 1;
        if ($type) {
            $map[$olt . '.type'] = array('in', $type);
        }

        if ($field != '*') {
            $fieldValue = explode(',', $field);
            foreach ($fieldValue as $key) {
                if (stripos($key, 'ot.') === 0) {
                    $fieldArray[] = $ot . substr($key, 2, strlen($key));
                } elseif (stripos($key, 'lt.') === 0) {
                    $fieldArray[] = $lt . substr($key, 2, strlen($key));
                } else {
                    $fieldArray[] = $key;
                }
            }

            $field = implode(',', $fieldArray);
        }

        if (in_array('logistics', (array) $tableArray)) {
            $list = table('Orders')->join($olt, "$ot.order_sn = $olt.order_sn", 'left')->join($lt, "$ot.order_sn = $lt.order_sn", 'left')->where($map)->field($field)->order("$ot.created desc")->limit($offer, $pageSize)->find('array');

        } else {
            $list = table('Orders')->join($lt, "$ot.order_sn = $olt.order_sn", 'left')->where($map)->field($field)->limit($offer, $pageSize)->order("$ot.created desc")->find('array');
        }

        return $list;
    }

    /** 获取物流详情 */
    public function getLogisticsList($list, $map, $field = '*')
    {
        if (!$list) {
            return false;
        }

        foreach ($list as $key => $value) {
            $logistics               = table($map)->field($field)->find();
            $list[$key]['logistics'] = $logistics ? $logistics : array();
        }

        return $list;
    }

    /** 获取商品详情 */
    public function getOrdersData($list, $map, $field = '*')
    {
        if (!$list || !$list['type']) {
            return false;
        }

        foreach ($list as $key => $value) {
            $goods               = table('ordersPackage')->where($map)->field($field)->find('array');
            $list[$key]['goods'] = $goods ? $goods : array();
        }

        return $list;
    }

    /** 修复is_new */
    public function repairFieldIsNew($pageNo = 0, $pageSize = 100)
    {
        $offer = max(($pageNo - 1) * $pageSize, 0);

        $map['is_new'] = 1;

        //获取异常数据
        $list = table('OrdersLog')->where($map)->field("count(id) as num,order_sn,max(id) as id")->group('concat(is_new,order_sn) HAVING num > 1')->find('array');

        if ($list) {
            foreach ($list as $key => $value) {
                //关闭全部状态
                table('OrdersLog')->where('order_sn', $value['order_sn'])->save('is_new', 0);
                //开启最新状态
                table('OrdersLog')->where('id', $value['id'])->save('is_new', 1);
            }
        }

    }
}
