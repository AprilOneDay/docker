<?php
namespace app\admin\controller\setting;

class Admin extends \app\admin\controller\Init
{
    public function index()
    {
        $list = table('ConsoleAdmin')->find('array');

        $this->assign('list', $list);
        $this->assign('statusCopy', array(0 => '关闭', 1 => '开启'));
        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {

            $data['nickname'] = post('nickname', 'text', '');
            $data['username'] = post('username', 'text', '');
            $data['password'] = trim(strtolower(post('password', 'text', '')));
            $data['group']    = (int) max(post('group', 'intval', 1), 1);
            $data['mobile']   = post('mobile', 'text', '');
            $data['status']   = post('status', 'intval', 0);

            if ((!$data['username'] || !$data['password']) && !$id) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写用户名/密码']);
            }

            if ($id) {
                if ($id == 1 && !$data['status']) {
                    $this->ajaxReturn(['status' => false, 'msg' => '初始账户不可关闭']);
                }

                $admin = table('ConsoleAdmin')->where(['id' => $id])->field('salt,id,username')->find();
                if (!$admin) {
                    $this->ajaxReturn(['status' => false, 'msg' => '账号不存在']);
                }

                if (!$data['password']) {
                    unset($data['password']);
                } else {
                    $data['password'] = md5($admin['salt'] . $data['password']);
                }

                $result = table('ConsoleAdmin')->where(array('id' => $id))->save($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '修改成功']);
                } else {
                    $this->ajaxReturn(['status' => false, 'msg' => '修改失败']);
                }
            } else {
                $data['salt']      = mt_rand(10000, 99999);
                $data['created']   = TIME;
                $data['create_ip'] = getIP();
                $data['password']  = md5($data['salt'] . $data['password']);
                $result            = table('ConsoleAdmin')->add($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '添加成功']);
                } else {
                    $this->ajaxReturn(['status' => false, 'msg' => '添加失败']);
                }
            }

        } else {
            if ($id) {
                $rs = table('ConsoleAdmin')->field('id,username,`group`,status,nickname,mobile')->where(array('id' => $id))->find();
                $this->assign('data', $rs);
            } else {
                $rs['status'] = 1;
                $this->assign('data', $rs);
            }

            $this->show();
        }
    }

    public function delete()
    {
        $id = post('id', 'intval', 0);
        if ($id <= 1) {
            $this->ajaxReturn(['status' => false, 'msg' => '初试账户不可删除！！']);
        }

        $result = table('ConsoleAdmin')->where(['id' => $id])->delete();

        if ($result) {
            $this->ajaxReturn(['status' => true, 'msg' => '删除成功']);
        }

        $this->ajaxReturn(['status' => true, 'msg' => '执行失败']);
    }
}
