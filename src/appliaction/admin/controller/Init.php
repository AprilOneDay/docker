<?php
namespace app\admin\controller;

use denha\Controller;

class Init extends Controller
{
    public $consoleid;
    public $consoleName;
    public $group;
    public $power;
    public $webType; //站点标记
    public $lg; //后台语言版本 zh:中文 en：英文 jp：日文

    public function __construct()
    {

        $webConfig = getVar('config', 'admin.website');

        $this->webType = $webConfig['site_id'];
        $this->lg      = (array) explode(',', $webConfig['lg']);

        $isPass = $this->getWhiteList();
        if (!$isPass) {
            $console           = session('console');
            $this->consoleid   = $console['id'];
            $this->consoleName = $console['nickname'];
            $this->group       = $console['group'];

            //获取权限信息
            if ($this->group) {
                $checkArray  = table('ConsoleGroup')->where('id', $this->group)->field('power')->find('one');
                $this->power = explode(',', $checkArray);
            }

            if (!$console || !$checkArray) {
                header('Location:/index/login/');
            }
        }
    }

    /**
     * 获取非登录用户白名单
     * @date   2017-09-16T00:06:52+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getWhiteList()
    {

        $list = getVar('list', 'admin.white');
        //文件权限验证
        if (isset($list[MODULE])) {
            //controller文件权限验证 如果存在则验证 否则表明MODULE文件夹全部不需验证
            if (is_array($list[MODULE])) {
                //是否存在白名单中
                if (isset($list[MODULE][CONTROLLER])) {
                    //action文件验证 如果存在则验证 否则表明CONTROLLER文件全部不需验证
                    if (is_array($list[MODULE][CONTROLLER]) || in_array(CONTROLLER, $list[MODULE])) {
                        if (isset($list[MODULE][CONTROLLER][ACTION]) || in_array(ACTION, $list[MODULE][CONTROLLER])) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    public function modelField($id, $model, $field)
    {

        $result = table($model)->where(array('id' => $id))->field($field)->find();

        return $result[$field];

    }
}
