<?php
/**
 * 汽车服务信息模块
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Service extends \app\app\controller\Init
{

    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 预约看车
     * @date   2017-09-20T16:25:21+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function appointment()
    {
        $data['start_time'] = post('start_time', 'intval', 0);
        $data['end_time']   = post('end_time', 'intval', 0);
        $data['my_car_id']  = post('my_car_id', 'intval', 0);

        $id     = post('id', 'intval', 0);
        $origin = post('origin', 'intval', 0);

        $message = post('message', 'text', '');

        $files['ablum'] = files('ablum_files');

        $data['ablum'] = $this->appUpload($files['ablum'], $data['ablum'], 'shop');

        $version = APP_VERSION;

        if (!$data['my_car_id']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择对应爱车'));
        }

        if (!$id || !$data['start_time'] || !$data['end_time']) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (date('Y-m-d', $data['start_time']) != date('Y-m-d', $data['end_time'])) {
            $this->appReturn(array('status' => false, 'msg' => '预约超过一天了'));
        }

        $map['goods_id']   = $id;
        $map['start_time'] = $data['start_time'];
        $map['end_time']   = $data['end_time'];

        $is = table('OrdersService')->where($map)->field('id')->find('one');
        if ($is) {
            $this->appReturn(array('status' => false, 'msg' => '请选择其他时间段，该时间已有预约了'));
        }

        $dataInfo = dao('Orders')->getAddAttachedInfo(2, $id, $data);
        $result   = dao('Orders')->add($this->uid, 2, $dataInfo, 0, 0, $message, $origin, $version);

        $this->appReturn($result);
    }

    /**
     * 帮忙推荐服务
     */
    public function help()
    {

        $data['sign']        = post('sign', 'intval', 0);
        $data['price']       = post('prcie', 'text', '');
        $data['description'] = post('description', 'text', '');

        $data['created'] = TIME;
        $data['uid']     = $this->uid;

        if (!$data['sign']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择服务类型'));
        }

        if (!$data['price']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择服务价格'));
        }

        if (!$data['description']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入详情内容'));
        }

        $result = table('HelpService')->add($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '提交成功'));

    }

    //获取帮助分类
    public function getHelpType()
    {
        $data = $this->appArray(getVar('helpType', 'app.service'));
        $this->appReturn(array('data' => $data));
    }

    //获取帮助价格分类
    public function getHelpPrice()
    {
        $data = $this->appArray(getVar('helpPice', 'app.service'));
        $this->appReturn(array('data' => $data));
    }
}
