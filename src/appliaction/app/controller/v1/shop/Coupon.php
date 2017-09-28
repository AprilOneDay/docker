<?php
/**
 * 抵扣卷模块管理
 */
namespace app\app\controller\v1\shop;

use app\app\controller;

class Coupon extends \app\app\controller\Init
{

    public function __construct()
    {
        parent::__construct();
        $this->checkShop();
        $this->checkIde();
    }

    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['uid']        = $this->uid;
        $map['del_status'] = 0;

        $list = table('Coupon')->where($map)->limit($offer, $pageSize)->order('status desc,start_time asc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['category_copy'] = dao('Category')->getName($value['category']);
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 商家发布抵扣卷
     * @date   2017-09-26T15:19:17+0800
     * @author ChenMingjiang
     */
    public function add()
    {
        $data['category']   = post('category', 'intval', 0);
        $data['type']       = post('type', 'intval', 0);
        $data['start_time'] = post('start_time', 'text', '');
        $data['end_time']   = post('end_time', 'text', '');
        $data['num']        = post('num', 'intval', 0);
        $data['title']      = post('title', 'text', 0);
        $data['full']       = post('full', 'intval', 0);
        $data['less']       = post('less', 'text', 0);
        $data['discount']   = post('discount', 'text', 0);

        if (!$data['category']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择所属分类'));
        }

        $result = dao('Coupon')->add($this->uid, $data);
        $this->appReturn($result);
    }

    /**
     * 开启/关闭 抵扣卷
     * @date   2017-09-26T15:24:45+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeStatus()
    {

        $id   = post('id', 'intval', 0);
        $type = post('type', 'intval', 0);

        if (!$id || !$type) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }
        $map['id'] = $id;

        switch ($type) {
            //开启
            case '1':
                $map['status'] = 0;
                $is            = table('Coupon')->where($map)->field('id')->find('one');
                if (!$is) {
                    $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
                }

                $result = table('Coupon')->where('id', $id)->save('status', 1);
                break;
            //关闭
            case '2':
                $map['status'] = 1;
                $is            = table('Coupon')->where($map)->field('id')->find('one');
                if (!$is) {
                    $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
                }

                $result = table('Coupon')->where('id', $id)->save('status', 0);
                break;
            default:
                $this->appReturn(array('status' => false, 'msg' => '类型参数错误'));
                break;
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

    /**
     * 获取抵扣卷类型
     * @date   2017-09-27T11:24:56+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getCouponType()
    {
        $data = $this->appArray(getVar('type', 'app.coupon'));
        $this->appReturn(array('data' => $data));
    }

    /**
     * 改变抵扣卷数量
     * @date   2017-09-26T15:25:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeNum()
    {
        $id  = post('id', 'intval', 0);
        $num = post('num', 'intval', 0);

        if (!$num) {
            $this->appReturn(array('status' => false, 'msg' => '请输入抵扣卷数量'));
        }

        $coupon = table('Coupon')->where(array('uid' => $this->uid, 'id' => $id))->field('num,remainder_num')->find();
        if (!$coupon) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        if ($num < $num['num'] - $coupon['remainder_num']) {
            $this->appReturn(array('status' => false, 'msg' => '修改数量不可小于已领取数量'));
        }

        $result = table('Coupon')->where('id', $id)->save('num', $num);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '修改失败'));
        }

        $this->appReturn(array('msg' => '修改数量成功'));
    }

}
