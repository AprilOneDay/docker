<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

use denha;

class User extends \app\admin\controller\Init
{
    public function lists()
    {

        $pageNo = get('pageNo', 'intval', 0);

        $field   = get('field', 'text', '');
        $keyword = get('keyword', 'text', '');

        $pageSize = 20;
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $map['type']       = 1;
        $map['del_status'] = 0;
        $param['field']    = 'id';

        if ($field && $keyword) {
            if ($field == 'id') {
                $map[$field] = $keyword;
            } elseif ($field == 'username') {
                $map['username'] = array('like', '%' . $keyword . '%');
            }
            $param['field']   = $field;
            $param['keyword'] = $keyword;
        }

        $list  = table('User')->where($map)->limit($offer, $pageSize)->find('array');
        $total = table('User')->where($map)->count();

        $page = new denha\Pages($total, $pageNo, $pageSize, url('lists', $param));

        $this->assign('list', $list);
        $this->assign('pages', $page->loadConsole());
        $this->assign('param', $param);
        $this->show();
    }

    public function editPost()
    {
        $id = get('id', 'intval', 0);
        if (!$id) {
            denha\Log::error('参数错误');
        }

        $data['status'] = post('status', 'intval', 0);

        $result = table('User')->where('id', $id)->save($data);
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
        }

        $this->assign('data', $data);
        $this->show();
    }
}
