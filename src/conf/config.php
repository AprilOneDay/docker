<?php
return array(
    'ststic'            => URL . '/ststic',
    'vendor'            => URL . '/vendor',
    'uploadfile'        => URL . '/uploadfile/',
    'imgUrl'            => URL,

    /** Cookie配置 */
    'cookieDomain'      => '', //Cookie 作用域
    'cookiePath'        => '', //Cookie 作用路径
    'cookiePre'         => 'xYoum_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
    'cookieTtl'         => 0, //Cookie 生命周期，0 表示随浏览器进程

    'charset'           => 'utf-8', //网站字符集
    'timezone'          => 'Etc/GMT-8', //网站时区（只对php 5.1以上版本有效），Etc/GMT-8 实际表示的是 GMT+8

    'adminLog'          => 1, //是否记录后台操作日志
    'errorLog'          => 1, //1、保存错误日志到 cache/error_log.php | 0、在页面直接显示
    'authKey'           => 'QGa9h95r9Q5dYsnpsPb9', //密钥用于可逆加密

    /** 基础配置 */
    'debug'             => true, // 是否开启调试
    'trace'             => true, // 是否显示页面Trace信息
    'tag_trans'         => true, //是否开启标签翻译功能
    'app_debug'         => true, //app接口调试模式

    /** 微信api */
    'weixin_appid'      => 'wxeec81ef588a9214a',
    'weixin_secret'     => '1c326927e93070e1d4f4efbfb17cfb6f',

    /** 云屋直播 */
    'yunwu_key'         => '15923882847',
    'yunwu_secret'      => md5('123456'),
    'yunwu_api_key'     => md5('220762'), //加密企业ID

    /** 百度翻译api */
    'baidu_trans_appid' => '20170926000085221',
    'baidu_trans_key'   => 'dEmcRIqvTsUWa1greRa4',

    /** 淘宝即时通讯API */
    'taobao_key'        => '24777145',
    'taobao_secret'     => '8dc19dc6731cbbb4a2a9ec026a166b32',

    /** 邮件发送配置 */
    'send_debug_mail'   => true, //错误信息是否发送邮箱
    'send_mail'         => '350375092@qq.com', //接收邮箱账户

    /** 微信小程序 */
    'wxs_appid'         => 'wx8ae62b0d8ad31d28', //wx8ae62b0d8ad31d28
    'wxs_secret'        => 'bef08514721db1f7250409a86d44a66d', //8ca3f1386e188315ce2247ea58fed323
);
