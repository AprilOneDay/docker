<?php
return array(
    'ststic'            => URL . '/ststic',
    'vendor'            => URL . '/vendor',
    'uploadfile'        => URL . '/uploadfile/',

    //Cookie配置
    'cookieDomain'      => '', //Cookie 作用域
    'cookiePath'        => '', //Cookie 作用路径
    'cookiePre'         => 'xYoum_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
    'cookieTtl'         => 0, //Cookie 生命周期，0 表示随浏览器进程

    'charset'           => 'utf-8', //网站字符集
    'timezone'          => 'Etc/GMT-8', //网站时区（只对php 5.1以上版本有效），Etc/GMT-8 实际表示的是 GMT+8

    'adminLog'          => 1, //是否记录后台操作日志
    'errorLog'          => 1, //1、保存错误日志到 cache/error_log.php | 0、在页面直接显示
    'authKey'           => 'QGa9h95r9Q5dYsnpsPb9', //密钥用于可逆加密

    /* 基础设置 */
    'debug'             => true, // 是否开启调试
    'trace'             => false, // 是否显示页面Trace信息

    /** 微信api */
    'weixin_appid'      => 'wxeec81ef588a9214a',
    'weixin_secret'     => '1c326927e93070e1d4f4efbfb17cfb6f',

    /** 云屋直播 */
    'yunwu_key'         => '15923882847',
    'yunwu_secret'      => md5('123456'),

    /* 百度翻译api */
    'baidu_trans_appid' => '20170926000085221',
    'baidu_trans_key'   => 'dEmcRIqvTsUWa1greRa4',

    /* 邮件发送设置 */
    'send_debug_mail'   => true, //错误信息是否发送邮箱
    'send_mail'         => '350375092@qq.com', //接收邮箱账户

    /* stmp邮箱发送设置 */
    'smtp_port'         => 25, //smtp端口
    'smtp_host'         => 'smtp.163.com', //服务器地址
    'smtp_user'         => 'senddebug', //账户
    'smtp_password'     => 'uVqe2aZ0Wc0DiAVo', //密码
    'smtp_mail'         => 'senddebug@163.com', //发送邮箱名

);
