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
     * 判断用户是否喜欢
     * @date   2017-11-17T14:32:00+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid   [description]
     * @param  [type]                   $type  [description]
     * @param  [type]                   $value [description]
     * @return boolean                         [description]
     */
    public function isLike($uid = 0, $type = 0, $value = 0)
    {
        if (!$uid || !$type || !$value) {
            return false;
        }

        $map['type']  = $type;
        $map['uid']   = $uid;
        $map['value'] = $value;

        return (bool) table('Enjoy')->where($map)->field('id')->find();
    }

    /**
     * 获取喜欢总数
     * @date   2017-11-17T14:31:47+0800
     * @author ChenMingjiang
     * @param  [type]                   $type  [description]
     * @param  [type]                   $value [description]
     * @return [type]                          [description]
     */
    public function count($type, $value)
    {
        $map['type']  = $type;
        $map['value'] = $value;

        $count = (int) table('Enjoy')->where($map)->count();
        return (int) $count;
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
