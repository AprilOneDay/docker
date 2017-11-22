<?php
/**
 * 积分管理
 */
namespace app\tools\dao;

class Integral
{
    /**
     * 获取积分规则
     * @date   2017-09-18T13:48:49+0800
     * @author ChenMingjiang
     * @param  [type]                   $id [description]
     * @return [type]                       [description]
     */
    public function get($id)
    {
        $data = where('IntegralRul')->where(array('id' => $id))->find();
        return $data;
    }

    /**
     * 直接增加积分明细
     * @date   2017-10-25T14:23:33+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid     [用户uid]
     * @param  [type]                   $content [内容]
     * @param  [type]                   $value   [积分值]
     * @param  boolean                  $isUser  [是否执行修改会员积分]
     */
    public function addTemp($uid = 0, $content = '', $value = 0, $flag = '', $isUser = true)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        if (!$content) {
            return array('status' => false, 'msg' => '请输入积分内容');
        }

        $data['flag']    = $flag;
        $data['uid']     = $uid;
        $data['content'] = $content;
        $data['value']   = $value;
        $data['created'] = TIME;
        $reslut          = table('IntegralLog')->add($data);

        if ($reslut && $isUser) {
            $reslut = $this->changeUserIntegral($uid, $data['value']);
        }

        return array('status' => true, 'msg' => '操作完成', 'data' => array('value' => $value));
    }

    /**
     * 通过积分规则增加积分明细
     * @date   2017-09-18T13:42:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid       [增加积分明细用户]
     * @param  [type]                   $id        [积分规则]
     * @param  [type]                   $content   [增加积分文案替换]
     * @param  [type]                   $value     [直接填写积分]
     * @param  [type]                   $isUser    [是否执行修改会员积分]
     */
    public function add($uid = 0, $flag = '', $isUser = true, $limit = '')
    {
        if (!$uid || !$flag) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $integralRul = table('IntegralRul')->where('flag', $flag)->find();

        if (!$integralRul) {
            return array('status' => false, 'msg' => '规则信息不存在');
        }

        if (!$integralRul['status']) {
            return array('status' => false, 'msg' => '操作失败,该功能已关闭');
        }

        //判断限制内容是否存在于限制条件中
        if ($limit && $integralRul['limit'] && !in_array($limit, json_decode($integralRul['limit'], true))) {
            return array('status' => false, 'msg' => '操作失败，请在限制条件中增加规则');
        }

        $data['uid']     = $uid;
        $data['flag']    = $flag;
        $data['value']   = $integralRul['value'];
        $data['content'] = $integralRul['content'];
        $data['created'] = TIME;

        $reslut = table('IntegralLog')->add($data);
        if ($reslut && $isUser) {
            $reslutUser = $this->changeUserIntegral($uid, $data['value']);
            if (!$reslutUser) {
                return $reslutUser;
            }
        }

        return array('status' => true, 'msg' => '操作完成', 'data' => array('value' => $data['value']));
    }

    /**
     * 更改用户积分
     * @date   2017-09-18T13:49:40+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function changeUserIntegral($uid, $value)
    {
        //更变用户积分
        $result = table('User')->where(array('id' => $uid))->save(array('integral' => array('add', $value)));
        if (!$result) {
            return array('status' => false, 'msg' => '操作失败');
        }

        return array('status' => true, 'msg' => '操作成功');
    }
}
