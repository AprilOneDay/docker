<?php
/**
 * 通知模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Notice extends Init
{
    public function lists()
    {

        $depotId  = get('depot_id', 'text', '');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $isRecommend = get('is_recommend', 'text', '');

        $depotId = (int) table('Category')->where(array('bname_2' => $depotId))->field('id')->find('one');

        if (!$depotId) {
            $this->appReturn(array('stauts' => false, 'msg' => 'depotId参数错误'));
        }

        $map['column_id'] = 54;
        if ($isRecommend) {
            $map['is_recommend'] = $isRecommend;
        }

        $map['depot_id'] = $depotId;
        $data            = dao('Article')->getList($map, '', 1, $pageSize, $pageNo);

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['thumb'] = $this->appImg($value['thumb'], 'article');
            $data['list'][$key]['video'] = $value['video'] ? Start::$config['h5Url'] . $value['video'] : '';

            $data['list'][$key]['finally_title']       = $this->lg != 'zh' && isset($value['title_' . $this->lg]) ? $value['title_' . $this->lg] : $value['content'];
            $data['list'][$key]['finally_description'] = $this->lg != 'zh' && isset($value['description_' . $this->lg]) ? $value['description_' . $this->lg] : $value['description'];

        }

        $data['list'] = $data['list'] ? $data['list'] : array();

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }
}
