<?php
/**
 * 淘宝用户相关API接口
 */

namespace app\tools\dao;

use denha\Start;

class TaobaoUser
{
    private static $config;
    private static $client;

    public function __construct()
    {
        if (is_null(self::$config)) {
            self::$config = array(
                'taobao_key'    => Start::$config['taobao_key'],
                'taobao_secret' => Start::$config['taobao_secret'],
            );
        }

        if (is_null(self::$client)) {
            require_once APP_PATH . 'tools' . DS . 'vendor' . DS . 'taobao' . DS . 'TopSdk.php';
            self::$client            = new \TopClient;
            self::$client->appkey    = self::$config['taobao_key'];
            self::$client->secretKey = self::$config['taobao_secret'];
        }

    }

    /** 增加用户 */
    public function add($param)
    {

        if (!$param['uid'] || !$param['nickname'] || !$param['password']) {
            return array('status' => false, 'msg' => '淘宝用户添加API参数错误');
        }

        $req                 = new \OpenimUsersAddRequest;
        $userinfos           = new \Userinfos;
        $userinfos->nick     = $param['nickname'];
        $userinfos->icon_url = " ";
        $userinfos->email    = " ";
        $userinfos->mobile   = " ";
        $userinfos->taobaoid = " ";
        $userinfos->userid   = $param['uid'];
        $userinfos->password = $param['password'];
        $userinfos->remark   = " ";
        $userinfos->extra    = "{}";
        $userinfos->career   = "";
        $userinfos->vip      = "{}";
        $userinfos->address  = " ";
        $userinfos->name     = " ";
        $userinfos->age      = "18";
        $userinfos->gender   = " ";
        $userinfos->wechat   = " ";
        $userinfos->qq       = " ";
        $userinfos->weibo    = " ";
        $req->setUserinfos(json_encode($userinfos));
        $resp = self::$client->execute($req);

        if (isset($resp->code)) {
            return array('status' => false, 'msg' => '淘宝用户查询API调用异常');
        }

        return array('status' => true, 'msg' => '添加成功');
    }

    /** 更新用户 */
    public function update($param)
    {

        if (!$param['uid']) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $req       = new \OpenimUsersUpdateRequest;
        $userinfos = new \Userinfos;

        $userinfos->userid = $param['uid'];
        if ($param['nickname']) {
            $userinfos->nick = $param['nickname'];
        }

        if ($param['password']) {
            $userinfos->password = $param['password'];
        }

        if ($param['name']) {
            $userinfos->name = $param['name'];
        }

        $req->setUserinfos(json_encode($userinfos));
        $resp = self::$client->execute($req);

        if ($resp->fail_msg) {
            return array('status' => false, 'msg' => '更新失败');
        }

        return array('status' => true, 'msg' => '更新成功');

    }

    public function index($uid)
    {

        $req = new \OpenimUsersGetRequest;
        $req->setUserids($uid);
        $resp = self::$client->execute($req);

        if (isset($resp->code)) {
            return array('status' => false, 'msg' => '淘宝用户查询API调用异常');
        } elseif (!$resp) {
            return array('status' => false, 'msg' => '信息不存在');
        }

        return array('status' => true, 'msg' => '用户查询成功', 'data' => $resp);
    }

    private function taobaoReturn($obj, $msg)
    {
        if (isset($obj['error_response'])) {
            return array('status' => false, 'msg' => '淘宝API调用异常');
        } else {
            return array('status' => true);
        }

    }
}
