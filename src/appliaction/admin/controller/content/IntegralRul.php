<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

class IntegralRul extends \app\admin\controller\Init
{

    /**
     * 积分规则列表
     * @date   2017-10-25T13:33:29+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $list = table('IntegralRule')->find('array');

        $this->assign('list', $list);
        $this->show();
    }

    /**
     * 积分规则编辑
     * @date   2017-10-25T13:33:22+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function editPost()
    {
        $id = get('id', 'intval', 0);

        $data          = post('all');
        $data['limit'] = post('limit', 'text', '');

        if (!is_numeric($data['value'])) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入正确的积分'));
        }

        //修改
        if ($id) {
            $result = table('IntegralRule')->where(array('id' => $id))->save($data);

        } else {
            $result = table('IntegralRule')->add($data);
        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '执行失败'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /**
     * 获取编辑规则雷荣
     * @date   2017-10-25T13:33:38+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $data = table('IntegralRule')->where(array('id' => $id))->find();
        } else {
            $data = array('integral' => 0, 'status' => 1);
        }

        $this->assign('data', $data);
        $this->show();

    }
}
