<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class User extends Init
{
    public function lists()
    {

        $pageNo = get('pageNo', 'intval', 0);

        $field   = get('field', 'text', '');
        $keyword = get('keyword', 'text', '');

        $param = get('param');

        $pageSize = 20;
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $param['field']    = 'id';

        if ($param['type']) {
            $map['type'] = $param['type'];
        }

        if ($param['status']) {
            $map['status'] = $param['status'];
        }

        if ($field && $keyword) {
            if ($field == 'id' || $field == 'mobile') {
                $map[$field] = $keyword;
            } elseif ($field == 'username') {
                $map['username'] = array('instr', $keyword);
            } elseif ($field = 'nickname') {
                $map['nickname'] = array('instr', $keyword);
            }
            $param['field']   = $field;
            $param['keyword'] = $keyword;
        }

        $list  = table('User')->where($map)->limit($offer, $pageSize)->find('array');
        $total = table('User')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('lists', $param));

        $other = array(
            'typeCopy'   => getVar('type', 'admin.user'),
            'statusCopy' => array('0' => '关闭', '1' => '开启'),
        );

        $this->assign('other', $other);
        $this->assign('list', $list);
        $this->assign('pages', $page->loadConsole());
        $this->assign('param', $param);
        $this->show();
    }

    public function editPost()
    {
        $id = get('id', 'intval', 0);

        //编辑
        if ($id) {
            $data['status']    = post('status', 'intval', 0);
            $data['nickname']  = post('nickname', 'text', '');
            $data['real_name'] = post('real_name', 'text', '');
            $data['mobile']    = post('mobile', 'text', '');
            $result            = table('User')->where('id', $id)->save($data);
        }
        //添加
        else {
            $data['type']      = post('type', 'intval', 0);
            $data['username']  = post('username', 'text', '');
            $data['nickname']  = post('nickname', 'text', '');
            $data['real_name'] = post('real_name', 'text', '');
            $data['mobile']    = post('mobile', 'text', '');
            $data['integral']  = post('integral', 'intval', '');
            $data['password']  = post('password', 'text', '');

            $password2 = post('password2', 'text', '');

            $result = dao('User')->register($data, $password2);
            if (!$result['status']) {
                $this->ajaxReturn($result);
            }

        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->ajaxReturn(array('msg' => '操作成功'));
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        if ($id) {
            $data = table('User')->where('id', $id)->find();
        } else {
            $data = array('integral' => 0, 'status' => 1);
        }
        $other = array(
            'typeCopy'   => getVar('type', 'admin.user'),
            'statusCopy' => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);
        $this->show();
    }
}
