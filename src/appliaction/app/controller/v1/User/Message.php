<?php
/**
 * 站内消息管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Message extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 获取站内信信息列表
     * @date   2017-09-26T11:40:36+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['to_uid']     = $this->uid;
        $map['del_status'] = 0;

        $list = table('UserMessage')->where($map)->limit($offer, $pageSize)->field('uid,content,created,is_reader,jump_app')->order('created desc')->find('array');

        foreach ($list as $key => $value) {
            if ($uid == 0) {
                $user = array('nickname' => '系统消息', 'avatar' => '');
            } else {
                $user           = dao('User')->getInfo($value['uid'], 'nickname,avatar');
                $user['avatar'] = $this->appImg($user['avatar'], 'avatar');
            }

            $list[$key]['user']     = $user;
            $list[$key]['jump_app'] = $value['jump_app'] ? json_decode($value['jump_app']) : array();
        }

        $list = $list ? $list : array();

        $this->appReturn(array('data' => $list));
    }
}
