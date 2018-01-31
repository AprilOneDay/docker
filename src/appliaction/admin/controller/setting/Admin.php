<?php
namespace app\admin\controller\setting;

use app\admin\controller\Init;

class Admin extends Init
{
    /**
     * 管理员列表
     * @date   2017-10-09T10:51:21+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function index()
    {
        $list = table('ConsoleAdmin')->find('array');

        $group = table('ConsoleGroup')->where(array('status' => 1))->field('name,id')->find('array');
        foreach ($group as $key => $value) {
            $groupList[$value['id']] = $value['name'];
        }

        $this->assign('list', $list);
        $this->assign('statusCopy', array(0 => '关闭', 1 => '开启'));
        $this->assign('groupList', $groupList);
        $this->show();
    }

    /**
     * 添加/编辑管理员
     * @date   2017-10-09T10:51:28+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {

            $data['nickname'] = post('nickname', 'text', '');
            $data['password'] = trim(strtolower(post('password', 'text', '')));
            $data['group']    = (int) max(post('group', 'intval', 1), 1);
            $data['mobile']   = post('mobile', 'text', '');
            $data['status']   = post('status', 'intval', 0);

            //获取当前管理员权限等级
            $consoleLevel = table('ConsoleGroup')->where('id', $this->group)->field('level')->find('one');

            //除超管以外 用户组更改只可高级想低级更改
            if ($this->group != 1) {
                //获取更改用户组的权限等级
                $adminLevel = table('ConsoleGroup')->where('id', $data['group'])->field('name,level')->find();
                if ($adminLevel['level'] < $consoleLevel) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '权限不足，不可设置用户组为【' . $adminLevel['name'] . '】'));
                }
            }

            //编辑
            if ($id) {
                if ($id == 1 && !$data['status']) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '初始账户不可关闭'));
                }

                $admin = table('ConsoleAdmin')->where('id', $id)->field('group,salt,id,username')->find();
                if (!$admin) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '账号不存在'));
                }

                //除超级管理员外 当前用户组这可编辑对于用户组信息
                if ($this->group != 1 && $this->group != $admin['group']) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '只可修改当前用户组的数据'));
                }

                //自己不能关闭自己
                if ($this->consoleid == $id && $data['status'] == 0) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '请不要关闭自己的账户'));
                }

                if (!$data['password']) {
                    unset($data['password']);
                } else {
                    $data['password'] = md5($admin['salt'] . $data['password']);
                }

                $result = table('ConsoleAdmin')->where(array('id' => $id))->save($data);
                if (!$result) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '修改失败'));

                }

                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            }
            //添加
            else {
                $data['username'] = post('username', 'text', '');
                if (!$data['username'] || !$data['password']) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '请填写用户名/密码'));
                }

                //判断用户名是否存在
                $isAdmin = table('ConsoleAdmin')->where('username', $data['username'])->field('id')->find();
                if ($isAdmin) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '用户名已存在'));
                }

                $data['salt']      = mt_rand(10000, 99999);
                $data['created']   = TIME;
                $data['create_ip'] = getIP();
                $data['password']  = md5($data['salt'] . $data['password']);
                $result            = table('ConsoleAdmin')->add($data);
                if (!$result) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
                }

                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
            }

        } else {
            if ($id) {
                $rs = table('ConsoleAdmin')->field('id,username,`group`,status,nickname,mobile')->where('id', $id)->find();
            } else {
                $rs['status'] = 1;
            }

            $groupList = table('ConsoleGroup')->where(array('status' => 1))->field('name,id')->find('array');

            $this->assign('data', $rs);
            $this->assign('groupList', $groupList);

            $this->show();
        }
    }

    public function delete()
    {
        $id = post('id', 'intval', 0);
        if ($id <= 1) {
            $this->ajaxReturn(array('status' => false, 'msg' => '初试账户不可删除！！'));
        }

        $result = table('ConsoleAdmin')->where('id', $id)->delete();

        if ($result) {
            $this->ajaxReturn(array('status' => true, 'msg' => '删除成功'));
        }

        $this->ajaxReturn(array('status' => true, 'msg' => '执行失败'));
    }
}
