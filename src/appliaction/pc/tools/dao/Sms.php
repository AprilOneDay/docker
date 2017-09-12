<?php
namespace app\pc\tools\dao;

class Sms
{

    public function send($moblie = '', $flag = '', $data = '')
    {
        if (!$moblie || !$flag) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $config = vars('sms', 0);

        $dataContent = $this->smsTemplate($flag, $data, $moblie);
        if (!$dataContent['status']) {
            return array('status' => false, 'msg' => $dataContent['msg']);
        }

        $url  = $config['url'];
        $data = array(
            'CorpID'   => $config['username'],
            'Pwd'      => $config['password'],
            'Content'  => $dataContent['data'],
            'Mobile'   => $moblie,
            'Cell'     => '',
            'SendTime' => '',
        );

        $url = $config['url'] . '?CorpID=' . $config['username'] . '&Pwd=' . $config['password'] . '&Content=' . $dataContent['data'] . '&Mobile=' . $moblie . '&Cell=&SendTime=';

        $curl = curl_init();
        //跳过SSL证书检查 https会因为证书问题返回flas
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $config['url']);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //执行命令
        $result = curl_exec($curl);
        //关闭URL请求

        if (curl_errno($curl)) {
            return array('status' => false, 'msg' => curl_error($curl));
        }
        curl_close($curl);

        if ($result) {
            return array('status' => true, 'msg' => '发送成功', 'result' => $result, 'url' => $url);
        }

        return array('status' => false, 'msg' => $data);
    }

    private function smsTemplate($flag = '', $data = '', $moblie = '')
    {
        $content = '';
        switch ($flag) {
            case 'verification':
                $verificationTime = getCookie('verificationTime', true);
                if ($verificationTime) {
                    return array('status' => false, 'msg' => '请等待' . (180 - (time() - $verificationTime)) . '秒');
                }
                $verification = rand('11111', '99999');
                cookie('verificationTime', time(), '180', true);
                session('verificationMoblie', $moblie); //保存手机号
                session('verification', $verification); //保存验证码
                $content = '验证码：' . $verification . '您正在使用医院预约系统';
                break;
            default:
                # code...
                break;
        }
        $content = mb_convert_encoding($content, "GBK", "UTF-8");
        return array('status' => true, 'msg' => '发送成功', 'data' => $content);
    }

}
