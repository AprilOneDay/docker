<?php
/**
 * 评论管理
 */
namespace app\tools\dao;

class Comment
{
    //发表评论
    public function add($uid = 0, $type = 0, $goodsId = 0, $content, $dataContent = array(), $toUid = 0)
    {
        if (!$uid || !$goodsId || !$type) {
            return array('status' => false, 'msg' => '参数错误');
        }

        /*if (!$content) {
        return array('status' => false, 'msg' => '内容不能为空');
        }*/

        $data              = $dataContent;
        $data['uid']       = $uid;
        $data['goods_id']  = $goodsId;
        $data['content']   = $content;
        $data['parent_id'] = 0;
        $data['created']   = TIME;
        $data['type']      = $type;
        $data['to_uid']    = $toUid;

        if ($type == 1) {
            $data['to_uid'] = table('Circle')->where(array('id' => $goodsId))->field('uid')->find('one');
        }

        $result = table('Comment')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '评论失败');
        }

        //车友圈 聊天发送站内信
        if ($type == 1 || $type == 4) {
            //发送站内信
            $sendData = array(
                'nickname' => dao('User')->getNickname($uid),
            );
            $sendJump = array(
                'type'     => 1,
                'goods_id' => $goodsId,
            );
            dao('Message')->send($data['to_uid'], 'comment', $sendData, $sendJump, 0, 2);
        }

        return array('status' => true, 'msg' => '评论成功');
    }

    /**
     * 回复评论
     * @date   2017-11-17T14:10:51+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid         [评论人uid]
     * @param  [type]                   $type        [类型]
     * @param  [type]                   $content     [评论内容]
     * @param  [type]                   $parentId    [评论父级id]
     * @param  [type]                   $toUid       [接收人]
     * @param  array                    $dataContent [附加表参数]
     * @return [type]                                [description]
     */
    public function reply($uid, $type, $content, $parentId, $toUid, $dataContent = array())
    {
        if (!$uid || !$type || !$toUid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$content) {
            return array('status' => false, 'msg' => '内容不能为空');
        }

        /* if ($uid == $toUid) {
        return array('status' => false, 'msg' => '自己不能对自己回复');
        }*/

        $map['id'] = $parentId;
        $comment   = table('Comment')->where($map)->field('goods_id,uid')->find();
        if (!$comment) {
            return array('status' => false, 'msg' => '回复信息不存在');
        }

        $data              = $dataContent;
        $data['uid']       = $uid;
        $data['goods_id']  = $comment['goods_id'];
        $data['content']   = $content;
        $data['parent_id'] = $parentId;
        $data['to_uid']    = $toUid;
        $data['created']   = TIME;

        $result = table('Comment')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '评论失败');
        }

        //发送站内信
        $sendData = array(
            'nickname' => dao('User')->getNickname($uid),
        );
        $sendJump = array(
            'type'     => 1,
            'goods_id' => $comment['goods_id'],
        );
        dao('Message')->send($toUid, 'comment', $sendData, $sendJump, $uid, 2);
        return array('status' => true, 'msg' => '评论成功');
    }

    public function getNotReadTotal($uid)
    {
        if (!$uid) {
            return 0;
        }

        $map['to_uid']           = $uid;
        $map['is_to_uid_reader'] = 0;
        $count                   = (int) table('Comment')->where($map)->count();

        return $count;
    }

    /**
     * 评价内容
     * @date   2017-09-25T17:07:01+0800
     * @author ChenMingjiang
     * @param  [type]                   $type    [description]
     * @param  [type]                   $goodsId [description]
     * @return [type]                            [description]
     */
    public function getList($type, $goodsId)
    {
        if (!$type || !$goodsId) {
            return false;
        }

        $map['type']       = $type;
        $map['goods_id']   = $goodsId;
        $map['parent_id']  = 0;
        $map['del_status'] = 0;

        $list = table('Comment')->where($map)->order('created desc')->field('id,content,uid,created,ablum')->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['child'] = $this->getChildList($type, $goodsId, $value['id']);
            $user                = dao('User')->getInfo($value['uid'], 'nickname,avatar');
            $user['avatar']      = getConfig('config.app', 'imgUrl') . '/uploadfile/avatar/' . $user['avatar'];
            $list[$key]['user']  = $user;

        }

        $list = $list ? $list : array();
        return $list;

    }

    /**
     * 回复内容
     * @date   2017-09-25T17:06:48+0800
     * @author ChenMingjiang
     * @param  [type]                   $type     [description]
     * @param  [type]                   $goodsId  [description]
     * @param  [type]                   $parentId [description]
     * @return [type]                             [description]
     */
    public function getChildList($type, $goodsId, $parentId)
    {
        $map['type']       = 1;
        $map['goods_id']   = $goodsId;
        $map['parent_id']  = $parentId;
        $map['del_status'] = 0;

        $list = table('Comment')->where($map)->order('created desc')->field('content,uid,created,to_uid')->order('id asc')->find('array');
        if ($list) {
            foreach ($list as $key => $value) {
                $toUser               = dao('User')->getInfo($value['to_uid'], 'nickname,avatar');
                $toUser['avatar']     = getConfig('config.app', 'imgUrl') . '/uploadfile/avatar/' . $toUser['avatar'];
                $list[$key]['toUser'] = $toUser;
                $user                 = dao('User')->getInfo($value['uid'], 'nickname,avatar');
                $user['avatar']       = getConfig('config.app', 'imgUrl') . '/uploadfile/avatar/' . $user['avatar'];
                $list[$key]['user']   = $user;
            }
        }

        $list = $list ? $list : array();
        return $list;
    }
}
