<?php
/**
 * 时间处理模块
 */
namespace app\tools\dao;

class Time
{
    /**
     * 判断活动时间
     * @date   2017-09-27T12:06:33+0800
     * @author ChenMingjiang
     * @param  [type]                   $startTime [开始时间戳]
     * @param  [type]                   $endTime   [结束时间戳]
     * @return [type]                              [description]
     */
    public function hdStatus($startTime = 0, $endTime = 0)
    {
        //交换时间
        if ($startTime > $endTime) {
            list($startTime, $endTime) = array($endTime, $startTime);
        }

        //未开始
        $status = 0;

        //进行中
        if ($startTime <= TIME) {
            $status = 1;
        }

        //已结束
        if ($endTime <= TIME) {
            $status = 2;
        }

        return $status;
    }

    /**
     * 计算相隔时间
     * @date   2018-01-08T11:02:46+0800
     * @author ChenMingjiang
     * @param  integer                  $startTime [开始时间]
     * @param  integer                  $endTime   [结束时间]
     * @param  integer                  $pointer   [显示样式]
     * @param  string                   $lg        [语音版本]
     * @return [type]                              [description]
     */
    public function diffDate($startTime = 0, $endTime = 0, $pointer = 0, $lg = 'zh')
    {

        //交换时间
        if ($startTime > $endTime) {
            list($startTime, $endTime) = array($endTime, $startTime);
        }

        list($Y1, $m1, $d1) = explode('-', date('Y-m-d', $startTime));
        list($Y2, $m2, $d2) = explode('-', date('Y-m-d', $endTime));

        $year  = $Y2 - $Y1;
        $month = $m2 - $m1;
        $day   = $d2 - $d1;

        $timeDiff = $endTime - $startTime;
        $days     = intval($timeDiff / 86400);
        $remain   = $timeDiff % 86400;

        $hours  = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins   = intval($remain / 60);
        $secs   = $remain % 60;

        if ($day < 0) {
            $day += (int) date('t', strtotime("-1 month $endTime"));
            $month--;
        }

        if ($month < 0) {
            $month += 12;
            $year--;
        }

        $data = array(
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
            'hours' => $hours,
            'mins'  => $mins,
            'secs'  => $secs,
        );

        $lgCopy = array(
            'zh' => array('year' => '年', 'month' => '月', 'day' => '日', 'mins' => '分', 'secs' => '秒'),
            'en' => array('year' => 'year', 'month' => 'month', 'day' => 'day', 'mins' => 'mins', 'secs' => 'secs'),
        );

        //最合适的值放第一个
        foreach ($data as $key => $value) {
            if ($value) {
                unset($data[$key]);
                $data       = array_reverse($data);
                $data[$key] = $value;
                $data       = array_reverse($data);

                break;
            }
        }

        //直接返回第一个数据
        if ($pointer == 1) {
            $pointerData = reset($data) . $lgCopy[$lg][key($data)];
            return $pointerData;
        }

        return $data;
    }

    /**
     * 万年历
     * @date   2017-11-10T16:26:13+0800
     * @author ChenMingjiang
     * @param  [type]                   $year  [description]
     * @param  [type]                   $month [description]
     * @return [type]                          [description]
     */
    public function calendar($year, $month)
    {

        //获取当前月有多少天
        $days = date('t', strtotime("{$year}-{$month}-1"));
        //获取上月天数
        //$perDays = date('t', strtotime("{$year}-{" . ($month - 1) . "}-1"));
        //当前1号是星期几
        $week = date('w', strtotime("{$year}-{$month}-1"));

        $tmpDay = 1;
        for ($i = 0; $i < 7; $i++) {
            if ($i == $week) {
                $weekArray[0][$i] = $tmpDay;
                $week++;
                $tmpDay++;
            } else {
                $weekArray[0][$i] = '';
            }

        }

        $tmpDay = 0;
        $row    = 1;
        $i      = count(array_filter($weekArray[0])) + 1;
        for (; $i <= $days; $i++) {
            $tmpDay = $tmpDay == 7 ? 0 : $tmpDay;

            $weekArray[$row][$tmpDay] = $i;

            $tmpDay++;
            if ($tmpDay == 7) {
                $row++;
            }
        }

        if (count(end($weekArray)) < 7) {
            for ($i = count(end($weekArray)); $i < 7; $i++) {
                $weekArray[count($weekArray) - 1][] = '';
            }
        }

        $data['month'] = $weekArray;
        $data['time']  = array('year' => $year, 'month' => $month);
        if ($month != 1 && $month != 12) {
            $data['up']   = array('year' => $year, 'month' => $month - 1);
            $data['down'] = array('year' => $year, 'month' => $month + 1);
        } else {
            if ($month == 12) {
                $data['up']   = array('year' => $year, 'month' => $month - 1);
                $data['down'] = array('year' => $year + 1, 'month' => $month + 1);
            } elseif ($month == 1) {
                $data['up']   = array('year' => $year - 1, 'month' => 12);
                $data['down'] = array('year' => $year + 1, 'month' => $month + 1);
            }
        }

        return $data;
    }
}
