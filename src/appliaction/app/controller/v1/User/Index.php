<?php
/**
 * 会员模块
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Index extends \app\app\controller\Init
{
    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    public function index()
    {
        $user = table('User')->where('id', $this->uid)->field('avatar,nickname,mobile')->find();

        $user['avatar'] = $this->appImg($user['avatar'], 'avatar');

        $this->appReturn(array('data' => $user));

    }

    /**
     * 我的收藏
     */
    public function collection()
    {
        $map['type']       = 1;
        $map['uid']        = $this->uid;
        $map['del_status'] = 0;

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $idArray = table('Collection')->where($map)->field('value')->find('one', true);

        $mapCar['status']  = 1;
        $mapCar['is_show'] = 1;
        $mapCar['id']      = array('in', implode(',', $idArray));
        $list              = table('GoodsCar')->where($mapCar)->field('title,type,id,thumb,price,mileage,produce_time,is_lease')->limit($offer, $pageSize)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $list[$key]['title'] = "【转lease】" . $value['title'];
            }

            $list[$key]['price']   = dao('Number')->price($value['price']);
            $list[$key]['mileage'] = $value['mileage'] . '万公里';
            $list[$key]['thumb']   = $this->appImg($value['thumb'], 'car');
        }

        $data = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     *  我的足迹
     */
    public function footprints()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $footprints = table('Footprints')->tableName();
        $goodsCar   = table('GoodsCar')->tableName();

        $map[$footprints . '.uid']        = $this->uid;
        $map[$footprints . '.type']       = 1;
        $map[$footprints . '.del_status'] = 0;

        $map[$goodsCar . '.status']  = 1;
        $map[$goodsCar . '.is_show'] = 1;

        $field = "$goodsCar.title,$goodsCar.type,$goodsCar.id,$goodsCar.thumb,$goodsCar.price,$goodsCar.mileage,$goodsCar.produce_time,$goodsCar.is_lease,$goodsCar.guarantee,$footprints.created";
        $list  = table('GoodsCar')->join($footprints, "$goodsCar.id = $footprints.value")->where($map)->limit($offer, $pageSize)->field($field)->order("$footprints.created desc")->find('array');
        foreach ($list as $key => $value) {
            $time = date('Y/m/d', $value['created']);
            if ($value['is_lease'] || stripos($value['guarantee'], 3) !== false) {
                $value['title'] = "【转lease】" . $value['title'];
            }

            $value['price']   = dao('Number')->price($value['price']);
            $value['mileage'] = $value['mileage'] . '万公里';
            $value['thumb']   = $this->appImg($value['thumb'], 'car');

            $listTmp[$time][] = $value;
        }

        foreach ($listTmp as $key => $value) {
            $data[$key]['time'] = $key;
            $data[$key]['list'] = $value;
        }

        $data = $data ? array_values($data) : array();
        $this->appReturn(array('data' => $data));
    }

    /**
     * 编辑个人信息
     * @date   2017-09-25T10:47:27+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {
        if (IS_POST) {

            $data['mail']       = post('mail', 'text', '');
            $data['nickname']   = post('nickname', 'text', '');
            $data['is_message'] = post('is_message', 'intval', 0);

            $files['avatar'] = files('avatar');

            !$files['avatar'] ?: $data['avatar'] = $this->appUpload($files['avatar'], '', 'avatar');

            if (!$data['nickname']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入昵称'));
            }

            if (!$data['mail']) {
                $this->appReturn(array('status' => false, 'msg' => '请输入邮箱地址'));
            }

            $reslut = table('User')->where(array('id' => $this->uid))->save($data);

            if ($reslut) {
                $this->appReturn(array('msg' => '保存成功'));
            }

            $this->appReturn(array('status' => false, 'msg' => '执行失败'));
        } else {
            $data           = table('User')->where(array('id' => $this->uid))->field('id,nickname,mail,avatar,mobile,is_message,type')->find();
            $data['uid']    = $data['id'];
            $data['avatar'] = $this->appImg($data['avatar'], 'avatar');
            $data['mobile'] = substr_replace($data['mobile'], '*****', 4, 5);
            $this->appReturn(array('data' => $data));
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
        $uid    = $this->uid;
        $mobile = post('mobile', 'text', '');

        $password  = post('password', 'text', '');
        $password2 = post('password2', 'text', '');

        $code = post('code', 'intval', 0);

        $map['id']     = $this->uid;
        $map['mobile'] = $mobile;
        $map['type']   = 1;

        if (!$mobile) {
            $this->appReturn(array('status' => false, 'msg' => '请输入手机号'));
        }

        $is = table('User')->where($map)->field('id')->find('one');
        if (!$is) {
            $this->appReturn(array('status' => false, 'msg' => '非绑定手机号'));
        }

        $reslut = dao('User')->findPassword($this->uid, $password, $password2, $code);
        $this->appReturn($reslut);
    }
}
