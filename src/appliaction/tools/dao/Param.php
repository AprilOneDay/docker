<?php
/**
 * 百度抓取
 */
namespace app\tools\dao;

class Param
{
    /**
     * 查看名称
     * @date   2017-10-12T17:23:48+0800
     * @author ChenMingjiang
     * @param  integer                  $id [description]
     * @return [type]                       [description]
     */
    public function getName($id = 0)
    {
        $name = (string) table('ConsoleParameter')->where('id', $id)->field('name')->find('one');
        return $name;
    }

    /**
     * 查看值
     * @date   2017-10-12T17:23:56+0800
     * @author ChenMingjiang
     * @param  integer                  $id [description]
     * @return [type]                       [description]
     */
    public function getValue($id = 0)
    {
        $value = (string) table('ConsoleParameter')->where('id', $id)->field('value')->find('one');
        return $value;
    }
}
