<?php
namespace app\tools\dao;

class BaiduSpider
{
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
