<?php
/**
 * 博客内容管理
 */
namespace app\admin\controller\content;

use denha;

class Blog extends \app\admin\controller\Init
{

    public function index()
    {
        $param = get('param', 'text');

        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 25);

        $param['field'] ?: $param['field'] = 'title';

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['del_status'] = 0;

        if ($param['tag']) {
            $map['tag'] = $param['tag'];
        }

        if ($param['is_recommend'] != '') {
            $map['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_show'] != '') {
            $map['is_show'] = $param['is_show'];
        }

        if ($param['field'] && $param['keyword']) {
            if ($param['field'] == 'title') {
                $map['title'] = array('like', '%' . $param['keyword'] . '%');
            }
        }
        $list  = table('Article')->where($map)->limit($offer, $pageSize)->order('id desc')->find('array');
        $total = table('Article')->where($map)->count();
        $page  = new denha\Pages($total, $pageNo, $pageSize, url('', $param));

        $other = array(
            'tag'             => getVar('tags', 'admin.article'),
            'isShowCopy'      => array(0 => '隐藏', 1 => '显示'),
            'isRecommendCopy' => array(1 => '推荐', 0 => '不推荐'),
        );

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign('pages', $page->loadConsole());
        $this->assign('other', $other);

        $this->show();
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);
        if (IS_POST) {

            $data['title']       = post('title', 'text', '');
            $data['description'] = post('description', 'text', '');
            $data['thumb']       = post('thumb', 'img', '');
            $data['origin']      = post('origin', 'text', '');

            $data['tag']          = max(post('tag', 'intval', 0), 1);
            $data['is_show']      = post('is_show', 'intval', '');
            $data['is_recommend'] = post('is_recommend', 'intval', '');

            $dataContent['content'] = post('content', 'text', '');

            if (!$data['title']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请填写标题']);
            }

            if (!$dataContent['content']) {
                $this->ajaxReturn(['status' => false, 'msg' => '请输入内容']);
            }

            if (!$data['description']) {
                $data['description'] = mb_substr(str_replace(' ', '', strip_tags($dataContent['content'])), 0, 255, 'UTF-8');
            }

            if ($id) {
                $result = table('Article')->where(array('id' => $id))->save($data);
                if ($result) {
                    $resultData = table('ArticleBlog')->where(array('id' => $id))->save($dataContent);
                    dao('BaiduSpider')->pull($id); //百度主动推送
                    $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('status' => false, 'msg' => '修改失败'));
                }
            } else {
                $data['created'] = TIME;
                $result          = table('Article')->add($data);
                if ($result) {
                    $dataContent['id'] = $result;
                    $resultData        = table('ArticleBlog')->add($dataContent);
                    dao('BaiduSpider')->pull($result); //百度主动推送
                    $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('status' => false, 'msg' => '添加失败'));
                }
            }

        } else {
            if ($id) {
                $article     = table('Article')->tableName();
                $articleData = table('ArticleBlog')->tableName();

                $map[$article . '.id'] = $id;

                $rs = table('Article')->join($articleData, "$articleData.id = $article.id", 'left')->where($map)->find();

                $rs['created'] = date('Y-m-d', $rs['created']);
                $rs['thumb']   = json_encode((array) imgUrl($rs['thumb'], 'blog'));

            } else {
                $rs = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME));
            }

            $other = array(
                'tag' => getVar('tags', 'admin.article'),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show();
        }
    }

    /**
     * 手动推送 http://admin.denha.loc/content/blog/send_baidu_pull?id=1
     * @date   2017-09-30T15:07:43+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function sendBaiduPull()
    {
        $id = get('id', 'intval', 0);

        $result = dao('BaiduSpider')->pull($id);

        $this->ajaxReturn($result);
    }
}
