<?php
/**
 * 商家会员相关
 */
namespace app\fastgo\app\controller\v1\common;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class User extends Init
{
    /**
     * @method 注册
     * @url    email/send?token=xxx
     * @http   POST
     * @param  type                string [必填] 类型 1个人 2商家
     * @param  username            string [必填] 用户名
     * @param  password            string [必填] 密码
     * @param  password2           string [必填] 确认密码
     * @param  mobile              string [必填] 手机号
     * @param  code                string [必填] 验证码
     * @param  is_agree            string [非必填] 是否同意 1同意 0不同意(默认0)
     * @author Chen Mingjiang
     * @return
     * {"status":false,"msg":'失败原因',"code":200,data:[]}
     */
    public function register()
    {
        $data['username'] = post('username', 'text', '');
        $data['password'] = post('password', 'trim', '');
        $data['mobile']   = post('mobile', 'text', '');
        $data['country']  = post('country', 'text', '');
        $data['type']     = 1;

        $weixinId = post('weixin_id', 'text', '');
        $code     = '';

        $password2 = $data['password'];
        $isAgree   = post('is_agree', 'intval', 0);

        if (!$data['country']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入国家'));
        }

        if (!$data['mobile']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入手机号'));
        }

        $thirdParty = array();
        if ($weixinId) {
            $thirdParty['weixin_id'] = $weixinId;
        }

        $result = dao('User')->register($data, $password2, $isAgree, $code, $thirdParty);

        $this->appReturn($result);
    }

    /**
     * 登录
     * @date   2017-10-13T09:58:01+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function login()
    {
        $account  = post('account', 'text', '');
        $password = post('password', 'text', '');
        $type     = post('type', 'intval', 0);

        $typeCopy = array_flip(getVar('type', 'admin.user'));
        if (!$type) {
            $this->appReturn(array('status' => false, 'msg' => '请选择登录方式'));
        }

        $result = dao('User')->login($account, $password, $this->imei, $type);

        if ($result['status']) {
            if ($result['data']['type'] != $type) {
                $this->appReturn(array('status' => false, 'msg' => '请选择' . $typeCopy[$result['data']['type']] . '登录'));
            }
        }

        if ($result['status']) {
            dao('User')->updateLevel($result['data']['uid']);
        }

        $this->appReturn($result);
    }

    /** 绑定邮箱 */
    public function bindMail()
    {
        parent::__construct();

        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录', 'code' => 501));
        }

        $time = 60 * 60 * 2;

        $mail = post('mail', 'text', '');
        $code = post('code', 'text', '');

        $result = dao('User')->checkMailCode($mail, $code);
        if (!$result) {
            $this->appReturn($result);
        }

        $user = dao('User')->getInfo($this->uid, 'is_bind_mail,mail');
        //解除绑定
        if ($user['is_bind_mail']) {
            $data['is_bind_mail'] = 0;
        }
        //增加绑定
        else {
            $data['mail']         = $mail;
            $data['is_bind_mail'] = 1;
        }

