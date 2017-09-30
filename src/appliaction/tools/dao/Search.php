<?php
namespace app\tools\dao;

class Search
{

    /**
     * 执行
     * @date   2017-09-28T17:08:52+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  integer                  $type  [description]
     * @param  string                   $value [description]
     * @return [type]                          [description]
     */
    public function run($uid = 0, $type = 0, $value = '')
    {
        $result = $this->check($type, $value);
        if (!$result['status']) {
            return $result;
        }

        $result = $this->addLog($uid, $type, $value);
        return true;
    }

    /**
     * 搜索检测
     * @date   2017-09-20T09:16:01+0800
     * @author ChenMingjiang
     * @param  integer                  $type  [description]
     * @param  string                   $value [description]
     * @return [type]                          [description]
     */
    public function check($type = 0, $value = '')
    {
        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['value']  = $value;
        $map['type']   = $type;
        $map['status'] = 1;

        $is = table('SearchDisable')->where($map)->field('id')->find('one');
        if (!$is) {
            return array('status' => false, 'msg' => '根据相关规定 ' . $value . ' 已被禁止');
        }

        return array('status' => true, 'msg' => '通过验证');
    }

    /**
     * 增加搜索记录
     * @date   2017-09-20T09:16:28+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid   [description]
     * @param  [type]                   $type  [description]
     * @param  [type]                   $value [description]
     */
    public function addLog($uid = 0, $type = 0, $value = '')
    {
        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data['uid']     = $uid;
        $data['type']    = $type;
        $data['value']   = $value;
        $data['created'] = TIME;

        $map['value'] = $value;
        $map['type']  = $type;
        $map['uid']   = $uid;

        $id = table('SearchLog')->where($map)->field('id')->find('one');
        if (!$id) {
            $result = table('SearchLog')->add($data);
        } else {
            $result = table('SearchLog')->where(array('id' => $id))->save(array('hot' => array('add', 1)));
        }

        if (!$result) {
            return array('status' => false, 'msg' => '执行失败');
        }
        return array('status' => true, 'msg' => '添加成功');
    }

    /**
     * 增加搜索推荐
     * @date   2017-09-20T09:17:51+0800
     * @author ChenMingjiang
     */
    public function addRecommend($type = 0, $value = '')
    {
        if (!$type) {
            return array('status' => false, 'msg' => '请选择类型');
        }

        if (!$value) {
            return array('status' => false, 'msg' => '请输入内容');
        }

        $data['type']    = (int) $type;
        $data['value']   = (string) $value;
        $data['created'] = TIME;

        //查询是否禁用
        $isDisable = table('SearchDisable')->where(array('type' => $type, 'value' => $value))->field('id')->find('one');
        if ($isDisable) {
            return array('status' => false, 'msg' => '关键词已被禁用不可推荐,请先删除禁用关键字');
        }

        $id = table('SearchRemmond')->where(array('type' => $type, 'value' => $value))->field('id')->find('one');
        if ($id) {
            return array('status' => false, 'msg' => '请勿重复添加');
        }

        $result = table('SearchRemmond')->add($data);

        if ($result) {
            return array('status' => true, 'msg' => '添加搜索推荐成功');
        }

    }

    /**
     * 增加搜索禁用
     * @date   2017-09-20T09:17:58+0800
     * @author ChenMingjiang
     */
    public function addDisable($type = 0, $value = '')
    {
        if (!$type) {
            return array('status' => false, 'msg' => '请选择类型');
        }

        if (!$value) {
            return array('status' => false, 'msg' => '请输入内容');
        }

        $data['type']  = (int) $type;
        $data['value'] = (string) $value;

        //查询是否推荐
        $isDisable = table('SearchRemmond')->where(array('type' => $type, 'value' => $value))->field('id')->find('one');
        if ($isDisable) {
            return array('status' => false, 'msg' => '关键词已被推荐不可禁用,请先删除推荐关键字');
        }

        $id = table('SearchDisable')->where(array('type' => $type, 'value' => $value))->field('id')->find('one');

        if ($id) {
            return array('status' => false, 'msg' => '请勿重复添加');
        }

        $result = table('SearchDisable')->add($data);

        if ($result) {
            return array('status' => true, 'msg' => '添加搜索禁用成功');
        }
    }

    /**
     * 删除推荐 删除禁用
     * @date   2017-09-30T11:56:12+0800
     * @author ChenMingjiang
     * @param  [type]                   $table [description]
     * @param  integer                  $type  [description]
     * @param  string                   $value [description]
     * @return [type]                          [description]
     */
    public function del($table, $type = 0, $value = '')
    {
        if (!in_array($table, array('SearchDisable', 'SearchRemmond'))) {
            return array('status' => false, 'msg' => '非法操作');
        }

        if (!$type || !$value) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['type']  = $type;
        $map['value'] = $value;

        $id = table($table)->where($map)->field('id')->find('one');

        if (!$id) {
            return array('status' => false, 'msg' => '信息不存在');
        }

        $reslut = table($table)->where('id', $id)->delete();
        if (!$reslut) {
            $this->ajaxReturn(array('status' => false, 'msg' => '删除失败'));
        }
        return array('status' => true, 'msg' => '删除成功');
    }

}
