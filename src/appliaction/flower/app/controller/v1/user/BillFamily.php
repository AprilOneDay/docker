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

        $list = table('BillFamily')->where($map)->find('array');

        $data['is_master'] = 0;
        foreach ($list as $key => $value) {
            $user          = table('UserThirdParty')->where('id', $value['uid'])->field('avatar,nickname')->find();
            $tmpList[$key] = $user;
            //获取是否属于家庭主人
            if ($value['uid'] == $this->uid) {
                $data['is_master'] = $value['is_master'] == 1 ? 1 : 0;
                $nickname          = $user['nickname'];
            }
        }

        $data['list']  = $tmpList ? $tmpList : array();
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
        $map        = array();
        $map['uid'] = $this->uid;

        $data['family_sn'] = $familySn;
        $data['is_master'] = 0; //取消主人身份

        $result = table('BillFamily')->where('uid', $this->uid)->save($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '加入家庭失败'));
        }

        //将自己的账单更新进入新的家庭中
        $map        = array();
        $map['uid'] = $this->uid;

        $data['family_sn'] = $familySn;

        $result = table('BillLog')->where('uid', $uid)->save($data);

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }
}
