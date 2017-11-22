<?php
/**
 * 分类模块管理
 */
namespace app\tools\dao;

class Category
{
    /**
     * 获取分类数组
     * @date   2017-09-18T10:17:39+0800
     * @author ChenMingjiang
     * @param  integer                  $id [description]
     * @return [type]                       [description]
     */
    public function getList($id = 0, $lg)
    {

        static $_category = array();

        if (!isset($_category[$id])) {
            $map['parentid'] = $id;
            $list            = table('Category')->where($map)->field('id,name,bname')->order('name asc,sort asc')->find('array');

            $_category[$id] = null;

            foreach ($list as $key => $value) {
                if ($lg == 'en') {
                    $_category[$id][$value['id']] = $value['bname'];
                } else {
                    $_category[$id][$value['id']] = $value['name'];
                }
            }

        }

        return $_category[$id];
    }

    /**
     * 通过id获取分类名称
     * @date   2017-09-18T10:17:21+0800
     * @author ChenMingjiang
     * @param  [type]                   $id [description]
     * @return [type]                       [description]
     */
    public function getName($id)
    {

        $map['id'] = array('in', $id);
        $name      = table('Category')->where($map)->field('name')->find('one', true);
        if (count($name) == 1) {
            return (string) $name[0];
        }

        return (array) $name;
    }

}
