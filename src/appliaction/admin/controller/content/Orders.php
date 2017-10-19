<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

use denha;

class Orders extends \app\admin\controller\Init
{
    public function lists()
    {
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['type']) {
            $map['type'] = $param['type'];
        }

        if ($param['origin'] != '') {
            $map['origin'] = $param['origin'];
        }

        if ($param['order_status'] != '') {
            $map['order_status'] = $param['order_status'];
        }

        if ($param['status'] != '') {
            $map['status'] = $param['status'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'order_sn') {
                $map['order_sn'] = $param['keyword'];
            }
        }

        $field = 'id,type,uid,seller_uid,order_sn,account,order_status,status';
        $list  = table('Orders')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('Orders')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $seller               = dao('User')->getInfo($value['seller_uid'], 'nickname,type');
            $list[$key]['seller'] = $seller;
            $user['nickname']     = dao('User')->getNickname($value['uid']);
            $list[$key]['user']   = $user;
        }

        $other = array(
            'typeCopy'         => getVar('type', 'admin.orders'),
            'originCopy'       => getVar('origin', 'admin.orders'),
            'orderStatusCopy'  => array('1' => '待确认', '2' => '待完成', '3' => '已完成', '4' => '待评价', '5' => '已评价'),
            'statusCopy'       => array('1' => '审核通过', '0' => '代审核', '2' => '另设时间', '3' => '直接拒绝'),
            'isTempCopy'       => array('0' => '正常订单', '1' => '临时订单'),
            'isPercentageCopy' => array('0' => '未收取', '1' => '已收取'),
            'userTypeCopy'     => array('1' => '个人', '2' => '商家'),
        );

        $this->assign('commission', dao('Param')->getValue(1));
        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }

    public function detail()
    {
        $orderSn = get('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['order_sn'] = $orderSn;

        $result = dao('Orders')->detail($map);
        if (!$result['status']) {
            $this->appReturn($result);
        }

        $data = $result['data'];

        foreach ($data['goods'] as $key => $value) {
            $data['goods'][$key]['thumb'] = imgUrl($value['thumb'], 'car');
        }

        $other = array(
            'typeCopy'        => getVar('type', 'admin.orders'),
            'originCopy'      => getVar('origin', 'admin.orders'),
            'orderStatusCopy' => array('1' => '待确认', '2' => '待完成', '3' => '已完成', '4' => '已评价'),
            'statusCopy'      => array('1' => '审核通过', '0' => '代审核', '2' => '另设时间', '3' => '直接拒绝'),
            'isTempCopy'      => array('0' => '正常订单', '1' => '临时订单'),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);
        $this->show();
    }

    /**
     * 结算佣金
     * @date   2017-10-13T11:12:52+0800
     * @author ChenMingjiang
     */
    public function clearingCommission()
    {
        $orderSn = post('order_sn', 'text', '');
        if (!$orderSn) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $money = dao('Percentage')->getOnePercentage($orderSn);
        if (!$money) {
            $this->appReturn(array('status' => false, 'msg' => '佣金异常'));
        }

        //修改状态
        $result = table('Orders')->where('order_sn', $orderSn)->save('is_percentage', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '结算异常'));
        }

        $map['type']    = 1;
        $map['content'] = $orderSn;

        //增加财务记录
        dao('Finance')->add(1, $money, $orderSn, 1);

        $this->appReturn(array('msg' => '结算成功'));

    }
}
