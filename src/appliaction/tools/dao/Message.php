<?php
/**
 * 提示消息管理
 */
namespace app\tools\dao;

class Message
{
    /**
     * 发送站内信
     * @date   2017-09-26T11:07:25+0800
     * @author ChenMingjiang
     * @param  [type]                   $toUid [接受信息uid]
     * @param  [type]                   $flag  [标识符]
     * @param  array                    $data  [动态参数]
     * @param  [type]                   $uid   [发送信息uid]
     * @return [type]                          [description]
     */
    public function send($toUid = 0, $flag = '', $data = array(), $jumpData = array(), $uid = 0)
    {
        if (!$toUid) {
            return false;
        }

        $data['content'] = $this->getContent($flag, $data);
        if (!$data['content']) {
            return false;
        }

        $data['uid']     = $uid;
        $data['created'] = TIME;
        $data['to_uid']  = $toUid;

        $reslut = table('UserMessage')->add($data);
    }

    /**
     * 获取发送信息内容
     * @date   2017-09-26T11:09:36+0800
     * @author ChenMingjiang
     * @param  [type]                   $flag [description]
     * @param  array                    $data [description]
     * @return [type]                         [description]
     */
    public function getContent($flag, $data = array())
    {
        $content = '';
        switch ($flag) {
            case 'register_user':
                $content = '恭喜你成为会员,祝你购车愉快';
                break;
            case 'comment':
                $content = '会员' . $data['nickname'] . ',给你留言了,请尽快查看哦！';
            default:
                # code...
                break;
        }

        return $content;
    }
}
