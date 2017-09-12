<?php
namespace app\console\controller\setting;

use denha;

class Admin extends denha\Controller
{
    public function index()
    {
        $list = table('ConsoleAdmin')->find('array');
        $data = [
            'data'  => [
                'list' => $list,
            ],
            'other' => [
                'statusCopy' => [0 => '关闭', 1 => '开启'],
            ],
        ];
        $this->ajaxReturn(['status' => true, 'data' => $data]);
    }

    public function edit()
    {
        if (IS_POST) {
            $param = post('data', 'json');
            if (!is_array($param)) {
                $this->ajaxReturn(['status' => false, 'msg' => '参数错误']);
            }

            $data['nickname'] = (string) $param['nickname'];
            $data['username'] = (string) $param['username'];
            $data['password'] = (string) $param['password'];
            $data['group']    = (int) max($param['group'], 1);
            $data['mobile']   = (string) $param['mobile'];

            if ((!$data['username'] || !$data['password']) && !$param['id']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写用户名/密码']);
            }

            if ($param['id']) {
                $admin = table('ConsoleAdmin')->where(['id' => $param['id']])->field('salt,id,username')->find();
                if (!$admin) {
                    $this->ajaxReturn(['status' => false, 'msg' => '账号不存在']);
                }

                if (!$data['password']) {
                    unset($data['password']);
                } else {
                    $data['password'] = md5($admin['salt'] . $data['password']);
                }

                $result = table('ConsoleAdmin')->where(array('id' => $param['id']))->save($data);
                if ($result) {
                    $this->ajaxReturn(['status' => true, 'msg' => '修改成功', 'id' => $result]);
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
                    $this->ajaxReturn(['status' => true, 'msg' => '添加成功', 'id' => $result]);
                } else {
                    $this->ajaxReturn(['status' => false, 'msg' => '添加失败']);
                }
            }

        } else {
            $id = get('id', 'intval');
            $rs = table('ConsoleAdmin')->field('id,username,`group`,status,nickname,mobile')->where(array('id' => $id))->find();

            $data = [
                'data' => $rs,
            ];
            $this->ajaxReturn(['status' => true, 'data' => $data]);
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
