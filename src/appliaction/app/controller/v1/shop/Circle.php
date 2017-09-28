<?php
/**
 * 车友圈模块
 */
namespace app\app\controller\v1\shop;

use app\app\controller;

class Circle extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkShop();
        $this->checkIde();
    }

    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['uid']        = $this->uid;

        $list = table('Circle')->where($map)->field('id,ablum,description,uid,created')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['like']    = (int) table('Enjoy')->where(array('type' => 1, 'value' => $value['id']))->count();
            $list[$key]['ablum']   = $this->appImgArray($value['ablum'], 'circle');
            $list[$key]['created'] = date('Y/m/d', $value['created']);
            $user                  = dao('User')->getInfo($value['uid'], 'nickname,avatar');
            $user['avatar']        = $this->appImg($user['avatar'], 'avatar');
            $list[$key]['user']    = $user;
            $comment               = dao('Comment')->getList(1, $value['id']);
            $list[$key]['comment'] = $comment;
        }

        $data['tot_read_total'] = (int) dao('Comment')->getNotReadTotal($this->uid);
        $data['list']           = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

}
