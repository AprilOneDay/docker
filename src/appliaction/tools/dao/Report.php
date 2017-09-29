<?php
namespace app\tools\dao;

class Report
{
    public function add($uid = 0, $type = 1, $goodsId = 0)
    {
        if (!$type || !$goodsId) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['type']     = $type;
        $map['uid']      = $uid;
        $map['goods_id'] = $goodsId;
        $map['ip']       = getIP();

        $id = table('ReportLog')->where($map)->field('id')->find();
        if ($id) {
            return array('status' => false, 'msg' => '感谢您的举报！！！');
        }

        var_dump($id);
        echo table('ReportLog')->getSql();die;

        $data['uid']      = $uid;
        $data['type']     = $type;
        $data['goods_id'] = $goodsId;
        $data['ip']       = getIP();
        $data['created']  = TIME;

        $result = table('ReportLog')->add($data);
        if (!$result) {
            return array('status' => false, 'msg' => '举报失败,请联系管理员');
        }

        return array('status' => true, 'msg' => '举报成功');
    }
}
