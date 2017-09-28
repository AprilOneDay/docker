<?php
/**
 * 抵扣卷模块管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Comment extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }
    }

    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['type'] = 2;
        $map['uid']  = $this->uid;

        $list = table('Comment')->where($map)->limit($offer, $pageSize)->order('status desc,created desc')->find('array');
        foreach ($list as $key => $value) {
            $user           = dao('User')->getInfo($value['uid'], 'avatar,nickname');
            $user['avatar'] = $this->appImg($user['avatar'], 'avatar');

            $list[$key]['to_user'] = $user;
        }
        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }
}