        $result = table('User')->where('uid', $this->uid)->save($data);

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '邮箱已绑定失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '邮箱绑定成功'));

    }

    /**
     * 修改用户密码
     * @date   2017-09-25T11:11:42+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function findPassword()
    {
        parent::__construct();

        $mobile = post('mobile', 'text', '');

        $password  = post('password', 'text', '');
        $password2 = post('password2', 'text', '');
        $type      = post('type', 'intval', 1);

        $code = post('code', 'text', '');
        $code = '';

        if (!$mobile) {
            $this->appReturn(array('status' => false, 'msg' => '请输入手机号'));
        }

        $map['mobile'] = $mobile;

        $user = table('User')->where($map)->field('id')->find();

        if (!$user) {
            $this->appReturn(array('status' => false, 'msg' => '尚未注册'));
        }

        $reslut = dao('User')->findPassword($user['id'], $password, $password2, $code, $mobile);
        $this->appReturn($reslut);
    }

    /**
     * 第三方登录
     * @date   2017-10-13T09:58:01+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function thirdPartyLogin()
    {
        $account = post('account', 'text', '');
        $type    = post('type', 'intval', 0);

        $typeCopy = array('1' => '个人', '2' => '商家');

        if (!$type) {
            $this->appReturn(array('status' => false, 'msg' => '请选择登录方式'));
        }

        $result = dao('User')->thirdPartyLogin($account, $this->imei);

        if ($result['status']) {
            if ($result['data']['type'] != $type) {
                $this->appReturn(array('status' => false, 'msg' => '请选择' . $typeCopy[$result['data']['type']] . '登录'));
            }
        }

        if ($type == 2) {
            $$result['data']['is_ide'] = table('UserShop')->where('uid', $result['data']['uid'])->field('is_ide')->find('one');
        }

        $this->appReturn($result);
    }

    /** 发送验证码 */
    public function sendMailCode()
    {
        $mail   = post('mail', 'text', '');
        $result = dao('User')->sendMailCode($mail);
        $this->appReturn($result);

    }

    /*修改手机号*/
    public function updateMobile()
    {

        $password   = post('password', 'text', '');
        $mobile     = post('mobile', 'text', '');
        $mobileCode = post('code', 'intval', '');

        $model = table('User');
        $user  = $model->where(array('id' => $this->uid))->find();

        if (md5($password . $user['salt']) != $user['password']) {
            $this->appReturn(array('status' => false, 'msg' => '原密码输入有误' . $this->uid));
        }
        $res = $model->where(array('mobile' => $mobile))->field('id')->find();
        if ($res) {

            $this->appReturn(array('status' => false, 'msg' => '当前手机号已被注册'));

        }
        //未验证短信验证码

        $result = $model->where('id', $this->uid)->save(array('mobile' => $mobile));
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '手机号修改失败,请稍后重试'));
        }
        $this->appReturn(array('msg' => '手机号修改成功'));
    }

    /*添加问题反馈*/
    public function addProFeedback()
    {

        $data['priority']    = post('priority', 'intval', '');
        $data['type']        = post('type', 'intval', '');
        $data['number']      = post('number', 'text', '');
        $data['uid']         = $this->uid;
        $data['description'] = post('description', 'text', '');
        $data['remark']      = post('remark', 'text', '');

        if (!$data['priority']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择问题优先级'));
        }
        if (!$data['type']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择问题类型'));
        }
        if (!$data['number']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写运单号'));
        }
        if (!$data['description']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写问题描述'));
        }

        $files['file_name'] = files('file_name');

        if ($files['file_name']) {
            $data['file_name'] = $this->appUpload($files['file_name'], '', 'feedBack');
        }

        $data['create_time'] = time();
        $result              = table('userFeedback')->add($data);

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '问题反馈失败,请稍后重试'));
        }
        $this->appReturn(array('msg' => '问题反馈成功，请等待回复'));

    }

    /*问题反馈列表*/
    public function proFeedList()
    {

        $type        = post('type', 'intval', '');
        $page        = post('page', 'intval', 1);
        $limit       = post('limit', 'intval', 5);
        $whe['uid']  = $this->uid;
        $whe['type'] = $type;

        $feedlist = table('userFeedback')->where($whe)->limit(($page - 1) * $limit, $limit)->field('id,number,description,status,create_time,file_name,result')->find('array');

        if (empty($feedlist)) {

            $this->appReturn(array('status' => false, 'msg' => '暂无问题反馈内容'));

        }

        foreach ($feedlist as $k => $v) {

            $feedlist[$k]['create_time'] = date('Y-m-d', $v['create_time']);
            $feedlist[$k]['file_name']   = $this->appImgArray($v['file_name'], 'feedBack');
            $feedlist[$k]['stat_title']  = $v['status'] == 0 ? '待回复' : '已回复';

        }

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $feedlist));

    }

    /*包裹统计*/
    public function packageStatistics()
    {

        $page       = post('page', 'intval', 1);
        $limit      = post('limit', 'intval', 5);
        $start_time = post('start_time', 'text', '');
        $end_time   = post('end_time', 'text', '');

        if ($start_time && !$end_time) {

            $arr['created'] = array('>=', strtotime($start_time));

        }
        if (!$start_time && $end_time) {

            $arr['created'] = array('<=', strtotime($end_time));

        }
        if ($start_time && $end_time) {

            $arr['created'] = array(array('>=', strtotime($start_time)), array('<=', strtotime($end_time)));

        }

        $arr['type']   = 4;
        $arr['uid']    = $this->uid;
        $arr['is_pay'] = 1;

        $data = table('orders')->where($arr)->field("id,order_sn,acount,created")->limit(($page - 1) * $limit, $limit)->find('array');

        if (empty($data)) {

            $this->appReturn(array('status' => false, 'msg' => '暂无包裹信息'));

        }
        foreach ($data as $k => $v) {

            $data[$k]['created'] = date('Y-m-d', $v['created']);

        }
        $total_price = array_sum(array_column($data, 'acount'));

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data, 'total_price' => $total_price));

    }

    /*消费统计*/
    public function consumeStatistics()
    {

        $ordersPackage = table('ordersPackage')->tableName();
        $orders        = table('orders')->tableName();
        $logistics     = table('logistics')->tableName();

        $page       = post('page', 'intval', 1);
        $limit      = post('limit', 'intval', 5);
        $start_time = post('start_time', 'text', '');
        $end_time   = post('end_time', 'text', '');

        if ($start_time && !$end_time) {
            $arr[$orders . '.created'] = array('>=', strtotime($start_time));
        } elseif (!$start_time && $end_time) {
            $arr[$orders . '.created'] = array('<=', strtotime($end_time));
        } elseif ($start_time && $end_time) {
            $arr[$orders . '.created'] = array('between', strtotime($start_time), strtotime($end_time));
        }

        $arr[$orders . '.type']   = 4;
        $arr[$orders . '.uid']    = $this->uid;
        $arr[$orders . '.is_pay'] = 1;

        $data = table('ordersPackage')->join($orders, "$orders.order_sn = $ordersPackage.order_sn", 'left')->join($logistics, "$orders.order_sn = $logistics.order_sn", 'left')->where($arr)->field("$logistics.name as log_name,$ordersPackage.name,$ordersPackage.num,$ordersPackage.account,$orders.created")->limit(($page - 1) * $limit, $limit)->find('array');

        if (empty($data)) {

            $this->appReturn(array('status' => false, 'msg' => '暂无物品信息'));

        }
        foreach ($data as $k => $v) {

            $data[$k]['created'] = date('Y-m-d', $v['created']);

        }

        $total_price = array_sum(array_column($statList, 'account'));
        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data, 'total_price' => $total_price));

    }
}
