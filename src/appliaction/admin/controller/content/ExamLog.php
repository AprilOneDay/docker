<?php
/**
 * 试卷模块管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class ExamLog extends Init
{
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map = array();

        $list  = table('UserExamLog')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('UserExamLog')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['exam']        = table('ExamList')->where('id', $value['exam_id'])->field('name')->find();
            $list[$key]['user']        = dao('User')->getInfo($value['uid'], 'nickname,real_name');
            $list[$key]['total_score'] = (int) table('ExamData')->where($map)->field('SUM(score) AS score')->find('one');
            $list[$key]['use_time']    = dao('Time')->diffDate($value['start_time'], $value['end_time']);
        }

        $other = array(
            'statusCopy' => array(0 => '关闭', 1 => '开启'),
            'timeCopy'   => getVar('time', 'admin.sys'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }
}
