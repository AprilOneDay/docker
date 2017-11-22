<?php
/**
 * 用户管理模块
 */
namespace app\tools\dao;

class User
{
    /**
     * 注册
     * @date   2017-10-13T09:14:10+0800
     * @author ChenMingjiang
     * @param  array                    $data       [主注册信息]
     * @param  string                   $password2  [确认密码]
     * @param  integer                  $isAgree    [授权同意]
     * @param  string                   $code       [验证码]
     * @param  array                    $thirdParty [第三方登录信息]
     * @return [type]                               [description]
     */
    public function register($data = array(), $password2 = '', $isAgree = 0, $code = '', $thirdParty = array())
    {
        $data['password'] = trim(strtolower($data['password']));
        $password2        = trim(strtolower($password2));

        if (!in_array($data['type'], array(1, 2))) {
            return array('status' => false, 'msg' => '注册类型不存在');
        }

        /* if (!$data['mobile']) {
        return array('status' => false, 'msg' => '请输入手机号码');
        }*/

        if (!$data['username']) {
            return array('status' => false, 'msg' => '请输入用户名');
        }

        /* if (!preg_match("/^[a-zA-Z0-9_@.]+$/", $data['username'])) {
        return array('status' => false, 'msg' => '用户名请勿使用特殊字符汉字字符');
        }*/

        if (!$data['password']) {
            return array('status' => false, 'msg' => '请输入密码');
        }

        if (strlen($data['password']) < 6) {
            return array('status' => false, 'msg' => '密码太过简单了');
        }

        if ($data['password'] !== $password2) {
            return array('status' => false, 'msg' => '两次密码不一致');
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

        if ($data['mobile']) {
            $isMobile = table('User')->where(array('mobile' => $data['mobile'], 'type' => $data['type']))->field('id')->find('one');
            if ($isMobile) {
                return array('status' => false, 'msg' => '手机号已注册');
            }
        }

        //检测验证码
        if ($code) {
            $reslutCode = dao('Sms')->checkVerification($data['mobile'], $code);
            if (!$reslutCode['status']) {
                return $reslutCode;
            }
        }

        //检测第三方登录
        if ($thirdParty) {
            $map = array();
            foreach ($thirdParty as $key => $value) {
                $map[$key] = $value;
            }

            $isThirdParty = table('UserThirdParty')->where($map)->field('uid')->find('one');

            if ($isThirdParty) {
                return array('status' => false, 'msg' => '已存在第三方授权记录,请直接登录');
            }
        }

        $data['uid']      = $this->createUid();
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
            table('UserShop')->add(array('uid' => $reslut, 'name' => $data['username'], 'credit_level' => 50));
        } else {
            //发送站内信
            dao('Message')->send($reslut, 'register_user');
        }

        //增加第三方登录信息
        if ($thirdParty) {
            $thirdParty['uid'] = $reslut;
            table('UserThirdParty')->add($thirdParty);
        }

        //增加积分明细
        dao('Integral')->add($reslut, 'user_registered');
        return array('status' => true, 'msg' => '注册成功');
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
    public function findPassword($uid, $password, $password2, $code = '')
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$password) {
            return array('status' => false, 'msg' => '请输入修改密码');
        }

        if (!$password2) {
            return array('status' => false, 'msg' => '请再次输入密码');
        }

        $password = trim(strtolower($password));

        if ($password !== $password2) {
            return array('status' => false, 'msg' => '两次密码不一致');
        }

