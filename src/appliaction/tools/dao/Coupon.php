<?php
/**
 * 抵扣券管理模块
 */
namespace app\tools\dao;

class Coupon
{
    /**
     * 增加抵扣卷模板
     * @date   2017-10-25T16:29:23+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  array                    $param [description]
     */
    public function add($uid = 0, $param = array())
    {
        $id = (int) $param['id'];

        $data['uid']        = $uid;
        $data['category']   = (int) $param['category'];
        $data['type']       = (int) $param['type'];
        $data['start_time'] = (int) $param['start_time'];
        $data['end_time']   = (int) $param['end_time'];
        $data['num']        = $data['remainder_num']        = (int) $param['num'];

        $data['title']    = (string) $param['title'];
        $data['title_en'] = (string) $param['title_en'];
        $data['title_jp'] = (string) $param['title_jp'];

        $data['description']    = (string) $param['description'];
        $data['description_en'] = (string) $param['description_en'];
        $data['description_jp'] = (string) $param['description_jp'];

        $data['is_exchange'] = (int) $param['is_exchange'];
        $data['unit']        = $param['unit'] == '' ? 'CNY' : (string) $param['unit'];

        $data['created'] = TIME;

        if (!$data['title']) {
            return array('status' => false, 'msg' => '请输入抵扣卷名称');
        }

        if (!$data['unit']) {
            return array('status' => false, 'msg' => '请输入抵扣币种');
        }

        if (!$data['start_time'] || !$data['end_time']) {
            return array('status' => false, 'msg' => '请输入完整的抵扣卷生效时间');
        }

        if (!$data['type']) {
            return array('status' => false, 'msg' => '请选择抵扣卷类型');
        }

        if ($data['type'] == 1) {

            $data['full'] = (int) $param['full'];
            $data['less'] = (int) $param['less'];

            if (!$data['less']) {
                return array('status' => false, 'msg' => '请输入抵扣金额');
            }

        } else {
            $data['discount'] = (int) $param['discount'];
            if (!$data['discount']) {
                return array('status' => false, 'msg' => '请输入折扣值[数字]');
            }

            if ($data['discount'] >= 10) {
                return array('status' => false, 'msg' => '最多9.9折');
            }

            if ($data['discount'] < 1) {
                return array('status' => false, 'msg' => '最少9.9折');
            }
        }

        //编辑
        if (isset($id)) {
            if (!$this->checkCoupon($id)) {
                return array('status' => false, 'msg' => '抵扣卷已在使用了');
            }

            $result = table('Coupon')->where('id', $id)->save($data);
        }
        //添加
        else {
            $result = table('Coupon')->add($data);
        }

        if (!$result) {
            return array('status' => false, 'msg' => '创建失败');
        }

        return array('status' => true, 'msg' => '操作成功');
    }

    /** 检测抵扣卷模板是否已在使用 true:未使用 false:已有人使用 */
    public function checkCoupon($id)
    {
        $log = table('CouponLog')->where('coupon_id', $id)->find();
        if ($log) {
            return false;
        }

        return true;

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
    public function lists($map, $lg = 'zh', $offer = 0, $pageSize = 1000)
    {
        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        if ($lg == 'zh') {
            $field = "$coupon.title,$coupon.description,";
        } else {
            $field = "$coupon.title_$lg,$coupon.description_$lg,";
        }

        $field .= "$coupon.uid as shop_uid,$coupon.start_time,$coupon.end_time,$coupon.type,$coupon.full,$coupon.less,$coupon.discount,$coupon.category,$couponLog.use_time,$couponLog.uid,$couponLog.id,$couponLog.origin";

        $list = table('CouponLog')->join($coupon, "$coupon.id = $couponLog.coupon_id")->where($map)->limit($offer, $pageSize)->field($field)->order("$couponLog.id desc,$couponLog.use_time asc,$coupon.end_time desc")->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['status'] = dao('Time')->hdStatus($value['start_time'], $value['end_time']);
            if ($value['use_time']) {
                $list[$key]['status'] = 3;
            }

            $list[$key]['title']       = $lg != 'zh' ? (string) $value['title_' . $lg] : (string) $value['title'];
            $list[$key]['description'] = $lg != 'zh' ? (string) $value['description_' . $lg] : (string) $value['description'];
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

        //status 1未使用 2已过期 3已使用
        $data['status'] = dao('Time')->hdStatus($data['start_time'], $data['end_time']);
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

    /**
     * 发送抵扣卷
     * @date   2017-10-24T16:45:48+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid      [获取抵扣卷id]
     * @param  [type]                   $giftId   [礼包id]
     * @param  [type]                   $couponId [抵扣卷模板id]
     * @param  [type]                   $origin   [获得抵扣卷方式 1兑换 2赠送]
     * @return [type]                             [description]
     */
    public function send($uid, $couponId, $origin = 2, $giftId = 0)
    {
        if (!$uid || !$couponId) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $coupon = table('Coupon')->where('id', $couponId)->order('RAND()')->find();
        if (!$coupon) {
            return array('status' => false, 'msg' => '暂无相关抵扣卷');
        }

        //增加抵扣卷领取记录
        $data['coupon_id'] = $couponId;
        $data['uid']       = $uid;
        $data['created']   = TIME;
        $data['origin']    = $origin;

        table('CouponLog')->startTrans();
        $result = table('CouponLog')->add($data);
        if (!$result) {
            table('CouponLog')->rollback();
            return array('status' => false, 'msg' => '领取失败', 'sql' => table('CouponLog')->getSql());
        }

        //存在礼包id 标记为已领取
        if ($giftId) {
            $result = table('Gift')->where('id', $giftId)->save('status', 1);
            if (!$result) {
                return array('status' => false, 'msg' => '状态修改失败');
            }
        }

        return array('status' => true, 'msg' => '领取成功');
    }

    /**
     * 用户自己使用
     * @date   2017-10-25T14:17:13+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function userUse($id = 0, $uid = 0)
    {

        if (!$id || !$uid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data = $this->logDetail($id);

        if (!$data) {
            return array('status' => false, 'msg' => '抵扣券信息不存在');
        }

        if ($data['uid'] != $uid) {
            return array('status' => false, 'msg' => '非法操作');
        }

        if ($data['status'] != 1) {
            return array('status' => false, 'msg' => '抵扣券不可用');
        }

        $data             = array();
        $data['use_time'] = TIME;

        $result = table('CouponLog')->where('id', $id)->save($data);
        if (!$result) {
            return array('status' => false, 'msg' => '使用失败');
        }

        return array('status' => true, 'msg' => '使用成功');
    }
}
