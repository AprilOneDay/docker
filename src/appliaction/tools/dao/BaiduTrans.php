<?php
/**
 * 百度翻译API
 */
namespace app\tools\dao;

class BaiduTrans
{

    private static $appid;
    private static $key;

    private $url;
    private $content;
    private $to;
    private $from;

    public function __construct()
    {
        if (is_null(self::$appid)) {
            self::$appid = getConfig('config', 'baidu_trans_appid');
            self::$key   = getConfig('config', 'baidu_trans_key');
        }
    }

    /**
     * 百度翻译接口api调用
     * @date   2017-09-26T10:26:40+0800
     * @author ChenMingjiang
     * @param  string                   $value [翻译文案]
     * @param  string                   $to    [翻译成语言]
     * @param  string                   $from  [需要翻译的语言]
     * @return [type]                          [description]
     */
    public function baiduTrans($content = '', $to = 'en', $from = 'auto', $debug = false)
    {
        $this->content = $content;
        $this->to      = $to;
        $this->from    = $from;
        if (is_array($this->content)) {
            //$this->content = array_unique($this->content);
            $this->content = implode(PHP_EOL, $this->content);
        }

        $result = $this->execute();

        //debug
        if ($debug) {
            print_r('------URL----' . PHP_EOL);
            print_r($this->url . PHP_EOL);
            print_r('------END-----' . PHP_EOL);
            print_r('------结果-----' . PHP_EOL);
            print_r($result['trans_result']);
            print_r('------END-----' . PHP_EOL);
            die;
        }

        if ($result['trans_result']) {
            if (count($result['trans_result']) == 1) {
                return (string) $result['trans_result'][0]['dst'];

            } else {
                foreach ($result['trans_result'] as $key => $value) {
                    $transCopy[$value['src']] = $value['dst'];
                }

                return $transCopy;
            }
        }

        return (string) $value;

    }

    public function execute()
    {
        //header("Content-Type:application/json; charset=utf-8");
        $salt      = TIME;
        $sign      = md5(self::$appid . $this->content . $salt . self::$key);
        $this->url = 'http://fanyi-api.baidu.com/api/trans/vip/translate?q=' . urlencode($this->content) . '&from=' . $this->from . '&to=' . $this->to . '&appid=' . self::$appid . '&salt=' . $salt . '&sign=' . $sign;

        $result = file_get_contents($this->url);
        $result = json_decode($result, true);

        return $result;
    }

}
