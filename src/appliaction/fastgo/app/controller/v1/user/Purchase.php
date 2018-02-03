<?php
/**
 * 用户采购明细
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Purchase extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    public function lists()
    {
        $map['uid']  = $this->uid;
        $map['type'] = 4;

        $list = table('Orders')->where($map)->field('id,order_sn,tags')->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['status'] = 1;
            $list[$key]['name']   = $value['tags'] ? $value['tags'] : $value['order_sn'];
            $goodsList            = table('OrdersPackage')->where('order_sn', $value['order_sn'])->find('array');

            foreach ($goodsList as $k => $v) {
                $goodsList[$k]['weight'] = $v['spec'] * $v['num']; //商品重量
                $goodsList[$k]['stock']  = 0; //商品库存

                //小仓库库存
                if ($v['warehouse_id']) {
                    $goodsList[$k]['stock'] = table('UserWarehouse')->where('id', $v['warehouse_id'])->field('num')->find('one');
                }

                //如果存在商品未完成 则标记未完成
                if (!$v['status']) {
                    $list[$key]['status'] = 0;
                }

            }

            $list[$key]['goodsList'] = $goodsList;
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 订单标签 */
    public function changeOrdersTags()
    {
        $id   = post('id', 'intval', 0);
        $tags = post('tags', 'text', '');

        if (!$tags) {
            $this->appReturn(array('status' => false, 'msg' => '请输入名称'));
        }

        $map['uid']  = $this->uid;
        $map['type'] = 4;
        $map['id']   = $id;

        $isOrders = table('Orders')->where($map)->field('id')->find();
        if (!$isOrders) {
            $this->appReturn(array('status' => false, 'msg' => '订单不存在'));
        }

        $result = table('Orders')->where('id', $id)->save('tags', $tags);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '保存失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

    /** 标记已完成 */
    public function changeOrdersGoodsStatus()
    {
        $id      = post('orders_id', 'intval', 0);
        $goodsId = post('goods_id', 'intval', 0);

        $map['uid']  = $this->uid;
        $map['type'] = 4;
        $map['id']   = $id;

        $isOrders = table('Orders')->where($map)->field('id')->find();
        if (!$isOrders) {
            $this->appReturn(array('status' => false, 'msg' => '订单不存在'));
        }

        $result = table('OrdersPackage')->where('id', $goodsId)->save('status', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }
}
