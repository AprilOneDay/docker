<?php
/**
 * 淘宝用户相关API接口
 */

namespace app\tools\dao;

use denha\Start;

class TaoBaoUser
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
    public function add($uid = 0, $nickname = '', $password = '', $data)
    {

        if (!$uid || !$nickname || !$password) {
            return array('status' => false, 'msg' => '淘宝用户添加API参数错误');
        }

        $req                 = new \OpenimUsersAddRequest;
        $userinfos           = new \Userinfos;
        $userinfos->nick     = $nickname;
        $userinfos->icon_url = " ";
        $userinfos->email    = " ";
        $userinfos->mobile   = " ";
        $userinfos->taobaoid = " ";
        $userinfos->userid   = $uid;
        $userinfos->password = $password;
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

    public function index($uid)
    {

        $req = new \OpenimUsersGetRequest;
        $req->setUserids($uid);
        $resp = self::$client->execute($req);

        print_r($resp);
        print_r((array) $resp->userinfos->userinfos);

        if (isset($resp->code)) {
            return array('status' => false, 'msg' => '淘宝用户查询API调用异常');
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
