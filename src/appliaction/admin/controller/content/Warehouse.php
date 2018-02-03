<?php
/**
 * 仓库模块管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class Warehouse extends Init
{
    public function lists()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('like', '%' . $param['keyword'] . '%');
            }
        }
        $list  = table('WarehouseInfo')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('WarehouseInfo')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        $abc = table('Column')->limit($offer, $pageSize)->find('array');

        foreach ($list as $key => $value) {
            $map            = array();
            $map['bname_2'] = array('in', "$value[country_id],$value[category_id]");

            $house = table('Category')->where($map)->field('name')->find('one', true);

            $list[$key]['house_name'] = $house[0] . ' ' . $house[1];
        }

        $other = array(
            'tag'             => getVar('tags', 'console.article'),
            'isShowCopy'      => array(0 => '未审核', 1 => '已审核'),
            'isRecommendCopy' => array(0 => '未推荐', 1 => '已推荐'),
            'columnListCopy'  => dao('Column', 'admin')->columnList($param['column_id'], $this->webType),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function editPost()
    {

        $id = get('id', 'intval', 0);

        $data['country_id']  = post('country_id', 'text', 0);
        $data['category_id'] = post('category_id', 'text', 0);
        $data['name']        = post('name', 'text', '');
        $data['mobile']      = post('mobile', 'text', '');
        $data['address']     = post('address', 'text', '');
        $data['zip_code']    = post('zip_code', 'text', '');

        if (!$data['category_id']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请选择仓库'));
        }

        if (!$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入收货人姓名'));
        }

        if (!$data['mobile']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入收货人电话'));
        }

        if (!$data['address']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入仓库地址'));
        }

        if (!$data['zip_code']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入邮政编码'));
        }

        if ($id) {
            $result = table('WarehouseInfo')->where(array('id' => $id))->save($data);
        } else {
            $result = table('WarehouseInfo')->add($data);
        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '提交失败'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功' . $data['avatar']));

    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        $data = table('WarehouseInfo')->where(array('id' => $id))->find();

        $other = array(
            'countryCopy' => dao('Category')->getListAllInfo(743),
            'houseCopy'   => dao('Category')->getListAllInfo($data['country_id']),
        );

        $this->assign('data', $data);
        $this->assign('other', $other);

        $this->show();

    }

    /** 仓库下拉联动 */
    public function getCategory()
    {
        $id = post('value', 'intval', 0);

        $id   = table('Category')->where('bname_2', $id)->id->find('one');
        $data = dao('Category')->getListAllInfo($id);

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功', 'data' => $data));
    }

}
