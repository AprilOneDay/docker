<?php
/**
 * 全站公用模块管理
 */
namespace app\tools\dao;

class ChangeTable
{
    /**
     * 修改状态全站通用 统一修改status字段
     * @date   2017-09-20T09:18:09+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeStatus($table = '', $id = 0, $status = '')
    {
        if (!$table || !$id || $status === '') {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!table($table)->isTable()) {
            return array('status' => false, 'msg' => '非法操作');
        }

        $data = table($table)->where(array('id' => $id))->field('id,status')->find();

        if (!$data['id']) {
            return array('status' => false, 'msg' => '信息不存在');
        }

        if ($data['status'] == $status) {
            return array('status' => false, 'msg' => '请勿重复修改');
        }

        $result = table($table)->where(array('id' => $id))->save(array('status' => (int) $status));

        if (!$result) {
            return array('status' => false, 'msg' => '执行失败');
        }

        return array('status' => true, 'msg' => '操作成功');
    }

}
