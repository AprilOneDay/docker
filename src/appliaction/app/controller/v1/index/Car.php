<?php
/**
 * 汽车信息模块
 */
namespace app\app\controller\v1\index;

use app\app\controller;

class Car extends \app\app\controller\Init
{
    public function lists()
    {
        $param['is_recommend'] = get('is_recommend', 'text', '');
        $param['is_urgency']   = get('is_urgency', 'text', '');
        $param['order_type']   = get('order_type', 'intval', 0);
        $param['price_type']   = get('price_type', 'intval', 0);
        $param['brand']        = get('brand', 'intval', 0);
        $param['field']        = get('field', 'text', 'title');
        $param['keyword']      = get('keyword', 'text', '');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['is_show'] = 1;
        $map['status']  = 1;

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_urgency'] != '') {
            $map['is_urgency'] = $param['is_urgency'];
        }

        $param['brand_copy'] = '';
        if ($param['brand']) {
            $map['brand']        = $param['brand'];
            $param['brand_copy'] = dao('Category')->getName($param['brand']);
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('like', '%' . $param['keyword'] . '%');
                //增加搜索记录
                dao('Search')->addLog($this->uid, 1, $param['keyword']);
            }
        }

        $order = 'id desc';
        switch ($param['order_type']) {
            //最新发布
            case '2':
                $order = 'created desc';
                break;
            //价格最低
            case '3':
                $order = 'price asc';
                break;
            //价格最高
            case '4':
                $order = 'price desc';
                break;
            //车龄最小
            case '5':
                $order = 'buy_time desc';
                break;
            //里程最短
            case '6':
                $order = 'mileage asc';
                break;
            default:
                # code...
                break;
        }

        switch ($param['price_type']) {
            case '1':
                $map['price'] = array('<=', 5);
                break;
            case '2':
                $map['price'] = array('between', 5, 10);
                break;
            case '3':
                $map['price'] = array('between', 10, 15);
                break;
            case '4':
                $map['price'] = array('>=', 15);
                break;
            case '5':
                $map['is_urgency'] = 1;
                break;
            default:
                # code...
                break;
        }

        $list = table('GoodsCar')->where($map)->order($order)->limit($offer, $pageSize)->find('array');

        foreach ($list as $key => $value) {
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $list[$key]['title'] = "【转lease】" . $value['title'];
            }

            $list[$key]['price']   = dao('Number')->price($value['price']);
            $list[$key]['mileage'] = $value['mileage'] . '万公里';
            $list[$key]['thumb']   = $this->appImg($value['thumb'], 'car');

        }

        $data['param'] = $param;
        $data['list']  = $list ? $list : array();

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 字母区分带图标的品牌
     */
    public function brandList()
    {
        $list = table('Category')->where(array('parentid' => 1))->field('id,name,thumb')->find('array');
        $data = array();
        foreach ($list as $key => $value) {
            $chart          = getFirstCharter($value['name']);
            $value['thumb'] = $this->appimg($value['thumb'], 'category');
            if (!isset($data[$chart])) {
                $data[$chart][] = $value;
            } else {
                $data[$chart][] = $value;
            }
        }

        foreach ($data as $key => $value) {
            $brand[$key]['letter'] = $key;
            $brand[$key]['list']   = $value;
        }

        //$chart = getFirstCharter('讴歌');
        //var_dump($chart);die;

        $brand = array_values($brand);

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $brand));
    }

    /**
     * 汽车详情
     * @date   2017-09-19T14:49:54+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function detail()
    {
        $id = get('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['id']     = $id;
        $map['status'] = 1;

        $data = table('GoodsCar')->where($map)->find();
        if (!$data) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $city = dao('Category')->getList(8);

        $data['price']         = dao('Number')->price($data['price']);
        $data['mileage']       = $data['mileage'] . '万公里';
        $data['thumb']         = $this->appImg($data['thumb'], 'car');
        $data['ablum']         = $this->appImgArray($data['ablum'], 'car');
        $data['guarantee']     = $data['guarantee'] ? explode(',', $data['guarantee']) : array();
        $data['is_collection'] = (bool) table('Collection')->where(array('uid' => $this->uid, 'value' => $data['id'], 'type' => 1, 'del_status' => 0))->field('id')->find('one');

        $data['banner'] = $this->appImgArray($data['banner'], 'car');

        //获取车龄
        $age['year']                      = (int) date('Y', TIME) - (int) date('Y', $data['buy_time']);
        $age['month']                     = (int) date('m', TIME) - (int) date('m', $data['buy_time']);
        $data['car_age']                  = '';
        !$age['year'] ?: $data['car_age'] = $age['year'] . '年';
        !$age['month'] ?: $data['car_age'] .= $age['month'] . '月';

        $data['user'] = array();
        $data['shop'] = array();

        $data['city_copy'] = (string) $city[$data['city']];

        //获取图片介绍
        $data['content'] = '';
        $ablum           = table('GoodsAblum')->where(array('goods_id' => $data['id']))->find('array');
        foreach ($ablum as $key => $value) {
            $data['content'] .= '<p><img src="' . $this->appImg($value['path'], 'car') . '" style="width:90%;text-algin:center" /></p>';
            if ($value['description']) {
                $data['content'] .= '<p>' . $value['description'] . '</p>';
            }

        }

        if ($data['type'] == 1) {
            $user = dao('User')->getInfo($data['uid'], 'nickname,avatar,mobile');

            $data['user']['avatar']   = $this->appImg($user['avatar'], 'avatar');
            $data['user']['nickname'] = $user['nickname'];
            $data['user']['address']  = $data['address'];
            $data['user']['mobile']   = $data['mobile'];
        }

        if ($data['type'] == 2) {
            $shop                         = table('UserShop')->where(array('uid' => $data['uid']))->field('avatar,name,address,credit_level')->find();
            $data['shop']                 = $shop;
            $data['shop']['avatar']       = $this->appImg($shop['avatar'], 'avatar');
            $data['shop']['mobile']       = (string) dao('User')->getInfo($data['uid'], 'mobile');
            $data['shop']['credit_level'] = dao('User')->getShopCredit($shop['credit_level']);
            $data['coment']               = dao('Comment')->getList(2, $id); //获取评价内容
            foreach ($data['coment'] as $key => $value) {
                $data['coment'][$key]['ablum'] = $this->appImgArray($value['ablum'], 'comment');
            }
        }

        //增加浏览记录
        dao('Footprints')->add($this->uid, 1, $data['id'], $data['uid']);
        //增加数据库访问记录
        dao('Footprints')->addHot($this->uid, 1, $data['id']);
        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 获取汽车搜索推荐
     * @date   2017-09-20T10:26:09+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function searchTags()
    {
        $map['type']   = 1;
        $map['status'] = 1;

        $list         = table('SearchRemmond')->where($map)->find()->limit(5)->order('sort asc')->field('name')->find('one', true);
        $data['list'] = (array) $list;

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 排序文案
     * @date   2017-09-20T15:46:38+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getSeachOrderByTags()
    {
        $data = $this->appArray(getVar('carListOrderbyType', 'app.car'));
        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 筛选价格文案
     * @date   2017-09-20T15:46:46+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getSeachPriceTags()
    {
        $data = $this->appArray(getVar('carListOrderbyPrice', 'app.car'));
        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

}
