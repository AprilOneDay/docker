<?php
/**
 * 物流模块模块
 */
namespace app\fastgo\tools\dao;

class Material
{
    /** 剩余材料统计 */
    public function stockList($uid)
    {
        $map['uid'] = $uid;

        //获取总共使用的数量
        $list = table('MaterialUseLog')->where($map)->field('goods_id,SUM(num) as num')->group('CONCAT(goods_id,uid)')->find('array');

        foreach ($list as $key => $value) {
            $useArray[$value['goods_id']] = $value['num'];
        }

        //获取领取的数量
        $list = table('material_goods')->where('uid', $uid)->group('CONCAT(goods_id,uid)')->field('goods_id,SUM(num) as num,price')->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['name']         = dao('Category')->getName($value['goods_id']);
            $list[$key]['lave']         = $value['num'] - (int) $useArray[$value['goods_id']];
            $list[$key]['lave_account'] = $list[$key]['lave'] * $value['price'];

            $data['account'] = sprintf('2%d', $data['account'] + $value['num'] * $value['price']);
        }

        $data['list'] = $list ? $list : array();

        return $data;
    }
}
