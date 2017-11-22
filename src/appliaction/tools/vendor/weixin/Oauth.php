<?php
/**
 * 微信授权
 * @author Chenmingjiang <Chenmingjiang1@linksus.com.cn>
 * @version $Id: Oauth.php 2015-09-29 14:32:06 $
 */
namespace app\tools\vendor\weixin;

use app\tools\vendor\weixin;

class Oauth
{
    private $appId;
    private $appSecret;
    public function __construct()
    {
        $this->appId     = \denha\Start::$config['weixin_appid'];
        $this->appSecret = \denha\Start::$config['weixin_secret'];
    }
    /**
     * [获取微信授权链接]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:33:14+0800
     * @param    string                   $redirectUri [跳转地址]
     * @param    string                   $state       [参数]
     * @return   [string]                 [授权链接]
     */
    public function getAuthorizeUrl($redirectUri = '', $state = '', $scope = 'snsapi_userinfo')
    {
        $redirectUri = urlencode($redirectUri);
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . $redirectUri . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
    }
    /**
     * [获取授权token]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:34:16+0800
     * @param    string                   $code [通过getAuthorizeUrl获取到的code]
     * @return   [type]                   [description]
     */
    public function getAccessToken($code = '')
    {
        $tokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        //$tokenUrl = 'http://www.baidu.com';
        // ==================== 本地进行测试更改
        $tokenData = file_get_contents($tokenUrl);
        return json_decode($tokenData, true);
        //===================== 本地测试结束
        $tokenData = $this->http($tokenUrl);
        if ($tokenData[0] == 200) {
            return json_decode($tokenData[1], true);
        }
        return false;
    }
    /**
     * [获取授权后的微信用户信息]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:37:12+0800
     * @param    string                   $accessToken [token]
     * @param    string                   $openId      [openid]
     * @return   [json|bool]              [用户信息]
     */
    public function getUserInfo($accessToken = '', $openId = '')
    {
        if ($accessToken && $openId) {
            $infoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
            //查看是否关注公众号
            // $subscribe = $this->getUserInfoBySubscribe($openId);
            // ==================== 本地进行测试更改
            $infoData          = file_get_contents($infoUrl);
            $data              = json_decode($infoData, true);
            $data['subscribe'] = 0;
            //表示用户关注了该公众号
            if ($subscribe['subscribe'] == '1') {
                $data['subscribe'] = 1;
            }
            return $data;
            //===================== 本地测试结束
            $infoData = $this->http($infoUrl);
            if ($infoData[0] == 200) {
                $data = json_decode($infoData[1], true);
                // $data['subscribe'] = 0;
                // //表示用户关注了该公众号
                // if ($subscribe['subscribe'] == '1') {
                //     $data['subscribe'] = 1;
                // }
                return $data;
            }
        }
        return false;
    }
    /**
     * [查看用户是否关注公众号]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:37:12+0800
     *
     * @param    string                   $openId      [openid]
     * @return   [json|bool]              [用户信息]
     */
    public function getUserInfoBySubscribe($openId = '')
    {
        if ($openId) {
            //查看是否关注公众号的token和网页授权的token不一样。需要重新请求
            $Jssdk       = new Jssdk($this->appId, $this->appSecret);
            $accessToken = $Jssdk->getAccessToken();
            $infoUrl     = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
            $infoData    = $this->http($infoUrl);
            if ($infoData[0] == 200) {
                return json_decode($infoData[1], true);
            }
        }
        return false;
    }
    /**
     * [发起HTTP请求]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:41:10+0800
     * @param    [string]                 $url        [请求URL]
     * @param    [string]                 $method     [请求方法]
     * @param    [string]                 $postfields [POST字段]
     * @param    array                    $headers    [头信息]
     * @param    boolean                  $debug      [是否显示调试信息]
     * @return   [type]                   [请求结果]
     */
    public function http($url, $method, $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ci);
        $httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($httpCode, $response);
    }
}