        //检测验证码
        if ($code) {
            $reslutCode = dao('Sms')->checkVerification($data['mobile'], $code);
            if (!$reslutCode['status']) {
                return $reslutCode;
            }
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
     * 检测用户密码是否正确
     * @date   2017-11-16T10:43:55+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid [description]
     * @return [type]                        [description]
     */
    public function checkUserPassword($uid = 0, $password = '')
    {
        if (!$uid || !$password) {
            return false;
        }

        $user = dao('User')->getInfo($uid, 'salt,password');

        if (md5(trim(strtolower($password)) . $user['salt']) !== $user['password']) {
            return false;
        }

        return ture;
    }

    //创建uid
    public function createUid()
    {
        $id  = table('User')->order('id desc')->field('id')->find('one');
        $uid = rand(1000, 9999) . $id + 1;
        return $uid;
    }

    /**
     * 第三方登录
     * @date   2017-10-13T09:47:47+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function thirdPartyLogin($value, $imei = '')
    {
        $map['weixin_id'] = $value;

        $uid = table('UserThirdParty')->where($map)->field('uid')->find('one');
        if (!$uid) {
            return array('status' => false, 'msg' => '尚未注册');
        }

        $user = table('User')->where('id', $uid)->field('type,password,salt,id')->find();

        $data['token']      = md5(TIME . $user['salt']);
        $data['time_out']   = TIME + 3600 * 24 * 2;
        $data['type']       = $user['type'];
        $data['login_ip']   = getIP();
        $data['login_time'] = TIME;
        $data['imei']       = $imei;

        $reslut = table('User')->where(array('id' => $uid))->save($data);

        if (!$reslut) {
            return array('status' => false, 'msg' => '登录失败');
        }

        return array('status' => true, 'msg' => '登录成功', 'data' => $data);
    }

    /**
     * 登录
     * @date   2017-09-18T13:52:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $account  [description]
     * @param  [type]                   $password [description]
     * @return [type]                             [description]
     */
    public function login($account, $password, $imei = '', $type = 1)
    {
        $password = trim(strtolower($password));
        if (!$account) {
            return array('status' => false, 'msg' => '请输入手机号/用户名');
        }

        if (!$password) {
            return array('status' => false, 'msg' => '请输入手机号码');
        }
        $map['type']    = $type;
        $map['_string'] = "(mobile = '$account' or username = '$account')";
        $user           = table('User')->where($map)->field('type,password,salt,id')->find();

        if (!$user) {
            return array('status' => false, 'msg' => '该用户不存在');
        }

        if (md5($password . $user['salt']) != $user['password']) {
            return array('status' => false, 'msg' => '密码有误');
        }

        $data['token']          = md5(TIME . $user['salt']);
        $data['time_out']       = TIME + 3600 * 24 * 2;
        $data['type']           = $user['type'];
        $data['login_ip']       = getIP();
        $data['login_time']     = TIME;
        !$imei ?: $data['imei'] = (string) $imei;

        $reslut      = table('User')->where(array('id' => $user['id']))->save($data);
        $data['uid'] = $user['id'];

        if (!$reslut) {
            return array('status' => false, 'msg' => '登录失败');
        }

        //登录成功保存token
        cookie('token', $data['token'], $data['time_out']);

        return array('status' => true, 'msg' => '登录成功', 'data' => $data);
    }

    /**
     * 检测用户今日可用行为 每日签到/每日分享
     * @date   2017-09-18T13:58:32+0800
     * @author ChenMingjiang
     * @param  integer                  $uid [description]
     * @return boolean                       [true 可用 false 不可用]
     */
    public function todayAvailableBehavior($uid = 0, $content = '')
    {
        if (!$uid || !$content) {
            return array('status' => false, 'msg' => '参数错误', 'data' => false);
        }
        //今日时间戳
        $map['created'] = array('>=', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $map['uid']     = $uid;
        $map['flag']    = $content;

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

    /**
     * 根据uid获取用户信息
     * @date   2017-10-25T16:28:32+0800
     * @author ChenMingjiang
     * @param  integer                  $uid   [description]
     * @param  string                   $field [description]
     * @return [type]                          [description]
     */
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
    public function getShopCredit($uid = 0)
    {
        if (!$uid) {
            return '';
        }

        $map['type']     = 1;
        $map['shop_uid'] = $uid;
        $value           = table('Score')->where($map)->field('AVG(score) as score')->find('one');

        if ($value) {
            $value         = max($value, 0);
            $data['star']  = $value * 2;
            $data['value'] = sprintf('%.1f', $value / 10);
        } else {
            $data['star']  = 100;
            $data['value'] = 5;
        }

        return $data;
    }
}
