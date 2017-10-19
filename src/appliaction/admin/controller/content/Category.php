<?php
/**
 * 分类管理
 */
namespace app\admin\controller\content;

class Category extends \app\admin\controller\Init
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

        if (IS_POST) {
            $id      = post('id', 'intval', 0);
            $add     = post('add', 'intval', 1);
            $content = post('content', 'text', '');

            $data['parentid'] = post('parentid', 'intval', 0);
            $data['sort']     = post('sort', 'intval', 0);
            $data['is_show']  = post('is_show', 'intval', 1);

            $data['name']  = post('name', 'text', '');
            $data['thumb'] = post('thumb', 'img', '');

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
                        if ($value) {
                            $data['name'] = trim($value);
                            $result       = table('Category')->add($data);
                        }
                    }
                } else {
                    $result = table('Category')->add($data);
                }
                if ($result) {
                    $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
                }
            }

            $this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
        } else {
            $id          = get('id', 'intval', 0);
            $parentid    = get('parentid', 'intval', 0);
            $rs          = table('Category')->where(array('id' => $id))->find();
            $rs['thumb'] = json_encode((array) imgUrl($rs['thumb'], 'category'));

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
}
