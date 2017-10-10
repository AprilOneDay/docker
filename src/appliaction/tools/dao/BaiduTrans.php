<?php
/**
 * 百度翻译API
 */
namespace app\tools\dao;

class BaiduTrans
{
    /**
     * 百度翻译接口api调用
     * @date   2017-09-26T10:26:40+0800
     * @author ChenMingjiang
     * @param  string                   $value [翻译文案]
     * @param  string                   $to    [翻译成语言]
     * @param  string                   $from  [需要翻译的语言]
     * @return [type]                          [description]
     */
    public function baiduTrans($value = '', $to = 'en', $from = 'zh')
    {
        header("Content-Type:application/json; charset=utf-8");
        $appid = getConfig('config', 'baidu_trans_appid');
        $key   = getConfig('config', 'baidu_trans_key');
        $salt  = TIME;
        $sign  = md5($appid . $value . $salt . $key);
        $url   = 'http://fanyi-api.baidu.com/api/trans/vip/translate?q=' . $value . '&from=' . $from . '&to=' . $to . '&appid=' . $appid . '&salt=' . $salt . '&sign=' . $sign;

        $result = file_get_contents($url);
        $result = json_decode($result, true);
        //echo $url;die;
        if ($result['trans_result']) {
            return (string) $result['trans_result'][0]['dst'];
        }

        return (string) $value;

    }
}
