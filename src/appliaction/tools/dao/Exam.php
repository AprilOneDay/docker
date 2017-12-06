<?php
/**
 * 考试相关处理
 */
namespace app\tools\dao;

class Exam
{
    /**
     * 记录学生开考信息
     * @date   2017-11-30T11:02:24+0800
     * @author ChenMingjiang
     * @param  integer                  $uid      [用户uid]
     * @param  integer                  $examId   [考卷id]
     * @param  boolean                  $isRepeat [false 不可重复录入 true可重复录入]
     * @return [type]                             [description]
     */
    public function userExamStart($uid = 0, $examId = 0, $isRepeat = false)
    {
        $data['uid']        = $uid;
        $data['exam_id']    = $examId;
        $data['start_time'] = TIME;

        //如果存在记录 并且结束时间等于0 则更改开始时间
        $map['uid']     = $uid;
        $map['exam_id'] = $examId;
        $examLog        = table('UserExamLog')->where($map)->find();
        //如果存在考试结束记录 则不再记录
        if ($examLog && $examLog['end_time']) {
            return false;
        }
        //如果不存在结束记录则更新开始记录
        elseif ($examLog && !$examLog['end_time']) {
            $result = table('UserExamLog')->where('id', $examLog['id'])->save('start_time', TIME);
        }
        //反之 则插入新记录
        else {
            $result = table('UserExamLog')->add($data);
        }

        if (!$result) {
            return false;
        }

        return ture;
    }

    /**
     * 记录学生考试成绩
     * @date   2017-11-30T11:12:41+0800
     * @author ChenMingjiang
     * @param  integer                  $uid    [description]
     * @param  integer                  $examId [description]
     * @param  array                    $param  [description]
     * @return [type]                           [description]
     */
    public function userExamEnd($uid = 0, $examId = 0, $param = array())
    {

        $map            = array();
        $map['uid']     = $uid;
        $map['exam_id'] = $examId;

        //获取最近的考试记录
        $examLog = table('UserExamLog')->where($map)->order('id desc')->find();
        if (!$examLog) {
            return array('status' => false, 'msg' => '暂无相关信息');
        }

        if ($examLog['end_time']) {
            return array('status' => false, 'msg' => '已经答过该试卷了');
        }

        //获取考试题目
        $examData = table('ExamData')->where('exam_id', $examId)->find('array');
        foreach ($examData as $key => $value) {
            $content = json_decode($value['content'], true);
            $answer  = array();
            foreach ($content as $k => $v) {
                $answer[$k + 1] = $v['is_answer'] ? 1 : 0;
            }

            $examDataList[$value['id']]['score']  = $value['score'];
            $examDataList[$value['id']]['answer'] = $answer;
        }

        //匹配答案
        $score = 0; //成绩
        foreach ($examDataList as $key => $value) {
            //答案相等 成绩增加
            if (!array_diff($value['answer'], $param[$key])) {
                $score += $value['score'];
            }
        }

        $data['end_time'] = TIME;
        $data['score']    = $score;
        $data['answer']   = json_encode($param);

        $result = table('UserExamLog')->where('id', $examLog['id'])->save($data);
        if (!$result) {
            return array('status' => false, 'msg' => '答案保存失败，请联系管理员');
        }

        return array('status' => true, 'msg' => '考试完成');
    }
}
