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
        $param['field']        = get('field', 'text', 'order_sn');
        $param['keyword']      = get('keyword', 'text', '');
        $param['type']         = get('type', 'intval', 1);
        $param['origin']       = get('origin', 'text', '');
        $param['order_status'] = get('order_status', 'text', '');
        $param['status']       = get('status', 'text', '');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($param['pageNo'] - 1), 0) * $pageSize;

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
            $seller['nickname']   = $value['type'] == 1 ? dao('User')->getNickname($value['seller_uid']) : dao('User')->getShopName($value['seller_uid']);
            $list[$key]['seller'] = $seller;
            $user['nickname']     = dao('User')->getNickname($value['uid']);
            $list[$key]['user']   = $user;
        }

        $other = array(
            'typeCopy'        => getVar('type', 'admin.orders'),
            'originCopy'      => getVar('origin', 'admin.orders'),
            'orderStatusCopy' => array('1' => '待确认', '2' => '待完成', '3' => '已完成', '4' => '已评价'),
            'statusCopy'      => array('1' => '审核通过', '0' => '代审核', '2' => '另设时间', '3' => '直接拒绝'),
        );

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }
}
