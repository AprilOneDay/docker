<?php
/**
 * 文章内容管理
 */
namespace app\admin\controller\content;

use denha;

class ArticleEdit extends \app\admin\controller\Init
{
    public function edit()
    {
        $columnId = get('column_id', 'intval', 0);
        $modelId  = table('Column')->where('id', $columnId)->field('model_id')->find('one');

        switch ($modelId) {
            case '1':
                $this->edit_1();
                break;
            case '2':
                # code...
                break;
            default:
                # code...
                break;
        }

    }

    //保存主表信息
    public function defaults()
    {
        $id = get('id', 'intval', 0);

        $data['title']       = post('title', 'text', '');
        $data['description'] = post('description', 'text', '');
        $data['thumb']       = post('thumb', 'img', '');
        $data['origin']      = post('origin', 'text', '');

        $data['tag']          = max(post('tag', 'intval', 0), 1);
        $data['is_review']    = post('is_show', 'intval', 1);
        $data['is_recommend'] = post('is_recommend', 'intval', 1);
        $data['model_id']     = post('model_id', 'intval', 1);

        if (!$data['title']) {
            $this->ajaxReturn(['status' => false, 'msg' => '请填写标题']);
        }

        //编辑
        if ($id) {
            $result = table('Article')->where(array('id' => $id))->save($data);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        }
        //添加
        else {
            $data['created'] = TIME;
            $result          = table('Article')->add($data);
            if ($result) {
                return $id;
            } else {
                return false;
            }
        }

    }

    //图文模型
    public function edit_1()
    {
        $id       = get('id', 'intval', 0);
        $columnId = get('column_id', 'intval', 0);
        if (IS_POST) {
            $data['content'] = post('content', 'text', '');

            $dataId = $this->defaults(); //保存主表

            if ($dataId && $id) {
                $resultData = table('ArticleData')->where(array('id' => $dataId))->save($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            } else {
                $resultData = table('ArticleData')->where(array('id' => $dataId))->add($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
            }

        } else {
            if ($id) {
                $article     = table('Article')->tableName();
                $articleData = table('ArticleData')->tableName();

                $map[$article . '.id']     = $id;
                $map[$articleData . '.id'] = $id;

                $rs = table('Article')->join($articleData, "$articleData.id = $article.id", 'left')->where($map)->find();
                if (!$rs) {
                    denha\Log::error('附属表异常');
                }

                $rs['created'] = date('Y-m-d', $rs['created']);
                $rs['thumb']   = json_encode((array) imgUrl($rs['thumb'], 'blog'));

            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => 1);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'console.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show();
        }
    }
}
