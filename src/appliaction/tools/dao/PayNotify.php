<?php
/**
 * 支付回调检验
 */
namespace app\tools\dao;

class PayNotify
{
    private static $config;

    public function __construct()
    {
        if (is_null(self::$config)) {
            self::$config = getConfig('pay');
        }

    }

    /**
     * 日志记录
     * @date   2018-01-17T14:42:06+0800
     * @author ChenMingjiang
     * @param  [type]                   $callBackType [description]
     * @param  [type]                   $data         [description]
     * @param  [type]                   $result       [description]
     * @return [type]                                 [description]
     */
    private function log($callBackType, $data, $result)
    {

        //如果没有写入权限尝试修改权限 如果修改后还是失败 则跳过
        if (!isWritable(DATA_PATH)) {
            return false;
        }

        $path = DATA_PATH . 'pay_log' . DS;
        is_dir($path) ? '' : mkdir($path, 0755, true);

        $path = DATA_PATH . 'pay_log' . DS . date('Y_m_d') . '.log';

        $content = '--------CallBackType:' . $callBackType . '-------------' . PHP_EOL;
        $content .= json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $content .= '--------Result----------------------------------------' . PHP_EOL;
        $content .= json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $content .= '--------End-' . date('Y-m-d H:i:s', TIME) . '---------------' . PHP_EOL . PHP_EOL;

        $file = fopen($path, 'a');
        fwrite($file, $content . PHP_EOL);
        fclose($file);
    }

    public function main($callBackType)
    {

        switch ($callBackType) {
            case 1:
                $result = $this->Paysapi($callBackType);
                break;
            case 2:
                $result = $this->PayRoyalpay($callBackType);
                break;
            default:
                # code...
                break;
        }

        if (!$result['status']) {
            return $result;
        }

        $result = dao('Finance')->checkPrice($param);
        if (!$result) {
            return $result;
        }

        //执行支付成功后的后续处理
        dao('PayCallBack')->main($param);
    }

    /** Paysapi验证 */
    public function Paysapi($callBackType)
    {
        $paysapi_id = post('paysapi_id', 'intval', 0);
        $realprice  = post('realprice', 'float', 0);
        $key        = post('key', 'text', '');

        $param['pay_sn'] = post('orderid', 'text', '');
        $param['price']  = post('price', 'float', '');
        $param['uid']    = post('orderuid', 'intval', 0);

        //校验传入的参数是否格式正确，略
        $token = self::$config[$callBackType]['token'];

        $temps = md5($param['pay_sn'] . $param['uid'] . $paysapi_id . $param['price'] . $realprice . $token);

        if ($temps != $key) {
            $result = array('status' => false, 'msg' => '支付检验失败');
        } else {
            $result = array('status' => true, 'msg' => '认证通过', 'data' => $param);
        }

        $this->log($callBackType, $param, $result);

        return $result;
    }

    /** PayRoyalpay验证 */
    public function PayRoyalpay()
    {

        $response = json_decode(file_get_contents('php://input'), true);

        $sign = hash('sha256', self::$config[$callBackType]['partner_code'] . '&' . $response['time'] . '&' . $response['nonce_str'] . '&' . self::$config[$callBackType]['credential_code']);

        //商户订单号
        $param['pay_sn'] = $response['partner_order_id'];
        //支付金额，单位是最小货币单位
        $param['pay_money'] = $response['real_fee'] / 100;
        //币种
        $param['unit'] = $response['currency'];
        //订单创建时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
        $create_time = $response['create_time'];
        //订单支付时间，格式为'yyyy-MM-dd HH:mm:ss'，澳洲东部时间
        $pay_time = $response['pay_time'];
        //RoyalPay订单号
        $royal_order_id = $response['order_id'];
        //订单金额，单位是最小货币单位
        $order_amt = $response['total_fee'];

        if ($sign != $response['sign']) {
            $result = array('status' => false, 'msg' => '支付检验失败');
        } else {
            $result = array('status' => true, 'msg' => '认证通过', 'data' => $param);
        }

        $this->log($callBackType, $param, $result);

        return $result;
    }

}
