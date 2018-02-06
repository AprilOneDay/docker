<?php
/**
 * 首页模块
 */
namespace app\flower\app\controller\v1\common;

use app\app\controller;
use app\flower\app\controller\v1\WeixinSmallInit;

class Category extends WeixinSmallInit
{

    /**
     * 获取分类
     * @date   2017-09-18T10:16:11+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getList()
    {
        $id = get('id', 'intval', 0);

        $data = $this->appArray(dao('Category')->getList($id, $this->lg));
        $this->appReturn(array('data' => $data));
    }

    /**
     * 获取分类完整版
     * @date   2017-12-29T10:39:30+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getAllList()
    {
        $id = get('id', 'intval', 0);

        $list = dao('Category')->getListAllInfo($id, '', $this->lg);

        foreach ($list as $key => $value) {
            $list[$key]['thumb'] = $value['thumb'] ? $this->appImg($value['thumb'], 'category') : '';
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 模糊搜索分类
     * @date   2017-12-29T11:33:43+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function searchAllList()
    {
        $id      = get('id', 'intval', 0);
        $keyword = get('keyword', 'text', '');

        $data = dao('Category')->searchListAllInfo($id, $keyword, $this->lg);

        $data['list'] = $data ? $data : array();

        $this->appReturn(array('data' => $data));
    }
}
