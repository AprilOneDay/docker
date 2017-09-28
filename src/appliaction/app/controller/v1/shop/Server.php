<?php
/**
 * 服务信息管理
 */
namespace app\app\controller\v1\shop;

use app\app\controller;

class Server extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkShop();
        $this->checkIde();
    }

    /**
     * 商品发布汽车列表
     * @date   2017-09-19T10:17:04+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function carList()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;
        $status   = get('status', 'text', '');

        $map['uid'] = $this->uid;

        if ($status > 2) {
            $this->appReturn(array('status' => false, 'msg' => 'status 参数错误'));
        }

        if ($status) {
            $map['status'] = $status;
        }

        $list = table('GoodsCar')->where($map)->order('created desc')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $list[$key]['title'] = "【转lease】" . $value['title'];
            }
            $list[$key]['price']   = dao('Number')->price($value['price']);
            $list[$key]['mileage'] = $value['mileage'] . '万公里';
            $list[$key]['thumb']   = $this->appImg($value['thumb'], 'car');
        }

        $list = $list ? $list : array();

        $this->appReturn(array('msg' => '数据获取成功', 'data' => $list));
    }

    /**
     * 编辑查看 提交 新增 二手车
     * @date   2017-09-15T09:32:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function carEdit()
    {
        if (IS_POST) {
            $id = post('id', 'intval', 0);

            $data['brand']        = post('brand', 'intval', 0);
            $data['produce_time'] = post('produce_time', 'intval', 0);
            $data['brand']        = post('brand', 'intval', 0);
            $data['is_lease']     = post('is_lease', 'intval', 0);

            $data['mileage'] = post('mileage', 'floatval', 0);
            $data['price']   = post('price', 'floatval', 0);

            $data['style']        = post('style', 'text', '');
            $data['model']        = post('model', 'text', '');
            $data['buy_time']     = post('buy_time', 'text', '');
            $data['city']         = post('city', 'text', '');
            $data['gearbox']      = post('gearbox', 'text', '');
            $data['gases']        = post('gases', 'text', '');
            $data['displacement'] = post('displacement', 'text', '');
            $data['model_remark'] = post('model_remark', 'text', '');
            $data['vin']          = post('vin', 'text', '');
            $data['mobile']       = post('mobile', 'text', '');
            $data['weixin']       = post('weixin', 'text', '');
            $data['qq']           = post('qq', 'text', '');
            $data['address']      = post('address', 'text', '');
            $data['description']  = post('description', 'text', '');

            $data['banner'] = post('banner', 'json', '');

            $ablum['ablum']       = post('ablum', 'json', '');
            $ablum['description'] = post('ablum_description', 'json', '');

            $files['ablum']  = files('ablum_files');
            $files['banner'] = files('banner_files');

            if (!$data['brand']) {
                $this->appReturn(array('status' => false, 'msg' => '请选择品牌'));
            }

            if (!$data['style']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入款号'));
            }

            if (!$data['mileage']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入里程数'));
            }

            if (!$data['city']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入城市'));
            }

            if (!$data['price']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入报价'));
            }

            //上传banner图 并生成封面图片
            $data['banner'] = $this->appUpload($files['banner'], $data['banner'], 'car');
            if (stripos($data['banner'], ',') !== false) {
                $data['thumb'] = substr($data['banner'], 0, stripos($data['banner'], ','));
            } else {
                $data['thumb'] = $data['banner'];
            }

            if (!$data['banner']) {
                $this->appReturn(array('status' => false, 'msg' => '请上传图片'));
            }

            if (count(explode(',', $data['banner'])) > 5) {
                $this->appReturn(array('status' => false, 'msg' => '最多可传5张图片'));
            }

            $ablum['ablum'] = explode(',', $this->appUpload($files['ablum'], $data['ablum'], 'car'));

            //添加
            if (!$id) {
                $data['type']    = $this->group;
                $data['uid']     = $this->uid;
                $data['created'] = TIME;

                //拼接标题
                $data['title'] = dao('Category')->getName($data['brand'])
                . ($data['produce_time'] != '' ? ' ' . $data['produce_time'] : '')
                . ($data['style'] != '' ? ' ' . $data['style'] : '')
                . ($data['displacement'] != '' ? ' ' . $data['displacement'] : '')
                . ($data['model_remark'] != '' ? ' ' . $data['model_remark'] : '');

                $result = table('GoodsCar')->add($data);

                if ($result) {
                    //添加相册
                    foreach ($ablum['ablum'] as $key => $value) {
                        table('GoodsAblum')->add(array('path' => $value, 'goods_id' => $result, 'description' => $ablum['description'][$key]));
                    }
                }
            }
            //编辑
            else {
                $is = table('GoodsCar')->where(array('uid' => $this->uid, 'id' => $id))->field('id')->find('one');
                if (!$is) {
                    $this->appReturn(array('status' => false, 'msg' => '非法操作'));
                }

                $result = table('GoodsCar')->where(array('uid' => $this->uid, 'id' => $id))->save($data);
                if ($result) {
                    table('GoodsAblum')->where(array('goods_id' => $id))->delete();
                    //添加相册
                    foreach ($ablum['ablum'] as $key => $value) {
                        table('GoodsAblum')->add(array('path' => $value, 'goods_id' => $result, 'description' => $ablum['description'][$key]));
                    }

                    $this->appReturn(array('msg' => '编辑成功'));
                }
            }

            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        } else {
            $id   = get('id', 'intval', 0);
            $city = getVar('province', 'city');
            if ($id) {
                $data               = table('GoodsCar')->where(array('uid' => $this->uid, 'id' => $id))->find();
                $data['banner']     = $this->appImgArray($data['banner'], 'car');
                $data['guarantee']  = explode(',', $data['guarantee']);
                $data['city_copy']  = $city[$data['city']];
                $data['brand_copy'] = dao('Category')->getName($data['brand']);
                //获取相册信息
                $ablum = table('GoodsAblum')->where(array('goods_id' => $data['id']))->field('path,description')->find('array');
                foreach ($ablum as $key => $value) {
                    $ablum[$key]['path'] = $this->appImg($value['path'], 'car');
                }
                $data['ablum'] = (array) $ablum;
            }

            $data['other'] = array(
                'city'    => $this->appArray(dao('Category')->getList(8)),
                'gearbox' => $this->appArray(getVar('gearbox', 'car')),
                'gases'   => $this->appArray(getVar('gases', 'car')),
                'model'   => $this->appArray(getVar('model', 'car')),
            );

            $this->appReturn(array('data' => $data));
        }
    }

    /**
     * 编辑查看 提交 新增 服务
     * @date   2017-09-15T09:32:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function serviceEdit()
    {

        if (IS_POST) {
            $id = $_POST['id'];

            $data['type'] = post('type', 'intval', 0);

            $data['price'] = post('price', 'float', 0);

            $data['title'] = post('title', 'text', '');

            $data['description'] = post('description', 'text', '');

            $data['ablum'] = post('ablum', 'json', '');

            $files['ablum'] = files('ablum_files');

            $data['ablum'] = $this->appUpload($files['ablum'], $data['ablum'], 'car');

            if (!$data['type']) {
                $this->appReturn(array('status' => false, 'msg' => '请选择类型'));
            }

            if (!$data['title']) {
                $this->appReturn(array('status' => false, 'msg' => '请输服务标题'));
            }

            if (!$data['price']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入价格'));
            }

            if ($id) {
                $is = table('GoodsService')->where(array('uid' => $this->uid, 'id' => $id))->field('id')->find('one');
                if (!$is) {
                    $this->appReturn(array('status' => false, 'msg' => '非法操作'));
                }

                $result = table('GoodsService')->where(array('id' => $id))->save($data);

                if ($result) {
                    $this->appReturn(array('msg' => '编辑成功'));
                }

            } else {
                $data['created'] = TIME;
                $data['uid']     = $this->uid;
                $result          = table('GoodsService')->add($data);

                if ($result) {
                    $this->appReturn(array('msg' => '添加成功'));
                }
                //增加商品总数 + 1
                table('UserShop')->where(array('uid' => $this->uid))->save(array('goods_num' => array('add', 1)));
                $this->appReturn(array('msg' => '添加成功'));
            }

            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        } else {
            $id                = get('id', 'intval', 0);
            $data              = (array) table('GoodsService')->where(array('id' => $id))->find();
            $data['type_copy'] = dao('Category')->getName($data['type']);
            $data['ablum']     = $this->appImgArray($data['ablum'], 'car');
            $this->appReturn(array('msg' => '数据获取成功', 'data' => $data));
        }
    }

    /**
     * 服务列表
     * @date   2017-09-18T11:32:25+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function serviceList()
    {
        $map['uid'] = $this->uid;
        $pageNo     = get('pageNo', 'intval', 1);
        $pageSize   = get('pageSize', 'intval', 10);
        $offer      = max(($pageNo - 1), 0) * $pageSize;

        $list = table('GoodsService')->where($map)->order('created desc')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['ablum'] = $this->appImgArray($value['ablum'], 'car');
        }

        $list = $list ? $list : array();

        $this->appReturn(array('msg' => '数据获取成功', 'data' => $list));
    }

}
