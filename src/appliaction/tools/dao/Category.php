<?php
/**
 * 分类模块管理
 */
namespace app\tools\dao;

class Category
{
    private static $category;

    /**
     * 获取分类数组
     * @date   2017-09-18T10:17:39+0800
     * @author ChenMingjiang
     * @param  integer                  $id [description]
     * @return [type]                       [description]
     */
    public function getList($id = 0, $lg = '')
    {

        if (!isset(self::$category[$id])) {
            $map['parentid'] = $id;
            $list            = table('Category')->where($map)->order('name asc,sort asc')->find('array');

            self::$category[$id] = null;

            foreach ($list as $key => $value) {
                if ($lg != 'zh' && $lg) {
                    self::$category[$id][$value['id']] = $value['name_' . $lg];
                } else {
                    self::$category[$id][$value['id']] = $value['name'];
                }
            }

        }

        return self::$category[$id];
    }

    /**
     * 获取name bname 其他参数
     * @date   2017-12-29T10:30:34+0800
     * @author ChenMingjiang
     * @param  [type]                   $field [description]
     * @param  string                   $lg    [description]
     * @return [type]                          [description]
     */
    public function getListAllInfo($id = 0, $fieldValue = '', $lg = '')
    {
        $map['parentid'] = $id;

        $field = 'id,thumb,name,bname,bname_2';
        if ($lg && $lg != 'zh') {
            $field .= ',name_' . $lg;
        }

        $field .= $fieldValue ? $fieldValue : '';
        $list = table('Category')->where($map)->field($field)->order('bname asc,sort asc')->find('array');

        foreach ($list as $key => $value) {
            if ($lg != 'zh' && $lg) {
                $list[$key]['value'] = $value['name_' . $lg];
            } else {
                $list[$key]['value'] = $value['name'];
            }
        }

        $list = $list ? $list : array();

        return (array) $list;
    }

    /**
     * 模糊搜索
     * @date   2017-12-29T11:38:21+0800
     * @author ChenMingjiang
     * @param  [type]                   $id      [description]
     * @param  string                   $keyword [description]
     * @param  string                   $lg      [description]
     * @return [type]                            [description]
     */
    public function searchListAllInfo($id = 0, $keyword = '', $lg = '')
    {
        $map['parentid'] = $id;

        $keyword = (string) $keyword;

        $field = 'id,name,bname,bname_2';
        if ($lg && $lg != 'zh') {
            $field .= ',name_' . $lg;
            if ($keyword) {
                $map['name_' . $lg] = array('instr', $keyword);
            }
        } else {
            $map['name'] = array('instr', $keyword);
        }

        $field .= $fieldValue ? $fieldValue : '';
        $list = table('Category')->where($map)->field($field)->order('bname asc,sort asc')->find('array');

        foreach ($list as $key => $value) {
            if ($lg != 'zh' && $lg) {
                $list[$key]['value'] = $value['name_' . $lg];
            } else {
                $list[$key]['value'] = $value['name'];
            }
        }

        $list = $list ? $list : array();

        return (array) $list;
    }

    /**
     * 通过id获取分类名称
     * @date   2017-09-18T10:17:21+0800
     * @author ChenMingjiang
     * @param  [type]                   $id [description]
     * @return [type]                       [description]
     */
    public function getBname($id)
    {

        $map['id'] = array('in', $id);

        $name = table('Category')->where($map)->field('bname')->find('one', true);

        if (!$name) {
            return null;
        }

        if (count($name) == 1) {
            return (string) $name[0];
        }

        return (array) $name;
    }

    /**
     * 通过id获取分类名称
     * @date   2017-09-18T10:17:21+0800
     * @author ChenMingjiang
     * @param  [type]                   $id [description]
     * @return [type]                       [description]
     */
    public function getName($id, $lg = '')
    {

        $map['id'] = array('in', $id);
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
