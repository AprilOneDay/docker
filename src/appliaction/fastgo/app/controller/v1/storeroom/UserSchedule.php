<?php
/**
 * 排班计划
 */
namespace app\fastgo\app\controller\v1\storeroom;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class UserSchedule extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('3,4');
    }

    public function index()
    {

        $pageWeek = get('pageWeek', 'intval', 0);

        $week = date("w");

        $weekCopy = array(
            '0' => '星期天',
            '1' => '星期一',
            '2' => '星期二',
            '3' => '星期三',
            '4' => '星期四',
            '5' => '星期五',
            '6' => '星期六',
        );

        //本周
        $startWeek = strtotime(date('Y-m-d', strtotime("this week Monday", TIME)));
        $endWeek   = strtotime(date('Y-m-d', strtotime("this week Sunday", TIME))) + 24 * 3600 - 1;

        //上一周 下一周
        if ($pageWeek > 0) {
            $startWeek = strtotime(date('Y-m-d', strtotime("-$pageWeek week", $startWeek)));
            $endWeek   = strtotime(date('Y-m-d', strtotime("-$pageWeek week", $startWeek))) + 24 * 3600 - 1;
        } elseif ($pageWeek < 0) {
            $startWeek = strtotime(date('Y-m-d', strtotime("+$pageWeek week", $startWeek)));
            $endWeek   = strtotime(date('Y-m-d', strtotime("+$pageWeek week", $startWeek))) + 24 * 3600 - 1;
        }

        //获取周排班计划
        $userSchedule = array(array(), array(), array(), array(), array(), array(), array());
        if ($this->group == 3 || $this->group == 4) {
            $map            = array();
            $map['uid']     = $this->uid;
            $map['created'] = array('between', $startWeek, $endWeek);

            $tmpUserScheduleLog = table('UserScheduleLog')->where($map)->find('array');

            foreach ($tmpUserScheduleLog as $key => $value) {
                $value['real_name'] = dao('User')->getInfo($value['uid'], 'real_name');
                $value['week_copy'] = $weekCopy[$value['week']];

                $userScheduleLog[$value['week']] = $value;
            }
        }

        //debug
        //print_r($userScheduleLog);die;
        //print_r($userSchedule);
        //print_r($userScheduleLog);

        //合并排班计划
        foreach ($userSchedule as $key => $value) {
            if (isset($userScheduleLog[$key])) {
                $userSchedule[$key] = $userScheduleLog[$key];
            }
        }

        //debug
        //print_r($userSchedule);die;

        $data['list']      = $userSchedule ? $userSchedule : array();
        $data['time_copy'] = array(
            'start_time' => date('Y-m-d', $startWeek),
            'end_time'   => date('Y-m-d', $endWeek),
        );

        $this->appReturn(array('data' => $data));
    }

}
