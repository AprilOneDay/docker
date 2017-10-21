<?php
/**
 * 文章内容管理
 */
namespace app\admin\controller\content;

use denha;

class ArticleEdit extends \app\admin\controller\Init
{
    //模型ID
    private static $modelId;
    //当前模型数据库
    private static $dataTable;
    //模型数据库类型
    public function edit()
    {
        $columnId = get('column_id', 'intval', 0);
        $modelId  = table('Column')->where('id', $columnId)->field('model_id')->find('one');
        $isEdit   = table('Column')->where('parentid', $columnId)->field('id')->find('one');

        $modelTable = getVar('model_table', 'admin.article');

        self::$modelId   = $modelId;
        self::$dataTable = $modelTable[self::$modelId];

        if ($isEdit) {
            denha\Log::error('存在子级栏目,不可创建文章');
        }

        switch (self::$modelId) {
            case '1':
                $this->edit_1();
                break;
            case '2':
                $this->edit_2();
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

        $data['title']          = post('title', 'text', '');
        $data['btitle']         = post('btitle', 'text', '');
        $data['description']    = post('description', 'text', '');
        $data['description_en'] = post('description_en', 'text', '');
        $data['thumb']          = post('thumb', 'img', '');
        $data['origin']         = post('origin', 'text', '');

        $data['tag']          = max(post('tag', 'intval', 0), 1);
        $data['is_review']    = post('is_show', 'intval', 1);
        $data['is_recommend'] = post('is_recommend', 'intval', 1);
        $data['column_id']    = post('column_id', 'intval', 1);
        $data['uid']          = post('uid', 'intval', '');

        $data['model_id'] = self::$modelId;

        //var_dump($data);die;

        $modelId = table('Column')->where('id', $data['column_id'])->field('model_id')->find('one');
        if ($modelId != self::$modelId) {
            $this->ajaxReturn(['status' => false, 'msg' => '栏目模型不一致,不可保存']);
        }

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
                return $result;
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
            $data['content']    = post('content', 'text', '');
            $data['content_en'] = post('content_en', 'text', '');

            $dataId = $this->defaults(); //保存主表

            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
            }

        } else {
            if ($id) {

                $rs = $this->getEditConent($id);
            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'console.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show('article_edit/edit');
        }
    }

    //教师
    public function edit_2()
    {
        $id       = get('id', 'intval', 0);
        $columnId = get('column_id', 'intval', 0);

        if (IS_POST) {
            $data['teacher_uid'] = post('teacher_uid', 'intval', 0);

            $data['position']    = post('position', 'text', '');
            $data['position_en'] = post('position_en', 'text', '');

            $dataId = $this->defaults(); //保存主表

            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '修改成功'));
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
                $this->ajaxReturn(array('status' => true, 'msg' => '添加成功'));
            }

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);

            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'console.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show('article_edit/edit_2');
        }
    }

    public function getEditConent($id = 0)
    {
        if (!$id) {
            return '';
        }

        $article     = table('Article')->tableName();
        $articleData = table('Article' . self::$dataTable)->tableName();

        $map[$article . '.id']     = $id;
        $map[$articleData . '.id'] = $id;

        $rs = table('Article')->join($articleData)->where($map)->find();

        if (!$rs) {
            denha\Log::error('附属表异常');
        }

        $rs['created'] = date('Y-m-d', $rs['created']);
        $rs['thumb']   = json_encode((array) imgUrl($rs['thumb'], 'article'));

        return $rs;
    }
}
