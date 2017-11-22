<?php
/**
 * 抵扣卷后后台模块管理
 */
namespace app\admin\controller\content;

use denha;

class Coupon extends \app\admin\controller\Init
{
    /**
     * 模板列表
     * @date   2017-10-25T10:45:18+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

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
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['seller'] = dao('User')->getInfo($value['uid'], 'nickname,mobile');
        }

        $other = array(
            'categoryCopy' => dao('Category')->getList(19),
            'typeCopy'     => array(1 => '满减', 2 => '折扣'),
            'statusCopy'   => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    /**
     * 抵扣券兑换规则列表
     * @date   2017-10-25T11:19:38+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function exchangeRuleList()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

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

        $list  = table('CouponExchangeRule')->where($map)->limit($offer, $pageSize)->order('sort asc')->find('array');
        $total = table('CouponExchangeRule')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        $other = array(
            'categoryCopy' => dao('Category')->getList(19),
            'typeCopy'     => array(1 => '满减', 2 => '折扣'),
            'statusCopy'   => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    /**
     * 抵扣卷兑换编辑提交
     * @date   2017-10-25T11:20:01+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function exchangeRuleEditPost()
    {
        $id = get('id', 'intval', 0);

        $data        = post('all');
        $data['ico'] = post('ico', 'img', '');

        if (!$data['category']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请选择分类'));
        }

        if (!$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '兑换名称'));
        }

        //添加规则
        if (!$id) {
            $isCategory = table('CouponExchangeRule')->where('category', $data['category'])->field('id')->find();
            if ($isCategory) {
                $this->ajaxReturn(array('status' => false, 'msg' => '每个分类规则只能存在一条记录'));
            }

            $result = table('CouponExchangeRule')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
            }

        } else {
            $result = table('CouponExchangeRule')->where('id', $id)->save($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '编辑失败'));
            }
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /**
     * 抵扣卷兑换编辑详情
     * @date   2017-10-25T11:20:01+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function exchangeRuleEdit()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $data = table('CouponExchangeRule')->where('id', $id)->find();
        } else {
            $data = array('status' => 1, 'sort' => 0, 'integral' => 0);
        }
        $other = array(
            'categoryCopy' => dao('Category')->getList(19),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);
        $this->show();
    }

}
