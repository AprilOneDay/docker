<?php
/**
 * 财务模块
 */
namespace app\tools\dao;

class Finance
{
    //增加财务记录
    public function add($type = 0, $money = 0, $content = '', $isPay = 0)
    {
        if (!$money || !$type) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data['type']    = $type;
        $data['money']   = $money;
        $data['content'] = $content;
        $data['created'] = TIME;
        $data['is_pay']  = $isPay;

        $result = table('FinanceLog')->add($data);
        if (!$result) {
            dao('Log')->error(1, '财务记录插入异常,请立即查明原因');
            return false;
        }

        return true;
    }

    /**
     * 确认收取
     * @date   2017-10-13T11:51:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $map [description]
     * @return [type]                        [description]
     */
    public function pay($map)
    {
        if (!$map) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['is_pay'] = 0;

        $id = table('FinanceLog')->where($map)->field('id')->find('one');
        if (!$id) {
            return array('status' => false, 'msg' => '财务信息不存在');
        }

        $result = table('FinanceLog')->where('id', $id)->save('is_pay', 1);
        if (!$result) {
            return array('status' => false, 'msg' => '修改失败');
        }

        return array('status' => true, 'msg' => '操作成功');

    }
}
