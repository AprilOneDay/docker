<?php
/**
 * 分组管理模块
 */
namespace app\admin\controller\setting;

class Group extends \app\admin\controller\Init
{
    /**
     * 分组列表
     * @date   2017-10-09T10:51:28+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function index()
    {
        $list = table('ConsoleGroup')->order('level asc')->find('array');

        $this->assign('list', $list);
        $this->assign('statusCopy', array(0 => '关闭', 1 => '开启'));
        $this->show();
    }

    /**
     * 添加/编辑分组
     * @date   2017-10-09T10:51:28+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {

            $data['name']   = post('name', 'text', '');
            $data['level']  = post('level', 'intval', 0);
            $data['status'] = post('status', 'text', '');

            if (!$data['name']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请输入分组名称'));
            }

            if ($id) {
                if ($id == 1) {
                    $this->ajaxReturn(['status' => false, 'msg' => '超级管理员权限不可修改']);
                }

                $admin = table('ConsoleGroup')->where(array('id' => $id))->field('id')->find();
                if (!$admin) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '信息不存在'));
                }

                $result = table('ConsoleGroup')->where(array('id' => $id))->save($data);
                if ($result) {
                    $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('status' => false, 'msg' => '修改失败'));
                }
            } else {
                $result = table('ConsoleGroup')->add($data);
                if ($result) {
                    $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
                }
            }

        } else {
            if ($id) {
                $rs = table('ConsoleGroup')->field('id,name,status,level')->where(array('id' => $id))->find();
                $this->assign('data', $rs);
            } else {
                $rs['status'] = 1;
                $rs['level']  = 1;
                $this->assign('data', $rs);
            }

            $this->show();
        }
    }
}
