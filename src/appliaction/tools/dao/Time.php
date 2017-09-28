<?php
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
        //未开始
        $status = 0;
        //进行中
        if ($startTime >= TIME) {
            $status = 1;
        }
        //已结束
        elseif ($endTime <= TIME) {
            $status = 2;
        }

        return $status;
    }
}
