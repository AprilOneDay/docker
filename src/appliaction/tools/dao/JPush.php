<?php
/**
 * 激光推送
 * @author ChenMingjiang <dancefun@163.com>
 * @version $Id: JPush.php 2016-12-13 15:55:59 $
 */
namespace app\tools\dao;

class JPush
{
    protected $client;
    protected $app_key       = '97e1492b5bd9610055adb390';
    protected $master_secret = '8edf329d1029d6ca8bfa1965';
    public function __construct()
    {
        require_once APP_PATH . 'tools' . DS . 'vendor' . DS . 'JPush' . DS . 'autoload.php';
        $this->client = new \JPush\Client($this->app_key, $this->master_secret);
    }

    /**
     * 按注册ID推送
     * @date   2016-12-16T14:34:25+0800
     * @author ChenMingjiang
     * @param  array                  $uids      会员ID
     * @param  string                 $title     推送标题
     * @param  string                 $content   推送内容
     * @param  array                  $jumpParam 参数
     * @return boolean
     */
    public function sendByRegId($uids, $title, $content, $jumpParam = array())
    {
        if (empty($uids) || !$content) {
            return false;
        }

        //获取可推送列表
        $map               = array();
        $map['id']         = array('in', $uids);
        $map['is_message'] = 1;
        $map['imei']       = array('!=', '');
        $list              = table('User')->field('imei')->where($map)->find('array');
        $registration_ids  = array();

        if ($list) {
            foreach ($list as $val) {
                $registration_ids[] = $val['imei'];
            }
        } else {
            return false;
        }

        $count = count($registration_ids);
        //每次最多推1000条
        $queryLimit = 1000;
        if ($count > $queryLimit) {
            //按执行限定，分批执行
            $queryTimes = ceil($count / $queryLimit);
            for ($i = 1; $i <= $queryTimes; $i++) {
                $curValue = array_slice($registration_ids, ($i - 1) * $queryLimit, $queryLimit);
                $this->sendByRegIdExe($curValue, $title, $content, $jumpParam);
            }
        } else if ($count > 0) {
            $this->sendByRegIdExe($registration_ids, $title, $content, $jumpParam);
        }
    }

    /**
     * [按注册ID推送]
     * @date   2016-12-16T17:44:53+0800
     * @author ChenMingjiang
     * @param  array                    $registration_ids      注册ID
     * @param  string                   $title                 推送标题
     * @param  string                   $content               推送内容
     * @param  array                    $jumpParam             跳转参数
     * @param  array                    $avatar                头像/图标
     * @return boolean
     */
    private function sendByRegIdExe($registration_ids, $title, $content, $jumpParam, $avatar = 'http://static.chayu.com/app/2.2.0/images/logo_freight.png')
    {
        $pusher = $this->client->push();
        $pusher->setPlatform('all');
        $pusher->addAlias($registration_ids);
        $pusher->options(['apns_production' => true]); //true 苹果如果是正式环境需要增加这个参数
        //$pusher->addRegistrationId($registration_ids);
        $pusher->iosNotification($content, array(
            'sound'  => 'sound.caf',
            "style"  => 1, // 1,2,3
            // "big_pic_path" => $avatar,//当style=3时
            // 'badge' => '+1',
            // 'content-available' => true,
            // 'mutable-content' => true,
            // 'category' => 'jiguang',
            'extras' => array(
                'jump_param' => $jumpParam,
            ),
        ));
        $pusher->androidNotification($content, array(
            'title'  => $title,
            // 'build_id' => 3,
            'extras' => array(
                'jump_param' => $jumpParam,
            ),
        ));
        try {
            $result = $pusher->send();
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            //print $e;die;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            //print $e;die;
        }
        return true;
    }
    /**
     * 关闭推送功能
     * @date   2017-02-06T09:57:57+0800
     * @author ChenMingjiang
     * @param  int                    $uid      会员ID
     * @param  int                    $status   是否推送 （0、不推送   1、推送）
     * @return Boolean
     */
    public function close($uid, $status)
    {
        if (!in_array($status, array(0, 1))) {
            return false;
        }
        M('Seller')->where(array('uid' => $uid))->save(array('is_push' => $status));
        return true;
    }
}
