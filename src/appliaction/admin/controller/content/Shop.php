<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

class Shop extends \app\admin\controller\Init
{
    public function lists()
    {
        $param    = get('param', 'text');
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $data = dao('Shop', 'admin')->lists($param, $pageNo, $pageSize);

        $other = array(
            'categoryCopy' => getVar('tags', 'console.article'),
            'isIdeCopy'    => array(0 => '未认证', 1 => '已认证', 2 => '认证未通过'),
        );

        $this->assign('list', $data['list']);
        $this->assign('param', $param);
        $this->assign('pages', $data['page']->loadConsole());
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
