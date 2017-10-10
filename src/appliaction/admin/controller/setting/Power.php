<?php
/**
 * 权限管理模块
 */
namespace app\admin\controller\setting;

use denha;

class Power extends \app\admin\controller\Init
{
    public function lists()
    {
        $id = get('id', 'intval', 0);

        if (!$id) {
            denha\Log::error('参数错误');
        }

        $checkArray = table('ConsoleGroup')->where('id', $id)->field('power')->find('one');
        $checkArray = explode(',', $checkArray);

        $map['del_status'] = 0;
        $map['is_white']   = 0;

        $list = table('ConsoleMenus')->where($map)->field('id,name,parentid,module,controller,action')->order('sort asc,id asc')->find('array');

        $tree = new \app\console\tools\util\MenuTree();
        $tree->setConfig('id', 'parentid', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $list = $tree->getLevelTreeArray($list);

        $max = $this->getMaxDArray($list);

        $this->assign('id', $id);
        $this->assign('max', $max);
        $this->assign('checkArray', $checkArray);
        $this->assign('list', $list);
        $this->show();
    }

    public function edit()
    {
        $id      = get('id', 'intval', 0);
        $idArray = post('id');
        if (!$id) {
            $this->ajaxReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!$idArray) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请选择权限'));
        }
        $data['power'] = implode(',', $idArray);

        $reslut = table('ConsoleGroup')->where('id', $id)->save($data);
        if (!$reslut) {
            $this->ajaxReturn(array('status' => false, 'msg' => '保存失败'));
        }

        $this->ajaxReturn(array('msg' => '保存成功'));
    }

    //获取多维数组最大维度
    private function getMaxDArray($arrayValue, $childValue = 'child')
    {
        if (is_array($arrayValue)) {
            $max = 0;
            foreach ($arrayValue as $key => $value) {
                if ($childValue) {
                    $dArray = $this->getMaxDArray($arrayValue[$key][$childValue]);
                } else {
                    $dArray = $this->getMaxDArray($arrayValue[$key]);
                }

                if ($dArray > $max) {
                    $max = $dArray;
                }

                return $max + 1;
            }
        }

    }
}
