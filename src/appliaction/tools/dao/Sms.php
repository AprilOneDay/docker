<?php
namespace app\tools\dao;

class Sms
{
    public function getConfigNexmo($moblie, $content)
    {
        !isset($_SERVER['HTTP_LG']) ?: $lg = (string) $_SERVER['HTTP_LG'];

        if (isset($lg)) {
            $content = dao('BaiduTrans')->baiduTrans($content, $lg);
        } else {
            //标识中国号码
            stripos($moblie, '86') === 0 ?: $moblie = '86' . $moblie;
            //utf8字符转换成Unicode字符
            $content = enUnicode($content);
        }
        $content = urlencode($content);

        $content          = mbDetectEncoding($content, "UTF-8");
        $data['url']      = 'https://rest.nexmo.com/sms/json';
        $data['urlValue'] = array(
            'api_key'    => 'f3bcd87d',
            'api_secret' => '7bdf89e00ac58e81',
            'to'         => $moblie,
            'from'       => 'KDC',
            'text'       => $content,
        );

        return $data;
    }

    /**
     * 获取getUrl
     * @date   2017-10-10T14:12:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $url  [description]
     * @param  [type]                   $data [description]
     * @return [type]                         [description]
     */
    private function getRestUrl($url, $data)
    {
        $urlRest = $url . '?';
        foreach ($data as $key => $value) {
            $urlRest .= $key . '=' . $value . '&';
        }
        $urlRest = substr($urlRest, 0, -1);
        return $urlRest;
    }

    /**
     * 发送短信
     * @date   2017-10-10T14:12:56+0800
     * @author ChenMingjiang
     * @param  string                   $moblie [description]
     * @param  string                   $flag   [description]
     * @param  string                   $param  [description]
     * @return [type]                           [description]
     */
    public function send($moblie = '', $flag = '', $param = '', $method = 'get')
    {
        if (!$moblie || !$flag) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $dataContent = $this->smsTemplate($flag, $param, $moblie);
        if (!$dataContent['status']) {
            return array('status' => false, 'msg' => $dataContent['msg']);
        }

        $data = $this->getConfigNexmo($moblie, $dataContent['data']);
        $url  = $this->getRestUrl($data['url'], $data['urlValue']);

        if (strtolower($method) == 'post') {
            $result = $this->curlPost($data['url'], $data['urlValue']);
        } else {
            $result = $this->curlGet($url);
        }

        if ($result) {
            return array('status' => true, 'msg' => '发送成功', 'result' => $result, 'url' => $url);
        }

        return array('status' => false, 'msg' => $data);
    }

    /**
     * 获取短信模板
     * @date   2017-10-10T14:13:04+0800
     * @author ChenMingjiang
     * @param  string                   $flag   [标识符]
     * @param  string                   $param  [动态参数]
     * @param  string                   $moblie [手机号]
     * @return [type]                           [description]
     */
    private function smsTemplate($flag = '', $param = '', $moblie = '')
    {
        $content = '';
        switch ($flag) {
            case 'verification':
                $verificationTime = getCookie('verificationTime', true);
                if ($verificationTime) {
                    //return array('status' => false, 'msg' => '请等待' . (180 - (time() - $verificationTime)) . '秒');
                }
                $verification = rand('11111', '99999');
                cookie('verificationTime', time(), '180', true);
                session('verificationMoblie', $moblie); //保存手机号
                session('verification', $verification); //保存验证码
                $content = '验证码：' . $verification . '。3分钟有效';
                break;
            default:
                # code...
                break;
        }
        return array('status' => true, 'msg' => '发送成功', 'data' => $content);
    }

    /**
     * 检测验证码
     * @date   2017-10-10T12:04:31+0800
     * @author ChenMingjiang
     * @param  [type]                   $moblie [手机号]
     * @param  [type]                   $code   [验证码]
     * @return [type]                           [description]
     */
    public function checkVerification($moblie = '', $code = '')
    {
        $verification       = getSession('verification');
        $verificationMoblie = getSession('verificationMoblie');

        if (!$moblie) {
            return array('status' => false, 'msg' => '请输入手机号');
        }

        if (!$code) {
            return array('status' => false, 'msg' => '请输入验证码');
        }

        if (!$verificationMoblie || !$verification) {
            return array('status' => false, 'msg' => '请发送验证码');
        }

        if ($verificationMoblie != $moblie) {
            return array('status' => false, 'msg' => '接受验证码手机不一致');
        }

        if ($verification != $code) {
            return array('status' => false, 'msg' => '验证码错误');
        }

        return array('status' => true, 'msg' => '验证成功');
    }

    public function curlPost($url, $data)
    {
        $curl = curl_init();
        //跳过SSL证书检查 https会因为证书问题返回false
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //执行命令
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    public function curlGet($url)
    {
        $curl = curl_init();
        //跳过SSL证书检查 https会因为证书问题返回false
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, _USERAGENT_);
        curl_setopt($curl, CURLOPT_REFERER, _REFERER_);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;

    }

}
