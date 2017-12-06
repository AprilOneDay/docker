<?php
/**
 * 车友圈模块
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class AppVersion extends Init
{
    public function lists()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map = array();

        $list  = table('AppVersion')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('AppVersion')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['thumb'] = 'http://qr.liantu.com/api.php?text=' . URL . $value['apk_url'] . '&w=200&h=200';
        }

        $other = array(
            'tag'             => getVar('tags', 'admin.article'),
            'isShowCopy'      => array(0 => '隐藏', 1 => '显示'),
            'isRecommendCopy' => array(1 => '推荐', 0 => '不推荐'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function edit()
    {
        $this->show();
    }
}
