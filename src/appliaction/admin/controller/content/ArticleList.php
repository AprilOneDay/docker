<?php
/**
 * 文章内容管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class ArticleList extends Init
{
    public function lists()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;

        if ($param['column_id']) {
            $map['column_id'] = $param['column_id'];
        }

        if ($param['tag']) {
            $map['tag'] = $param['tag'];
        }

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_show'] != '') {
            $map['is_show'] = $param['is_show'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('like', '%' . $param['keyword'] . '%');
            }
        }
        $list  = table('Article')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('Article')->where($map)->count();
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

}
