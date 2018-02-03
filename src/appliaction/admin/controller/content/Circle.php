<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class Circle extends Init
{
    public function lists()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;

        if ($param['type'] != '') {
            $map['type'] = $param['type'];
        }

        if ($param['status'] != '') {
            $map['status'] = $param['status'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'description') {
                $map['description'] = array('instr', $param['keyword']);
            }
        }

        $list  = table('Circle')->where($map)->field('id,ablum,description,uid,created,del_status,status')->limit($offer, $pageSize)->order('created desc')->find('array');
        $total = table('Circle')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['like']  = (int) table('Enjoy')->where(array('type' => 1, 'value' => $value['id']))->count();
            $list[$key]['ablum'] = $value['ablum'] ? (array) explode(',', $value['ablum']) : array();

            $user               = dao('User')->getInfo($value['uid'], 'nickname,avatar,type');
            $list[$key]['user'] = $user;
        }

        $other = array(
            'categoryCopy'  => dao('Category')->getList(19),
            'delStatusCopy' => array(1 => '已删除', 0 => '正常'),
            'statusCopy'    => array(0 => '待审核', 1 => '已审核'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);
        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        if ($id) {
            $data = table('Circle')->where('id', $id)->find();
        }

        $this->show();
    }
}
