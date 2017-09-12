<?php
namespace app\console\tools\util;

/**
 * 通用的树型类，可以生成任何树型结构
 */
class MenuTree
{
    private $pid       = 'pid'; //父级ID
    private $id        = 'id'; //自身ID
    private $child     = 'child';
    private $blank     = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    private $delimiter = '|—'; //空位符
    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($id, $pid, $blank, $delimiter)
    {
        if ($id) {
            $this->id = $id;
        }
        if ($pid) {
            $this->pid = $pid;
        }
        if ($blank) {
            $this->blank = $blank;
        }
        if ($delimiter) {
            $this->delimiter = $delimiter;
        }
    }
    /**
     * 获取树形多位数组
     * @param arr $items 待处理的数组
     */
    public function getTreeArray($items)
    {
        $tree   = array(); //格式化的树
        $tmpMap = array(); //临时扁平数据
        foreach ($items as $item) {
            $tmpMap[$item[$this->id]] = $item;
        }
        $level = 0;
        foreach ($items as $item) {
            if (isset($tmpMap[$item[$this->pid]])) {
                $tmpMap[$item[$this->pid]][$this->child][] = &$tmpMap[$item[$this->id]];
            } else {
                $tree[] = &$tmpMap[$item[$this->id]];
            }
        }
        return $tree;
    }
    /**
     * 获取树形一维数组
     * @param arr $items 待处理的数组
     */
    public function getLevelTreeArray($cate, $pid = 0, $level = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->pid] == $pid) {
                $v['level'] = $level + 1;
                if ($level) {
                    $v['delimiter'] = str_repeat($this->blank, $level) . $this->delimiter;
                }
                $arr[] = $v;
                $arr   = array_merge($arr, self::getLevelTreeArray($cate, $v[$this->id], $v['level']));
            }
        }
        return $arr;
    }
    /**
     * [getChilds 传递一个父级分类ID返回所有子级分类]
     * @date   2016-09-30T10:16:07+0800
     * @author Sunpeiliang
     * @param  [type]                   $cate [description]
     * @param  integer                  $pid  [description]
     * @return [type]                         [description]
     */
    public function getChilds($cate = null, $pid = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->pid] == $pid) {
                $arr[] = $v;
                $arr   = array_merge($arr, self::getChilds($cate, $v[$this->id]));
            }
        }
        return $arr;
    }
    //传递一个子分类ID返回他的所有父级分类
    public static function getParents($cate, $id)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$this->id] == $id) {
                $arr[] = $v;
                $arr   = array_merge(self::getParents($cate, $v[$this->pid]), $arr);
            }
        }
        return $arr;
    }
    /**
     * 传递一个子分类ID返回他的所有父级分类ID
     */
    public static function getParentsId($cate, $id, $id_name = 'id', $pid_name = 'parentid')
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v[$id_name] == $id) {
                $arr[] = $v[$pid_name];
                $arr   = array_merge(self::getParentsId($cate, $v[$pid_name]), $arr);
            }
        }
        return $arr;
    }
    /**
     * 传递一个父级分类ID返回他的所有子级分类ID
     */
    public function getChildsId($cate = null, $pid = 0)
    {
        $arr = array();
        if ($flag) {
            $arr[] = $pid;
        }
        foreach ($cate as $v) {
            if ($v[$this->pid] == $pid) {
                $arr[] = $v[$this->id];
                $arr   = array_merge($arr, self::getChildsId($cate, $v[$this->id]));
            }
        }
        return $arr;
    }
    //组成多维数组
    public static function toLayer($cate, $name = 'child', $pid = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['parentid'] == $pid) {
                $v[$name] = self::toLayer($cate, $name, $v['groupid']);
                $arr[]    = $v;
            }
        }
        return $arr;
    }
    //一维数组(同模型)(model = tablename相同)，删除其他模型的分类
    public static function getLevelOfModel($cate, $tablename = 'article')
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['tablename'] == $tablename) {
                $arr[] = $v;
            }
        }
        return $arr;
    }
    //一维数组(同模型)(modelid)，删除其他模型的分类
    public static function getLevelOfModelId($cate, $modelid = 0)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['modelid'] == $modelid) {
                $arr[] = $v;
            }
        }
        return $arr;
    }
    //判断分类是否有子分类,返回false,true
    public static function hasChild($cate, $id)
    {
        $arr = false;
        foreach ($cate as $v) {
            if ($v['pid'] == $id) {
                $arr = true;
                return $arr;
            }
        }
        return $arr;
    }
    //传递一个分类ID返回该分类相当信息
    public static function getSelf($cate, $id)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['id'] == $id) {
                $arr = $v;
                return $arr;
            }
        }
        return $arr;
    }
    //传递一个分类ID返回该分类相当信息
    public static function getSelfByEName($cate, $ename)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['ename'] == $ename) {
                $arr = $v;
                return $arr;
            }
        }
        return $arr;
    }
}
