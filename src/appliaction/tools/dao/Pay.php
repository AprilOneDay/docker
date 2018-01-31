<?php
/**
 * 支付接口调用
 */
namespace app\tools\dao;

class Pay
{
    /**
     * 支付接口
     * @date   2018-01-15T15:24:20+0800
     * @author ChenMingjiang
     * @param  [type]                   $param     [支付参数]
     * @param  [type]                   $payMatch  [支付调用接口]
     * @param  [type]                   $returnUrl [支付完成通知地址]
     * @return [type]                              [description]
     */
    public function main($param, $payMatch, $returnUrl)
    {

        switch ($payMatch) {
            case '1': //paysapi
                $result = dao('Paysapi')->pay($param, $payMatch, $returnUrl);
                break;
            case '2': //royalpay
                $result = dao('PayRoyalpay')->pay($param, $payMatch, $returnUrl);
                break;
            default:
                # code...
                break;
        }

        $result['status'] = true;

        return $result;
    }

}
