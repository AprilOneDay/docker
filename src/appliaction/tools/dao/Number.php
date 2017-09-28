<?php
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
        if ($price > 10000) {
            $price = sprintf('%.2f', ($price / 10000)) . '万';
        }

        return $price;
    }
}
