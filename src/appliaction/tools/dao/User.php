<?php
/**
 * 用户管理模块
 */
namespace app\tools\dao;

use denha\Smtp;

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
    public function register($data = array(), $password2 = '', $isAgree = 1, $code = '', $thirdParty = array())
    {
        $data['password'] = trim(strtolower($data['password']));
        $password2        = trim(strtolower($password2));

        $userType = array_flip(getVar('type', 'admin.user'));

        if (!in_array($data['type'], $userType)) {
            return array('status' => false, 'msg' => '注册类型不存在');
        }

        if (!$data['username']) {
            return array('status' => false, 'msg' => '请输入用户名');
        }

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
            $resultCode = dao('Sms')->checkVerification($data['mobile'], $code);
            if (!$resultCode['status']) {
                return $resultCode;
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

        //创建新账户
        $data['nickname'] = $data['username'];
        $data['salt']     = rand(10000, 99999);
        $data['password'] = md5($data['password'] . $data['salt']);
        $data['created']  = TIME;
        $data['ip']       = getIP();
        if (!$data['uid']) {
            $data['uid'] = $this->createUid();
        }

        //注册支付宝账户
        $result = dao('TaobaoUser')->index($data['uid']);
        if ($result['status']) {
            $result = dao('TaobaoUser')->update(array('uid' => $data['uid'], 'nickname' => $data['nickname'], 'password' => $data['salt']));
        } else {
            $result = dao('TaobaoUser')->add(array('uid' => $data['uid'], 'nickname' => $data['nickname'], 'password' => $data['salt']));
        }

        if (!$result) {
            return array('status' => false, 'msg' => '通讯组件注册失败,请重试');
        }

        $result = table('User')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '注册失败' . $data['ip']);
        }

        //添加店铺信息
        if ($data['type'] == 2) {
            table('UserShop')->add(array('uid' => $result, 'name' => $data['username'], 'avatar' => empty($data['avatar']) ? '' : $data['avatar'], 'credit_level' => 50));
        } else {
            //发送站内信
            dao('Message')->send($result, 'register_user');
        }

        //增加第三方登录信息
        if ($thirdParty) {
            $thirdParty['uid'] = $result;
            table('UserThirdParty')->add($thirdParty);
        }

        //增加积分明细
        dao('Integral')->add($result, 'user_registered');
        return array('status' => true, 'msg' => '注册成功', 'data' => $result);
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
    public function findPassword($uid, $password, $password2, $code = '', $mobile)
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
            $resultCode = dao('Sms')->checkVerification($mobile, $code);
            if (!$resultCode['status']) {
                return $resultCode;
            }
        }

        $salt = table('User')->where('id', $uid)->field('salt')->find('one');
        if (!$salt) {
            return array('status' => false, 'msg' => '信息有误');
        }

        $data['password'] = md5($password . $salt);
        $data['token']    = '';
        $result           = table('User')->where('id', $uid)->save($data);

        if (!$result) {
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
        $user = table('User')->fieldStatus('Auto_increment');
        $uid  = rand(1000, 9999) . $user['Auto_increment'];
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

        $result = table('User')->where(array('uid' => $uid))->save($data);

        if (!$result) {
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
            return array('status' => false, 'msg' => '请输入密码');
        }
        $map['type']    = $type;
        $map['_string'] = "(mobile = '$account' or username = '$account')";
        $user           = table('User')->where($map)->field('id,uid,type,password,salt,id')->find();

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

        !$imei ?: $data['imei'] = (string) $imei;

        $result = table('User')->where(array('id' => $user['id']))->save($data);

        //增加返回字段
        $data['uid']  = $user['uid'];
        $data['salt'] = $user['salt'];

        if (!$result) {
            return array('status' => false, 'msg' => '登录失败');
        }

        //登录成功保存token
        cookie('token', $data['token'], $data['time_out']);
        return array('status' => true, 'msg' => '登录成功', 'data' => $data);
    }

    /**
     * 发送邮箱验证码
     * @date   2017-12-25T11:38:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid [description]
     * @return [type]                        [description]
     */
    public function sendMailCode($mail, $title = '您的验证码信息', $group = 1)
    {

        if (!$mail) {
            return array('status' => false, 'msg' => '请填写邮箱');
        }

        $code    = rand('10000', '99999');
        $content = '您的邮箱验证：' . $code;

        $smtp   = new Smtp($group);
        $result = $smtp->sendmail($mail, $title, $content);

        if (!$result) {
            return array('status' => false, 'msg' => '验证码发送失败，请联系管理员', 'debug' => $result);
        }

        //保存验证码
        $map['mail'] = $mail;
        $mailVerify  = table('MailVerify')->where($map)->field('id,created')->find();
        if ($mailVerify) {

            $data['code']    = $code;
            $data['created'] = TIME;
            table('MailVerify')->where('id', $mailVerify['id'])->save($data);

        } else {
            $data['code']    = $code;
            $data['created'] = TIME;
            $data['mail']    = $mail;
            table('MailVerify')->add($data);
        }

        return array('status' => true, 'msg' => '验证码发送成功');
    }

    /**
     * 验证码匹配
     * @date   2017-12-25T14:57:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $mail [邮箱号]
     * @param  [type]                   $code [验证码]
     * @return [type]                         [description]
     */
    public function checkMailCode($mail, $code)
    {
        if (!$mail) {
            return array('status' => false, 'msg' => '请填写邮箱');
        }

        if (!$code) {
            return array('status' => false, 'msg' => '请填写验证码');
        }

        $mailVerify = table('MailVerify')->where('mail', $mail)->field('code,created')->find();
        if (!$mailVerify) {
            return array('status' => false, 'msg' => '验证码不存在');
        }

        if (TIME - $mailVerify['created'] > $time) {
            return array('status' => false, 'msg' => '验证码时间已过期');
        }

        if ($mailVerify['code'] != $code) {
            return array('status' => false, 'msg' => '验证码错误');
        }

        return array('status' => true, 'msg' => '匹配成功');
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

        $data = (int) table('User')->where(array('uid' => $uid))->field('integral')->find('one');

        return $data;
    }

    public function updateLevel($uid)
    {
        //获取历史积分
        $map          = array();
        $map['uid']   = $uid;
        $map['value'] = array('>', 0);

        $integral = table('integralLog')->where($map)->field('SUM(value) as value')->find('one');

        //获取可达等级
        $map          = array();
        $map['value'] = array('>=', (int) $value);

        $rule = table('UserLevelRule')->where($map)->order('value desc')->find();
        if (!$rule) {
            return false;
        }
        //判断是否为当前等级
        $level = $this->getInfo($uid, 'level');
        if ($level == $rule['id']) {
            return false;
        }

        //更新等级
        $result = table('User')->where('uid', $uid)->save('level', $rule['id']);
        if (!$result) {
            return false;
        }

        //赠送抵扣卷
        if ($rule['coupon_gift']) {
            $couponGift = explode(',', $rule['coupon_gift']);

            foreach ($couponGift as $key => $value) {
                $result = dao('Coupon')->send($uid, $value, 1, 0);
            }
        }

        return true;
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
        $data = table('User')->where(array('uid' => $uid))->field($field)->find();
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
        $data = (string) table('User')->where(array('uid' => $uid))->field('nickname')->find('one');

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
