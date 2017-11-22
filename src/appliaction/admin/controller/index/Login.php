<?php
namespace app\admin\controller\index;

class Login extends \app\admin\controller\Init
{
    //登录
    public function index()
    {

        if (IS_POST) {
            $username = (string) post('username', 'text', '');
            $password = (string) post('password', 'text', '');

            $admin = table('ConsoleAdmin')->where('username', $username)->field('id,consoleid,password,salt,status,nickname,group')->find();
            //判断帐号
            if (!$admin || !$username) {
                $this->ajaxReturn(array('status' => false, 'msg' => '用户名错误'));
            }

            if (!$admin['status']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '账户已禁用'));
            }

            //判断密码
            if (md5($admin['salt'] . $password) !== $admin['password']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '密码错误'));
            }

            //判断用户组
            $group = table('ConsoleGroup')->where('id', $admin['group'])->field('status')->find();
            if (!$group['status']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '用户组已被关闭,请联系管理员'));
            }

            $data['login_ip']   = getIP();
            $data['login_time'] = TIME;
            table('ConsoleAdmin')->where('id', $admin['id'])->save($data);

            $console['id']       = $admin['id'];
            $console['nickname'] = $admin['nickname'];
            $console['group']    = $admin['group'];

            session('console', $console);

            $this->ajaxReturn(array('status' => true, 'msg' => '登录成功'));

        } else {
            $this->show();
        }

    }

    /**
     * 退出登录
     * @date   2017-09-19T21:45:21+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function loginOut()
    {
        session_start();
        unset($_SESSION['console']);
        session_write_close();
        header('Location:/index/login/');
    }

    //检测是否登录
    public function oauth()
    {
        $callback = get('callback');
        if (issetSession('consoleid')) {
            $this->jsonpReturn(array('status' => true, 'msg' => '已登录'), $callback);
        } else {
            $this->jsonpReturn(array('status' => false, 'msg' => '未登录'), $callback);
        }
    }
}
