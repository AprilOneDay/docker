<?php
/**
 * 商家会员相关
 */
namespace app\flower\app\controller\v1\common;

use app\app\controller;
use app\flower\app\controller\v1\WeixinSmallInit;

class User extends WeixinSmallInit
{
    /** 获取账户信息 */
    public function userInfo()
    {

        $code   = get('code', 'text', '');
        $result = dao('WeixinSmallOauth')->getUserInfo($code);

        if (!$result['status']) {
            $this->appReturn($result);
        }

        $user = table('UserThirdParty')->where('weixin_id', $result['data']['openid'])->find();
        if (!$user) {
            $dataUser['weixin_id'] = $result['data']['openid'];
            $dataUser['created']   = TIME;
            $uid                   = table('userThirdParty')->add($dataUser);

            //创建家庭信息
            $dataUser              = array();
            $dataUser['family_sn'] = dao('Orders')->createOrderSn();
            $dataUser['uid']       = $uid;
            $dataUser['created']   = TIME;

            $result = table('BillFamily')->add($dataUser);

            $data['is_update'] = 0;
        } else {
            $uid               = $user['id'];
            $data['is_update'] = $user['is_update'];
        }

        $data['token'] = auth($uid);

        $this->appReturn(array('status' => true, 'data' => $data));
    }

    /** 更新用户信息 */
    public function update($uid)
    {

        $this->checkIndividual();

        $nickname = get('nickname', 'text', '');
        $avatar   = get('avatar', 'text', '');

        if (!$nickname) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $user = table('UserThirdParty')->where('id', $this->uid)->find();
        if (!$user) {
            $this->appReturn(array('status' => false, 'msg' => '用户不存在'));
        }

        $data['is_update'] = 1;
        $data['nickname']  = $nickname;
        $data['avatar']    = $avatar;

        $result = table('UserThirdParty')->where('id', $this->uid)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '保存失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '保存成功'));
    }

}
