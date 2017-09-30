<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

class Car extends \app\admin\controller\Init
{
    public function lists()
    {

        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $data = dao('Car', 'admin')->lists($param, $pageNo, $pageSize);

        $other = array(
            'brandCopy'     => dao('Category')->getList(1),
            'recommendCopy' => array('1' => '推荐', '0' => '未推荐'),
            'urgencyCopy'   => array('1' => '是', '0' => '否'),
            'typeCopy'      => array('1' => '个人', '2' => '商家'),
            'statusCopy'    => array('1' => '上架', '0' => '下架'),
        );

        $this->assign('list', $data['list']);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $data['page']->loadConsole());
        $this->show();
    }

}
