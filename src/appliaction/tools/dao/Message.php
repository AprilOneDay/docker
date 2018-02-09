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
    public function send($toUid = 0, $flag = '', $param = array(), $uid = 0, $type = 1, $lg = 'zh', $isPush = ture)
    {
        if (!$toUid) {
            return false;
        }

        $content = $this->getContent($flag);
        $content = $this->analysisTemplate($content, $param);

        //消息模板不存在 或则 关闭
        if (!$content || !$content['status']) {
            return false;
        }

        $data['type']    = $type;
        $data['uid']     = $uid;
        $data['created'] = TIME;
        $data['to_uid']  = $toUid;
        $data['flag']    = $flag;
        $data['param']   = $param ? json_encode($param, JSON_UNESCAPED_UNICODE) : '';

        //如果存在相同推送内容信息则直接更新时间
        $map['flag']   = $flag;
        $map['type']   = $type;
        $map['uid']    = $uid;
        $map['to_uid'] = $toUid;
        $data['param'] = $param ? json_encode($param, JSON_UNESCAPED_UNICODE) : '';

        $map['del_status'] = 0;

        $id = table('UserMessage')->where($map)->field('id')->find('one');
        if ($id) {
            $reslut = table('UserMessage')->where('id', $id)->save(array('is_reader' => 0, 'created' => TIME));
        } else {
            //增加推送记录
            $reslut = table('UserMessage')->add($data);
        }

        //发送推送信息
        if ($isPush) {
            $content = $this->getContent($flag, $lg);
            dao('JPush')->sendByRegId($toUid, $content['title'], $content['content'], json_encode($param, JSON_UNESCAPED_UNICODE));
        }

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
            $content['title']   = str_replace('{$' . $key . '}', $value, $content['title']);
            $content['content'] = str_replace('{$' . $key . '}', $value, $content['content']);
        }

        return $content;
    }

    /**
     * 获取发送信息内容
     * @date   2017-09-26T11:09:36+0800
     * @author ChenMingjiang
     * @param  [type]                   $flag [description]
     * @param  array                    $data [description]
     * @return [type]                         [description]
     */
    public function getContent($flag, $lg = 'zh')
    {
        $map         = array();
        $map['flag'] = $flag;

        if ($lg != 'zh') {
            $field = "title_$lg,content_$lg";
        } else {
            $field = 'title,content';
        }

        $content = table('SysNoticeRule')->where($map)->find();

        if ($content) {
            $content['title']   = $lg == 'zh' ? $content['title'] : $content['title_' . $lg];
            $content['content'] = $lg == 'zh' ? $content['content'] : $content['content_' . $lg];
        }

        $content = $content ? $content : '';

        /*$content = '';
        switch ($flag) {
        case 'register_user':
        $content = '恭喜你成为会员,祝你购车愉快';
        break;
        case 'comment':
        $content = '会员{nickname},给你留言了,请尽快查看哦！';
        break;
        case 'newComment':
        $content = '你有新的消息';
        break;
        case 'user_appointment_success':
        $content = '商家：[{nickname}]已确认你的预约，准时到达，商家电话：{mobile}';
        break;
        case 'user_appointment_fail':
        $content = '商家：[{nickname}]拒绝了你的预约';
        break;
        case 'user_appointment_edit_time':
        $content = '商家：[{nickname}]修改了预约时间,请及时确认';
        break;
        case 'user_get_coupon':
        $content = '你有一张代金券可以免费领取,确认订单即可领取';
        break;
        case 'seller_appointment_success':
        $content = '你有新的预约订单';
        break;
        case 'seller_appointment_refuse_time':
        $content = '会员{nickname}，拒绝了你设置的预约时间';
        break;
        default:
        # code...
        break;
        }*/

        return $content;
    }

    public function getList($lg = 'zh', $mapValue = array())
    {
        $map = array();

        $map = array_merge($map, $mapValue);

        $list = table('UserMessage')->where($map)->find('array');

        foreach ($list as $key => $value) {
            $content = $this->getContent($value['flag'], $lg);

            $content = $this->analysisTemplate($content, json_decode($value['param'], true));

            $list[$key]['title']   = $content['title'];
            $list[$key]['content'] = $content['content'];
        }

        return $list;
    }
}
