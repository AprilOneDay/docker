<?php
/**
 * 云屋直播管理
 */
namespace app\tools\dao;

class YunwuRoom
{
    public static $config;
    public $url = 'http://www.cloudroom.com/api/';
    public $param;

    public function __construct()
    {
        self::$config['RequestId'] = '123456789';
        self::$config['UserName']  = \denha\Start::$config['yunwu_key'];
        self::$config['UserPswd']  = \denha\Start::$config['yunwu_secret'];

        $this->param = '?RequestId=' . self::$config['RequestId'] . '&UserName=' . self::$config['UserName'] . '&UserPswd=' . self::$config['UserPswd'] . '&DoAction=get';
    }

    /**
     * 创建直播房间
     * @date   2017-11-20T16:41:41+0800
     * @author ChenMingjiang
     * @param  string                   $url [description]
     * @return [type]                        [description]
     */
    public function created($name = '')
    {

        if (!$name) {
            return false;
        }

        if ($this->getList($name)) {
            return false;
        }

        $url    = $this->url . '/createLiveAPI' . $this->param . '&LiveSubject=' . $name;
        $result = file_get_contents($url);
        $result = json_decode($result, true);

    }

    /**
     * 查询直播房间
     * @date   2017-11-20T16:41:28+0800
     * @author ChenMingjiang
     * @param  [type]                   $name [description]
     * @param  string                   $url   [description]
     * @return [type]                          [description]
     */
    public function getList($name)
    {
        if (!$name) {
            return false;
        }

        $url    = $this->url . '/queryLiveAPI' . $this->param . '&LiveSubject=' . $name;
        $result = file_get_contents($url);
        $result = json_decode($result, true);

        if (!$result['RspCode']) {
            if (!$result['Data']['LiveNum']) {
                return false;
            }

            foreach ($result['Data']['LiveList'] as $key => $value) {
                $result['Data']['LiveList'][$key]['LiveCode'] = substr($value['liveUrl'], -6);
            }

            return $result['Data']['LiveList'];
        }

        return false;
    }

    /**
     * 百度主动推送
     * @date   2017-09-30T14:37:51+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function pull($content = '', $url = 'http://www.denha.cn/index/index/detail/id/', $token = '', $site = 'www.denha.cn')
    {
        $urls = (array) ($url . $content);
        if (!$content) {
            return array('status' => false, 'msg' => '内容为空');
        }

        $token = $token ? $token : getConfig('config.blog', 'sitemap_token');
        if (!$token) {
            return array('status' => false, 'msg' => 'token配置失败');
        }

        $api     = 'http://data.zz.baidu.com/urls?site=' . $site . '&token=' . $token;
        $ch      = curl_init();
        $options = array(
            CURLOPT_URL            => $api,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => implode("\n", $urls),
            CURLOPT_HTTPHEADER     => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if (isset($result['error'])) {
            return array('status' => false, 'msg' => '百度主动推送失败：' . $result['error'] . ' ' . $result['message']);
        }

        return array('status' => true, 'msg' => '推送成功');
    }
}
