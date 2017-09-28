<?php
/**
 * 抵扣卷模块管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Coupon extends \app\app\controller\Init
{

    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 兑换数据
     * @date   2017-09-27T10:00:38+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function index()
    {
        $user = table('User')->where('id', $this->uid)->field('integral')->find();

        $list = getVar('rule', 'app.coupon');
        foreach ($list as $key => $value) {
            $list[$key]['status'] = 1;
            if ($value['integral'] > $user['integral']) {
                $list[$key]['status'] = 2;
            }
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 领取抵扣卷
     * @date   2017-09-27T10:00:02+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function get()
    {
        $id = post('id', 'intval', 0);

        $list = getVar('rule', 'app.coupon');

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        foreach ($list as $key => $value) {
            if ($value['id'] == $id) {
                $param = $value;
                break;
            }

        }

        if (!$param) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $map['category']      = $param['type'];
        $map['remainder_num'] = array('>', 0);
        $map['status']        = 1;
        $map['is_exchange']   = 1;
        $map['del_status']    = 0;

        $coupon = table('Coupon')->where($map)->order('RAND()')->find();
        if (!$coupon) {
            $this->appReturn(array('status' => false, 'msg' => '暂无相关抵扣卷'));
        }

        //增加抵扣卷领取记录
        $data['coupon_id'] = $coupon['id'];
        $data['uid']       = $this->uid;
        $data['created']   = TIME;

        table('User')->startTrans();
        $result = table('CouponLog')->add($data);
        if (!$result) {
            table('User')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '领取失败', 'sql' => table('CouponLog')->getSql()));
        }

        //判断用户积分是否满足
        $user = table('User')->where('id', $this->uid)->field('id,integral')->find();
        if ($user['integral'] < $param['integral']) {
            table('User')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '积分不足'));
        }

        //修改商户抵扣卷记录
        $dataCoupon['remainder_num'] = array('less', 1);
        $dataCoupon['version']       = array('add', 1);

        $resultCoupon = table('Coupon')->where(array('id' => $coupon['id'], 'version' => $coupon['version']))->save($dataCoupon);
        if (!$resultCoupon) {
            table('User')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '抵扣卷领取失败了,请稍后尝试', 'sql' => table('Coupon')->getSql()));
        }

        //删除用户积分
        $dataUser['integral'] = array('less', $param['integral']);
        $resultUser           = table('User')->where(array('id' => $this->uid))->save($dataUser);
        if (!$resultUser) {
            table('User')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '积分抵扣异常,请稍后尝试', 'sql' => table('User')->getSql()));
        }

        table('User')->commit();

        //增加积分明细记录
        dao('Integral')->add($this->uid, 0, '领取' . $param['name'], -$param['integral'], false);
        $this->appReturn(array('msg' => '抵扣卷领取成功'));
    }

    /**
     * 抵扣卷列表
     * @date   2017-09-27T13:30:58+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        $map[$couponLog . '.uid'] = $this->uid;

        $list         = dao('Coupon')->lists($map, $offer, $pageSize);
        $data['list'] = $list ? $list : array();
        $this->appReturn(array('data' => $data));
    }

}
