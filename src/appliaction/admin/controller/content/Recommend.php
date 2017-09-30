<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

use denha;

class Recommend extends \app\admin\controller\Init
{
    public function car()
    {
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['status'] != '') {
            $map['status'] = $param['status'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'order_sn') {
                $map['order_sn'] = $param['keyword'];
            }
        }

        $field = '*';
        $list  = table('helpCar')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('helpCar')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {

            $user['nickname']   = dao('User')->getNickname($value['uid']);
            $list[$key]['user'] = $user;
        }

        $other = array(
            'statusCopy' => array('1' => '申请推荐', '3' => '推荐完成'),
        );

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }

    public function showCarList()
    {
        $id    = get('id', 'intval', 0);
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $checkValue = table('helpCar')->where('id', $id)->field('recommend_id')->find('one');
        $checkValue = $checkValue ? explode(',', $checkValue) : '';

        $data = dao('Car', 'admin')->lists($param, $pageNo, $pageSize);

        $other = array(
            'brandCopy'     => dao('Category')->getList(1),
            'recommendCopy' => array('1' => '推荐', '0' => '未推荐'),
            'urgencyCopy'   => array('1' => '是', '0' => '否'),
            'typeCopy'      => array('1' => '个人', '2' => '商家'),
            'statusCopy'    => array('1' => '上架', '0' => '下架'),
        );

        $this->assign('checkValue', $checkValue);
        $this->assign('id', $id);
        $this->assign('list', $data['list']);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $data['page']->loadConsole());
        $this->show();
    }

    public function addCarId()
    {
        $id      = get('id', 'intval', 0);
        $goodsId = post('id', 'intval');

        if (!$goodsId) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请勾选汽车'));
        }
        $goodsId = implode(',', $goodsId);

        $data['status']       = 3;
        $data['recommend_id'] = $goodsId;

        $uid = table('helpCar')->where('id', $id)->field('uid')->find('one');

        $reslut = table('helpCar')->where('id', $id)->save($data);
        if (!$reslut) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        //发送推送信息
        dao('JPush')->sendByRegId($uid, '推荐汽车', '推荐汽车完成', array('type' => 1, 'id' => $id));

        $this->ajaxReturn(array('msg' => '推荐完成'));
    }

    public function service()
    {
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['status'] != '') {
            $map['status'] = $param['status'];
        }

        if ($param['sign'] != '') {
            $map['sign'] = $param['sign'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'order_sn') {
                $map['order_sn'] = $param['keyword'];
            }
        }

        $field = '*';
        $list  = table('helpService')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('helpService')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {

            $user['nickname']   = dao('User')->getNickname($value['uid']);
            $list[$key]['user'] = $user;
        }

        $other = array(
            'signCopy'   => dao('Category')->getList(4),
            'statusCopy' => array('1' => '申请推荐', '3' => '推荐完成'),
        );

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }

    public function showShopList()
    {
        $id       = get('id', 'intval', 0);
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $data = dao('Shop', 'admin')->lists($param, $pageNo, $pageSize);

        $other = array(
            'categoryCopy' => getVar('tags', 'console.article'),
            'isIdeCopy'    => array(0 => '未认证', 1 => '已认证', 2 => '认证未通过'),
        );

        $checkValue = table('helpService')->where('id', $id)->field('recommend_id')->find('one');
        $checkValue = $checkValue ? explode(',', $checkValue) : '';

        $this->assign('checkValue', $checkValue);
        $this->assign('list', $data['list']);
        $this->assign('param', $param);
        $this->assign('pages', $data['page']->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function addServiceId()
    {
        $id      = get('id', 'intval', 0);
        $goodsId = post('id', 'intval');

        if (!$goodsId) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请勾选商家'));
        }
        $goodsId = implode(',', $goodsId);

        $data['status']       = 3;
        $data['recommend_id'] = $goodsId;

        $uid = table('helpService')->where('id', $id)->field('uid')->find('one');

        $reslut = table('helpService')->where('id', $id)->save($data);
        if (!$reslut) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        //发送推送信息
        dao('JPush')->sendByRegId($uid, '推荐服务', '推荐服务完成', array('type' => 2, 'id' => $id));

        $this->ajaxReturn(array('msg' => '推荐完成'));
    }
}
