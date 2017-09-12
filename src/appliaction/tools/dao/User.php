<?php
namespace app\tools\dao;

class User
{
    public function register($data = array(), $password2 = '', $isAgree = 0, $code = '')
    {
        $data['password'] = trim(strtolower($data['password']));
        if (!in_array($data['type'], array(1, 2))) {
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

        if ($data['password'] !== $password2) {
            return array('status' => false, 'msg' => '两次密码不一致');
        }

        if (!$isAgree) {
            return array('status' => false, 'msg' => '请勾选服务协议');
        }

        $isUser = table('User')->where(array('username' => $data['username']))->field('id')->find('one');
        if ($isUser) {
            return array('status' => false, 'msg' => '用户名已注册请更换用户名');
        }

        $isMobile = table('User')->where(array('mobile' => $data['mobile']))->field('id')->find('one');
        if ($isUser) {
            return array('status' => false, 'msg' => '手机号已注册');
        }

        $data['salt']     = rand(10000, 99999);
        $data['password'] = md5($password . $data['salt']);
        $data['created']  = TIME;
        $data['ip']       = getIP();

        $reslut = table('User')->add($data);
        if (!$reslut) {
            return array('status' => false, 'msg' => '注册失败');
        }

        return array('status' => true, 'msg' => '注册成功');
    }

    public function login($account, $password)
    {
        $password = trim(strtolower($password));
        if (!$account) {
            return array('status' => false, 'msg' => '请输入手机号/用户名');
        }

        if (!$password) {
            return array('status' => false, 'msg' => '请输入手机号码');
        }

        $map['mobile']   = array('or', $account);
        $map['username'] = $account;
        $user            = table('User')->where($map)->field('password,salt,id')->find();
        if (!$user) {
            return array('status' => false, 'msg' => '该用户不存在');
        }

        if (md5($password . $user['salt']) != $user['password']) {
            return array('status' => false, 'msg' => '密码有误');
        }

        $data['token']    = md5(TIME . $user['salt']);
        $data['time_out'] = TIME + 3600 * 24 * 2;

        $reslut = table('User')->where(array('id' => $user['id']))->save($data);
        if (!$reslut) {
            return array('status' => false, 'msg' => '登录失败');
        }

        return array('status' => false, 'msg' => '登录失败', 'data' => $data);
    }
}
