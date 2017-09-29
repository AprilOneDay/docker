<?php
namespace app\tools\dao;

class Coupon
{
    public function add($uid = 0, $param = array())
    {
        $data['uid']         = $uid;
        $data['category']    = (int) $param['category'];
        $data['type']        = (int) $param['type'];
        $data['start_time']  = (int) $param['start_time'];
        $data['end_time']    = (int) $param['end_time'];
        $data['num']         = $data['remainder_num']         = (int) $param['num'];
        $data['title']       = (string) $param['title'];
        $data['is_exchange'] = (int) $param['is_exchange'];

        $data['created'] = TIME;
        if (!$data['title']) {
            return array('stauts' => false, 'msg' => '请输入抵扣卷名称');
        }

        if (!$data['title']) {
            return array('stauts' => false, 'msg' => '请输入抵扣卷名称');
        }

        if (!$data['start_time'] || !$data['end_time']) {
            return array('stauts' => false, 'msg' => '请输入完整的抵扣卷生效时间');
        }

        if (!$data['type']) {
            return array('stauts' => false, 'msg' => '请选择抵扣卷类型');
        }

        if ($data['type'] == 1) {

            $data['full'] = (int) $param['full'];
            $data['less'] = (int) $param['less'];

            if (!$data['less']) {
                return array('stauts' => false, 'msg' => '请输入抵扣金额');
            }

        } else {
            $data['discount'] = (int) $param['discount'];
            if (!$data['discount']) {
                return array('stauts' => false, 'msg' => '请输入折扣值');
            }

            if ($data['discount'] >= 10) {
                return array('stauts' => false, 'msg' => '最多9.9折');
            }

            if ($data['discount'] < 1) {
                return array('stauts' => false, 'msg' => '最少9.9折');
            }
        }

        $result = table('Coupon')->add($data);
        if (!$result) {
            return array('stauts' => false, 'msg' => '创建失败');
        }

        return array('stauts' => true, 'msg' => '创建成功');
    }

    /**
     * 抵扣卷列表信息
     * @date   2017-09-27T14:31:59+0800
     * @author ChenMingjiang
     * @param  [type]                   $map      [description]
     * @param  integer                  $offer    [description]
     * @param  integer                  $pageSize [description]
     * @return [type]                             [description]
     */
    public function lists($map, $offer = 0, $pageSize = 1000)
    {
        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        $field = "$coupon.title,$coupon.uid as shop_uid,$coupon.start_time,$coupon.end_time,$coupon.type,$coupon.full,$coupon.less,$coupon.discount,$coupon.category,$couponLog.use_time,$couponLog.uid,$couponLog.id,$couponLog.origin";
        $list  = table('CouponLog')->join($coupon, "$coupon.id = $couponLog.coupon_id", 'left')->where($map)->limit($offer, $pageSize)->field($field)->order("$couponLog.use_time asc,$coupon.end_time desc")->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['status'] = dao('Time')->hdStatus($value['start_time'], $value['end_time']);
            if ($value['use_time']) {
                $list[$key]['status'] = 3;
            }
            $list[$key]['shop_name']   = dao('User')->getInfo($value['shop_uid'], 'nickname');
            $list[$key]['origin_copy'] = $value['origin'] == 1 ? '积分兑换' : '消费赠送';
        }

        $list = $list ? $list : array();

        return $list;
    }

    public function logDetail($id = 0)
    {
        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        $map[$couponLog . '.id'] = $id;

        $field = "$coupon.title,$coupon.uid as shop_uid,$coupon.start_time,$coupon.end_time,$coupon.type,$coupon.full,$coupon.less,$coupon.discount,$coupon.category,$couponLog.use_time,$couponLog.uid,$couponLog.id";
        $data  = table('CouponLog')->join($coupon, "$coupon.id = $couponLog.coupon_id", 'left')->where($map)->field($field)->find();

        $data['status'] = dao('Time')->hdStatus($value['start_time'], $value['end_time']);
        if ($data['use_time']) {
            $data['status'] = 3;
        }

        $data = $data ? $data : '';

        return $data;
    }

    /**
     * 可使用的抵扣卷数组信息  seller_uid =>{ 抵扣卷type => 抵扣卷id }
     * @date   2017-09-27T14:17:49+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid [description]
     * @return [type]                        [description]
     */
    public function canUseCouponList($uid, $price = 0)
    {
        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        $map[$couponLog . '.uid']      = $uid;
        $map[$coupon . '.start_time']  = array('<=', TIME);
        $map[$coupon . '.end_time']    = array('>=', TIME);
        $map[$couponLog . '.use_time'] = 0;

        $field = "$couponLog.id,$coupon.category,$coupon.uid,$coupon.type,$coupon.full";
        $list  = table('CouponLog')->join($coupon, "$coupon.id = $couponLog.coupon_id", 'left')->where($map)->field($field)->order("$couponLog.use_time asc,$coupon.end_time desc")->find('array');

        foreach ($list as $key => $value) {
            if ($value['type'] == 1 && $price && $value['full'] <= $price) {
                $listTmp[$value['uid']][$value['category']][] = $value['id'];
            } elseif ($value['type'] == 2) {
                $listTmp[$value['uid']][$value['category']][] = $value['id'];
            }

        }

        $listTmp = $listTmp ? $listTmp : array();

        return $listTmp;
    }

    public function send($uid, $id, $couponId)
    {
        if (!$uid || !$id || !$couponId) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $coupon = table('Coupon')->where($map)->order('RAND()')->find();
        if (!$coupon) {
            return array('status' => false, 'msg' => '暂无相关抵扣卷');
        }

        //增加抵扣卷领取记录
        $data['coupon_id'] = $couponId;
        $data['uid']       = $uid;
        $data['created']   = TIME;
        $data['origin']    = 2;

        table('CouponLog')->startTrans();
        $result = table('CouponLog')->add($data);
        if (!$result) {
            table('CouponLog')->rollback();
            return array('status' => false, 'msg' => '领取失败', 'sql' => table('CouponLog')->getSql());
        }

        $result = table('Gift')->where('id', $id)->save('status', 1);
        if (!$result) {
            return array('status' => false, 'msg' => '状态修改失败');
        }

        return array('status' => true, 'msg' => '领取成功');
    }
}
