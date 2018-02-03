<?php
/**
 * 聊天模块管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha;

class Chat extends Init
{
    public function lists()
    {
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['type'] === '0') {
            $map['_string'] = "( uid = 0 or to_uid = 0)";
        } elseif ($param['type'] === '1') {
            $map['_string'] = "( uid != 0 and to_uid != 0)";
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'content') {
                $map['content'] = array('instr', $param['keyword']);
            } elseif ($param['field'] == 'nickname') {
                $uid = table('User')->where(array('nickname' => array('instr', $param['keyword'])))->field('id')->find('one');
                if ($uid) {
                    $map['uid'] = $uid;
                }

            }
        }

        //接受者筛选
        if ($param['to_field'] && $param['to_keyword']) {
            $toUid = table('User')->where(array('nickname' => array('instr', $param['to_keyword'])))->field('id')->find('one');
            if ($toUid) {
                $map['to_uid'] = $toUid;
            }
        }

        $list  = table('ChatLog')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('ChatLog')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $user = dao('User')->getInfo($value['uid'], 'nickname,type');
            if (!$value['uid']) {
                $user['nickname'] = '平台管理员';
            }
            $toUser = dao('User')->getInfo($value['to_uid'], 'nickname,avatar');
            if (!$value['to_uid']) {
                $toUser['nickname'] = '平台管理员';
            }

            $list[$key]['user']   = $user;
            $list[$key]['toUser'] = $toUser;
        }

        $other = array(
            'isReaderCopy' => array('1' => '已读', '0' => '未读'),
            'userTypeCopy' => array('0' => '平台', '1' => '用户'),
        );

        $this->assign('commission', dao('Param')->getValue(1));
        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }

    /**
     * 平台回复
     * @date   2017-10-18T15:34:41+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function reply()
    {
        if (IS_POST) {
            $toUid   = post('to_uid', 'intval', 0);
            $content = post('content', 'text', '');
            if (!$toUid) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请选择回复人'));
            }

            if (!$content) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请输入回复内容'));
            }

            $data['to_uid']    = $toUid;
            $data['uid']       = 0;
            $data['created']   = TIME;
            $data['content']   = $content;
            $data['is_reader'] = 1; //默认已读

            $reslut = table('ChatLog')->add($data);
            if (!$reslut) {
                $this->ajaxReturn(array('status' => false, 'msg' => '消息发送失败'));
            }

            //发送站内推送提示
            dao('Message')->send($toUid, 'newComment', '', '', 0, 3);

            $this->ajaxReturn(array('msg' => '发送成功'));
        } else {
            $toUid = get('to_uid', 'intval', 0);
            if (!$toUid) {
                denha\Log::error('参数错误');
            }

            $list = dao('Chart')->histroyLists(0, $toUid);
            $data = table('ChatLog')->where('id', $id)->find();

            $this->assign('data', $data);
            $this->assign('list', $list);
            $this->assign('to_uid', $toUid);
            $this->show();
        }

    }
}
