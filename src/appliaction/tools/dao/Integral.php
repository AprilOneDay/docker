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
     * 增加积分明细
     * @date   2017-09-18T13:42:46+0800
     * @author ChenMingjiang
     * @param  [type]                   $uid       [增加积分明细用户]
     * @param  [type]                   $id        [积分规则]
     * @param  [type]                   $content   [增加积分文案替换]
     * @param  [type]                   $value     [直接填写积分]
     * @param  [type]                   $isUser    [是否执行修改会员积分]
     */
    public function add($uid = 0, $id = 0, $content = '', $value = '', $isUser = true)
    {
        if (!$uid) {
            return array('status' => false, 'msg' => '参数错误');
        }

        $data['uid']     = $uid;
        $data['created'] = TIME;
        if ($id) {
            $integralRul = table('IntegralRul')->where(array('id' => $id))->find();
            if (!$integralRul['status']) {
                return array('status' => false, 'msg' => '操作失败,该功能已关闭');
            }
            $data['value']   = $integralRul['value'];
            $data['content'] = $integralRul['content'];
        }
        $content == '' ?: $data['content'] = $content;
        $value == '' ?: $data['value']     = $value;

        $reslut = table('IntegralLog')->add($data);
        if ($reslut && $isUser) {
            //更变用户积分
            table('User')->where(array('id' => $uid))->save(array('integral' => array('add', $data['value'])));
            return array('status' => true, 'msg' => '操作成功', 'data' => array('value' => $data['value']));
        }

        return array('status' => true, 'msg' => '执行失败');
    }

    /**
     * 检测规则
     * @date   2017-09-18T13:49:40+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function checkRule($uid, $id)
    {
        switch ($id) {
            case '1':
                return true;
                break;
            case '2':

            default:
                # code...
                break;
        }
    }
}
