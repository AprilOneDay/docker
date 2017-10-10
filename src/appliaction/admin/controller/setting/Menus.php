<?php
namespace app\admin\controller\setting;

class Menus extends \app\admin\controller\Init
{
    const TYPE = ['1' => ''];

    /**
     * [index 菜单管理首页]
     * @date   2016-09-05T10:22:28+0800
     * @author Sunpeiliang
     * @return [type]                   [description]
     */
    public function index()
    {
        $map['del_status'] = 0;

        $result = table('ConsoleMenus')->where($map)->order('sort asc,id asc')->find('array');

        if ($result) {
            $tree = new \app\console\tools\util\MenuTree();
            $tree->setConfig('id', 'parentid', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            $list = $tree->getLevelTreeArray($result);
            foreach ($list as $key => $value) {
                $list[$key]['status']  = $value['status'] ? '√' : '×';
                $list[$key]['is_show'] = $value['is_show'] ? '√' : '×';
            }

            $data = [
                'data' => [
                    'list' => $list,
                ],
            ];
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
        if (IS_POST) {
            $id = post('id', 'intval', 0);

            $data['name']      = post('name', 'text', '');
            $data['parameter'] = post('parameter', 'text', '');
            $data['url']       = post('url', 'text', '');
            $data['icon']      = post('icon', 'text', '');

            $data['type']     = max(post('type', 'intval', 0), 1);
            $data['parentid'] = post('parentid', 'intval', 0);
            $data['status']   = post('status', 'intval', 0);
            $data['is_show']  = post('is_show', 'intval', 0);
            $data['is_white'] = post('is_white', 'intval', 0);
            $data['sort']     = post('sort', 'intval', 0);

            $data['module']     = strtolower(post('module', 'text', ''));
            $data['controller'] = strtolower(post('controller', 'text', ''));
            $data['action']     = strtolower(post('action', 'text', ''));
            $data['created']    = TIME;

            $data['url'] = (string) $data['url'] ?: '/' . self::TYPE[$data['type']] . $data['module'] . '/' . $data['controller'] . '/' . $data['action'] . $data['parameter'];

            if (!$data['name']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写菜单名称']);
            }

            if (!$data['module'] || !$data['controller'] || !$data['action']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写模块/控制器/方法名称']);
            }

            if ($id) {
                if ($data['parentid'] == $id) {
                    $this->ajaxReturn(['status' => false, 'msg' => '上级栏目选择错误,不可选择自己为上级栏目']);
                }

                $result = table('ConsoleMenus')->where(array('id' => $id))->save($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '修改成功']);
                } else {
                    $this->ajaxReturn(['status' => false, 'msg' => '修改失败']);
                }
            } else {
                $result = table('ConsoleMenus')->add($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '添加成功', 'id' => $result]);
                } else {
                    $this->ajaxReturn(['status' => false, 'msg' => '添加失败']);
                }
            }

        } else {
            $id       = get('id', 'intval', 0);
            $parentid = get('parentid', 'intval', 0);
            $rs       = table('ConsoleMenus')->where(['id' => $id])->find();

            if (!$id) {
                $rs = array('is_show' => 1, 'is_white' => 0, 'status' => 1);
            }

            if ($id == 0 && $parentid != 0) {
                $rs['parentid'] = $parentid;
                $rs['sort']     = 0;
            }

            $this->assign('treeList', $this->treeList());
            $this->assign('data', $rs);
            $this->show();
        }
    }

    /**
     * 删除菜单
     * @date   2017-08-22T14:59:55+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function delete()
    {
        $id = post('id', 'intval', 0);
        if (!$id) {
            $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
        }

        $result = table('ConsoleMenus')->where(['id' => $id])->save(['del_status' => 1]);

        if ($result) {
            $this->ajaxReturn(['status' => true, 'msg' => '删除成功']);
        }

        $this->ajaxReturn(['status' => false, 'msg' => '删除失败']);

    }

    /**
     * [获取树状菜单列表]
     * @date   2016-09-05T10:21:46+0800
     * @author Sunpeiliang
     */
    private function treeList()
    {
        //格式化菜单
        $result = table('ConsoleMenus')->field('id,parentid,name,icon,module,controller,action')->find('array');
        if ($result) {
            $tree = new \app\console\tools\util\MenuTree();
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
            $tree = new \app\console\tools\util\MenuTree();
            $tree->setConfig('id', 'parentid');
            $ids = $tree->getChildsId($menu, $id);
            if ($ids) {
                $this->ajaxReturn(['status' => 1, 'data' => $ids]);
            }
        }
        $this->ajaxReturn(['status' => 0]);
    }
}
