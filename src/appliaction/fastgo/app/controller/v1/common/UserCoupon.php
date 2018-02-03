<?php
/**
 * 会员抵扣卷管理
 */
namespace app\fastgo\app\controller\v1\common;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserCoupon extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2,3');
    }

    /**
     * 抵扣券列表
     * @date   2017-09-27T13:30:58+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function index()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $couponLog = table('CouponLog')->tableName();
        $coupon    = table('Coupon')->tableName();

        $map[$couponLog . '.uid'] = $this->uid;

        $list         = dao('Coupon')->lists($map, $this->lg, $offer, $pageSize);
        $data['list'] = $list ? $list : array();

        foreach ($data['list'] as $key => $value) {
            //$data['list'][$key]['category_copy'] = dao('Category')->getName($value['category']);
        }

        $this->appReturn(array('data' => $data));
    }
}
