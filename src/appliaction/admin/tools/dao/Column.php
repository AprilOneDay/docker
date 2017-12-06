<?php
namespace app\admin\tools\dao;

class Column
{

    public function columnList($columnId = 0, $webType = 1)
    {
        $map['web_type'] = $webType;
        if ($columnId) {
            $map            = array();
            $map['id']      = $columnId;
            $parentid       = table('Column')->where($map)->field('parentid')->find('one');
            $map['_string'] = "parentid =$parentid or parentid=$columnId or id=$columnId or id=$parentid";
        } else {
            $map = array();
        }

        $tree = new \app\admin\tools\util\MenuTree();
        $tree->setConfig('id', 'parentid', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $result = table('Column')->where($map)->order('sort asc,id asc')->find('array');

        $treeList = $tree->getLevelTreeArray($result);

        //print_r($result);
        foreach ($treeList as $key => $value) {
            $list[$value['id']] = isset($value['delimiter']) ? $value['delimiter'] . $value['name'] : $value['name'];
        }

        return $list;
    }

}
