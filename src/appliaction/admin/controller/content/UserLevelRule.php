<?php
/**
 * 会员等级模块
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class UserLevelRule extends Init
{
    public function index()
    {
        $map  = array();
        $list = table('UserLevelRule')->where($map)->find('array');
        $this->assign('list', $list);
        $this->show();
    }

    /** 保存操作 */
    public function indexPost()
    {
        $name      = post('name');
        $postValue = post('value');

        foreach ($name as $key => $value) {
            $data          = array();
            $data['name']  = $value;
            $data['value'] = $postValue[$key];
            $result        = table('UserLevelRule')->where('id', $key)->save($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '保存失败'));
            }
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '保存成功'));
    }

    public function edit()
    {
        $this->show();
    }

    /** 添加操作 */
    public function editPost()
    {
        $data = post('info');

        if (!$data) {
            $this->ajaxReturn(array('status' => false, 'msg' => '参数请填写完成'));
        }

        $result = table('UserLevelRule')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
        }

        $this->ajaxReturn(array('msg' => '添加成功'));
    }

    /** 删除 */
    public function del()
    {
        $id     = post('id', 'intval', 0);
        $result = table('UserLevelRule')->where('id', $id)->save('del_status', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));
    }

    /** 赠送礼品 */
    public function sendGift()
    {
        $id = get('id', 'intval', 0);

        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;

        if ($param['status']) {
            $map['status'] = $param['status'];
        }

        if ($param['type'] != '') {
            $map['type'] = $param['type'];
        }

        if ($param['status'] != '') {
            $map['status'] = $param['status'];
        }

        if ($param['category']) {
            $map['category'] = $param['category'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('instr', $param['keyword']);
            }
        }

        $list  = table('Coupon')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('Coupon')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $seller = dao('User')->getInfo($value['uid'], 'nickname,mobile');
            $seller = $seller ? $seller : array('nickname' => '系统配送', 'mobile' => 15923882847);

            $list[$key]['seller'] = $seller;
        }

        $checkValue = table('UserLevelRule')->where('id', $id)->field('coupon_gift')->find('one');

        $other = array(
            'categoryCopy' => dao('Category')->getList(19),
            'typeCopy'     => array(1 => '满减', 2 => '折扣'),
            'statusCopy'   => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('checkValue', $checkValue);
        $this->assign('other', $other);

        $this->show();
    }

    /** 增加抵扣卷礼物赠送 */
    public function sendGiftPost()
    {
        $id       = post('id', 'intval', 0);
        $couponId = post('coupon_id', 'intval', 0);
        $type     = post('type', 'intval', 0);

        $map = array();

        $map['id']  = $id;
        $couponGift = table('UserLevelRule')->where($map)->field('coupon_gift')->find('one');

        if ($couponGift === false) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $couponGift = (array) explode(',', $couponGift);
        $couponGift = array_filter($couponGift);

        //添加
        if ($type == 1) {
            $couponGift[] = $couponId;
        }
        //删除
        else {
            $couponGift = array_flip($couponGift);
            unset($couponGift[$couponId]);
            $couponGift = array_flip($couponGift);
        }

        $couponGift = array_unique($couponGift);

        $data                = array();
        $data['coupon_gift'] = implode(',', $couponGift);

        $result = table('UserLevelRule')->where('id', $id)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }
}
