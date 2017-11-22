<?php
/**
 * 通用微信授权
 * @author ChenMingjiang <ChenMingjiang1@linksus.com.cn>
 * @version $Id: WeixinOauth.php 2015-09-29 15:28:21 $
 */
namespace app\tools\dao;

class WeixinOauth
{
    /**
     * [微信授权获取用户信息]
     * @author ChenMingjiang
     * @datetime 2015-09-29T15:29:38+0800
     * @param    [string]                 $appId     [应用ID]
     * @param    [string]                 $appSecret [应用密钥]
     * @return   [array]                  [用户信息]
     */
    public function getUserInfo()
    {
        $code = get('code', 'trim');
        if (!$code) {
            echo '对不起，您取消了授权，无法参加此活动';
            exit;
        }

        $Oauth       = new \app\tools\vendor\weixin\Oauth();
        $accessToken = $Oauth->getAccessToken($code);

        $userInfo = $Oauth->getUserInfo($accessToken['access_token'], $accessToken['openid']);
        return $userInfo;
    }
    /**
     * [getAuthorizeUrl 获取微信授权地址]
     * @author kuangxiaojin
     * @DateTime 2016-04-08T10:07:22+0800
     * @param
     * @param    [type]                   $appId [应用ID]
     * @param    [type]                   $url   [回调url地址]
     * @param    [type]                   $scope [授权方式] snsapi_base：静默授权 snsapi_userinfo 需要用户同意
     * @param    [type]                   $state [参数] 可以判断是否来源微信
     * @return   [type]                          [description]
     */
    public function getAuthorizeUrl($url, $scope, $state)
    {
        if (!$url) {
            echo '回调地址不能为空';
            exit;
        }
        $Oauth     = new \app\tools\vendor\weixin\Oauth();
        $returnUrl = $Oauth->getAuthorizeUrl($url, $state, $scope);
        return $returnUrl;
    }
    /**
     * [redirectUrl 跳转地址]
     * @author kuangxiaojin
     * @DateTime 2016-04-08T10:07:22+0800
     * @param
     * @param    [type]                   $appId [应用ID]
     * @param    [type]                   $url   [回调url地址]
     * @param    [type]                   $scope [授权方式] snsapi_base：静默授权 snsapi_userinfo 需要用户同意
     * @param    [type]                   $state [参数] 可以判断是否来源微信
     * @return   [type]                          [description]
     */
    public function redirectUrl($url, $scope, $state)
    {
        /*//目前只有市集授权，后期多个授权，跳转地址需要更改
        $mobileShijiUrl = C('url', 'mobile.shiji');
        //测试环境
        $hostArr = array('http://m.shiji.chayu.loc', 'http://m.shiji.chayu.alp', 'http://m.shiji.chayu.bet', 'http://m1.shiji.chayu.com');
        if (in_array($mobileShijiUrl, $hostArr)) {
        $mobileShijiUrl = 'http://m.shiji.chayu.com';
        }
        if ($this->checkScience() != false) {
        $mobileShijiUrl = $this->checkScience();
        }*/

        $appId  = \denha\Start::$config['weixin_appid'];
        $newUrl = URL . '/weixin_oauth?appId=' . $appId . '&scope=' . $scope . '&state=' . $state . '&return_url=' . urlencode($url);
        // redirect($newUrl);
        exit('<script>window.location.href="' . $newUrl . '";</script>');
    }
    /**
     * [checkScience 检测环境]
     * @date   2016-06-24T15:54:06+0800
     * @author kuangjin
     * @return [type]                   [description]
     */
    public function checkScience()
    {
        $mobileShijiUrl = C('url', 'mobile.shiji');
        $hostArr        = array('http://m.shiji.chayu.loc', 'http://m.shiji.chayu.alp', 'http://m.shiji.chayu.bet', 'http://m.shiji.chayu.dev', 'http://m1.shiji.chayu.com');
        $url            = 'http://m.shiji.chayu.com';
        if (in_array($mobileShijiUrl, $hostArr)) {
            return $url;
        }
        return false;
    }
}
