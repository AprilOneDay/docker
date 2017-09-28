<?php
namespace app\tools\dao;

class Collection
{
    /**
     * 增加收藏
     * @date   2017-09-19T14:38:07+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  integer                  $type  [description]
     * @param  string                   $value [description]
     */
    public function add($uid = 0, $type = 0, $value = '')
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '请登录');
        }

        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data['uid']     = $uid;
        $data['type']    = $type;
        $data['value']   = $value;
        $data['created'] = TIME;

        $is = table('Collection')->where(array('uid' => $uid, 'type' => $type, 'value' => $value, 'del_status' => 0))->field('id')->find('one');
        if ($is) {
            return array('status' => false, 'msg' => '已经收藏了,请勿重复收藏');
        }

        $result = table('Collection')->add($data);
        if ($result) {
            return array('status' => true, 'msg' => '收藏成功');
        }

        return array('status' => false, 'msg' => '执行失败');
    }

    /**
     * 删除收藏
     * @date   2017-09-19T14:38:01+0800
     * @author ChenMingjiang
     * @param  integer                  $uid [description]
     * @param  integer                  $id  [description]
     * @return [type]                        [description]
     */
    public function del($uid = 0, $type = 0, $id = 0)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '请登录');
        }

        if (!$id) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$type) {
            return array('status' => false, 'msg' => '参数类型错误');
        }

        $collectionId = table('Collection')->where(array('value' => $id, 'uid' => $uid, 'type' => $type, 'del_status' => 0))->field('id')->find('one');
        if (!$collectionId) {
            return array('status' => true, 'msg' => '操作失败,信息不存在');
        }

        $result = table('Collection')->where(array('id' => $collectionId))->save(array('del_status' => 1));

        if ($result) {
            return array('status' => true, 'msg' => '删除收藏成功');
        }

        return array('status' => false, 'msg' => '执行失败');
    }
}
