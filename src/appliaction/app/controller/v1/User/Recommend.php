<?php
/**
 * 推荐模块管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Recommend extends \app\app\controller\Init
{

    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 推荐汽车信息
     * @date   2017-09-26T14:40:39+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function car()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['uid']        = $this->uid;

        $sign = getVar('helpType', 'app.service');
        $list = table('HelpCar')->field('id,brand,price,buy_time,mileage,description,created,status')->limit($offer, $pageSize)->order('id desc')->find('array');

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 推荐服务信息
     * @date   2017-09-26T14:40:39+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function service()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['uid']        = $this->uid;

        $sign = getVar('helpType', 'app.service');
        $list = table('HelpService')->field('id,sign,price,description,created,status')->limit($offer, $pageSize)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['sign_copy'] = $sign[$value['sign']];
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 推荐服务列表
     * @date   2017-10-09T09:04:03+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function serviceList()
    {
        $id       = get('id', 'intval', 0);
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['id']     = $id;
        $map['uid']    = $this->uid;
        $map['status'] = 3;
        $recommendId   = table('HelpService')->where($map)->field('recommend_id')->find('one');
        if (!$recommendId) {
            $this->appReturn(array('status' => false, 'msg' => '尚未推荐请耐心等待'));
        }

        $map              = array();
        $map['status']    = 1;
        $map['is_ide']    = 1;
        $map['goods_num'] = array('>', 0);

        $orderby = 'id desc';

        $list = table('UserShop')->where($map)->order($orderby)->limit($offer, $pageSize)->field('name,uid')->find('array');
        foreach ($list as $key => $value) {
            $goods = table('GoodsService')->where(array('uid' => $value['uid'], 'status' => 1))->field('id,price,thumb,title,orders')->limit(3)->find('array');
            foreach ($goods as $k => $v) {
                $goods[$k]['thumb'] = $this->appImg($v['thumb'], 'car');
            }
            $list[$key]['list'] = $goods ? $goods : array();
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 推荐汽车列表
     * @date   2017-10-09T09:04:12+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function carList()
    {
        $id       = get('id', 'intval', 0);
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['id']     = $id;
        $map['uid']    = $this->uid;
        $map['status'] = 3;
        $recommendId   = table('HelpCar')->where($map)->field('recommend_id')->find('one');
        if (!$recommendId) {
            $this->appReturn(array('status' => false, 'msg' => '尚未推荐请耐心等待'));
        }

        $map       = array();
        $map['id'] = array('in', $recommendId);
        $list      = table('GoodsCar')->where($map)->order('created desc')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $list[$key]['title'] = "【转lease】" . $value['title'];
            }
            $list[$key]['price']   = dao('Number')->price($value['price']);
            $list[$key]['mileage'] = $value['mileage'] . '万公里';
            $list[$key]['thumb']   = $this->appImg($value['thumb'], 'car');
        }

        $list = $list ? $list : array();

        $this->appReturn(array('data' => $list));
    }
}
