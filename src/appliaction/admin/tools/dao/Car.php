<?php
namespace app\admin\tools\dao;

use denha;

class Car
{
    public function lists($param, $pageNo, $pageSize)
    {

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['type']) {
            $map['type'] = $param['type'];
        }

        if ($param['brand']) {
            $map['brand'] = $param['brand'];
        }

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_urgency'] != '') {
            $map['is_urgency'] = $param['is_urgency'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('like', '%' . $param['keyword'] . '%');
            }
        }

        $field = 'id,type,title,uid,is_recommend,is_urgency,created,status';
        $list  = table('GoodsCar')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('GoodsCar')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['nickname'] = dao('User')->getNickname($value['uid']);
        }

        $data = array('page' => $page, 'list' => $list);

        return $data;
    }
}
