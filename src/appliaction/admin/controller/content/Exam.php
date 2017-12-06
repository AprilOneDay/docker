<?php
/**
 * 试卷模块管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class Exam extends Init
{
    public function lists()
    {

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map               = array();
        $map['del_status'] = 0;

        $list  = table('ExamList')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('ExamList')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $map               = array();
            $map['exam_id']    = $value['id'];
            $map['del_status'] = 0;

            $list[$key]['score'] = (int) table('ExamData')->where($map)->field('SUM(score) AS score')->find('one');
        }

        $other = array(
            'statusCopy' => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }
    public function editPost()
    {
        $id = get('id', 'intval', 0);

        $data = post('all');

        if (!$id) {
            $data['created'] = TIME;
            $result          = table('ExamList')->add($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '添加失败'));
            }
        } else {
            $result = table('ExamList')->where('id', $id)->save($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '修改失败'));
            }
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));
    }

    /** 编辑考卷 */
    public function edit()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $data = table('ExamList')->where('id', $id)->find();
        } else {
            $data = array('sort' => 0, 'status' => 1, 'exam_time' => 0);
        }

        $this->assign('data', $data);
        $this->show();
    }

    /** 题目列表 */
    public function QuestionList()
    {
        $id       = get('id', 'intval', 0);
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map = array();

        $list  = table('ExamData')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('ExamData')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('', $param));

        foreach ($list as $key => $value) {
            $list[$key]['thumb'] = 'http://qr.liantu.com/api.php?text=' . URL . $value['apk_url'] . '&w=200&h=200';
        }

        $other = array(
            'typeCopy'   => getVar('question_type', 'admin.exam'),
            'statusCopy' => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    /** 编辑考题 */
    public function editQuestion()
    {
        $id = get('id', 'intval', 0);
        if ($id) {
            $data            = table('ExamData')->where('id', $id)->find();
            $data['content'] = json_decode($data['content'], true);
        } else {
            $data = array('sort' => 0, 'status' => 1, 'score' => 0);
        }

        $other = array(
            'typeCopy'   => getVar('question_type', 'admin.exam'),
            'statusCopy' => array(0 => '关闭', 1 => '开启'),
        );

        $this->assign('other', $other);
        $this->assign('data', $data);
        $this->show();
    }

    /** 编辑考题操作 */
    public function editQuestionPost()
    {
        $id              = get('id', 'intval', 0);
        $data['exam_id'] = get('exam_id', 'intval', 0);

        $data['title'] = post('title', 'text', '');
        $data['type']  = post('type', 'intval', 0);
        $data['score'] = post('score', 'intval', 0);
        $data['sort']  = post('sort', 'intval', 0);

        $other = post('other');

        if (!$data['exam_id']) {
            $this->appReturn(array('status' => false, 'msg' => '试卷参数错误'));
        }

        if (!$data['title']) {
            $this->appReturn(array('status' => false, 'msg' => '请输入考题'));
        }

        if (!$data['type']) {
            $this->appReturn(array('status' => false, 'msg' => '请选择答案类型'));
        }

        $tmpContent  = null;
        $isAnswerNum = 0;
        foreach ($other['answer'] as $key => $value) {
            if ($value) {
                $isAnswerNum  = $other['is_answer'][$key] ? $isAnswerNum + 1 : $isAnswerNum;
                $tmpContent[] = array('answer' => $value, 'is_answer' => $other['is_answer'][$key]);
            }
        }
        $data['content'] = json_encode($tmpContent);

        if (($data['type'] == 1 || $data['type'] == 2)) {
            if (!$tmpContent) {
                $this->appReturn(array('status' => false, 'msg' => '请输入答案'));
            }

            if (!$isAnswerNum) {
                $this->appReturn(array('status' => false, 'msg' => '请勾选题目的正确答案'));
            }

            if ($data['type'] == 1 && $isAnswerNum > 1) {
                $this->appReturn(array('status' => false, 'msg' => '【单选模式】只能选择一个正确答案'));
            }
        }

        if (!$id) {
            $result = table('ExamData')->add($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '添加失败'));
            }
        } else {
            $result = table('ExamData')->where('id', $id)->save($data);
            if (!$result) {
                $this->appReturn(array('status' => false, 'msg' => '修改失败'));
            }
        }

        $this->appReturn(array('status' => true, 'msg' => '操作成功'));

    }
}
