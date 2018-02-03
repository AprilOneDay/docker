<?php
/**
 * 账户相关模块
 */
namespace app\fastgo\app\controller\v1\api;

use app\app\controller;
use app\fastgo\app\controller\v1\ApiInit;

class Account extends ApiInit
{

    /**
     * 添加
     * @date   2018-01-02T13:47:15+0800
     * @author ChenMingjiang
     */
    public function add()
    {
        $address   = post('address', 'text', '');
        $mobile    = post('mobile', 'text', '');
        $realName  = post('real_name', 'text', '');
        $nickname  = post('nickname', 'text', '');
        $type      = post('type', 'intval', 0);
        $cid       = post('cid', 'text', '');
        $apiSecret = post('api_secret', 'text', '');
        $sign      = post('sign', 'intval', 0);
        $category  = post('category', 'intval', 0);
        $shopSn    = post('shop_sn', 'text', '');

        $data['password']   = post('password', 'text', '');
        $data['username']   = post('username', 'text', '');
        $data['real_name']  = $realName;
        $data['nickname']   = $nickname;
        $data['type']       = $type;
        $data['cid']        = $cid;
        $data['api_secret'] = $apiSecret;
        $data['user_sn']    = $shopSn;

        if (!$shopSn) {
            $this->appReturn(array('status' => false, 'msg' => '请上传网点编码'));
        }

        if ($type == 2) {
            if (!$sign) {
                $this->appReturn(array('status' => false, 'msg' => '请选择店铺类型'));
            }

            if (!$apiSecret) {
                $this->appReturn(array('status' => false, 'msg' => '请上传店铺授权码'));
            }

            if (!$shopSn) {
                $this->appReturn(array('status' => false, 'msg' => '请选择店铺分类'));
            }
        }

        if (!$data['password'] || !$data['username'] || !$data['nickname'] || !$data['type']) {
            $this->apiReturn(array('status' => flase, 'msg' => '参数错误'));
        }

        $result = dao('User')->register($data, $data['password']);

        //增加店铺信息
        if ($type == 2 && $result['status']) {
            $uid = $result['data'];

            $data               = array();
            $data['name']       = $nickname;
            $data['city_id']    = $cid;
            $data['mobile']     = $mobile;
            $data['real_name']  = $realName;
            $data['sign']       = $sign;
            $data['api_secret'] = $apiSecret;
            $data['category']   = $category;
            $data['address']    = $address;
            $data['shop_sn']    = $shopSn;

            table('UserShop')->where('uid', $uid)->save($data);

        }

        $this->apiReturn($result);
    }

    /** 改变账户状态 */
    public function update()
    {
        $type     = post('type', 'text', '');
        $username = post('username', 'text', '');

        $data['status']     = post('status', 'text', '');
        $data['api_secret'] = post('api_secret', 'text', '');

        $map['type']     = $type;
        $map['username'] = $username;

        $uid = table('User')->where($map)->field('uid')->find();
        if (!$uid) {
            $this->appReturn(array('status' => false, 'msg' => '账户存在'));
        }

        $result = table('User')->where($map)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }

        //关闭店铺
        if ($type == 2) {
            table('UserShop')->where('uid', $uid)->save($data);
        }

        $this->appReturn(array('msg' => '操作成功'));
    }

}
