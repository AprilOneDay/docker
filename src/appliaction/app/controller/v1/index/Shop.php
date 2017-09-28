<?php
/**
 * 店铺信息模块
 */
namespace app\app\controller\v1\index;

use app\app\controller;

class Shop extends \app\app\controller\Init
{
    public function index()
    {
        $uid      = get('uid', 'intval', 0);
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        if (!$uid) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid'] = $uid;

        $shop = table('UserShop')->field('avatar,address,woker_time,name,ablum,status')->where($map)->find();

        if (!$shop) {
            $this->appReturn(array('status' => false, 'msg' => '店铺信息不存在'));
        }
        $shop['avatar'] = $this->appImg($shop['avatar'], 'avatar');
        $shop['ablum']  = $this->appImgArray($shop['ablum'], 'shop');

        if (!$shop['status']) {
            $this->appReturn(array('status' => false, 'msg' => '店铺暂停营业,敬请期待'));
        }

        $map['status'] = 1;

        $list = table('GoodsCar')->where($map)->order('id desc')->field('is_lease,id,thumb,title,produce_time,mileage,price')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $list[$key]['title'] = "【转lease】" . $value['title'];
            }

            $list[$key]['price']   = dao('Number')->price($value['price']);
            $list[$key]['mileage'] = $value['mileage'] . '万公里';
            $list[$key]['thumb']   = $this->appImgArray($value['thumb'], 'car');
        }

        $data         = $shop;
        $data['list'] = $list ? $list : array();

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));

    }

    public function service()
    {
        $uid      = get('uid', 'intval', 0);
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        if (!$uid) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid'] = $uid;

        $shop = table('UserShop')->field('avatar,address,woker_time,name,ablum,status')->where($map)->find();

        if (!$shop) {
            $this->appReturn(array('status' => false, 'msg' => '店铺信息不存在'));
        }
        $shop['avatar'] = $this->appImg($shop['avatar'], 'avatar');
        $shop['ablum']  = $this->appImgArray($shop['ablum'], 'shop');

        if (!$shop['status']) {
            $this->appReturn(array('status' => false, 'msg' => '店铺暂停营业,敬请期待'));
        }

        $map['status'] = 1;

        $list = table('GoodsService')->where($map)->order('id desc')->field('id,price,ablum,description,title,orders')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['ablum'] = $this->appImgArray($value['ablum'], 'car');
        }

        $data         = $shop;
        $data['list'] = $list ? $list : array();

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));

    }
}
