<?php
/**
 * 车友圈模块
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class VisitorComment extends Init
{
    public function lists()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['type'] = 2;

        $list  = table('VisitorComment')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('VisitorComment')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        $other = array(
            'tag'             => getVar('tags', 'console.article'),
            'isShowCopy'      => array(0 => '未审核', 1 => '已审核'),
            'isRecommendCopy' => array(0 => '未推荐', 1 => '已推荐'),
            'columnListCopy'  => dao('Column', 'admin')->columnList($param['column_id'], $this->webType),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function del()
    {
        $id = post('id');

        $map['type'] = 2;
        $map['id']   = $id;

        $result = table('VisitorComment')->where($map)->delete();
        if (!$result) {
            $this->ajaxReturn(array('status' => true, 'msg' => '删除失败'));
        }

        $this->ajaxReturn(array('msg' => '删除成功'));
    }
}
