<?php
/**
 * 聊天模块
 */
namespace app\tools\dao;

class Chart
{
    /**
     * 获取聊天记录
     * @date   2017-10-18T15:43:48+0800
     * @author ChenMingjiang
     * @param  integer                  $uid      [发送信息人]
     * @param  integer                  $toUid    [接收信息人]
     * @param  integer                  $pageNo   [description]
     * @param  integer                  $pageSize [description]
     * @return [type]                             [description]
     */
    public function histroyLists($uid = 0, $toUid = 0, $pageNo = 1, $pageSize = 999)
    {
        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['is_reader'] = 1;
        $map['_string']   = "(uid = $uid and to_uid = $toUid) or (uid = $toUid and to_uid = $uid)";

        if ($uid) {
            $user           = (array) dao('User')->getInfo($uid, 'avatar,nickname');
            $user['avatar'] = $user['avatar'];
        } else {
            $user['nickname'] = '口袋车平台';
            $user['avatar']   = '';
        }

        if ($toUid) {
            $toUser           = (array) dao('User')->getInfo($toUid, 'avatar,nickname');
            $toUser['avatar'] = $toUser['avatar'];
        } else {
            $toUser['nickname'] = '口袋车平台';
            $toUser['avatar']   = '';
        }

        $list = table('ChatLog')->where($map)->field('id,uid,to_uid,content,created')->limit($offer, $pageSize)->order('created asc')->find('array');
        //echo table('ChatLog')->getSql();die;
        foreach ($list as $key => $value) {
            $list[$key]['float']   = $value['uid'] == $uid ? 'right' : 'left';
            $list[$key]['created'] = date('Y/m/d H:i:s', $value['created']);
        }

        $data['list']['user']    = $user;
        $data['list']['to_user'] = $toUser;
        $data['list']['content'] = $list ? $list : array();

        return $data['list'];
    }
}
