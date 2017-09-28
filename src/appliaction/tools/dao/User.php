<?php
namespace app\tools\dao;

class User
{
    /**
     * 注册
     * @date   2017-09-18T13:52:36+0800
     * @author ChenMingjiang
     * @param  array                    $data      [description]
     * @param  string                   $password2 [description]
     * @param  integer                  $isAgree   [description]
     * @param  string                   $code      [description]
     * @return [type]                              [description]
     */
    public function register($data = array(), $password2 = '', $isAgree = 0, $code = '')
    {
        $data['password'] = trim(strtolower($data['password']));
        if (!in_array($data['type'], array(1, 2))) {
            return array('status' => false, 'msg' => '请选择商家/个人注册');
        }

        if (!$data['username']) {
            return array('status' => false, 'msg' => '请输入用户名');
        }

        if (!preg_match("/^[a-zA-Z0-9_]+$/", $data['username'])) {
            return array('status' => false, 'msg' => '用户名请勿使用特殊字符汉字字符');
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

        if (!preg_match("/^1[34578]{1}\d{9}$/", $data['mobile'])) {
            return array('status' => false, 'msg' => '请输入正确的电话号码');
        }

        if (!$data['type']) {
            return array('status' => false, 'msg' => '请选择注册类型');
        }

        if (!$isAgree) {
            return array('status' => false, 'msg' => '请勾选服务协议');
        }

        $isUser = table('User')->where(array('username' => $data['username'], 'type' => $data['type']))->field('id')->find('one');
        if ($isUser) {
            return array('status' => false, 'msg' => '用户名已注册请更换用户名');
        }

        $isMobile = table('User')->where(array('mobile' => $data['mobile'], 'type' => $data['type']))->field('id')->find('one');
        if ($isUser) {
            return array('status' => false, 'msg' => '手机号已注册');
        }

        $data['nickname'] = $data['username'];
        $data['salt']     = rand(10000, 99999);
        $data['password'] = md5($data['password'] . $data['salt']);
        $data['created']  = TIME;
        $data['ip']       = getIP();

        $reslut = table('User')->add($data);
        if (!$reslut) {
            return array('status' => false, 'msg' => '注册失败');
        }

        if ($data['type'] == 2) {
            table('UserShop')->add(array('uid' => $reslut, 'name' => $data['username']));
        } else {
            //发送站内信
            dao('Message')->send($reslut, 'register_user');
        }

        //增加积分明细
        dao('Integral')->add($reslut, 1);

        return array('status' => true, 'msg' => '注册成功');
    }

    /**
     * 登录
     * @date   2017-09-18T13:52:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $account  [description]
     * @param  [type]                   $password [description]
     * @return [type]                             [description]
     */
    public function login($account, $password, $imei = '')
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
        $user            = table('User')->where($map)->field('type,password,salt,id')->find();
        if (!$user) {
            return array('status' => false, 'msg' => '该用户不存在');
        }

        if (md5($password . $user['salt']) != $user['password']) {
            return array('status' => false, 'msg' => '密码有误');
        }

        $data['token']      = md5(TIME . $user['salt']);
        $data['time_out']   = TIME + 3600 * 24 * 2;
        $data['type']       = $user['type'];
        $data['login_ip']   = getIP();
        $data['login_time'] = TIME;
        $data['imei']       = $imei;

        $reslut = table('User')->where(array('id' => $user['id']))->save($data);

        if (!$reslut) {
            return array('status' => false, 'msg' => '登录失败');
        }

        return array('status' => true, 'msg' => '登录成功', 'data' => $data);
    }

    /**
     * 修改密码
     * @date   2017-09-25T10:47:02+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid       [description]
     * @param  [type]                   $password  [description]
     * @param  [type]                   $password2 [description]
     * @param  [type]                   $code      [description]
     * @return [type]                              [description]
     */
    public function findPassword($uid, $password, $password2, $code)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$password) {
            return array('status' => false, 'msg' => '请输入修改密码');
        }

        $password = trim(strtolower($password));

        if ($password !== $password2) {
            return array('status' => false, 'msg' => '两次密码不一致');
        }

        $salt = table('User')->where('id', $uid)->field('salt')->find('one');
        if (!$salt) {
            return array('status' => false, 'msg' => '信息有误');
        }

        $data['password'] = md5($password . $salt);
        $data['token']    = '';
        $reslut           = table('User')->where('id', $uid)->save($data);

        if (!$reslut) {
            return array('status' => false, 'msg' => '修改密码失败');
        }

        return array('status' => true, 'msg' => '修改密码成功');

    }

    /**
     * 检测用户今日可用行为 每日签到/每日分享
     * @date   2017-09-18T13:58:32+0800
     * @author ChenMingjiang
     * @param  integer                  $uid [description]
     * @return boolean                       [true 可用 false 不可用]
     */
    public function todayAvailableBehavior($uid = 0, $content = '每日签到')
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '参数错误', 'data' => false);
        }
        //今日时间戳
        $map['created'] = array('>=', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $map['uid']     = $uid;
        $map['content'] = $content;

        $is = table('IntegralLog')->where($map)->field('id')->find();
        //echo table('IntegralLog')->getSql();die;
        if ($is) {
            return array('status' => true, 'msg' => '已操作', 'data' => array('bool' => false));
        }

        return array('status' => true, 'msg' => '可用', 'data' => array('bool' => true));
    }

    /**
     * 获取我的积分
     * @date   2017-09-18T15:34:26+0800
     * @author ChenMingjiang
     * @param  integer                  $uid [description]
     * @return [type]                        [description]
     */
    public function getIntegral($uid = 0)
    {
        if (!$uid) {
            return false;
        }

        $data = (int) table('User')->where(array('id' => $uid))->field('integral')->find('one');

        return $data;
    }

    public function getInfo($uid = 0, $field = '*')
    {
        $data = table('User')->where(array('id' => $uid))->field($field)->find();
        if (count($data) == 1) {
            return $data[$field];
        }
        return $data;
    }

    /**
     * 获取用户名称
     * @date   2017-09-19T09:52:14+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid [description]
     * @return [type]                        [description]
     */
    public function getNickname($uid)
    {
        $data = (string) table('User')->where(array('id' => $uid))->field('nickname')->find('one');

        return $data;
    }

    /**
     * 获取商品店铺名称
     * @date   2017-09-19T09:55:58+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid [description]
     * @return [type]                        [description]
     */
    public function getShopName($uid)
    {
        $data = table('UserShop')->where(array('uid' => $uid))->field('name')->find('one');

        return $data;
    }

    /**
     * 转换星星数量 于评价值 满分50 一个星星10分
     * @date   2017-09-20T15:33:18+0800
     * @author ChenMingjiang
     * @param  [type]                   $value [description]
     * @return [type]                          [description]
     */
    public function getShopCredit($value)
    {
        $value         = max($value, 0);
        $data['star']  = $value * 2;
        $data['value'] = $value / 10;

        return $data;
    }
}
