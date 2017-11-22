<?php
/**
 * 举报模块管理
 */
namespace app\tools\dao;

class Report
{
    /**
     * 增加举报
     * @date   2017-10-25T16:32:11+0800
     * @author ChenMingjiang
     * @param  integer                  $uid     [description]
     * @param  integer                  $type    [description]
     * @param  integer                  $goodsId [description]
     */
    public function add($uid = 0, $type = 0, $goodsId = 0)
    {
        if (!$type || !$goodsId) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $map['type']     = $type;
        $map['uid']      = $uid;
        $map['goods_id'] = $goodsId;
        $map['ip']       = getIP();

        $id = table('ReportLog')->where($map)->field('id')->find('one');
        if ($id) {
            return array('status' => false, 'msg' => '感谢您的举报！！！');
        }

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
