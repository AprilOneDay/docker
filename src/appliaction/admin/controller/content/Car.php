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

        $param['field']        = get('field', 'text', 'title');
        $param['keyword']      = get('keyword', 'text', '');
        $param['type']         = get('type', 'intval', 0);
        $param['is_urgency']   = get('is_urgency', 'text', '');
        $param['is_recommend'] = get('is_recommend', 'text', '');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($param['pageNo'] - 1), 0) * $pageSize;

        if ($param['type']) {
            $map['type'] = $param['type'];
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
            $list[$key]['nickname'] = $value['type'] == 1 ? dao('User')->getNickname($value['uid']) : dao('User')->getShopName($value['uid']);
        }

        $other = array(
            'recommendCopy' => array('1' => '推荐', '0' => '未推荐'),
            'urgencyCopy'   => array('1' => '是', '0' => '否'),
            'typeCopy'      => array('1' => '个人', '2' => '商家'),
            'statusCopy'    => array('1' => '上架', '0' => '下架'),
        );

        $this->assign('list', $list);
        $this->assign('other', $other);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->show();
    }

}
