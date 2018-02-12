<?php
/**
 * 账本模块
 */
namespace app\flower\app\controller\v1\user;

use app\app\controller;
use app\flower\app\controller\v1\WeixinSmallInit;

class Bill extends WeixinSmallInit
{
    public function __construct()
    {
        parent::__construct();
        //检测用户登录权限
        $this->checkIndividual('1');
    }

    /** 账单流水 */
    public function lists()
    {

        $moneyAll   = array(1 => 0, 2 => 0);
        $moneyMonth = array(1 => 0, 2 => 0);

        //总收益
        $map              = array();
        $map['family_sn'] = $this->familySn;

        $moneyList = table('BillLog')->where($map)->group('type')->field('SUM(money) as money,type')->find('array');
        if ($moneyList) {
            foreach ($moneyList as $key => $value) {
                $moneyAll[$value['type']] = $value['money'];
            }
        }

        //本月收益
        $beginThismonth   = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endThismonth     = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        $map              = array();
        $map['family_sn'] = $this->familySn;
        $map['created']   = array('between', $beginThismonth, $endThismonth);

        $list = table('BillLog')->where($map)->order('created desc')->find('array');

        if ($list) {
            foreach ($list as $key => $value) {
                $user = table('UserThirdParty')->where('id', $value['uid'])->field('avatar,nickname')->find();

                $list[$key]['title'] = dao('Category')->getName($value['sign']);
                $list[$key]['user']  = $user;
                $list[$key]['time']  = date('Y-m-d', $value['created']);
                $moneyMonth[$value['type']] += $value['money'];

                unset($list[$key]['family_sn'], $list[$key]['uid']);
            }
        }

        $data['list']       = $list ? $list : array();
        $data['allMoney']   = number_format($moneyAll[1] - $moneyAll[2], 2);
        $data['trendMoney'] = dao('Number')->price(abs($moneyMonth[1] - $moneyMonth[2]));
        $data['trendInco']  = $moneyMonth[1] - $moneyMonth[2] > 0 ? 1 : 0;

        foreach ($moneyAll as $key => $value) {
            $moneyAll[$key]   = dao('Number')->price($value);
            $moneyMonth[$key] = dao('Number')->price($moneyMonth[$key]);
        }

        $data['moneyMonth'] = $moneyMonth;
        $data['moneyAll']   = $moneyAll;

        $this->appReturn(array('data' => $data));
    }

    /** 详情 */
    public function detail()
    {
        $id = get('id', 'intval', 0);

        $map              = array();
        $map['id']        = $id;
        $map['family_sn'] = $this->familySn;

        $data = table('BillLog')->where($map)->find();

        $user = table('UserThirdParty')->where('id', $data['uid'])->field('avatar,nickname')->find();

        $data['title']  = dao('Category')->getName($data['sign']);
        $data['user']   = $user;
        $data['time']   = date('Y-m-d', $data['created']);
        $data['is_del'] = $this->uid == $data['uid'] ? 1 : 0;

        unset($data['family_sn'], $data['uid']);

        $this->appReturn(array('data' => $data));
    }

    public function add()
    {
        $money  = get('money', 'float', 0.00);
        $sign   = get('sign', 'text', '');
        $time   = get('time', 'time', '');
        $remark = get('remark', 'text', '');

        if (!in_array($type, array(1055, 1056))) {
            $this->appReturn(array('status' => false, 'msg' => '参数错误'));
        }

        if (!$sign) {
            $this->appReturn(array('status' => false, 'msg' => '请选择收支项目'));
        }

        if (!$money || !is_numeric($money)) {
            $this->appReturn(array('status' => false, 'msg' => '请输入金额'));
        }

        $data['family_sn'] = $this->familySn;
        $data['uid']       = $this->uid;
        $data['money']     = abs($money);
        $data['sign']      = $sign;
        $data['type']      = $money >= 0 ? 1 : 2;
        $data['created']   = $time ? $time : TIME;
        $data['remark']    = $remark;

        $result = table('BillLog')->add($data);
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '保存失败'));
        }

        $this->appReturn(array('status' => true, 'msg' => '保存成功'));
    }

    /** 删除 */
    public function del()
    {
        $id = get('id', 'intval', 0);

        $billLog = table('BillLog')->where('id', $id)->field('id,uid')->find();
        if (!$billLog) {
            $this->appReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        if ($billLog['uid'] != $this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '操作异常'));
        }

        $result = table('BillLog')->where('id', $id)->delete();
        if (!$result) {
            $this->appReturn(array('status' => false, 'msg' => '删除失败,请反馈给管理员'));
        }

        $this->appReturn(array('status' => true, 'msg' => '删除成功'));
    }
}
