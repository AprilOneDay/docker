<?php
/**
 * 搜索模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class Search extends \app\app\controller\Init
{
    public function tages()
    {
        $type = get('type', 'intval', 1);

        $list = table('SearchRemmond')->where(array('type' => $type, 'status' => 1))->field('value')->order('sort asc')->find('one', true);

        $data['list'] = (array) $list;

        $this->appReturn(array('data' => $data));
    }
}
