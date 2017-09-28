<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

class IntegralRul extends \app\admin\controller\Init
{

    public function lists()
    {
        $list = table('IntegralRul')->find('array');

        $this->assign('list', $list);
        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {
            $data['value']  = post('value', 'intval', '');
            $data['status'] = post('status', 'intval', 0);

            if (!$id) {
                $this->ajaxReturn(array('status' => false, 'msg' => '参数错误'));
            }

            if (!$data['value']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请输入正确的积分'));
            }

            $result = table('IntegralRul')->where(array('id' => $id))->save($data);

            if ($result) {
                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            }

            $this->ajaxReturn(array('status' => false, 'msg' => '执行失败'));
        } else {
            $data = table('IntegralRul')->where(array('id' => $id))->find();

            $this->assign('data', $data);
            $this->show();
        }
    }
}
