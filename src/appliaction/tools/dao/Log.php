<?php
/**
 * 日志记录模块
 */
namespace app\tools\dao;

class Log
{
    /**
     * 增加错误日志
     * @date   2017-10-13T10:29:12+0800
     * @author ChenMingjiang
     * @param  integer                  $level   [description]
     * @param  string                   $message [description]
     * @return [type]                            [description]
     */
    public function error($level = 1, $message = '')
    {
        if (!$message) {
            return fasle;
        }

        $data['type']    = 2;
        $data['level']   = $level;
        $data['message'] = $message;
        $data['created'] = TIME;
        $data['url']     = $_SERVER['REQUEST_URI'];

        table('WebLog')->add($data);
    }
}
