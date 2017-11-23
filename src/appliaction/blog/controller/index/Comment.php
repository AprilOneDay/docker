<?php
/**
 * 抵扣卷模块管理
 */
namespace app\blog\controller\index;

use denha\Controller;

class Comment extends Controller
{
    /**
     * 添加评论
     * @date   2017-09-28T17:34:18+0800
     * @author ChenMingjiang
     */
    public function add()
    {
        $nickname = post('nickname', 'text', '');
        $goodsId  = post('goods_id', 'intval', 0);
        $content  = post('content', 'text', '');
        $mail     = post('mail', 'text', '');

        if (!$nickname) {
            $this->ajaxReturn(array('status' => false, 'msg' => '输入昵称'));
        }

        if (!$mail) {
            $this->ajaxReturn(array('status' => false, 'msg' => '输入邮箱名称'));
        }

        if (!$content) {
            $this->ajaxReturn(array('status' => false, 'msg' => '输入评论内容'));
        }

        $result = dao('VisitorComment', 'blog')->add($nickname, $mail, 1, $goodsId, $content);
        if ($result['status']) {
            cookie('user', array('nickname' => $nickname, 'mail' => $mail), 3600 * 24 * 30);
        }
        $this->ajaxReturn($result);
    }

    /**
     * 回复
     * @date   2017-09-28T17:34:10+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function reply()
    {
        $parentId = post('parent_id', 'intval', 0);
        $nickname = post('nickname', 'text', '');
        $goodsId  = post('goods_id', 'intval', 0);
        $content  = post('content', 'text', '');
        $mail     = post('mail', 'text', '');
        $toId     = post('to_id', 'intval', 0);

        if (!$nickname) {
            $this->ajaxReturn(array('status' => false, 'msg' => '输入昵称'));
        }

        if (!$mail) {
            $this->ajaxReturn(array('status' => false, 'msg' => '输入邮箱名称'));
        }

        $result = dao('VisitorComment', 'blog')->reply($nickname, 1, $content, $parentId, $toId);
        if ($result['status']) {
            session('user', array('nickname' => $nickname, 'mail' => $mail));
        }
        $this->ajaxReturn($result);
    }

    public function childrenList()
    {
        $goodsId  = post('goods_id', 'intval', 0);
        $parentId = post('parent_id', 'intval', 0);
        //获取评论
        $list = dao('VisitorComment', 'blog')->blogDetail($goodsId, $parentId);
        //$this->ajaxReturn(array('data' => $comment));

        $this->assign('list', $list);
        $this->show();
        die;
    }
}
