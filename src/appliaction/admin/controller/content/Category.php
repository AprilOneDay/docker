<?php
/**
 * 分类管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;

class Category extends Init
{
    public function lists()
    {
        $parentid = get('id', 'intval', 0);

        $keyword = get('keyword', 'text', '');

        $map['parentid'] = $parentid;

        $param = array();

        if ($keyword) {
            $map['name']      = array('like', '%' . $keyword . '%');
            $param['keyword'] = $keyword;
        }

        $list = table('Category')->where($map)->order('sort asc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['is_show'] = $value['is_show'] ? '√' : '×';
        }

        $this->assign('parentid', $parentid);
        $this->assign('param', $param);
        $this->assign('list', $list);
        $this->show();
    }

    public function edit()
    {

        $id       = get('id', 'intval', 0);
        $parentid = get('parentid', 'intval', 0);
        $rs       = table('Category')->where(array('id' => $id))->find();

        if ($id == 0 && $parentid != 0) {
            $rs['parentid'] = $parentid;
            $rs['sort']     = 0;
        }

        $this->assign('treeList', $this->treeList());
        $this->assign('data', $rs);
        $this->show();
    }

    public function editPost()
    {
        $id      = post('id', 'intval', 0);
        $add     = post('add', 'intval', 1);
        $content = post('content', 'text', '');

        $data['parentid'] = post('parentid', 'intval', 0);
        $data['sort']     = post('sort', 'intval', 0);
        $data['is_show']  = post('is_show', 'intval', 1);

        $data['name']  = post('name', 'text', '');
        $data['bname'] = post('bname', 'text', '');
        $data['thumb'] = post('thumb', 'img', '');

        $data['bname'] ?: $data['bname'] = $data['name'];

        if ($add == 1 && !$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请输入分类名称'));
        }

        if ($id) {
            $result = table('Category')->where(array('id' => $id))->save($data);
            if ($result) {
                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            }
        } else {
            if ($add == 2 && $content) {
                $content = explode(PHP_EOL, $content);
                foreach ($content as $key => $value) {
                    if (stripos($value, '|') !== false) {
                        $value         = explode('|', $value);
                        $data['name']  = $value[0];
                        $data['bname'] = $value[1];
                    } else {
                        $data['name'] = $data['bname'] = $value;
                    }
                    $result = table('Category')->add($data);
                }

            } else {
                $result = table('Category')->add($data);
            }
            if ($result) {
                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
            }
        }

        $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
    }

    /** 删除分类 */
    public function del()
    {
        $chlid = post('id', 'intval');

        //删除所有下级分类 引用 &很重要 不然返回不了完整信息
        $delCategory = function ($chlid, &$idArray) use (&$delCategory) {
            $idArray[] = $chlid;
            //获取下级分类
            $chlidList = table('Category')->where('parentid', $chlid)->field('id')->find('one', true);

            //递归条件
            if ($chlidList) {
                foreach ($chlidList as $key => $value) {
                    $i++;
                    $delCategory((int) $value, $idArray);
                }
            }
            //返回需要删除的id
            return $idArray;
        }; //记得这里必须加``;``分号，不加分号php会报错，闭包函数

        //执行闭包函数
        $idArray = $delCategory($chlid);

        //执行删除操作
        $map['id'] = array('in', $idArray);
        $result    = table('Category')->where($map)->delete();
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));
    }

    /**
     * [获取树状菜单列表]
     * @date   2016-09-05T10:21:46+0800
     * @author Sunpeiliang
     */
    private function treeList($id)
    {
        //格式化菜单
        $map['parentid'] = $id;

        $result = table('Category')->field('id,parentid,name')->find('array');
        if ($result) {
            $tree = new \app\admin\tools\util\MenuTree();
            $tree->setConfig('id', 'parentid');
            $list = $tree->getLevelTreeArray($result);
            if (isset($list) && $list) {
                foreach ($list as $key => $value) {
                    $list[$key]['htmlname'] = $value['delimiter'] . $value['name'];
                }
            }
        }

        return $list;
    }

}
