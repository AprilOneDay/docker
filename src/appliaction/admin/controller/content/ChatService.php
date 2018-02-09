<?php
/**
 * 聊天客服模块管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;

class ChatService extends Init
{
    public function lists()
    {
        $map = array();

        $list = table('ChatService')->where($map)->find('array');

        $other = array(
            'statusCopy' => array(
                '1' => '显示',
                '0' => '隐藏',
            ),
        );

        $this->assign('other', $other);
        $this->assign('list', $list);
        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        if ($id) {
            $data = table('ChatService')->where('id', $id)->find();
        } else {
            $data = array('is_show' => 1);
        }

        $this->assign('data', $data);
        $this->show();
    }

    public function editPost()
    {
        $id = get('id', 'intval', 0);

        $data           = post('info');
        $data['avatar'] = post('avatar', 'img', '');

        if (!$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请填写客服姓名'));
        }

        if (!$id) {
            $result = table('ChatService')->add($data);
        } else {
            $result = table('ChatService')->where('id', $id)->save($data);
        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));
    }

    public function del()
    {
        $id = post('id', 'intval', 0);

        if (!$id) {
            $this->ajaxReturn(array('status' => false, 'msg' => '删除成功'));
        }

        $result = table('ChatService')->where('id', $id)->delete();
        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));
    }
}
