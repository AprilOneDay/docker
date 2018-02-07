<?php
/**
 * 家庭模块
 */
namespace app\flower\app\controller\v1\user;

use app\app\controller;
use app\flower\app\controller\v1\WeixinSmallInit;

class BillFamily extends WeixinSmallInit
{

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1');
    }

    /** 家庭列表 */
    public function lists()
    {
        $toToken = get('token', 'text', '');

        $map['family_sn'] = $this->familySn;

        $list = table('BillFamily')->where($map)->order('is_master desc')->find('array');

        //print_r($list);die;

        $data['is_master'] = 0;
        foreach ($list as $key => $value) {
            $user            = table('UserThirdParty')->where('id', $value['uid'])->field('avatar,nickname')->find();
            $user['is_back'] = $value['uid'] == $this->uid ? 1 : 0;

            $list[$key]            = $user;
            $list[$key]['is_this'] = 0;
            $list[$key]['id']      = $value['id'];

            //获取是否属于家庭主人
            if ($value['uid'] == $this->uid) {

                $data['is_master']     = $value['is_master'] == 1 ? 1 : 0;
                $nickname              = $user['nickname'];
                $list[$key]['is_this'] = 1;

            }

        }

        $data['list']  = $list ? $list : array();
        $data['share'] = array(
            'title'    => $nickname . '邀请您进入他的小家庭',
            'path'     => '/pages/index/index?inviteSn=' . auth($this->familySn) . '&toToken=' . $nickname,
            'imageUrl' => URL . '/uploadfile/meme/' . rand(1, 10) . '.jpg',
        );

        $this->appReturn(array('data' => $data));
    }

    /** 二维码地址 */
    public function invite()
    {
        $familySn = auth($this->familySn);

        $text = URL . '/v1/user/BillFamily/add?invite_sn=' . $familySn;

        $data['url'] = 'http://qr.liantu.com/api.php?&bg=ffffff&fg=000000&text=' . $text;

        $this->appReturn(array('data' => $data));
    }

    /** 加入家庭 */
    public function add()
    {

        $familySn = get('invite_sn', 'text', '');
        $familySn = auth($familySn, 'DECODE');

        if (!$familySn || strlen($familySn) !== 18) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if ($familySn == $this->familySn) {
            $this->appReturn(array('status' => false, 'msg' => '自己抱自己大腿就不要了吧'));
        }

        //更新自己的家庭编码
        $data              = array();
        $data['family_sn'] = $familySn;
        $data['is_master'] = 0; //取消主人身份
        $data['created']   = TIME;

        table('BillLog')->startTrans();
        $result = table('BillFamily')->where('uid', $this->uid)->save($data);
        if (!$result) {
            table('BillLog')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '加入家庭失败'));
        }

        //将自己的账单更新进入新的家庭中

        $data              = array();
        $data['family_sn'] = $familySn;

        $result = table('BillLog')->where('uid', $this->uid)->save($data);
        if (!$result) {
            table('BillLog')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '账单更新失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 剔除成员 */
    public function remove()
    {
        $id = get('id', 'intval', 0);

        $map              = array();
        $map['uid']       = $this->uid;
        $map['is_master'] = 1;

        $isMaster = table('BillFamily')->where($map)->find();
        if (!$isMaster) {
            $this->appReturn(array('status' => false, 'msg' => '非法操作'));
        }

        $map       = array();
        $map['id'] = $id;

        $family = table('BillFamily')->where($map)->find();
        if (!$family) {
            $this->appReturn(array('status' => false, 'msg' => '信息异常'));
        }

        //创建自己新的家庭
        $familySn = dao('Orders')->createOrderSn();

        $data              = array();
        $data['family_sn'] = $familySn;
        $data['is_master'] = 1; //主人身份
        $data['created']   = TIME;

        table('BillFamily')->startTrans();
        $result = table('BillFamily')->where('uid', $family['uid'])->save($data);
        if (!$result) {
            table('BillFamily')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '退出家庭失败'));
        }

        //将自己的账单更新进入新的家庭中
        $map        = array();
        $map['uid'] = $family['uid'];

        $data              = array();
        $data['family_sn'] = $familySn;

        $result = table('BillLog')->where('uid', $family['uid'])->save($data);
        if (!$result) {
            table('BillFamily')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '账单更新失败'));
        }

        table('BillFamily')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 退出家庭 */
    public function goOut()
    {
        $id = get('id', 'intval', 0);

        if (!$id) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $map        = array();
        $map['uid'] = $this->uid;
        $map['id']  = $id;

        $family = table('BillFamily')->where($map)->find();
        if (!$family) {
            $this->appReturn(array('status' => false, 'msg' => '操作异常'));
        }

        //创建自己新的家庭
        $familySn = dao('Orders')->createOrderSn();

        $data              = array();
        $data['family_sn'] = $familySn;
        $data['is_master'] = 1; //主人身份
        $data['created']   = TIME;

        table('BillFamily')->startTrans();
        $result = table('BillFamily')->where('uid', $this->uid)->save($data);
        if (!$result) {
            table('BillFamily')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '退出家庭失败'));
        }

        //将自己的账单更新进入新的家庭中

        $data              = array();
        $data['family_sn'] = $familySn;

        $result = table('BillLog')->where('uid', $this->uid)->save($data);
        if (!$result) {
            table('BillFamily')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '账单更新失败'));
        }

        //如果是管理员退出 寻找新的管理员
        if ($family['is_master'] == 1) {
            $map              = array();
            $map['family_sn'] = $family['family_sn'];
            $map['is_master'] = 0;

            $id = table('BillFamily')->where($map)->order('created desc')->field('id')->find('one');

            $result = table('BillFamily')->where('id', $id)->save('is_master', 1);
            if (!$result) {
                table('BillFamily')->rollback();
                $this->appReturn(array('status' => false, 'msg' => '创建新族长失败'));
            }
        }

        table('BillFamily')->commit();
        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }
}
