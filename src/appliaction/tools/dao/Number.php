<?php
/**
 * 数字处理模块
 */
namespace app\tools\dao;

class Number
{
    /**
     * 转换价格
     * @date   2017-09-27T15:14:00+0800
     * @author ChenMingjiang
     * @param  integer                  $price [description]
     * @param  string                   $lg    [description]
     * @return [type]                          [description]
     */
    public function price($price = 0, $lg = 'zh')
    {
        $price = sprintf('%.2f', $price);
        if ($price > 10000) {
            $price = sprintf('%.2f', ($price / 10000)) . '万';
        }

        return $price;
    }

    /**
     * 数字转换为万计数 如果超过万
     * @date   2017-11-17T11:34:55+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function million($value)
    {
        if ($value > 10000) {
            $value = sprintf('%.2f', ($value / 10000)) . '万';
        }

        return (float) $value;
    }
}
