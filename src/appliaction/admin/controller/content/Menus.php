<?php
namespace app\admin\controller\content;

use app\admin\controller\Init;
use app\admin\tools\util\MenuTree;

class Menus extends Init
{

    /**
     * [index 菜单管理首页]
     * @date   2016-09-05T10:22:28+0800
     * @author Sunpeiliang
     * @return [type]                   [description]
     */
    public function index()
    {
        $map['web_type'] = $this->webType;
        $result          = table('Column')->where($map)->order('sort asc,id asc')->find('array');

        if ($result) {
            $tree = new MenuTree();
            $tree->setConfig('id', 'parentid', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            $list = $tree->getLevelTreeArray($result);
            foreach ($list as $key => $value) {
                $list[$key]['is_show_copy'] = $value['is_show'] ? '√' : '×';
            }

            $data = array(
                'data' => array(
                    'list' => $list,
                ),
            );
        }

        $this->assign('list', $list);
        $this->show();

    }

    /**
     * [edit 编辑菜单]
     * @date   2016-09-05T10:21:29+0800
     * @author Sunpeiliang
     * @return [type]                   [description]
     */
    public function edit()
    {

        $id       = get('id', 'intval', 0);
        $parentid = get('parentid', 'intval', 0);
        $rs       = table('Column')->where(['id' => $id])->find();

        if (!$id) {
            $rs = array('is_show' => 1);
        }

        if ($id == 0 && $parentid != 0) {
            $rs['parentid'] = $parentid;
            $rs['sort']     = 0;
        }

        $this->assign('modelIdCopy', getVar('model', 'admin.article'));
        $this->assign('treeList', $this->treeList());
        $this->assign('data', $rs);
        $this->show();

    }

    public function editPost()
    {
        $id      = post('id', 'intval', 0);
        $add     = post('add', 'intval', 1);
        $content = post('content', 'text', '');

        $data['web_type'] = $this->webType;
        $data['name']     = post('name', 'text', '');
        $data['name_en']  = post('name_en', 'text', '');
        $data['name_jp']  = post('name_jp', 'text', '');

        $data['bname']    = post('bname', 'text', '');
        $data['bname_en'] = post('bname_en', 'text', '');
        $data['bname_jp'] = post('bname_jp', 'text', '');

        $data['url']      = post('url', 'text', '');
        $data['jump_url'] = post('jump_url', 'text', '');

        $data['model_id'] = post('model_id', 'intval', 0);
        $data['parentid'] = post('parentid', 'intval', 0);
        $data['is_show']  = post('is_show', 'intval', 0);
        //$data['sort']     = post('sort', 'intval', 0);
        $data['thumb'] = post('thumb', 'img', '');

        $data['bname'] ?: $data['bname'] = $data['name'];

        $data['module']     = strtolower(post('module', 'text', 'content'));
        $data['controller'] = strtolower(post('controller', 'text', 'article_list'));
        $data['action']     = strtolower(post('action', 'text', 'lists'));

        $data['url'] = (string) $data['url'] ?: '/' . $data['module'] . '/' . $data['controller'] . '/' . $data['action'] . $data['parameter'];

        if ($add == 1 && !$data['name']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请填写菜单名称'));
        }

        if ($id) {
            if ($data['jump_url'] && stripos($data['jump_url'], '/cid/') === false) {
                $data['jump_url'] .= '/s/cid/' . $id;
            }

            $column = table('Column')->where('id', $id)->field('parentid,id')->find();
            if (!$column) {
                $this->ajaxReturn(array('status' => false, 'msg' => '栏目不存在'));
            }

            if ($data['parentid'] == $id) {
                $this->ajaxReturn(array('status' => false, 'msg' => '上级栏目选择错误,不可选择自己为上级栏目'));
            }

            $result = table('Column')->where(array('id' => $id))->save($data);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '修改失败', 'sql' => table('Column')->getSql()));
            }

            $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));

        } else {

            $isArticle = table('Article')->where('column_id', $data['parentid'])->field('id')->find('one');
            if ($isArticle && $data['parentid'] != 0) {
                $this->ajaxReturn(array('status' => false, 'msg' => '清除父级栏目文章后，再添加栏目'));
            }

            $data['created'] = TIME;
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
                    $result = table('Column')->add($data);
                }
            } else {
                $result = table('Column')->add($data);
            }

            if ($result) {
                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功', 'id' => $result));
            } else {
                $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
            }
        }
    }

    /**
     * 更新排序
     * @date   2017-10-12T11:40:35+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function updateSort()
    {
        $id = post('id');
        foreach ($id as $key => $value) {
            if ($value !== '') {
                $data[$value][] = $key;
            }
        }

        foreach ($data as $key => $value) {
            $map       = array();
            $map['id'] = array('in', $value);

            $result = table('Column')->where($map)->save('sort', $key);
            if (!$result) {
                $this->ajaxReturn(array('status' => false, 'msg' => '更新失败'));
            }

        }

        $this->ajaxReturn(array('msg' => '更新成功'));
    }

    /**
     * 删除菜单
     * @date   2017-08-22T14:59:55+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function delete()
    {
        $chlid = post('id', 'intval');

        //删除所有下级分类 引用 &很重要 不然返回不了完整信息
        $delColumn = function ($chlid, &$idArray) use (&$delColumn) {
            $idArray[] = $chlid;
            //获取下级分类
            $chlidList = table('Column')->where('parentid', $chlid)->field('id')->find('one', true);

            //递归条件
            if ($chlidList) {
                foreach ($chlidList as $key => $value) {
                    $i++;
                    $delColumn((int) $value, $idArray);
                }
            }
            //返回需要删除的id
            return $idArray;
        }; //记得这里必须加``;``分号，不加分号php会报错，闭包函数

        //执行闭包函数
        $idArray = $delColumn($chlid);

        //执行删除操作
        $map['id'] = array('in', $idArray);
        $result    = table('Column')->where($map)->delete();
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));

    }

    /**
     * [children 获取菜单子集]
     * @date   2016-09-30T10:37:55+0800
     * @author Sunpeiliang
     * @return [type]                   [description]
     */
    public function children()
    {
        $id   = get('id', 'intval', 0);
        $menu = table('ConsoleMenu')->order('sort asc,id asc')->select();
        if ($menu) {
            $tree = new MenuTree();
            $tree->setConfig('id', 'parentid');
            $ids = $tree->getChildsId($menu, $id);
            if ($ids) {
                $this->ajaxReturn(array('status' => true, 'data' => $ids));
            }
        }
        $this->ajaxReturn(array('status' => false));
    }

    /**
     * [获取树状菜单列表]
     * @date   2016-09-05T10:21:46+0800
     * @author Sunpeiliang
     */
    public function treeList()
    {
        //格式化菜单
        $result = table('Column')->field('id,parentid,name,bname')->find('array');
        if ($result) {
            $tree = new MenuTree();
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
