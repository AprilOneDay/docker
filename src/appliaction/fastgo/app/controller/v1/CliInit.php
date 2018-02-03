<?php
namespace app\fastgo\app\controller\v1;

use denha\Controller;

class CliInit extends Controller
{
    public $config;
    public $version;
    public $lg = 'zh'; //返回提示信息语言版本

    public function __construct()
    {
        $config = getConfig('api');

        $this->config = $config[0];

        !isset($_SERVER['HTTP_VERSION']) ?: $this->version = (string) $_SERVER['HTTP_VERSION'];
        !isset($_SERVER['HTTP_LG']) ?: $this->lg           = (string) $_SERVER['HTTP_LG'];

        $data['ip']         = getIP();
        $data['controller'] = CONTROLLER;
        $data['action']     = ACTION;
        $data['url']        = URL;
        $data['data']       = json_encode(post('all'), JSON_UNESCAPED_UNICODE);
        $data['created']    = TIME;

        //var_dump($data);die;

        table('ApiLog')->add($data);

    }

    /** api返回信息 */
    public function apiReturn($value)
    {
        header("Content-Type:application/json; charset=utf-8");
        $array = array(
            'code'   => 200,
            'status' => true,
            'data'   => array('list' => array()),
            'msg'    => '获取数据成功',
        );

        $debug = array(
            'debug' => array(
                'param' => array(
                    'post' => (array) post('all'),
                    'get'  => (array) get('all'),
                ),
                'ip'    => getIP(),
            ),
        );
        $array = array_merge($array, $debug);

        $value = array_merge($array, $value);
        if ($this->lg != 'zh') {
            $value['msg'] = dao('BaiduTrans')->baiduTrans($value['msg'], $this->lg);
        }

        //更新日志记录
        $map['created']    = TIME;
        $map['controller'] = CONTROLLER;
        $map['ACTION']     = ACTION;

        $data['status'] = ($value['status'] === fasle || $value['status'] === 'fasle' || $value['status'] == 0) ? 0 : 1;
        $data['msg']    = $value['msg'];

        table('ApiLog')->where($map)->save($data);

        exit(json_encode($value));
    }

}
