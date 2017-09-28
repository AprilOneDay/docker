<?php
/**
 * 会员模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class User extends \app\app\controller\Init
{
/**
 * 会员今日可用积分行为
 * @date   2017-09-18T14:51:07+0800
 * @author ChenMingjiang
 * @return [type]                   [description]
 */
    public function todayAvailableBehavior()
    {
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }

        $type = post('type', 'intval', 0);
        if (!in_array($type, array(1, 2))) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if ($type == 1) {
            $result = dao('User')->todayAvailableBehavior($this->uid, '每日签到');
        } elseif ($type == 2) {
            $result = dao('User')->todayAvailableBehavior($this->uid, '每日分享');
        }

        $this->appReturn($result);

    }

    /**
     * 会员签到
     * @date   2017-09-18T15:01:34+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function todaySign()
    {
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }

        $isResult = dao('User')->todayAvailableBehavior($this->uid, '每日签到');

        if ($isResult['status'] && !$isResult['data']['bool']) {
            $this->appReturn(array('status' => false, 'msg' => '已签到'));
        }

        $result = dao('Integral')->add($this->uid, 2);

        if ($result['status']) {
            $this->appReturn(array('msg' => '签到成功', 'data' => $result['data']));
        } else {
            $this->appReturn($result);
        }

    }

    /**
     * 我的积分
     * @date   2017-09-18T15:35:31+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function myIntegral()
    {
        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录'));
        }

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $list = table('IntegralLog')->where($map)->order('created desc')->limit($offer, $pageSize)->find('array');

        foreach ($list as $key => $value) {
            $value['value'] < 0 ?: $list[$key]['value'] = '+' . $value['value'];
        }

        $data['integral'] = dao('User')->getIntegral($this->uid);
        $data['list']     = $list;

        $this->appReturn(array('status' => true, 'msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 增加收藏
     * @date   2017-09-19T14:39:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function collectionAdd()
    {

        $value = post('value', 'intval', 0);
        $type  = post('type', 'intval', 0);

        $result = dao('Collection')->add($this->uid, $type, $value);

        $this->appReturn($result);
    }

}
