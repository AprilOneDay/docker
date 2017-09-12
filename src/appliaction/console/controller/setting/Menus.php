<?php
namespace app\console\controller\setting;

use denha;

class Menus extends denha\Controller
{
    const TYPE = ['1' => 'console'];

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
        $this->ajaxReturn(['status' => true, 'data' => $data]);
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
            $param = post('data', 'json');
            if (!is_array($param)) {
                $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
            }

            $data['name']      = (string) $param['name'];
            $data['parameter'] = (string) $param['parameter'];
            $data['url']       = (string) $param['url'];
            $data['icon']      = (string) $param['icon'];

            $data['type']     = max((int) $param['type'], 1);
            $data['parentid'] = (int) max($param['parentid'], 0);
            $data['status']   = (int) $param['status'];
            $data['is_show']  = (int) $param['is_show'];
            $data['is_white'] = (int) $param['is_white'];
            $data['sort']     = (int) $param['sort'];

            $data['module']     = strtolower($param['module']);
            $data['controller'] = strtolower($param['controller']);
            $data['action']     = strtolower($param['action']);
            $data['created']    = TIME;

            $data['url'] = (string) $param['url'] ?: '/' . self::TYPE[$data['type']] . '/' . $data['module'] . '/' . $data['controller'] . '/' . $data['action'] . $data['parameter'];

            if (!$data['name']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写菜单名称']);
            }

            if (!$data['module'] || !$data['controller'] || !$data['action']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写模块/控制器/方法名称']);
            }

            if ($param['id']) {
                $result = table('ConsoleMenus')->where(array('id' => $param['id']))->save($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '修改成功', 'id' => $result]);
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
            $id = get('id', 'intval');
            $rs = table('ConsoleMenus')->where(['id' => $id])->find();

            $data = [
                'data' => $rs,
            ];
            $this->ajaxReturn(['status' => true, 'data' => $data]);
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
    public function treeList()
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
        $this->ajaxReturn(['menulist' => $list, 'status' => true]);

    }
    /**
     * [children 获取菜单子集]
     * @date   2016-09-30T10:37:55+0800
     * @author Sunpeiliang
     * @return [type]                   [description]
     */
    public function children()
    {
        $id   = G('id', 'intval', 0);
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
