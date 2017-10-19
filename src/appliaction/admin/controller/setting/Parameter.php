<?php
namespace app\admin\controller\setting;

class Parameter extends \app\admin\controller\Init
{
    public function lists()
    {
        if (IS_POST) {
            $id = post('id');

            foreach ($id as $key => $value) {
                $reslut = table('ConsoleParameter')->where('id', $key)->save('value', $value);
                if (!$reslut) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '保存失败'));
                }
            }

            $this->ajaxReturn(array('status' => true, 'msg' => '保存成功'));

        } else {
            $list = table('ConsoleParameter')->find('array');
            $this->assign('list', $list);
            $this->show();
        }

    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {
            $data['name']   = post('name', 'text', '');
            $data['type']   = post('type', 'text', '');
            $data['option'] = post('option', 'text', '');

            if (!$data['name']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请输入参数名称'));
            }

            if ($id) {
                $reslut = table('ConsoleParameter')->where('id', $id)->save($data);
                if (!$reslut) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '修改失败'));
                }
            } else {
                $reslut = table('ConsoleParameter')->add($data);
                if (!$reslut) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
                }
            }

            $this->ajaxReturn(array('status' => true, 'msg' => '添加完成'));

        } else {
            $data  = table('ConsoleParameter')->where('id', $id)->find();
            $other = array(
                'typeCopy' => getVar('type', 'admin.param'),
            );

            $this->assign('other', $other);
            $this->assign('data', $data);
            $this->show();
        }
    }
}
