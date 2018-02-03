<?php
namespace app\admin\controller\finance;

use app\admin\controller\Init;
use denha\Pages;

class Orders extends Init
{
    public function lists()
    {

        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map   = $this->getMap();
        $list  = table('FinanceLog')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('FinanceLog')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $user = dao('User')->getInfo($value['uid'], 'nickname,uid');

            $list[$key]['user'] = $user;
        }

        $other = array(
            'typeCopy'        => getVar('type', 'admin.finance'),
            'issueStatusCopy' => getVar('issue_status', 'admin.finance'),
        );

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());

        $this->show();
    }

    /** 导出 */
    public function excel()
    {

        $param = get('param', 'text');

        $map   = $this->getMap();
        $list  = table('FinanceLog')->where($map)->order('id desc')->find('array');
        $total = table('FinanceLog')->where($map)->count();

        foreach ($list as $key => $value) {
            $user               = dao('User')->getInfo($value['uid'], 'nickname,uid');
            $list[$key]['user'] = $user;
        }

        $other = array(
            'typeCopy'        => getVar('type', 'admin.finance'),
            'issueStatusCopy' => getVar('issue_status', 'admin.finance'),
        );

        $filename = 'finance_orders' . time();
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=$filename.xls");

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->show();
    }

    /** 获取查询参数 */
    private function getMap()
    {
        $param = get('param', 'text');
        if ($param['type'] != '') {
            $map['type'] = $param['type'];
        }

        if ($param['issue_status'] != '') {
            $map['issue_status'] = $param['issue_status'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'pay_sn' || $param['field'] == 'order_sn') {
                $map[$param['field']] = $param['keyword'];
            }
        }

        if ($param['start_time'] || $param['end_time']) {
            $startTime = get('param.start_time', 'time', '');
            $endTime   = get('param.end_time', 'time', '');

            if ($param['start_time'] && $param['end_time']) {
                $map['created'] = array('between', $startTime, $endTime);
            } elseif ($param['start_time']) {
                $map['created'] = array('>=', $startTime);
            } elseif ($param['end_time']) {
                $map['created'] = array('<=', $endTime);
            }
        }

        //$map['is_pay']  = 1;
        //$map['is_lock'] = 1;

        return $map;
    }
}
