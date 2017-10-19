<?php
namespace app\admin\tools\dao;

class Column
{

    public function columnList($columnId = 0)
    {
        if ($columnId) {
            $map            = array();
            $parentid       = table('Column')->where('id', $columnId)->field('parentid')->find('one');
            $map['_string'] = "parentid =$parentid or parentid=$columnId or id=$columnId or id=$parentid";
        } else {
            $map = array();
        }

        $tree = new \app\console\tools\util\MenuTree();
        $tree->setConfig('id', 'parentid', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $result = table('Column')->where($map)->order('sort asc,id asc')->find('array');

        $treeList = $tree->getLevelTreeArray($result);

        //print_r($result);
        foreach ($treeList as $key => $value) {
            $list[$value['id']] = $value['delimiter'] . $value['name'];
        }

        return $list;
    }

}
