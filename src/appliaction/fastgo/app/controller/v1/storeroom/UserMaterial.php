<?php
/**
 * 会员任务分配模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserMaterial extends Init
{

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');
    }

    /** 获取分配任务列表 */
    public function lists()
    {

        $cid        = dao('User')->getInfo($this->uid, 'cid');
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        //获取所属处理中心的店铺
        $map         = array();
        $map['type'] = 2;
        $map['cid']  = $cid;

        $userArray = table('User')->where($map)->field('uid')->find('one', true);

        //debug
        //var_dump($userArray);die;

        //获取当日需要分配的任务
        $map               = array();
        $map['uid']        = array('in', $userArray);
        $map['apply_time'] = array('between', $beginToday, $endToday);

        //print_r($map);die;

        $list = table('Material')->where($map)->find('array');

        foreach ($list as $key => $value) {
            $shop = table('UserShop')->where('uid', $value['uid'])->field('name')->find();

            $list[$key]['shop'] = $shop;
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /** 执行分配任务 */
    public function update()
    {
        $uid            = post('uid', 'intval', 0);
        $materialSnText = post('material_sn', 'text', '');
        $license        = post('license', 'text', '');

        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday   = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;

        $materialSnArray = strpos($materialSnText, ',') !== false ? explode(',', $materialSnText) : (array) $materialSnText;

        if (!$uid || !$materialSnText) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!$license) {
            $this->appReturn(array('status' => false, 'msg' => '请输入车牌号'));
        }

        //批量预检测
        foreach ($materialSnArray as $key => $value) {
            $map               = array();
            $map['apply_time'] = array('between', $beginToday, $endToday);
            $map['status']     = 1;

            $isMaterial = table('Material')->where($map)->field('id')->find();
            if ($isMaterial) {
                $this->appReturn(array('status' => false, 'msg' => '存在不可操作任务'));
            }
        }

        //分配
        $map                = array();
        $map['material_sn'] = array('in', $materialSnText);

        $data                = array();
        $data['assign_uid']  = $uid;
        $data['console_uid'] = $this->uid;

        table('Material')->startTrans();
        $result = table('Material')->where($map)->save($data);
        if (!$result) {
            table('Material')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '执行失败了'));
        }

        //增加揽货员车牌号
        $map            = array();
        $map['uid']     = $uid;
        $map['created'] = array('between', $beginToday, $endToday);

        $scheduleLogId = table('UserScheduleLog')->where($map)->field('id')->find('one');
        if (!$scheduleLogId) {
            table('Material')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '请先执行打卡'));
        }

        $data                = array();
        $data['console_uid'] = $this->uid;
        $data['license']     = $license;

        $result = table('UserScheduleLog')->where('id', $scheduleLogId)->save($data);
        if (!$result) {
            table('Material')->rollback();
            $this->appReturn(array('status' => false, 'msg' => '车辆更新失败'));
        }

        table('Material')->commit();
        $this->appReturn(array('msg' => '操作成功'));
    }
}
