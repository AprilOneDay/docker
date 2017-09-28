<?php
namespace app\tools\dao;

class Enjoy
{
    /**
     * 增加喜欢
     * @date   2017-09-25T15:43:47+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  [type]                   $type  [description]
     * @param  [type]                   $value [description]
     */
    public function add($uid = 0, $type, $value)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '用户uid不存在');
        }

        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['type']  = $type;
        $map['value'] = $value;
        $map['uid']   = $uid;

        $is = table('Enjoy')->where($map)->field('id')->find('one');
        if ($is) {
            return array('status' => false, 'msg' => '请勿重复操作');
        }

        $data['uid']     = $uid;
        $data['type']    = $type;
        $data['value']   = $value;
        $data['created'] = TIME;

        $reslut = table('Enjoy')->add($data);
        if (!$reslut) {
            return array('status' => false, 'msg' => '操作失败');
        }

        return array('status' => true, 'msg' => '操作成功');

    }

    /**
     * 取消喜欢
     * @date   2017-09-25T15:43:54+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  [type]                   $type  [description]
     * @param  [type]                   $value [description]
     * @return [type]                          [description]
     */
    public function del($uid = 0, $type, $value)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '用户uid不存在');
        }

        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['type']  = $type;
        $map['value'] = $value;
        $map['uid']   = $uid;

        $id = table('Enjoy')->where($map)->field('id')->find('one');
        if (!$id) {
            return array('status' => false, 'msg' => '信息不存在');
        }

        $data['uid']     = $uid;
        $data['type']    = $type;
        $data['value']   = $value;
        $data['created'] = TIME;

        $reslut = table('Enjoy')->where('id', $id)->delete();
        if (!$reslut) {
            return array('status' => false, 'msg' => '操作失败');
        }

        return array('status' => true, 'msg' => '操作成功');
    }

}
