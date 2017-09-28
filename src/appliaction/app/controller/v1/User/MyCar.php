<?php
/**
 * 我的车库模块
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class MyCar extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 我的车库列表
     * @date   2017-09-25T10:08:19+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['uid']        = $this->uid;
        $map['del_status'] = 0;

        $list = table('MyCar')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['ablum']      = $this->appImg($value['ablum'], 'car');
            $list[$key]['brand_copy'] = dao('Category')->getName($value['brand']);
        }

        $list = $list ? $list : array();

        $this->appReturn(array('data' => $list));
    }

    /**
     * 编辑查看我的车库信息
     * @date   2017-09-25T10:08:08+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {
            $id = post('id', 'intval', 0);

            $data['brand']        = post('brand', 'intval', 0);
            $data['style']        = post('style', 'text', '');
            $data['produce_time'] = post('produce_time', 'text', '');
            $data['buy_time']     = post('buy_time', 'text', '');
            $data['vin']          = post('vin', 'text', '');
            $data['mileage']      = post('mileage', 'float', 0);
            $data['model']        = post('model', 'text', '');

            $files['ablum']                    = files('ablum');
            !$files['ablum'] ?: $data['ablum'] = $this->appUpload($files['ablum'], '', 'car');

            if (!$data['brand']) {
                $this->appReturn(array('status' => false, 'msg' => '请选择品牌'));
            }

            if (!$data['produce_time']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入生成时间'));
            }

            if (!$data['buy_time']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入购买时间'));
            }

            if (!$data['mileage']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入里程数(万公里)'));
            }

            if (!$id) {
                $is = table('MyCar')->where(array('uid' => $this->uid, 'brand' => $data['brand'], 'style' => $data['style']))->field('id')->find('one');

                if ($is) {
                    $this->appReturn(array('status' => false, 'msg' => '该车型您已存在,请勿重复添加'));
                }

                $data['created'] = TIME;
                $data['uid']     = $this->uid;
                $reslut          = table('MyCar')->add($data);
                if (!$reslut) {
                    $this->appReturn(array('status' => false, 'msg' => '添加失败'));
                }

                $this->appReturn(array('msg' => '添加成功'));
            } else {
                $reslut = table('MyCar')->where(array('id' => $id, 'uid' => $this->uid))->save($data);
                if (!$reslut) {
                    $this->appReturn(array('status' => false, 'msg' => '保存失败'));
                }

                $this->appReturn(array('msg' => '保存成功'));
            }

        } else {
            if (!$id) {
                $this->appReturn(array('status' => false, 'msg' => '参数错误'));
            }

            $data = table('MyCar')->where('id', $id)->find();
            if (!$data) {
                $this->appReturn(array('status' => false, 'msg' => '当前信息不存在'));
            }

            $data['ablum']      = $this->appImg($data['ablum'], 'car');
            $data['brand_copy'] = dao('Category')->getName($data['brand']);

            $this->appReturn(array('data' => $data));
        }
    }

    /**
     * 删除我的汽车信息
     * @date   2017-09-25T10:07:57+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function del()
    {
        $id = post('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']        = $this->uid;
        $map['id']         = $id;
        $map['del_status'] = 0;

        $reslut = table('MyCar')->where($map)->save('del_status', 1);
        if (!$reslut) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('msg' => '删除成功'));
    }

}
