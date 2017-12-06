<?php
namespace app\admin\controller\index;

use app\admin\controller\Init;

class Index extends Init
{
    public $thisConsoleMenusTopId; //顶级栏目信息
    public $thisConsoleMenusId; //最多记录二级栏目信息

    public function __construct()
    {
        parent::__construct();

        $this->thisConsoleMenusId = session('this_console_menus_id');
        if ($this->thisConsoleMenusId) {
            $this->thisConsoleMenusTopId = table('ConsoleMenus')->where('id', $this->thisConsoleMenusId)->field('parentid')->find('one');
        }
    }

    public function index()
    {
        //获取白名单 不需要登录验证的
        $list = getVar('list', 'admin.white');
        //获取栏目信息
        $list['list'] = $this->menus();
        if ($this->thisConsoleMenusId) {
            $list['two']['list'] = $this->menus(1, $this->thisConsoleMenusId);
        }

        $this->assign('list', $list);
        $this->show();
    }

    //二级导航显示
    public function menusPost()
    {
        $type = post('type', 'intval', 1);
        $id   = post('id', 'intval', 0);

        $map['type']       = $type;
        $map['is_show']    = 1;
        $map['parentid']   = 0;
        $map['del_status'] = 0;
        $map['parentid']   = $id;

        !(!$id && $this->thisConsoleMenusId) ?: $id = $this->thisConsoleMenusId;

        $list = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort asc,id asc')->find('array');
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

        session('this_console_menus_id', $id);
        $this->assign('list', $list);
        $this->show('', false, false);

    }

    //一级导航
    public function menus($type = 1, $id = 0)
    {

        $map['type']       = $type;
        $map['is_show']    = 1;
        $map['parentid']   = 0;
        $map['del_status'] = 0;
        $map['parentid']   = $id;

        $list = table('ConsoleMenus')->where($map)->field('id,name,icon,url')->order('sort asc,id asc')->find('array');
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

        return $list;
    }

}
