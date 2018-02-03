<?php
/**
 * 会员模块
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Address extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 我的地址列表 */
    public function lists()
    {
        $type = get('type', 'intval', 1);
        $sign = get('sign', 'intval', 1);

        $map['uid']  = $this->uid;
        $map['type'] = $type;
        $map['sign'] = $sign;

        $list = table('UserAddress')->where($map)->order('is_default desc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['back_code']     = $this->appImg($value['back_code'], 'code');
            $list[$key]['positive_code'] = $this->appImg($value['positive_code'], 'code');
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 编辑信息详情 */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $map['uid']            = $this->uid;
            $map['id']             = $id;
            $data                  = table('UserAddress')->where($map)->find();
            $data['back_code']     = $this->appImg($data['back_code'], 'code');
            $data['positive_code'] = $this->appImg($data['positive_code'], 'code');
        } else {
            $data = array();
        }

        $this->appReturn(array('data' => $data));
    }

    /** 添加/编辑地址操作 */
    public function editPost()
    {
        $id = post('id', 'intval');

        $data['type']     = post('type', 'intval', 0);
        $data['sign']     = post('sign', 'intval', 0);
        $data['name']     = post('name', 'text', '');
        $data['mobile']   = post('mobile', 'text', '');
        $data['address']  = post('address', 'text', '');
        $data['country']  = post('country', 'text', '');
        $data['province'] = post('province', 'text', '');
        $data['city']     = post('city', 'text', '');
        $data['area']     = post('area', 'text', '');
        $data['zip_code'] = post('zip_code', 'text', '');
        $data['code']     = post('code', 'text', '');

        $data['is_default'] = post('is_default', 'intval', 0);

        $files['back_code']     = files('back_code');
        $files['positive_code'] = files('positive_code');

        if ($files['back_code']) {
            $data['back_code'] = $this->appUpload($files['back_code'], '', 'code');
        }

        if ($files['positive_code']) {
            $data['positive_code'] = $this->appUpload($files['positive_code'], '', 'code');
        }

        if (!$data['type'] || !$data['sign']) {
            $this->appReturn(array('status' => false, 'msg' => '初始参数传输错误'));
        }

        if (!$data['name']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入姓名'));
        }

        if (!$data['mobile']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入手机号'));
        }

        if (!$data['address']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入详细地址'));
        }

        if (!$data['province']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择省份'));
        }

        if ($data['type'] == 2) {
            $checkContent = $data['address'] . $data['name'] . $data['mobile'] . $data['province'];
            if (preg_match("/[\x7f-\xff]/", $checkContent)) {
                $this->appReturn(array('status' => false, 'msg' => '国际转运必须输入英文信息'));
            }
        }

        if ($id) {
            $map['uid'] = $this->uid;
            $map['id']  = $id;

            $result = table('UserAddress')->where($map)->save($data);
        } else {
            $data['created'] = TIME;
            $data['uid']     = $this->uid;

            $result = table('UserAddress')->add($data);
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

    /** 删除地址 */
    public function del()
    {
        $id = post('id', 'intval', 0);

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $result = table('UserAddress')->where($map)->delete();

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

    //设置默认
    public function setDefault()
    {
        $id = post('id', 'intval', 0);

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $address = table('UserAddress')->where($map)->find();

        if (!$address) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        //取消默认
        if ($address['is_default'] == 1) {
            $result = table('UserAddress')->where('id', $id)->save('is_default', 0);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '操作失败,取消默认的时候中断了....'));
            }
        }
        //设置默认
        else {
            //取消之前默认选项
            $map         = array();
            $map['type'] = $address['type'];
            $map['sign'] = $address['sign'];
            $map['uid']  = $this->uid;
            $result      = table('UserAddress')->where($map)->save('is_default', 0);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '操作失败,取消默认的时候中断了....'));
            }

            $result = table('UserAddress')->where('id', $id)->save('is_default', 1);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '操作失败,请稍后重试'));
            }
        }

        $this->appReturn(array('msg' => '操作成功'));
    }
}
