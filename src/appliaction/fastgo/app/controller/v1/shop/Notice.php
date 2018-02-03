<?php
/**
 * 商家通知模块
 */
namespace app\fastgo\app\controller\v1\shop;

use app\fastgo\app\controller\v1\Init;

class Notice extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual(2);
    }

    /** 列表查看 */
    public function lists()
    {
        $map['uid'] = $this->uid;

        $list = table('ShopNotice')->where($map)->find('array');

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 编辑查看 */
    public function edit()
    {
        $id = post('id', 'intval', 0);

        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $data = table('ShopNotice')->where($map)->find();

        $this->appReturn(array('data' => $data));
    }

    /** 编辑提交 */
    public function editPost()
    {
        $id              = post('id', 'intval', 0);
        $data['title']   = post('title', 'text', '');
        $data['content'] = post('content', 'text', '');

        if (!$data['title']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写标题'));
        }

        if (!$data['content']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入内容'));
        }

        if (!$id) {
            $data['uid']     = $this->uid;
            $data['created'] = TIME;

            $result = table('ShopNotice')->add($data);
        } else {
            $map['uid'] = $this->uid;
            $map['id']  = $id;

            $result = table('ShopNotice')->where($map)->save($data);
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));

    }

    /** 删除 */
    public function del()
    {
        $id = post('id', 'intval', 0);

        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $result = table('ShopNotice')->where($map)->delete();

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }
}
