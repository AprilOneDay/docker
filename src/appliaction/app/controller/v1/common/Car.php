<?php
/**
 * 车友圈模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class Car extends \app\app\controller\Init
{
    /**
     * 上架/下架/删除
     * @date   2017-09-19T11:32:14+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeStatus()
    {

        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }

        $id     = post('id', 'intval', 0);
        $type   = post('type', 'intval', 0);
        $status = post('status', 'text', '');

        if (!$type || $status == '' || !$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['id']  = $id;
        $map['uid'] = $this->uid;

        $data['status'] = (int) $status;

        if (!in_array($type, array(1, 2))) {
            $this->appReturn(array('status' => false, 'msg' => '非法type参数', 'data' => $type));
        }

        if (!in_array($data['status'], array(1, 2, 3, 4))) {
            $this->appReturn(array('status' => false, 'msg' => '非法status参数'));
        }

        switch ($type) {
            //汽车上下架
            case '1':
                $table = 'GoodsCar';
                break;
            //服务上下架
            case '2':
                $table = 'GoodsService';
                break;
            default:
                $this->appReturn(array('status' => false, 'msg' => '类型有误'));
                break;
        }

        $tableId = table($table)->where($map)->field('id')->find('one');
        if (!$tableId) {
            $this->appReturn(array('status' => false, 'msg' => '非法操作，信息不存在'));
        }

        $result = table($table)->where(array('id' => $tableId))->save($data);

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));

        }

        //记录服务商品数量
        if ($type == 'GoodsService' && $status == 1) {
            //增加商品总数 + 1
            table('UserShop')->where(array('uid' => $this->uid))->save(array('goods_num' => array('add', 1)));
        } elseif ($type == 'GoodsService' && $status != 1) {
            //增加商品总数 - 1
            table('UserShop')->where(array('uid' => $this->uid))->save(array('goods_num' => array('less', 1)));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }
}
