<?php
/**
 * 提示消息管理
 */
namespace app\tools\dao;

class Message
{
    /**
     * 发送站内推送
     * @date   2017-09-26T11:07:25+0800
     * @author ChenMingjiang
     * @param  [type]                   $toUid [接受信息uid]
     * @param  [type]                   $flag  [标识符]
     * @param  array                    $data  [动态参数]
     * @param  [type]                   $uid   [发送信息uid]
     * @return [type]                          [description]
     */
    public function send($toUid = 0, $flag = '', $param = array(), $jumpData = array(), $uid = 0, $type = 1)
    {
        if (!$toUid) {
            return false;
        }

        $data['content'] = $this->getContent($flag, $param);
        if (!$data['content']) {
            return false;
        }

        $data['type']     = $type;
        $data['uid']      = $uid;
        $data['created']  = TIME;
        $data['to_uid']   = $toUid;
        $data['jump_app'] = $jumpData ? json_encode($jumpData) : '';

        //如果存在相同推送内容信息则直接更新时间
        $map['type']       = $type;
        $map['uid']        = $uid;
        $map['to_uid']     = $toUid;
        $map['content']    = $data['content'];
        $map['del_status'] = 0;

        $id = table('UserMessage')->where($map)->field('id')->find('one');
        if ($id) {
            $reslut = table('UserMessage')->where('id', $id)->save(array('is_reader' => 0, 'created' => TIME));
        } else {
            //增加推送记录
            $reslut = table('UserMessage')->add($data);
        }

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
                break;
            case 'newComment':
                $content = '你有新的消息';
                break;
            default:
                # code...
                break;
        }

        return $content;
    }
}
