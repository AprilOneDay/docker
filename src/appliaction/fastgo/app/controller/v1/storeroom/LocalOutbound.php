<?php
/**
 * 本地直邮出库模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class LocalOutbound extends Init
{
    /** 待出库信息 */
    public function lists()
    {

        $batchSn = get('batch_sn', 'text', '');

        $lt  = table('Logistics')->tableName();
        $olt = table('OrdersLog')->tableName();

        if ($batchSn) {
            $result = $this->add();

        } else {
            $map           = array();
            $map['uid']    = $this->uid;
            $map['is_new'] = 1;
            $batchSn       = table('FastgoUserBatch')->where($map)->field('batch_Sn')->find('one');
        }

        if ($batchSn) {
            $map                    = array();
            $map[$lt . '.batch_Sn'] = $batchSn;
            $map[$olt . '.type']    = 7;
            $map[$olt . '.is_new']  = 1;
            $field                  = "$lt.batch_sn,$lt.position_sn,$lt.order_sn,$lt.fee_weight,$lt.outbound_transport_sn";
            $list                   = table('Logistics')->join($olt, "$olt.order_sn = $lt.order_sn")->where($map)->field($field)->find('array');

            foreach ($list as $key => $value) {
                $value['status']                               = dao('OrdersLog')->getNewStatus($value['order_sn']);
                $tmpList[$value['position_sn']]['position_sn'] = $value['position_sn'];
                $tmpList[$value['position_sn']]['list'][]      = $value;
            }
        }

        $data['batch_sn'] = $batchSn;
        $data['list']     = $tmpList ? array_values($tmpList) : array();

        $this->appReturn(array('data' => $data));
    }

    /** 扫描功能 */
    public function scan()
    {
        $batchSn    = get('batch_sn', 'text', '');
        $positionSn = get('position_sn', 'text', '');
        $orderSn    = get('order_sn', 'text', '');

        $map               = array();
        $map['order_sn']   = $orderSn;
        $map['del_status'] = 0;

        $logistics = table('Logistics')->where($map)->field('batch_sn,position_sn')->find();
        if (!$logistics) {
            $this->appReturn(array('status' => false, 'msg' => '包裹异常 用户已删除'));
        }

        if ($logistics['batch_sn'] != $batchSn) {
            $this->appReturn(array('status' => false, 'msg' => '非本批次,该包裹批次号：' . $logistics['batch_sn']));
        }

        if ($logistics['position_sn'] != $positionSn) {
            $this->appReturn(array('status' => false, 'msg' => '非本托盘,该包裹托盘:' . $logistics['position_sn']));
        }

        $this->appReturn(array('status' => true, 'msg' => '正常宝包裹'));
    }

    /** 完成出库操作 */
    public function update()
    {
        $orderSnText = post('order_sn', 'text', '');
        if (!$orderSnText) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $orderSnArray = (array) explode(',', $orderSnText);

        foreach ($orderSnArray as $key => $value) {
            $map               = array();
            $map['order_sn']   = $value;
            $map['del_status'] = 0;

            $logistics = table('Logistics')->where($map)->field('id')->find();
            if (!$logistics) {
                $this->appReturn(array('status' => false, 'msg' => $value['order_sn'] . '包裹不存在'));
            }

            $status = dao('OrdersLog')->getNewStatus($value);
            if (!$status == 7) {
                $this->appReturn(array('status' => false, 'msg' => '运单' . $value['order_sn'] . '不可操作了'));
            }
        }

        //增加操作记录
        $result = dao('OrdersLog')->add($this->uid, $orderSnArray, 8);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }
        $this->appReturn(array('msg' => '操作成功'));
    }

    public function add()
    {
        $batchSn = get('batch_sn', 'text', '');
        if (!$batchSn) {
            $this->appReturn(array('status' => false, 'msg' => '请输入批次号'));
        }

        $map        = array();
        $map['uid'] = $this->uid;

        $is = table('FastgoUserBatch')->where($map)->save('is_new', 0);

        $data['batch_sn'] = $batchSn;
        $data['uid']      = $this->uid;
        $data['is_new']   = 1;

        $result = table('FastgoUserBatch')->add($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        return true;
    }
}
