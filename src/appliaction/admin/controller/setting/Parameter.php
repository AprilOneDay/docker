<?php
/** 参数配置模块 */
namespace app\admin\controller\setting;

use app\admin\controller\Init;

class Parameter extends Init
{
    /** 列表信息 */
    public function lists()
    {
        $map['del_status'] = 0;

        $list = table('ConsoleParameter')->where($map)->find('array');
        $this->assign('list', $list);
        $this->show();

    }

    /** 列表保存操作 */
    public function listsPost()
    {
        $id = post('id');

        foreach ($id as $key => $value) {
            $result = table('ConsoleParameter')->where('id', $key)->save('value', $value);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '保存失败'));
            }
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '保存成功'));
    }

    /** 添加配置信息参数 */
    public function editPost()
    {
        $id = get('id', 'intval', 0);

        $data['name']   = post('name', 'text', '');
        $data['type']   = post('type', 'text', '');
        $data['option'] = post('option', 'text', '');

        if (!$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入参数名称'));
        }

        if ($id) {
            $result = table('ConsoleParameter')->where('id', $id)->save($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '修改失败'));
            }
        } else {
            $result = table('ConsoleParameter')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
            }
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '添加完成'));
    }

    /** 添加配置信息 */
    public function edit()
    {
        $id = get('id', 'intval', 0);

        $data  = table('ConsoleParameter')->where('id', $id)->find();
        $other = array(
            'typeCopy' => getVar('type', 'admin.param'),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);
        $this->show();

    }

    public function del()
    {
        $id     = post('id', 'intval', 0);
        $result = table('ConsoleParameter')->where('id', $id)->save('del_status', 1);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));
    }
}
