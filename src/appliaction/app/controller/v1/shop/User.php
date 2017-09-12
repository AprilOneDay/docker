<?php
namespace app\app\controller\v1\shop;

use denha;

class User extends denha\Controller
{
    public function register()
    {
        $data['username'] = post('username', 'text', '');
        $data['password'] = post('password', 'trim', '');
        $data['mobile']   = post('mobile', 'text', '');
        $data['type']     = post('type', 'intval', 0);

        $code      = post('code', 'text', '');
        $password2 = post('password2', 'text', '');
        $isAgree   = post('is_agree', 'intval', 0);

        $reslut = dao('User')->register($data, $password2, $isAgree);

        $this->appReturn($reslut);
    }

    public function login()
    {
        $account  = post('account', 'text', '');
        $password = post('password', 'text', '');

        $reslut = dao('User')->login($account, $password);

        $this->appReturn($reslut);
    }
}
