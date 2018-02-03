<?php
/**
 * 商家通知模块
 */
namespace app\fastgo\app\controller\v1\shop;

use app\fastgo\app\controller\v1\Init;

class Material extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual(2);
    }

    /** 消耗材料统计 */
    public function useLists()
    {
        $map['uid'] = $this->uid;

        $list = table('MaterialUseLog')->where($map)->group('CONCAT(goods_id,uid)')->field('goods_id,SUM(num) as num')->find('array');

        foreach ($goods_id as $key => $value) {
            $list[$key]['name'] = dao('Category')->getName($value['goods_id']);
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 剩余材料统计 */
    public function stockList()
    {
        $data = dao('Material', 'fastgo')->stockList($this->uid);

        $this->appReturn(array('data' => $data));
    }

    /** 申请上门取件 */
    public function apply()
    {
        $applyTime = post('apply_time', 'intval', 0);
        $num       = post('num', 'intval', 0);
        $message   = post('message', 'text', '');

        $goods = post('goods', 'json');

        if (!$applyTime || $applyTime < TIME) {
            $this->appReturn(array('status' => false, 'msg' => '请选择正确的上门取件时间'));
        }

        if (!$num) {
            $this->appReturn(array('status' => false, 'msg' => '请输入预估件数'));
        }

        if (getMaxDim($goods) != 2 && is_array($goods)) {
            $this->appReturn(array('status' => false, 'msg' => 'goods参数层级只能为两层'));
        }

        if ($goods) {
            foreach ($goods as $key => $value) {
                if (!$value['goods_id']) {
                    $this->appReturn(array('status' => false, 'msg' => '申请耗材参数错误'));
                }
            }
        }

        //保存申请
        $orderSn = dao('Orders')->createOrderSn();

        $data['apply_time']  = $applyTime;
        $data['num']         = $num;
        $data['message']     = $message;
        $data['material_sn'] = $orderSn;
        $data['created']     = TIME;
        $data['uid']         = $this->uid;

        $result = table('Material')->add($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '保存失败,请稍后重试'));
        }

        //保存耗材
        foreach ($goods as $key => $value) {
            $goods = table('Category')->where('id', $value['goods_id'])->find();
            if ($goods) {
                $data                = array();
                $data['material_sn'] = $orderSn;
                $data['name']        = $goods['name'];
                $data['goods_id']    = $goods['id'];
                $data['spec']        = $goods['bname'];
                $data['price']       = $goods['bname_2'];
                $data['num']         = (int) $value['num'];
                $data['account']     = $data['price'] * $value['num'];
                $data['uid']         = $this->uid;
            }

            $result = table('MaterialGoods')->add($data);

            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '耗材保存失败,请稍后重试'));
            }

        }

        $this->appReturn(array('msg' => '操作成功'));
    }

}
