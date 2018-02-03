<?php
/**
 * 会员打卡模块
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserCheckin extends Init
{

    private $cid;

    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('4');

        $this->cid = dao('User')->getInfo($this->uid, 'cid');
        if (!$this->cid) {
            $this->appReturn(array('status' => false, 'msg' => '尚未关联网点仓库信息'));
        }
    }

    /**
     * 打卡列表
     * @date   2018-01-09T14:05:54+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {

        $week       = date("w");
        $todaystart = strtotime(date('Y-m-d' . '00:00:00', TIME)); //获取今天00:00
        $todayend   = strtotime(date('Y-m-d' . '00:00:00', TIME + 3600 * 24)); //获取今天24:00

        //获取仓库下的揽货员
        $map['type']   = array('in', '3,4');
        $map['cid']    = $this->cid;
        $map['status'] = 1;
        $userArray     = table('User')->where($map)->field('uid')->find('one', true);

        //deubug
        //print_r($userArray);die;

        //获取正在值班的揽货员
        $map           = array();
        $map['time']   = array('instr', $week);
        $map['status'] = 1;
        $map['uid']    = array('in', $userArray);

        $userArray = array();
        $userArray = table('UserSchedule')->where($map)->field('uid')->find('one', true);

        //deubug
        //print_r($userArray);die;

        //获取已存在的打卡记录
        $map            = array();
        $map['created'] = array('between', $todaystart, $todayend);
        $map['uid']     = array('in', $userArray);

        $tmpCheckinList = table('UserScheduleLog')->where($map)->find('array');
        foreach ($tmpCheckinList as $key => $value) {
            $checkinList[$value['uid']]['content'] = $value;
        }

        //补充尚未添加记录的人员
        foreach ($userArray as $key => $value) {
            $user = dao('User')->getInfo($value, 'uid,real_name,mobile');

            if (!isset($checkinList[$value])) {
                $checkinList[$value]['content'] = array();
            }

            $checkinList[$value]['user'] = $user;
        }

        $data['list'] = $checkinList ? array_values($checkinList) : array();

        $this->appReturn(array('data' => $data));
    }

    /** 执行打卡操作 */
    public function add()
    {
        $type = post('type', 'intval', 0);
        $uid  = post('uid', 'intval', 0);
        $time = post('time', 'intval', 0);

        if (!$uid) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!in_array($type, array(1, 2))) {
            $this->appReturn(array('status' => false, 'msg' => 'Type参数错误'));
        }

        $todaystart = strtotime(date('Y-m-d' . '00:00:00', TIME)); //获取今天00:00
        $todayend   = strtotime(date('Y-m-d' . '00:00:00', TIME + 3600 * 24)); //获取今天24:00

        $data['week']                    = date("w");
        $data['console_uid']             = $this->uid;
        $data['uid']                     = $uid;
        $time                            = $time ? $time : TIME;
        $type == 1 ? $data['start_time'] = $time : $data['end_time'] = $time;

        //获取已存在的打卡记录
        $map            = array();
        $map['created'] = array('between', $todaystart, $todayend);
        $map['uid']     = $uid;

        $chekinLog = table('UserScheduleLog')->where($map)->field('id,start_time,end_time')->find();

        if (!$chekinLog) {
            $data['created'] = TIME;

            $result = table('UserScheduleLog')->add($data);
        } else {
            //判断是否已经打卡
            if ($chekinLog['start_time'] && $type == 1) {
                $this->appReturn(array('status' => false, 'msg' => '请勿重复打卡'));
            }

            if ($chekinLog['end_time'] && $type == 2) {
                $this->appReturn(array('status' => false, 'msg' => '请勿重复打卡'));
            }

            $result = table('UserScheduleLog')->where('id', $chekinLog['id'])->save($data);
        }

        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '操作失败'));
        }

        $this->appReturn(array('msg' => '操作成功'));
    }
}
