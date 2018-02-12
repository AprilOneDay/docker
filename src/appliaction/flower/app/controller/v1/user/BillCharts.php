<?php
/**
 * 统计流水
 */
namespace app\flower\app\controller\v1\user;

use app\app\controller;
use app\flower\app\controller\v1\WeixinSmallInit;

class BillCharts extends WeixinSmallInit
{
    public function lists()
    {
        //0月统计
        $dateType = get('date_type', 'intval', 0);
        $time     = get('type', 'time', 0);
        $type     = get('type', 'text', '');

        $time = $time ? $time : TIME;

        switch ($type) {
            case '0':
                $beginTime = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
                $endTime   = mktime(23, 59, 59, date('m', $time), date('t', $time), date('Y', $time));
                break;

            default:
                $beginTime = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
                $endTime   = mktime(23, 59, 59, date('m', $time), date('t', $time), date('Y', $time));
                break;
        }

        $map              = array();
        $map['family_sn'] = $this->familySn;
        $map['created']   = array('between', $beginTime, $endTime);

        if ($type) {
            $map['type'] = $type;
        }

        $field = "SUM(money) as money,sign,type";
        $list  = table('BillLog')->where($map)->grou('sign')->field($field)->find('array');

        if ($list) {
            foreach ($list as $key => $value) {
                $list[$key]['title'] = dao('Category')->getName($value['sign']);
            }
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }
}
