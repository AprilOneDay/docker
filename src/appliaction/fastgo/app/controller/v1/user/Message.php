<?php
/**
 * 系统消息通知
 */
namespace app\fastgo\app\controller\v1\user;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Message extends Init
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1,2');
    }

    /** 系统通知列表 */
    public function index()
    {

        $type = get('type', 'text', 0);

        if ($type) {
            $map['type'] = array('in', $type);
        }

        $map['to_uid'] = $this->uid;

        $list = dao('Message')->getList($this->lg, $map);

        //收者将标记改为已读
        table('UserMessage')->where(array('to_uid' => $this->uid, 'is_reader' => 0))->save('is_reader', 1);

        $data['list'] = $list ? $list : array();

        $this->appReturn(array('data' => $data));
    }

}
