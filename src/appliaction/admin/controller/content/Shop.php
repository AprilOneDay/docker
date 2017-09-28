<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

use denha;

class Shop extends \app\admin\controller\Init
{
    public function lists()
    {
        $param['field']        = get('field', 'text', 'name');
        $param['keyword']      = get('keyword', 'text', '');
        $param['tag']          = get('tag', 'intval', 0);
        $param['is_show']      = get('is_show', 'text', '');
        $param['is_recommend'] = get('is_recommend', 'text', '');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($param['pageNo'] - 1), 0) * $pageSize;

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
            if ($param['field'] == 'name') {
                $map['name'] = array('like', '%' . $param['keyword'] . '%');
            }
        }
        $list  = table('UserShop')->where($map)->limit($offer, $pageSize)->order('id desc')->field('id,uid,name,category,is_ide,status')->find('array');
        $total = table('UserShop')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['user'] = dao('User')->getInfo($value['uid'], 'nickname,mobile');
        }

        $other = array(
            'categoryCopy' => getVar('tags', 'console.article'),
            'isIdeCopy'    => array(0 => '未认证', 1 => '已认证', 2 => '认证未通过'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {
            $data['is_ide'] = post('is_ide', 'intval', 0);
            $data['status'] = post('status', 'intval', 0);

            if ($id) {
                $reslut = table('UserShop')->where(array('id' => $id))->save($data);

                if (!$reslut) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '提交失败'));
                }

                $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));
            }
        } else {
            $data              = table('UserShop')->where(array('id' => $id))->find();
            $data['ide_ablum'] = imgUrl($data['ide_ablum'], 'ide');
            $data['ablum']     = imgUrl($data['ablum'], 'shop');
            $data['avatar']    = imgUrl($data['avatar'], 'avatar');

            $other = array(
                'categoryCopy' => getVar('tags', 'console.article'),
                'isIdeCopy'    => array(0 => '未认证', 1 => '已认证', 2 => '认证未通过'),
            );
            $this->assign('data', $data);
            $this->assign('other', $other);

            $this->show();
        }

    }
}
