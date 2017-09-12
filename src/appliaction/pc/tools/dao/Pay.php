<?php
namespace app\pc\tools\dao;

class Pay
{
    /**
     * 支付宝支付接口
     * @date   2017-07-16T21:46:28+0800
     * @author ChenMingjiang
     * @param  [type]                   $orderSn [description]
     * @return [type]                            [description]
     */
    public function alipay($orderSn)
    {

        require_once FRAME_LIB_PULS_PATH . '/alipay/config.php';
        require_once FRAME_LIB_PULS_PATH . '/alipay/pagepay/service/AlipayTradeService.php';
        require_once FRAME_LIB_PULS_PATH . '/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        $orders = table('orders')->where(array('order_sn' => $orderSn))->find();

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $orders['order_sn'];

        //订单名称，必填
        $subject = $orders['title'];

        //付款金额，必填
        $total_amount = $orders['amount'];

        //商品描述，可空
        $body = '';

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new \AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder, $config['return_url'], $config['notify_url']);
    }
}
