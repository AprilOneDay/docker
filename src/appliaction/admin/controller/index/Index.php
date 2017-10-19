<?php
namespace app\admin\controller\index;

use denha;

class Index extends \app\admin\controller\Init
{
    public function index()
    {
        //获取白名单
        $list = getVar('list', 'admin.white');
        //获取栏目信息
        $list = $this->menus();

        $this->assign('list', $list);
        $this->show();
    }

    public function menus()
    {

        $type = post('type', 'intval', 1);
        $id   = post('id', 'intval', 0);

        $map['type']       = $type;
        $map['is_show']    = 1;
        $map['parentid']   = 0;
        $map['del_status'] = 0;
        $map['parentid']   = $id;

        $list = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort asc')->find('array');
        foreach ($list as $key => $value) {
            //隐藏未授权栏目信息
            if (in_array($value['id'], $this->power)) {
                $map['parentid']     = $value['id'];
                $list[$key]['child'] = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort asc')->find('array');
                foreach ($list[$key]['child'] as $k => $v) {
                    if (!in_array($v['id'], $this->power)) {
                        unset($list[$key]['child'][$k]);
                    }
                }
            } else {
                unset($list[$key]);
            }

        }
        if (IS_POST) {
            $this->assign('list', $list);
            $this->show();
        } else {
            return $list;
        }

        //$this->ajaxReturn(['status' => true, 'list' => $list]);
    }

    //获取验证码
    public function validateCode()
    {
        $code = new denha\ValidateCode();
    }
}
