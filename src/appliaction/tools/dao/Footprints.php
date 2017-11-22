<?php
/**
 * 用户浏览记录管理 数据量应该非常大 后续持续关注
 */
namespace app\tools\dao;

class Footprints
{
    /**
     * 增加用户足迹
     * @date   2017-09-21T11:51:15+0800
     * @author ChenMingjiang
     * @param  integer                  $uid    [description]
     * @param  integer                  $type   [description]
     * @param  string                   $value  [description]
     * @param  string                   $value2 [description]
     */
    public function add($uid = 0, $type = 1, $value = '', $value2 = '')
    {
        if (!$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$uid) {
            return array('status' => false, 'msg' => '只记录会员信息');
        }

        $id = table('Footprints')->where(array('uid' => $uid, 'type' => $type, 'value' => $value, 'value2' => $value2))->field('id')->find('one');

        //更新记录
        if ($id) {
            $data['created'] = TIME;
            $data['ip']      = getIP();
            $reslut          = table('Footprints')->where(array('id' => $id))->save($data);
        }
        //新增记录
        else {
            $data['uid']     = $uid;
            $data['type']    = $type;
            $data['value']   = $value;
            $data['value2']  = $value2;
            $data['created'] = TIME;
            $data['ip']      = getIP();
            $reslut          = table('Footprints')->add($data);
        }

        if (!$reslut) {
            return array('status' => false, 'msg' => '执行失败');
        }

        return array('status' => true, 'msg' => '添加成功');
    }

    /**
     * [增加浏览记录]
     * @date   2017-09-21T11:47:00+0800
     * @author ChenMingjiang
     * @param  integer                  $shopUid  [店铺uid]
     * @param  integer                  $type [类型]
     * @param  [type]                   $id   [访问id]
     */
    public function addHot($shopUid = 0, $type = 1, $id)
    {
        //记录总访问记录
        if ($shopUid) {
            $map['uid']  = $shopUid;
            $map['time'] = date('Y-m-d', TIME);

            $hotLogId = table('ShopHotLog')->where($map)->field('id')->find('one');
            if ($hotLogId) {
                table('ShopHotLog')->where(array('id' => $hotLogId))->save(array('num' => array('add', 1)));
            } else {
                $data['uid']  = $shopUid;
                $data['time'] = date('Y-m-d', TIME);

                table('ShopHotLog')->add($data);
            }
        }

        //商品访问记录
        if ($type == 1) {
            table('GoodsCar')->where(array('id' => $id))->save(array('hot' => array('add', 1)));
        } elseif ($type == 2) {
            table('GoodsService')->where(array('id' => $id))->save(array('hot' => array('add', 1)));
        }

    }
}
