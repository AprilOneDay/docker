<?php
/**
 * 云屋直播管理
 */
namespace app\tools\dao;

use denha\Start;

class YunwuRoom
{
    public static $config;
    public $url = 'http://www.cloudroom.com/api/';
    public $param;

    public function __construct()
    {
        self::$config['RequestId'] = '123456789';
        self::$config['UserName']  = Start::$config['yunwu_key'];
        self::$config['UserPswd']  = Start::$config['yunwu_secret'];

        $this->param = '?RequestId=' . self::$config['RequestId'] . '&UserName=' . self::$config['UserName'] . '&UserPswd=' . self::$config['UserPswd'] . '&DoAction=get';
    }

    /**
     * 创建直播房间
     * @date   2017-11-20T16:41:41+0800
     * @author ChenMingjiang
     * @param  string                   $url [description]
     * @return [type]                        [description]
     */
    public function created($name = '')
    {

        if (!$name) {
            return false;
        }

        if ($this->getList($name)) {
            return false;
        }

        $url    = $this->url . 'createLiveAPI' . $this->param . '&LiveSubject=' . $name;
        $result = file_get_contents($url);
        $result = json_decode($result, true);

    }

    /**
     * 查询直播房间
     * @date   2017-11-20T16:41:28+0800
     * @author ChenMingjiang
     * @param  [type]                   $name [description]
     * @param  string                   $url   [description]
     * @return [type]                          [description]
     */
    public function getList($name)
    {
        if (!$name) {
            return false;
        }

        $url    = $this->url . 'queryLiveAPI' . $this->param . '&LiveSubject=' . $name;
        $result = file_get_contents($url);
        $result = json_decode($result, true);

        if (!$result['RspCode']) {
            if (!$result['Data']['LiveNum']) {
                return false;
            }

            foreach ($result['Data']['LiveList'] as $key => $value) {
                $result['Data']['LiveList'][$key]['LiveCode'] = substr($value['liveUrl'], -6);
            }

            return $result['Data']['LiveList'];
        }

        return false;
    }

    /**
     * 查询会议列表
     * @date   2017-11-29T16:55:19+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getMeetList($name)
    {
        $get                = self::$config;
        $get['LiveSubject'] = $name;

        $url = 'http://www.cloudroom.com/api/servlet/queryAllConfs?_data_=%7B%22RequestId%22%3A%22123456789%22%2C%22UserName%22%3A%22' . self::$config['UserName'] . '%22%2C%22UserPswd%22%3A%22' . self::$config['UserPswd'] . '%22%7D';

        $result = file_get_contents($url);
        $result = json_decode($result, true);

        if (!$result['RspCode']) {

            foreach ($result['MeetList'] as $key => $value) {
                //$result['Data']['MeetList'][$key]['LiveCode'] = substr($value['liveUrl'], -6);
                $meetList[$value['MeetSubject']] = $value;
            }

            if ($name) {
                return $meetList[$name];
            }

            return $meetList;
        }

        return false;
    }

    /**
     * 加入会议
     * @date   2017-11-29T15:15:44+0800
     * @author ChenMingjiang
     * @param  [type]                   $meetID   [会议号]
     * @param  integer                  $type     [参会者类型1:主持人 2:普通参会者]
     * @param  string                   $name     [参会者昵称]
     * @param  integer                  $uuid     [请求id，可以用UUID]
     * @param  string                   $password [认证串(32位md5加密企业ID，企业ID由云屋提供)]
     * @return [type]                             [description]
     */
    public function joinMeet($meetID, $type = 2, $name = '', $password = '123456', $uuid = 0)
    {
        $url    = $this->url . 'servlet/joinconfcebycomm?MeetID=' . $meetID . '&UserType=' . $type . '&Name=' . $name . '&RequestId=' . $uuid . '&MeetPwd=' . $password . '&UserKey=' . Start::$config['yunwu_api_key'];
        $result = file_get_contents($url);
        $result = json_decode($result, true);

        //var_dump($result);die;

        if (!$result['RspCode']) {
            return $result['CRMTStr'];
        }

        return null;
    }
}
