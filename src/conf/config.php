<?php
return [
    'ststic'       => URL . '/ststic',
    'vendor'       => URL . '/vendor',
    'uploadfile'   => URL . '/uploadfile/',

    //Cookie配置
    'cookieDomain' => '', //Cookie 作用域
    'cookiePath'   => '', //Cookie 作用路径
    'cookiePre'    => 'xYoum_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
    'cookieTtl'    => 0, //Cookie 生命周期，0 表示随浏览器进程

    'charset'      => 'utf-8', //网站字符集
    'timezone'     => 'Etc/GMT-8', //网站时区（只对php 5.1以上版本有效），Etc/GMT-8 实际表示的是 GMT+8

    'adminLog'     => 1, //是否记录后台操作日志
    'errorLog'     => 1, //1、保存错误日志到 cache/error_log.php | 0、在页面直接显示
    'authKey'      => 'QGa9h95r9Q5dYsnpsPb9', //密钥用于可逆加密

    /* 基础设置 */
    'debug'        => false, // 是否开启调试
    'trace'        => false, // 是否显示页面Trace信息
];
