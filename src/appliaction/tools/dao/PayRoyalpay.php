<?php
/**
 * royalpay支付接口
 */
namespace app\tools\dao;

class PayRoyalpay
{
    private static $config;
    private $baseUrl = 'https://mpay.royalpay.com.au/api/v1.0';
    private $headers = array(
        'Accept: application/json',
        'Content-Type: application/json');

    public function __construct()
    {
        if (is_null(self::$config)) {
            self::$config = getConfig('pay');
        }
    }

    public function pay($param, $type, $returnUrl, $debug = 0)
    {

        $nonceStr = rand(100000, 999999);
        $sign     = hash('sha256', self::$config[$type]['partner_code'] . '&' . (TIME * 1000) . '&' . $nonceStr . '&' . self::$config[$type]['credential_code']);

        $url = $this->baseUrl . '/alipay/partners/' . self::$config[$type]['partner_code'] . '/app_orders/' . $param['pay_sn'] . '?time=' . (TIME * 1000) . '&nonce_str=' . $nonceStr . '&sign=' . $sign;

        $data['price']       = $param['money'] * 100;
        $data['notify_url']  = self::$config[$type]['notify_url'];
        $data['orderid']     = $param['pay_sn'];
        $data['currency']    = $param['unit'];
        $data['operator']    = $param['uid'];
        $data['description'] = $param['title'];
        $data['return_url']  = $returnUrl;

        $result = response($url, 'PUT', json_encode($data), $this->headers);

        //debug
        if ($debug == 1) {
            print_r('-------输入参数TIME-----' . PHP_EOL);
            print_r(TIME * 1000 . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------输入参数nonceStr-----' . PHP_EOL);
            print_r($nonceStr . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------输入参数Config-----' . PHP_EOL);
            print_r(self::$config[$type]);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------输入参数Type-----' . PHP_EOL);
            print_r($type . PHP_EOL);
            print_r('-------输入参数header-----' . PHP_EOL);
            print_r($this->headers);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------输入参数Param-----' . PHP_EOL);
            print_r($data) . PHP_EOL;
            print_r(json_encode($data) . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------请求Url-----' . PHP_EOL);
            print_r($url . PHP_EOL);
            print_r('-------END-----' . PHP_EOL);
            print_r('-------返回结果-----' . PHP_EOL);
            print_r($result);
            print_r('-------END-----' . PHP_EOL);
            die;
        }

        if ($result['result_code']) {

            return array('status' => true, 'data' => $result);
        }
    }

}
