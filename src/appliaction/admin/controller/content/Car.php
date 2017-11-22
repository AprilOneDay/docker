<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

use denha;

class Car extends \app\admin\controller\Init
{
    public function lists()
    {

        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $data = dao('Car', 'admin')->lists($param, $pageNo, $pageSize);

        $other = array(
            'brandCopy'     => dao('Category')->getList(1),
            'recommendCopy' => array('1' => '推荐', '0' => '未推荐'),
            'urgencyCopy'   => array('1' => '是', '0' => '否'),
            'typeCopy'      => array('1' => '个人', '2' => '商家'),
            'statusCopy'    => array('1' => '上架', '0' => '下架'),
        );

        $this->assign('list', $data['list']);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $data['page']->loadConsole());
        $this->show();
    }

    public function editPost()
    {
        $id = get('id', 'intval');

        $data['is_urgency']   = post('is_urgency', 'intval', 0);
        $data['is_recommend'] = post('is_recommend', 'intval', 0);
        $data['status']       = post('status', 'intval', 0);

        $result = table('GoodsCar')->where('id', $id)->save($data);
        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->ajaxReturn(array('msg' => '操作成功'));
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (!$id) {
            denha\Log::error('参数错误');
        }

        $data = table('GoodsCar')->where('id', $id)->find();

        $data['banner']    = imgUrl($data['banner'], 'car');
        $data['ablum']     = imgUrl($data['ablum'], 'car');
        $data['guarantee'] = $data['guarantee'] ? explode(',', $data['guarantee']) : array();

        //获取图片介绍
        $data['content'] = '';
        $ablum           = table('GoodsAblum')->where(array('goods_id' => $data['id']))->find('array');
        foreach ($ablum as $key => $value) {
            $data['content'] .= '<p><img src="' . imgUrl($value['path'], 'car') . '" style="width:150px;text-algin:center" /></p>';
            if ($value['description']) {
                $data['content'] .= '<p>' . $value['description'] . '</p>';
            }

        }

        $other = array(
            'cityCopy' => dao('Category')->getList(34),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);

        $this->show();
    }

}
