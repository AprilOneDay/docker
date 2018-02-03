<?php
/**
 * 商家会员相关
 */
namespace app\fastgo\app\controller\v1\shop;

use app\fastgo\app\controller\v1\Init;

class User extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual(2);
    }

    /**
     * 编辑店铺信息
     */
    public function edit()
    {

        $data = table('UserShop')->where(array('uid' => $this->uid))->find();

        $data['avatar'] = $this->appImg($data['avatar'], 'avatar');
        $data['ablum']  = $this->appImgArray($data['ablum'], 'shop');
        $data['qr']     = $this->appImg($data['qr'], 'shop');

        $warehouse = table('Category')->where('id', $data['city_id'])->find();

        $data['city_copy']    = dao('Category')->getName($data['city_id']);
        $data['country_id']   = $warehouse['parentid'];
        $data['country_copy'] = dao('Category')->getName($data['country_id']);
        $data['ablum_num']    = count($data['ablum']);

        $this->appReturn(array('data' => $data));

    }

    /** 提交 */
    public function editPost()
    {

        $data['woker_time'] = post('woker_time', 'text', '');
        $data['address']    = post('address', 'text', '');
        $data['real_name']  = post('real_name', 'text', '');
        $data['mobile']     = post('mobile', 'text', '');

        $data['lng'] = post('lng', 'float', 0);
        $data['lat'] = post('lat', 'float', 0);

        $data['ablum'] = post('ablum', 'json', '');

        $files['qr']     = files('qr');
        $files['avatar'] = files('avatar');
        $files['ablum']  = files('ablum_files');

        $data['ablum'] = $this->appUpload($files['ablum'], $data['ablum'], 'shop');

        !$files['qr'] ?: $data['qr']         = $this->appUpload($files['qr'], '', 'shop');
        !$files['avatar'] ?: $data['avatar'] = $this->appUpload($files['avatar'], '', 'avatar');

        if (!$data['woker_time']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入营业时间'));
        }

        if (!$data['address']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入店铺地址'));
        }
        table('UserShop')->startTrans();
        $result = table('UserShop')->where(array('uid' => $this->uid))->save($data);

        if (!$result) {
            table('UserShop')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));

        }

        table('UserShop')->commit();
        $this->appReturn(array('msg' => '保存成功'));
    }

    /**
     * 店铺开启/关闭
     */
    public function changeStatus()
    {
        $this->checkShop(); //必须商户会员登录
        if (IS_POST) {
            $status = post('status', 'intval', 3);
            if (!in_array($status, array(0, 1))) {
                $this->appReturn(array('status' => false, 'msg' => '参数错误'));
            }

            $result = table('UserShop')->where(array('uid' => $this->uid))->save('status', $status);
            //开启店铺商品
            if ($status == 1) {
                table('GoodsCar')->where(array('uid' => $this->uid))->save('is_show', 1);
                table('GoodsService')->where(array('uid' => $this->uid))->save('is_show', 1);
            }
            //屏蔽店铺商品
            else {
                table('GoodsCar')->where(array('uid' => $this->uid))->save('is_show', 0);
                table('GoodsService')->where(array('uid' => $this->uid))->save('is_show', 0);
            }

            if ($result) {
                $this->appReturn(array('msg' => '操作成功'));
            }
            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        }
    }

    /**
     * 修改用户密码
     * @date   2017-09-25T11:11:42+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function findPassword()
    {
        parent::__construct();

        $mobile = post('mobile', 'text', '');

        $password  = post('password', 'text', '');
        $password2 = post('password2', 'text', '');

        $code = post('code', 'intval', 0);

        if (!$mobile) {
            $this->appReturn(array('status' => false, 'msg' => '请输入手机号'));
        }

        if (!$code) {
            $this->appReturn(array('status' => false, 'msg' => '请输入验证码'));
        }

        $map['mobile'] = $mobile;
        $map['type']   = 2;

        $user = table('User')->where($map)->field('id')->find();

        if (!$user) {
            $this->appReturn(array('status' => false, 'msg' => '尚未注册'));
        }

        $reslut = dao('User')->findPassword($user['id'], $password, $password2, $code, $mobile);
        $this->appReturn($reslut);
    }

}
