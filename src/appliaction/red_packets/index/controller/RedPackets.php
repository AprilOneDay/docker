<?php
namespace app\red_packets\index\controller;

use denha;

class RedPackets extends denha\Controller
{
    /** 开始游戏 */
    public function index()
    {
        $state = get('state', 'intval', 0);
        $url   = dao('WeixinOauth')->getAuthorizeUrl(URL . url('index'), 'snsapi_userinfo', 1);
        if (!$state && !session('uid')) {
            header('Location:' . $url);
        }

        if (!session('uid')) {
            $user = dao('WeixinOauth')->getUserInfo();
            if ($user) {
                $this->checkUser($user);
            } else {
                header('Location:' . url('index'));
            }
        }

        var_dump(session('uid'));

        $this->show();
    }

    /** 游戏房间 */
    public function game()
    {
        if (!session('uid')) {
            die('授权失败');
        }

        //如果已经玩过三次 则跳转回首页
        if ($this->checkUserGameNum() >= 3) {
            header('Location:' . url('index'));
        }

        $this->show();
    }

    /** 排行榜 */
    public function ranking()
    {
        if (!session('uid')) {
            die('授权失败');
        }

        $map['uid'] = session('uid');
        //获取总共用积分
        $user['allIntegral'] = (int) table('UserRedPacketsLog')->where($map)->field('sum(integral) as integral')->find('one');
        //获取排名
        $map             = array();
        $map['integral'] = array('>', $user['allIntegral']);

        $rankingAll = table('UserRedPacketsLog')->count('distinct uid');

        //debug
        //echo table('UserRedPacketsLog')->getSql();
        //var_dump($rankingAll);
        //--end

        $ranking         = table('UserRedPacketsLog')->field('sum(integral) as integral')->having('SUM(integral) >' . $user['allIntegral'])->group('uid')->childSql(true)->find('array');
        $user['ranking'] = $user['allIntegral'] ? max((int) table('UserRedPacketsLog')->childSqlQuery($ranking)->count() + 1, 1) : $rankingAll + 1;

        $list = table('UserRedPacketsLog')->field('sum(integral) as integral,uid')->group('uid')->order('integral desc')->limit(0, 17)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['user'] = table('UserThirdParty')->where('id', $value['uid'])->field('nickname')->find();
        }

        //debug
        //print_r($user);
        //end

        $this->assign('user', $user);
        $this->assign('list', $list);

        $this->show();
    }

    /** 游戏结束记录分数 */
    public function userGameEnd()
    {
        $map['uid']     = session('uid');
        $map['_string'] = "end_time = start_time";

        $log = table('UserRedPacketsLog')->where($map)->order('start_time desc')->find();
        if (!$log) {
            $this->ajaxReturn(array('status' => flase, 'msg' => '游戏异常'));
        }

        if (TIME - $log['start_time'] >= 100) {
            table('UserRedPacketsLog')->where('id', $log['id'])->delete();
            $this->ajaxReturn(array('status' => flase, 'msg' => '请不要作弊哦!'));
        }

        $data['integral'] = post('integral', 0);
        $data['end_time'] = TIME;

        $result = table('UserRedPacketsLog')->where('id', $log['id'])->save($data);
        if (!$result) {
            $this->ajaxReturn(array('status' => flase, 'msg' => '游戏记录保存失败,请联系管理员'));
        }

        $this->ajaxReturn(array('status' => ture, 'msg' => '记录完成'));

    }

    /** 今日游戏次数 */
    public function todayGameNum()
    {
        $data = $this->checkUserGameNum();
        $this->ajaxReturn(array('status' => ture, 'msg' => '获取成功', 'data' => $data));
    }

    /** 游戏开始记录 */
    public function userGameStart()
    {
        $data['uid']        = session('uid');
        $data['start_time'] = TIME;
        $data['end_time']   = TIME;

        $result = table('UserRedPacketsLog')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('status' => flase, 'msg' => '游戏记录保存失败,请联系管理员'));
        }
        $this->ajaxReturn(array('status' => ture, 'msg' => '记录完成'));
    }

    /** 检测用户今日游戏次数 */
    private function checkUserGameNum()
    {

        $todaystart        = strtotime(date('Y-m-d' . '00:00:00', TIME)); //获取今天00:00
        $todayend          = strtotime(date('Y-m-d' . '00:00:00', TIME + 3600 * 24)); //获取今天24:00
        $map['start_time'] = array('between', $todaystart, $todayend);
        $map['uid']        = session('uid');

        $num = (int) table('UserRedPacketsLog')->where($map)->count();

        return $num;
    }

    private function checkUser($user)
    {
        $userThirdParty = table('UserThirdParty')->where('weixin_id', $user['openid'])->find();
        if ($userThirdParty) {
            session('uid', $userThirdParty['id']);
        } else {
            $data['weixin_id'] = $user['openid'];
            $data['nickname']  = $user['nickname'];
            $data['avatar']    = $user['headimgurl'];

            $result = table('UserThirdParty')->add($data);

            session('uid', $result);
        }
    }
}
