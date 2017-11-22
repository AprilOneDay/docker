<?php
/**
 * 短信模块
 */
namespace app\tools\dao;

class Sms
{
    public function getConfigNexmo($mobile, $content, $country)
    {
        $type    = 'text';
        $content = urlencode($content);

        /*!isset($_SERVER['HTTP_LG']) ?: $lg = (string) $_SERVER['HTTP_LG'];

        if (isset($lg)) {
        $type    = 'text';
        $content = dao('BaiduTrans')->baiduTrans($content, $lg);

        $content = urlencode($content);
        } else {
        //标识中国号码
        //stripos($mobile, '86') === 0 ?: $mobile = '86' . $mobile;
        //utf8字符转换成Unicode字符
        $type = 'unicode';
        //$content = mb_convert_encoding($content, 'UCS-2', 'UTF-8');
        $content = enUnicode($content);
        $content = '\U9A8C\U8BC1\U7801\UFF1A\U0031\U0035\U0032\U0030\U0039\U3002\U0033\U5206\U949F\U6709\U6548';
        }*/
        /*  header("content-Type: text/html; charset=UTF-8");
        var_dump($content);
        $content = deUnicode($content);
        var_dump($content);
        die;
         */

        switch ($country) {
            case '1':
                $from = '12262101807';
                break;
            default:
                $from = 'China';
                break;
        }

        $content          = mbDetectEncoding($content, "UTF-8");
        $data['url']      = 'https://rest.nexmo.com/sms/json';
        $data['urlValue'] = array(
            'api_key'    => 'f3bcd87d',
            'api_secret' => '7bdf89e00ac58e81',
            'to'         => $mobile,
            'from'       => $from,
            'text'       => $content,
            'type'       => $type,
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
     * @param  string                   $mobile [description]
     * @param  string                   $flag   [description]
     * @param  string                   $param  [description]
     * @return [type]                           [description]
     */
    public function send($mobile = '', $flag = '', $param = '', $method = 'get', $country = '')
    {

        if (!$mobile || !$flag) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $dataContent = $this->smsTemplate($flag); //获取信息内容
        $content     = $this->analysisTemplate($dataContent['data'], $param); //解析动态参数

        if (!$dataContent['status']) {
            return array('status' => false, 'msg' => $dataContent['msg']);
        }

        $data = $this->getConfigNexmo((string) $country . $mobile, $content, $country);

        $url = $this->getRestUrl($data['url'], $data['urlValue']);

        if (strtolower($method) == 'post') {
            $result = $this->curlPost($data['url'], $data['urlValue']);
        } else {
            $result = $this->curlGet($url);
        }

        if (!$result) {
            return array('status' => false, 'msg' => '发送失败,请重试或联系管理员', 'result' => $result, 'url' => $url);
        }

        $resultData = str_replace(PHP_EOL, '', $result);
        $resultData = json_decode($resultData, true);

        //保存验证码
        if ($flag == 'verification' && $resultData['messages'][0]['status'] == 0) {
            $this->saveVerif($mobile, $param['code']);
        }

        //保存短信发送记录
        $this->log($mobile, $flag, $content);
        return array('status' => true, 'msg' => '发送成功', 'result' => $result);

    }

    /**
     * 获取短信模板
     * @date   2017-10-10T14:13:04+0800
     * @author ChenMingjiang
     * @param  string                   $flag   [标识符]
     * @param  string                   $param  [动态参数]
     * @param  string                   $mobile [手机号]
     * @return [type]                           [description]
     */
    private function smsTemplate($flag = '')
    {
        $content = '';
        switch ($flag) {
            case 'verification':
                //返回验证内容
                $content = 'Code:{code}';
                break;
            default:
                return array('status' => false, 'msg' => '内容为空');
                break;
        }
        return array('status' => true, 'msg' => '获取成功', 'data' => $content);
    }

    /**
     * 替换动态参数
     * @date   2017-10-16T12:49:51+0800
     * @author ChenMingjiang
     * @param  [type]                   $content [description]
     * @param  [type]                   $param   [description]
     * @return [type]                            [description]
     */
    private function analysisTemplate($content, $param)
    {
        if (!$content) {
            return '';
        }

        if (!$param) {
            return $content;
        }

        foreach ($param as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }

    /**
     * 保存短信发送记录
     * @date   2017-10-19T11:37:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $mobile  [description]
     * @param  [type]                   $flag    [description]
     * @param  [type]                   $content [description]
     */
    public function log($mobile, $flag, $content)
    {
        $data['content'] = $content;
        $data['ip']      = getIP();
        $data['created'] = TIME;
        $data['flag']    = $flag;
        $data['mobile']  = $mobile;

        $result = table('SmsLog')->add($data);
    }

    /**
     * 保存验证码
     * @date   2017-10-19T11:16:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $mobile [description]
     * @param  [type]                   $code   [description]
     * @return [type]                           [description]
     */
    public function saveVerif($mobile, $code)
    {
        //保存验证码
        $map['mobile'] = $mobile;
        $sms           = table('SmsVerify')->where($map)->field('id,created')->find();
        if ($sms) {

            $data['code']    = $code;
            $data['created'] = TIME;
            table('SmsVerify')->where('id', $sms['id'])->save($data);

        } else {
            $data['code']    = $code;
            $data['created'] = TIME;
            $data['mobile']  = $mobile;
            table('SmsVerify')->add($data);
        }

        $sendData['code'] = $code;
    }

    /**
     * 检测验证码
     * @date   2017-10-10T12:04:31+0800
     * @author ChenMingjiang
     * @param  [type]                   $mobile [手机号]
     * @param  [type]                   $code   [验证码]
     * @return [type]                           [description]
     */
    public function checkVerification($mobile = '', $code = '', $time = 3600)
    {

        if (!$mobile) {
            return array('status' => false, 'msg' => '请输入手机号');
        }

        if (!$code) {
            return array('status' => false, 'msg' => '验证码未输入');
        }

        $map['mobile'] = $mobile;

        $sms = table('SmsVerify')->where($map)->field('code,created')->find();

        if (!$sms) {
            return array('status' => false, 'msg' => '请发送验证码');
        }

        if (TIME - $sms['created'] > $time) {
            return array('status' => false, 'msg' => '验证码过期了,请重新申请');
        }

        if ($sms['code'] != $code) {
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
