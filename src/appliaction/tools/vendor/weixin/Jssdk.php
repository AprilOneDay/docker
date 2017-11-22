<?php
/**
 * 微信接口
 * @author Chenmingjiang <Chenmingjiang1@linksus.com.cn>
 * @version $Id: Jssdk.php 2015-03-12 11:41:37 $
 */
namespace app\tools\vendor\weixin;

use app\tools\vendor\weixin;

class Jssdk
{
    private $appId;
    private $appSecret;
    private $weixinName;
    public function __construct($weixinName = 'shiji')
    {
        $conf             = V($weixinName, 'weixin');
        $this->appId      = $conf['appId'];
        $this->appSecret  = $conf['appSecret'];
        $this->weixinName = $weixinName;
    }
    /**
     * [获取签名包]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:47:12+0800
     * @return   [string]                   [签名包]
     */
    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url       = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr  = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string      = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature   = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );
        return $signPackage;
    }
    /**
     * [获取签名包]
     * @author kuangxiaojin
     * @datetime 2015-09-29T14:47:12+0800
     * @return   [string]                   [签名包]
     */
    public function getSignPackageFromJs($url)
    {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        // $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        // $url       = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr  = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string      = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature   = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );
        return $signPackage;
    }
    /**
     * [生成随机字符]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:47:41+0800
     * @param    integer                  $length [长度]
     * @return   [string]                 [随机数]
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * [获取公众号用于调用微信JS接口的临时票据]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:48:18+0800
     * @return   [json]                   [票据]
     */
    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        // 读取ticket
        $jsapiTicket = cache('jsapiTicket');
        $data        = json_decode($jsapiTicket);
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url    = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res    = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time  = time() + 7000;
                $data->jsapi_ticket = $ticket;
                // 保存ticket
                cache('jsapiTicket', json_encode($data));
                // $fp = fopen("jsapi_ticket.json", "w");
                // fwrite($fp, json_encode($data));
                // fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    /**
     * [获取访问令牌]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:48:56+0800
     * @return   [json]                   [令牌]
     */
    public function getAccessToken()
    {
        $weixinName = $this->weixinName;
        $url        = "http://api.chayu.com/weixin/jssdk/get_access_token?weixinname=$weixinName";
        $data       = $this->httpGet($url);
        $data       = json_decode($data);
        if ($data->state) {
            return $data->access_token;
        }
        return '';
    }
    /**
     * [获取URL]
     * @author Chenmingjiang
     * @datetime 2015-09-29T14:49:27+0800
     * @param    [type]                   $url [请求URL]
     * @return   [type]                   [请求返回数据]
     */
    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        // 检查是否有错误发生
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }
}
