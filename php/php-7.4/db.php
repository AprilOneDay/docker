<?php
return [
    // +----------------------------------------------------------------------
    // | 数据库信息
    // +----------------------------------------------------------------------
    'dbInfo' => [
        [
            'type'        => 'mysql', // 数据库类型
            'host'        => '60.205.222.204', // 服务器地址
            'name'        => 'yxlive', // 数据库名
            'user'        => 'siyue', // 用户名
            'pwd'         => 'ihbdTjWY0xNQHXCmT41k2Ryxk3kMnQOJ', // 密
            'prefix'      => 'dh_', // 数据库表前缀
            'port'        => '3309', // 端口
            'dsn'         => '',
            'params'      => '',
            'charset'     => 'utf8mb4',
            'save_log'    => true, // 是否开启sql日志保存
            'error_log'   => true, // 是否开启错误Sql保存
            'slow_log'    => true, // 是否开启慢sql日志记录
            'slow_time'   => 5, // 查询时间超过多少秒记录日志
            'sql_explain' => false, // 开启sql性能查询
        ],
    ],
];
