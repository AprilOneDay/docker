<?php
/**
 * 车友圈模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Circle extends Init
{
    /**
     * 车友圈列表
     * @date   2017-09-25T15:13:59+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $filterId = get('filter_id', 'text', '');

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['status']     = 1;

        !$filterId ?: $filterId = explode(',', $filterId);

        $list = table('Circle')->where($map)->field('id,ablum,description,uid,created')->limit($offer, $pageSize)->order('created desc')->find('array');
        foreach ($list as $key => $value) {

            $list[$key]['is_like'] = (bool) table('Enjoy')->where(array('type' => 1, 'value' => $value['id'], 'uid' => $this->uid))->count();
            $list[$key]['like']    = (int) table('Enjoy')->where(array('type' => 1, 'value' => $value['id']))->count();
            $list[$key]['ablum']   = $this->appImgArray($value['ablum'], 'circle');
            $list[$key]['created'] = date('Y/m/d', $value['created']);
            $user                  = dao('User')->getInfo($value['uid'], 'nickname,avatar,type');
            $user['avatar']        = $this->appImg($user['avatar'], 'avatar');
            $list[$key]['user']    = $user;
            $comment               = dao('Comment')->getList(1, $value['id']);
            $list[$key]['comment'] = $comment;
            $list[$key]['is_del']  = $this->uid == $value['uid'] ? true : false;

            //过滤指定ID信息
            if ($filterId && in_array($value['id'], $filterId)) {
                unset($list[$key]);
            }
        }

        $data['tot_read_total'] = (int) dao('Comment')->getNotReadTotal($this->uid); //获取未读信息条数
        $data['list']           = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 分享详情
     * @date   2017-09-26T09:45:49+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function detail()
    {

        $id = get('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['del_status'] = 0;
        $map['id']         = $id;

        $data = table('Circle')->where($map)->field('id,ablum,description,uid,created')->find();
        if (!$data) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $data['is_del']  = $this->uid == $uid ? true : false;
        $data['like']    = (int) table('Enjoy')->where(array('type' => 1, 'value' => $data['id']))->count();
        $data['is_like'] = (bool) table('Enjoy')->where(array('type' => 1, 'value' => $data['id'], 'uid' => $this->uid))->count();
        $data['ablum']   = $this->appImgArray($data['ablum'], 'circle');
        $data['created'] = date('Y/m/d', $data['created']);
        $user            = dao('User')->getInfo($data['uid'], 'nickname,avatar');
        $user['avatar']  = $this->appImg($user['avatar'], 'avatar');
        $data['user']    = $user;
        $comment         = dao('Comment')->getList(1, $data['id']);
        $data['comment'] = $comment;

        $this->appReturn(array('data' => $data));
    }
}
