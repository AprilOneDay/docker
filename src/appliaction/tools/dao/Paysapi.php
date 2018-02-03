<?php
/**
 * paysapi支付接口
 */
namespace app\tools\dao;

class Paysapi
{
    private $baseUrl = 'https://pay.paysapi.com';
    private static $config;

    public function __construct()
    {
        if (is_null(self::$config)) {
            self::$config = getConfig('pay');
        }
    }

    public function pay($param, $type, $returnUrl, $debug = 0)
    {

        $url = $this->baseUrl . '/?format=json';

        $data['price']      = $param['money'];
        $data['uid']        = self::$config[$type]['uid'];
        $data['istype']     = $param['pay_type'];
        $data['notify_url'] = self::$config[$type]['notify_url'];
        $data['orderid']    = $param['pay_sn'];
        $data['orderuid']   = $param['uid'];
        $data['goodsname']  = $param['title'];
        $data['return_url'] = $returnUrl;

        $data['key'] = md5($data['goodsname'] . $data['istype'] . $data['notify_url'] . $data['orderid'] . $data['orderuid'] . $data['price'] . $data['return_url'] . self::$config[$type]['token'] . $data['uid']);

        $result = response($url, 'POST', $data);

        //debug
        if ($debug == 1) {
            print_r('-------输入参数Type-----' . PHP_EOL);
            print_r($type . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------输入参数Param-----' . PHP_EOL);
            print_r($data) . PHP_EOL;
            print_r('-------END-----' . PHP_EOL);
            print_r('-------请求Url-----' . PHP_EOL);
            print_r($url . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------返回结果-----' . PHP_EOL);
            print_r($result);die;
            print_r('-------END-----' . PHP_EOL);
        }

        if ($result['code']) {
            return $result;
        }
    }
}
