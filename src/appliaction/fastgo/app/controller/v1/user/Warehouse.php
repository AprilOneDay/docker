<?php
/**
 * 小仓库
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Warehouse extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    public function lists()
    {
        $map['uid'] = $this->uid;

        $list = table('UserWarehouse')->where($map)->find('array');

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $data = table('UserWarehouse')->where($map)->find();
        $this->appReturn(array('data' => $data));
    }

    public function editPost()
    {
        $id = post('id', 'intval', 0);

        $data['name']     = post('name', 'text', '');
        $data['category'] = post('category', 'text', '');
        $data['brand']    = post('brand', 'text', '');
        $data['spec']     = post('spec', 'floatval', 0.000);
        $data['num']      = post('num', 'intval', 0);
        $data['price']    = post('price', 'floatval', 0.00);

        if (!$data['name']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写商品名称'));
        }

        if (!$data['category']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写分类'));
        }

        if (!$data['brand']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写品牌'));
        }

        if (!$data['spec']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写规格'));
        }

        if (!$data['price']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写单价'));
        }

        if (!$id) {
            $data['uid']     = $this->uid;
            $data['created'] = TIME;

            $result = table('UserWarehouse')->add($data);
        } else {
            $result = table('UserWarehouse')->where('id', $id)->save($data);
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后尝试'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    public function del()
    {
        $id = post('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['id']  = $id;
        $map['uid'] = $this->uid;

        $result = table('UserWarehouse')->where($map)->delete();

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后尝试'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));
    }
}
