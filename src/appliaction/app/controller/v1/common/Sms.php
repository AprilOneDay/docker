<?php
/**
 * 车友圈模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class Sms extends \app\app\controller\Init
{
    /**
     * 验证码发送
     * @date   2017-10-10T13:53:28+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function verification()
    {
        $mobile = post('mobile', 'text', '');
        if (!$mobile) {
            $this->appReturn(array('status' => false, 'msg' => '请输入电话号码'));
        }

        $reslut = dao('Sms')->send($mobile, 'verification');
        $this->appReturn($reslut);
    }
}
