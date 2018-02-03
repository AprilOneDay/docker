<?php
/**
 * 会员模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Index extends Init
{

    public function testOrdersLog()
    {
        $result = table('OrdersLog')->add(123, 123, 9);
        var_dump($result);
    }

    public function testAddOrders()
    {
        $result = dao('FastgoApi', 'fastgo')->addOrders('TEST00014KO');
        $this->appReturn($result);
    }

    public function testPrice()
    {
        $result = dao('FastgoApi', 'fastgo')->getLogisticsPrice('', '9');
        $this->appReturn($result);
    }

    public function testTaobao()
    {
        $g      = get('g', 'text', '123456');
        $result = dao('TaobaoUser')->add($g, 'cmj', '123456789');
        $this->appReturn($result);
    }

    public function testTaobao2()
    {
        $g = get('g', 'text', 'imuser123,123456');
        dao('TaobaoUser')->index($g);
    }

    public function orderSn()
    {
        $result = dao('FastgoApi', 'fastgo')->createOrderSn($this->uid, $this->group, 'FG01');

        $this->appReturn($result);

        $orderSn = $result['data'];
    }

    public function siteUpdate()
    {
        $result = dao('FastgoApi', 'fastgo')->updateSite();

        $this->appReturn($result);
    }

    /**
     * 接口测试
     */
    public function index()
    {

        $data = array(

            'type'     => 1,
            'username' => '测试',
            'password' => '123456',
            'mobile'   => '15215051909',
            'is_agree' => 1,

        );

        //var_dump(json_encode($data));die();
        $result = $this->http_post_json('http://192.168.0.254:8092/v1/user/Operation/register', json_encode($data));
        var_dump($result);
    }

    public function testShow()
    {
        $test = post('test', 'text', '');
        $abc  = post('abc', 'text', '');

        echo '测试post参数获取:';
        echo $test . PHP_EOL;
        echo $abc . PHP_EOL;
    }

    public function testPost()
    {
        $url    = 'http://fastgo.59156.cn/v1/index/index/testShow';
        $result = response($url, 'POST', array('test' => '124', 'abc' => 'aaaa'));

        var_dump($result);die;
    }

    //HTTP JSON请求
    public function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr),
        )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array($httpCode, $response);
    }

}
