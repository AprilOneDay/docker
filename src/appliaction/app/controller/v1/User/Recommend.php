<?php
/**
 * 推荐模块管理
 */
namespace app\app\controller\v1\user;

use app\app\controller;

class Recommend extends \app\app\controller\Init
{

    public function __construct()
    {
        parent::__construct();
        $this->checkIndividual();
    }

    /**
     * 推荐汽车信息
     * @date   2017-09-26T14:40:39+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function car()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['uid']        = $this->uid;

        $sign = getVar('helpType', 'app.service');
        $list = table('HelpCar')->field('id,brand,price,buy_time,mileage,description,created,status')->limit($offer, $pageSize)->order('id desc')->find('array');

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

    /**
     * 推荐服务信息
     * @date   2017-09-26T14:40:39+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function service()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;
        $map['uid']        = $this->uid;

        $sign = getVar('helpType', 'app.service');
        $list = table('HelpService')->field('id,sign,price,description,created,status')->limit($offer, $pageSize)->order('id desc')->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['sign_copy'] = $sign[$value['sign']];
        }

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }
}
