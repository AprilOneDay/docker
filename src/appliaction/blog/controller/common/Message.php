<?php
/**
 * 抵扣卷模块管理
 */
namespace app\blog\controller\common;

use app\app\controller;

class Message extends \app\app\controller\Init
{
    public function index()
    {
        //获取分类
        $class = table('Article')->where(array('is_show' => 1))->field('count(*) as num,tag')->group('tag')->find('array');
        foreach ($class as $key => $value) {
            $listClass[$value['tag']] = $value;
        }
        $user = getCookie('user');

        $this->assign('user', $user);
        $this->assign('listClass', $listClass);
        $this->assign('tagCopy', getVar('tags', 'console.article'));
        $this->assign('randList', $this->rank());
        $this->show();
    }

    public function add()
    {
        $nickname = post('nickname', '');
        $mail     = post('mail', 'text', '');
        $content  = post('content', 'text', '');

        if (!$nickname) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入昵称'));
        }

        if (!$mail) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入邮箱'));
        }

        if (!$content) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入留言内容'));
        }

        $result = dao('VisitorComment', 'blog')->add($nickname, $mail, 2, 0, $content);
        $this->ajaxReturn($result);
    }

    /**
     * 排行榜
     * @date   2017-09-28T17:05:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    private function rank()
    {
        $map['is_show']      = 1;
        $map['is_recommend'] = 1;

        $field = 'id,title';
        $list  = table('Article')->where($map)->field($field)->limit(0, 10)->find('array');

        return $list;
    }
}
