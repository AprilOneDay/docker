<?php
/**
 * 评论管理
 */
namespace app\blog\tools\dao;

class VisitorComment
{
    //发表评论
    public function add($nickname = '', $mail = '', $type = 0, $goodsId = 0, $content, $dataContent = array())
    {

        if (!$nickname || !$type) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$content) {
            return array('status' => false, 'msg' => '内容不能为空');
        }

        $data              = $dataContent;
        $data['nickname']  = $nickname;
        $data['mail']      = $mail;
        $data['goods_id']  = $goodsId;
        $data['content']   = $content;
        $data['parent_id'] = 0;
        $data['created']   = TIME;
        $data['type']      = $type;
        $data['ip']        = getIP();

        $map             = array();
        $map['ip']       = $data['ip'];
        $map['nickname'] = $nickname;
        $map['content']  = $content;

        $id = table('VisitorComment')->where($map)->field('id')->find('one');
        if ($id) {
            return array('status' => false, 'msg' => '请勿发布重复内容');
        }

        //当日IP只可提交N条数据
        $beginToday     = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday       = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $map            = array();
        $map['ip']      = $data['ip'];
        $map['created'] = array('between', $beginToday, $endToday);
        $count          = table('VisitorComment')->where($map)->count();
        if ($count >= 5) {
            return array('status' => false, 'msg' => '请勿频繁提交');
        }

        $result = table('VisitorComment')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '评论失败');
        }

        return array('status' => true, 'msg' => '评论成功');
    }

    //回复评论
    public function reply($nickname, $type, $content, $parentId, $toId = 0, $dataContent = array())
    {
        if (!$nickname || !$type || !$toId) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$content) {
            return array('status' => false, 'msg' => '内容不能为空');
        }

        $map['id'] = $parentId;
        $comment   = table('VisitorComment')->where($map)->field('goods_id')->find();
        if (!$comment) {
            return array('status' => false, 'msg' => '回复信息不存在', 'sql' => table('VisitorComment')->getSql());
        }

        $data                = $dataContent;
        $data['nickname']    = $nickname;
        $data['goods_id']    = $comment['goods_id'];
        $data['content']     = $content;
        $data['parent_id']   = $parentId;
        $data['created']     = TIME;
        $data['ip']          = getIP();
        $data['to_id']       = $toId;
        $data['to_nickname'] = table('VisitorComment')->where('id', $toId)->field('nickname')->find('one');

        $result = table('VisitorComment')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '评论失败');
        }

        return array('status' => true, 'msg' => '评论成功');
    }

    public function blogDetail($goodsId = 0, $parentId = 0)
    {
        $map['type']      = 1;
        $map['goods_id']  = $goodsId;
        $map['parent_id'] = $parentId;

        $list = table('VisitorComment')->where($map)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            if ($value['parent_id'] == 0) {
                $list[$key]['total'] = (int) table('VisitorComment')->where(array('is_show' => 1, 'parent_id' => $value['id']))->count();
            }
        }
        return $list;
    }

}
