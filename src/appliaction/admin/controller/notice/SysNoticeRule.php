<?php
/**
 * 通知模板管理
 */
namespace app\admin\controller\notice;

use app\admin\controller\Init;

class SysNoticeRule extends Init
{
    public function index()
    {
        $list = table('SysNoticeRule')->field('id,title,flag,content,status')->find('array');

        $this->assign('list', $list);

        $this->show();
    }

    public function detail()
    {
        $id = get('id', 'intval', 0);

        if ($id) {
            $data = table('SysNoticeRule')->where('id', $id)->find();
        } else {
            $data = array('status' => 1);
        }

        $this->assign('data', $data);
        $this->show();
    }

    public function edit()
    {
        $id   = get('id', 'intval', 0);
        $data = post('info');

        if (!$data['title']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入标题'));
        }

        if (!$data['content']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入内容'));
        }

        if (!$id) {
            if (!$data['flag']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请上传标识符'));
            }

            $result = table('SysNoticeRule')->add($data);
        } else {
            $result = table('SysNoticeRule')->where('id', $id)->save($data);
        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

    }
}
