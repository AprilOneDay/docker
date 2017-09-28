<?php
/**
 * 城市模块
 */
namespace app\app\controller\v1\common;

use app\app\controller;

class City extends \app\app\controller\Init
{

    public function province()
    {
        $data = $this->appArray(getVar('province', 'city'));
        $this->appReturn(array('data' => $data));
    }
}
