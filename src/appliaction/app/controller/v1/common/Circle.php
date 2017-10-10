<?php
/**
 * 车友圈模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class Circle extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }
    }

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

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;

        $list = table('Circle')->where($map)->field('id,ablum,description,uid,created')->limit($offer, $pageSize)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['is_like'] = (bool) table('Enjoy')->where(array('type' => 1, 'value' => $value['id'], 'uid' => $this->uid))->count();
            $list[$key]['like']    = (int) table('Enjoy')->where(array('type' => 1, 'value' => $value['id']))->count();
            $list[$key]['ablum']   = $this->appImgArray($value['ablum'], 'circle');
            $list[$key]['created'] = date('Y/m/d', $value['created']);
            $user                  = dao('User')->getInfo($value['uid'], 'nickname,avatar');
            $user['avatar']        = $this->appImg($user['avatar'], 'avatar');
            $list[$key]['user']    = $user;
            $comment               = dao('Comment')->getList(1, $value['id']);
            $list[$key]['comment'] = $comment;
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

    /**
     * 分享
     * @date   2017-09-25T15:08:20+0800
     * @author ChenMingjiang
     */
    public function add()
    {
        $files['ablum']      = files('ablum');
        $data['description'] = post('description', 'text', '');

        if (!$files && !$data['description']) {
            $this->appReturn(array('status' => false, 'msg' => '请填写信息'));
        }

        //上传ablum图 并生成封面图片
        $data['ablum'] = $this->appUpload($files['ablum'], $data['ablum'], 'circle');
        if (stripos($data['ablum'], ',') !== false) {
            $data['thumb'] = substr($data['ablum'], 0, stripos($data['ablum'], ','));
        } else {
            $data['thumb'] = $data['ablum'];
        }

        if (!$data['ablum']) {
            $this->appReturn(array('status' => false, 'msg' => '请上传图片'));
        }

        $data['created'] = TIME;
        $data['uid']     = $this->uid;
        $data['type']    = 1;

        $reslut = table('Circle')->add($data);
        if (!$reslut) {
            $this->appReturn(array('status' => false, 'msg' => '分享失败'));
        }

        $this->appReturn(array('msg' => '分享成功'));
    }

    /**
     * 获取未读信息
     * @date   2017-09-25T17:13:40+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getNotReadList()
    {

        $map['to_uid']           = $this->uid;
        $map['is_to_uid_reader'] = 0;

        $list = table('Comment')->where($map)->field('id,goods_id,content,uid,created')->find('array');
        foreach ($list as $key => $value) {
            $user               = dao('User')->getInfo($value['uid'], 'nickname,avatar');
            $user['avatar']     = $this->appImg($user['avatar'], 'avatar');
            $list[$key]['user'] = $user;

            $goods               = table('Circle')->where(array('type' => 1, 'id' => $value['goods_id']))->field('thumb,description')->find();
            $goods['thumb']      = $this->appImg($goods['thumb'], 'circle');
            $list[$key]['goods'] = $goods;
        }

        $data['list'] = $list ? $list : array();

        //进入后直接标记为已读
        $reslut = table('Comment')->where($map)->save('is_to_uid_reader', 1);

        $this->appReturn(array('data' => $data));
    }

    /**
     * 删除自己文章
     * @date   2017-09-25T15:11:43+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function del()
    {
        $id = post('id', 'intval', 0);
        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map['uid']        = $this->uid;
        $map['del_status'] = 0;
        $map['id']         = $id;

        $is = table('Circle')->where($map)->field('id')->find('one');
        if (!$is) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $reslut = table('Circle')->where('id', $id)->save('del_status', 1);
        if (!$reslut) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('msg' => '删除成功'));
    }

    /**
     * 增加点赞
     * @date   2017-09-25T15:44:51+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function like()
    {
        $id = post('id', 'intval', 0);

        $reslut = dao('Enjoy')->add($this->uid, 1, $id);
        $this->appReturn($reslut);
    }

    /**
     * 取消点赞
     * @date   2017-09-25T15:47:52+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function delLike()
    {
        $id = post('id', 'intval', 0);

        $reslut = dao('Enjoy')->del($this->uid, 1, $id);
        $this->appReturn($reslut);
    }

    /**
     * 评论
     * @date   2017-09-25T16:16:12+0800
     * @author ChenMingjiang
     */
    public function addComment()
    {
        $goodsId = post('goods_id', 'intval', 0);
        $content = post('content', 'text', '');

        $reslut = dao('Comment')->add($this->uid, 1, $goodsId, $content);
        $this->appReturn($reslut);
    }

    /**
     * 回复
     * @date   2017-09-25T16:17:17+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function relpyComment()
    {
        $content  = post('content', 'text', '');
        $parentId = post('comment_id', 'intval', 0);
        $toUid    = post('to_uid', 'intval', 0);

        $reslut = dao('Comment')->reply($this->uid, 1, $content, $parentId, $toUid);
        $this->appReturn($reslut);
    }
}
