<?php
/**
 * 物流模块模块
 */
namespace app\fastgo\tools\dao;

class Depot
{
    /** 获取仓库名称 */
    public function getName($bname_2, $lg)
    {

        $map['bname_2'] = array('in', $bname_2);

        $name = table('Category')->where($map)->field('name')->find('one', true);

        if ($lg && $lg != 'zh') {
            $name = table('Category')->where($map)->field('name_' . $lg)->find('one', true);
        } else {
            $name = table('Category')->where($map)->field('name')->find('one', true);
        }

        if (!$name) {
            return null;
        }

        if (count($name) == 1) {
            return (string) $name[0];
        }

        return (array) $name;
    }
}
