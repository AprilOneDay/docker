<?php
/**
 * 会员模块
 */
namespace app\chd\app\controller\v1\user;

use app\app\controller;
use app\chd\app\controller\v1\Init;

class Index extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1');
    }

    /** 会员中心 */
    public function index()
    {
        $user = table('User')->where('id', $this->uid)->field('username,avatar,nickname,mail,sex')->find();

        $user['country_copy'] = dao('Category')->getName($user['country']);
        $user['sex_copy']     = dao('Category')->getName($user['sex']);

        $user['avatar'] = $this->appImg($user['avatar'], 'avatar');

        $this->appReturn(array('data' => $user));

    }

    /**
     * 编辑个人信息
     * @date   2017-09-25T10:47:27+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {

        $data = table('User')->where(array('id' => $this->uid))->field('uid,nickname,mail,avatar,mobile,is_message,type,sex,country')->find();

        $data['country_copy'] = dao('Category')->getName($data['country']);
        $data['sex_copy']     = dao('Category')->getName($data['sex']);
        $data['avatar']       = $this->appImg($data['avatar'], 'avatar');
        $data['mobile']       = substr_replace($data['mobile'], '*****', 4, 5);
        $this->appReturn(array('data' => $data));

    }

    public function editPost()
    {
        $data['sex']      = post('sex', 'intval', '');
        $data['mail']     = post('mail', 'text', '');
        $data['nickname'] = post('nickname', 'text', '');

        $files['avatar'] = files('avatar');

        !$files['avatar'] ?: $data['avatar'] = $this->appUpload($files['avatar'], '', 'avatar');

        if (!$data['nickname']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入昵称'));
        }

        $reslut = table('User')->where(array('id' => $this->uid))->save($data);

        if (!$reslut) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));

        }

        $this->appReturn(array('msg' => '保存成功'));
    }

}
