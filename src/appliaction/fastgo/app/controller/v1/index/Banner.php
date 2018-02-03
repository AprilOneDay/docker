<?php
/**
 * 广告图模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Banner extends Init
{
    public function lists()
    {

        $depotId = get('depot_id', 'text', '');

        $data['message_num'] = 0;

        if ($this->uid) {
            $data['default_transfer'] = dao('User')->getInfo($this->uid, 'default_transfer');
            $data['message_num']      = table('UserMessage')->where(array('to_uid' => $this->uid, 'is_reader' => 0))->field('count(*) as num')->find('one');
        }

        //默认地址
        $data['default_transfer'] = empty($data['default_transfer']) ? 'FG01' : $data['default_transfer'];

        $data['default_transfer_copy'] = dao('Depot', 'fastgo')->getName($data['default_transfer'], $this->lg);

        $depotId = $depotId ? $depotId : $data['default_transfer'];

        $depotName = table('Category')->where(array('bname_2' => $depotId))->field('name')->find('one');

        $id = table('Banner')->where('title', $depotName)->field('id')->find('one');

        if ($id) {
            $list = dao('Banner')->getBannerList($id);
            foreach ($list as $key => $value) {
                $list[$key]['path'] = $this->appImg($value['path'], 'banner');
            }

        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }
}
