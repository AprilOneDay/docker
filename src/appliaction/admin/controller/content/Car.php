<?php
/**
 * 用户积分规则管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;

class Car extends Init
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

        $data['uid']          = post('uid', 'text', '');
        $data['banner']       = post('banner', 'text', '');
        $data['is_urgency']   = post('is_urgency', 'intval', 0);
        $data['is_recommend'] = post('is_recommend', 'intval', 0);
        $data['status']       = post('status', 'intval', 0);
        $data['price']        = post('price', 'floatval', 0);
        $data['city']         = post('city', 'intval', 0);

        $data['brand']        = post('brand', 'intval', 0);
        $data['produce_time'] = post('produce_time', 'intval', 0);
        $data['is_lease']     = post('is_lease', 'intval', 0);

        $data['mileage'] = post('mileage', 'float', 0);
        $data['price']   = post('price', 'float', 0);

        $data['style']        = post('style', 'text', '');
        $data['model']        = post('model', 'text', '');
        $data['buy_time']     = post('buy_time', 'intval', 0);
        $data['city']         = post('city', 'text', '');
        $data['gearbox']      = post('gearbox', 'text', '');
        $data['gases']        = post('gases', 'text', '');
        $data['guarantee']    = post('guarantee', 'text', '');
        $data['displacement'] = post('displacement', 'text', '');
        $data['model_remark'] = post('model_remark', 'text', '');
        $data['vin']          = post('vin', 'text', '');
        $data['mobile']       = post('mobile', 'text', '');
        $data['weixin']       = post('weixin', 'text', '');
        $data['qq']           = post('qq', 'text', '');
        $data['address']      = post('address', 'text', '');
        $data['description']  = post('description', 'text', '');

        $data['content'] = post('content', 'text', 0);
        $data['content'] = strip_tags($data['content'], '<p><img>');

        if (!$data['brand']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择品牌'));
        }

        if (!$data['style']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入款号'));
        }

        if (!is_numeric($data['price'])) {
            $this->appReturn(array('status' => false, 'msg' => '价格请输入数字'));
        }

        /* if (!$data['mileage']) {
        $this->appReturn(array('status' => false, 'msg' => '请输入里程数'));
        }*/

        if (!$data['city']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入城市'));
        }

        if (!$data['price']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入报价'));
        }

        if (!$data['banner']) {
            $this->appReturn(array('status' => false, 'msg' => '请上传主图'));
        }

        if (count(explode(',', $data['banner'])) > 5) {
            $this->appReturn(array('status' => false, 'msg' => '最多可传5张主图'));
        }

        //拼接标题
        $data['title'] = dao('Category')->getName($data['brand'])
        . ($data['produce_time'] != '' ? ' ' . $data['produce_time'] : '')
        . ($data['style'] != '' ? ' ' . $data['style'] : '')
        . ($data['displacement'] != '' ? ' ' . $data['displacement'] : '')
        . ($data['model_remark'] != '' ? ' ' . $data['model_remark'] : '');

        if ($id) {
            $result = table('GoodsCar')->where('id', $id)->save($data);
        } else {
            $result = table('GoodsCar')->add($data);
        }

        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->ajaxReturn(array('msg' => '操作成功'));
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $data = table('GoodsCar')->where('id', $id)->find();

            $data['ablum']     = imgUrl($data['ablum'], 'car');
            $data['guarantee'] = $data['guarantee'] ? explode(',', $data['guarantee']) : array();

            if (!$data['content']) {
                //获取图片介绍
                $data['content'] = '';
                $ablum           = table('GoodsAblum')->where(array('goods_id' => $data['id']))->find('array');
                foreach ($ablum as $key => $value) {
                    $data['content'] .= '<p><img src="' . imgUrl($value['path'], 'car') . '" style="width:150px;text-algin:center" /></p>';
                    if ($value['description']) {
                        $data['content'] .= '<p>' . $value['description'] . '</p>';
                    }
                }
            }
        } else {
            $data = array('status' => 1, 'is_recommend' => 0, 'is_urgency' => 0, 'is_show' => 1);
        }

        $map           = array();
        $map['is_ide'] = 1;
        $map['status'] = 1;

        $shop = table('UserShop')->where($map)->field('name,uid')->find('one', 'uid');

        $other = array(
            'cityCopy'  => dao('Category')->getList(34),
            'shopName'  => $shop,
            'brandCopy' => dao('Category')->getList(1),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);

        $this->show();
    }

}
