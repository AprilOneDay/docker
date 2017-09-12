<?php
namespace app\console\controller\index;

use denha;

class Index extends denha\Controller
{
    public function menus()
    {
        $type = get('type', 'intval', 1);
        $id   = get('id', 'intval', 0);

        $map['type']       = $type;
        $map['is_show']    = 1;
        $map['parentid']   = 0;
        $map['del_status'] = 0;
        $map['parentid']   = $id;

        $list = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort desc')->find('array');
        foreach ($list as $key => $value) {
            $map['parentid'] = $value['id'];

            $list[$key]['child'] = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort desc')->find('array');

            !$list[$key]['child'] ?: $list[$key]['childShow'] = false;

        }

        $this->ajaxReturn(['status' => true, 'list' => $list]);
    }

    //获取验证码
    public function validateCode()
    {
        $code = new denha\ValidateCode();
    }
}
