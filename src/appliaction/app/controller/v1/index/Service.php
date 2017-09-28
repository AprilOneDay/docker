<?php
/**
 * 汽车服务信息模块
 */
namespace app\app\controller\v1\index;

use app\app\controller;

class Service extends \app\app\controller\Init
{
    public function lists()
    {
        $param['is_recommend'] = get('is_recommend', 'text', '');
        $param['category']     = get('category', 'intval', 0);

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['status']    = 1;
        $map['is_ide']    = 1;
        $map['goods_num'] = array('>', 0);

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['category']) {
            $map['category'] = array('like', '%' . $param['category'] . '%');
        }

        $orderby = 'id desc';

        $list = table('UserShop')->where($map)->order($orderby)->limit($offer, $pageSize)->field('name,uid')->find('array');
        foreach ($list as $key => $value) {
            $goods = table('GoodsService')->where(array('uid' => $value['uid'], 'status' => 1))->field('id,price,thumb,title,orders')->limit(3)->find('array');
            foreach ($goods as $k => $v) {
                $goods[$k]['thumb'] = $this->appImg($v['thumb'], 'car');
            }
            $list[$key]['list'] = $goods ? $goods : array();
        }

        $data['param'] = $param;
        $data['list']  = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    public function detail()
    {
        $id = get('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $data = table('GoodsService')->where('id', $id)->find();
        if (!$data) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        if ($data['status'] != 1) {
            $this->appReturn(array('status' => false, 'msg' => '服务已下架'));
        }

        $data['thumb']                = $this->appImg($data['thumb'], 'car');
        $data['ablum']                = $this->appImgArray($data['ablum'], 'car');
        $data['shop']                 = table('UserShop')->where('uid', $data['uid'])->field('name,uid,credit_level')->find();
        $data['shop']['credit_level'] = dao('User')->getShopCredit($data['shop']['credit_level']);
        $this->appReturn(array('data' => $data));
    }
}
