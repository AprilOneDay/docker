<?php
namespace app\app\tools\dao;

class User
{
    public function register($array, $type = 0, $isAgree = 0)
    {
        if (!$type) {
            return array('status' => false, 'msg' => '请选择商家/个人注册');
        }

        if (!$data['username']) {
            return array('status' => false, 'msg' => '请输入用户名');
        }

        if (!$data['mobile']) {
            return array('status' => false, 'msg' => '请输入手机号码');
        }

        if (!$data['password']) {
            return array('status' => false, 'msg' => '请输入密码');
        }

        if ($data['password'] !== $data['password2']) {
            return array('status' => false, 'msg' => '两次密码不一致');
        }

        if (!$isAgree) {
            return array('status' => false, 'msg' => '请勾选服务协议');
        }

        $isUser = table('User')->find();

    }
}
