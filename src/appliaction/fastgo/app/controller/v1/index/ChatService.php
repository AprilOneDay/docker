<?php
/**
 * 广告图模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class ChatService extends Init
{
    public function lists()
    {
        $map = array();

        $name = get('name', 'text', '');
        if ($name) {
            $map['name'] = $name;
        }

        $list = table('ChatService')->where($map)->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['finally_description'] = dao('Article')->getLgValue($value, 'description', $this->lg);
            $list[$key]['finally_name']        = dao('Article')->getLgValue($value, 'name', $this->lg);
            $list[$key]['avatar']              = $this->appImg($value['avatar'], 'avatar');
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }
}
