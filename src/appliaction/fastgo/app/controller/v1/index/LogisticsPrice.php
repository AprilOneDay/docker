<?php
/**
 * 物流价格预估查询
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class LogisticsPrice extends Init
{
    /** 预估相关价格 */
    public function index()
    {

        $country = get('country', 'text', '');
        $weight  = get('weight', 'float', 2);

        if (!$country) {
            $this->appReturn(array('status' => false, 'msg' => '请选择国家'));
        }

        //英文地址转换中文地址
        if (!preg_match("/[\x7f-\xff]/", $country)) {
            $country = table('Category')->where('name_en', $country)->field('name')->find('one');
        }

        $result = dao('FastgoApi', 'fastgo')->getLogisticsPrice($country, $weight);
        if ($result['status']) {
            foreach ($result['data']['list'] as $key => $value) {
                $result['data']['list'][$key]['message'] = (string) $value['message'];
            }
        }

        $this->appReturn($result);
    }
}
