<?php
namespace app\admin\tools\dao;

use denha;

class Shop
{
    public function lists($param, $pageNo, $pageSize)
    {

        $offer = max(($pageNo - 1), 0) * $pageSize;

        if ($param['tag']) {
            $map['tag'] = $param['tag'];
        }

        if ($param['is_ide'] != '') {
            $map['is_ide'] = $param['is_ide'];
        }

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_show'] != '') {
            $map['is_show'] = $param['is_show'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'name') {
                $map['name'] = array('like', '%' . $param['keyword'] . '%');
            } elseif ($param['field'] == 'uid') {
                $map['uid'] = $param['keyword'];
            }
        }

        $list  = table('UserShop')->where($map)->limit($offer, $pageSize)->order('id desc')->field('id,uid,name,category,is_ide,status')->find('array');
        $total = table('UserShop')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['user'] = dao('User')->getInfo($value['uid'], 'nickname,mobile');
        }

        $data = array('page' => $page, 'list' => $list);

        return $data;
    }
}
