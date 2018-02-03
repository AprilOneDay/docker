<?php
/**
 * 账户相关模块
 */
namespace app\fastgo\app\controller\v1\api;

use app\app\controller;
use app\fastgo\app\controller\v1\ApiInit;
use denha\Smtp;

class Mail extends ApiInit
{
    public function send()
    {
        $mail    = post('mail', 'text', '');
        $title   = post('title', 'text', '');
        $content = post('content', 'text', '');

        if (!$mail || !$title || !$content) {
            $this->apiReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $smtp   = new Smtp(1);
        $result = $smtp->sendmail($mail, $title, $content);
        if (!$result) {
            $this->apiReturn(array('status' => false, 'msg' => '发送失败', 'data' => $result));
        }

        $this->apiReturn(array('status' => true, 'msg' => '发送成功'));

    }

}
