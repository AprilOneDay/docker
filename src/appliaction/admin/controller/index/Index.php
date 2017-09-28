<?php
namespace app\admin\controller\index;

use denha;

class Index extends \app\admin\controller\Init
{
    public function index()
    {
        $list = getVar('list', 'admin.white');

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

        $list = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort desc')->find('array');
        foreach ($list as $key => $value) {
            $map['parentid'] = $value['id'];

            $list[$key]['child'] = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort desc')->find('array');

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
