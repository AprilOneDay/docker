<?php
/**
 * 会员任务模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserTask extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');
    }

    /**
     * 任务完成时间列表
     * @date   2018-01-10T10:06:19+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $type = get('type', 'intval', 0);

        if ($type) {
            $map['type'] = $type;
        }

        $list = dao('OrdersLog')->getOrdersList($map, '6,8,10,15', 0, 999, $field, 'logistics');

        $list = table('Logistics')->where($map)->field('order_sn,created')->find('array');

        foreach ($list as $key => $value) {
            //入库时间
            $map             = array();
            $map['type']     = array('in', '6,10');
            $map['order_sn'] = $value['order_sn'];
            $storageTime     = (int) table('OrdersLog')->where($map)->field('created')->find('one');

            //出库时间
            $map             = array();
            $map['type']     = array('in', '8,15');
            $map['order_sn'] = $value['order_sn'];
            $outboundTime    = (int) table('OrdersLog')->where($map)->field('created')->find('one');

            $list[$key]['storage_time_copy']  = $storageTime > 0 ? dao('Time')->diffDate($value['created'], $storageTime, '1', $this->lg) : '';
            $list[$key]['outbound_time_copy'] = $outboundTime > 0 ? dao('Time')->diffDate($storageTime, $outboundTime, '1', $this->lg) : '';

        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

}
