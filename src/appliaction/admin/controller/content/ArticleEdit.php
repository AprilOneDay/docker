<?php
/**
 * 文章内容管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Log;

class ArticleEdit extends Init
{
    //模型ID
    private static $modelId;
    //当前模型数据库
    private static $dataTable;
    //模板视图地址
    private static $tpl;
    //模型数据库类型
    public function edit()
    {
        $columnId = get('column_id', 'intval', 0);
        $modelId  = table('Column')->where('id', $columnId)->field('model_id')->find('one');
        $isEdit   = table('Column')->where('parentid', $columnId)->field('id')->find('one');

        $modelTable = getVar('model_table', 'admin.article');

        self::$modelId   = $modelId;
        self::$dataTable = $modelTable[self::$modelId];
        self::$tpl       = 'article_edit/edit_' . $modelId;

        if (!self::$dataTable) {
            Log::error('模型库尚未创建....');
        }

        if ($isEdit) {
            Log::error('存在子级栏目,不可创建文章');
        }

        switch (self::$modelId) {
            case '1':
                $this->edit_1();
                break;
            case '2':
                $this->edit_2();
                break;
            case '3':
                $this->edit_3();
                break;
            case '4':
                $this->edit_4();
                break;
            case '5':
                $this->edit_5();
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
        $data['is_show']      = post('is_show', 'intval', 1);
        $data['is_review']    = post('is_review', 'intval', 1);
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
            $data['created'] = $data['publish_time'] = TIME;
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

            //开启事务
            table('Article')->startTrans();
            $dataId = $this->defaults(); //保存主表

            //编辑
            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $dataId     = $id;
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
            }

            if (!$resultData) {
                table('Article')->rollback();
                $this->ajaxReturn(array('status' => false, 'msg' => '操作失败,请重新尝试'));
            }

            table('Article')->commit();
            $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);

            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'admin.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show(self::$tpl);
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

            if (!$data['teacher_uid']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请关联老师'));
            }

            //开启事务
            table('Article')->startTrans();
            $dataId = $this->defaults(); //保存主表

            //编辑
            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $dataId     = $id;
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
            }

            if (!$resultData) {
                table('Article')->rollback();
                $this->ajaxReturn(array('status' => false, 'msg' => '操作失败,请重新尝试'));
            }

            table('Article')->commit();
            $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);
            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'admin.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
                'teacherList'    => table('User')->where(array('type' => 2, 'status' => 1))->field('id,real_name')->find('array'),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show(self::$tpl);
        }
    }

    //课程
    public function edit_3()
    {
        $id       = get('id', 'intval', 0);
        $columnId = get('column_id', 'intval', 0);

        if (IS_POST) {

            $data                    = post('info');
            $data['video_url']       = post('video_url', 'text', '');
            $data['start_time']      = post('info.start_time', 'time');
            $data['end_time']        = post('info.end_time', 'time');
            $data['characteristics'] = implode(',', $data['characteristics']);

            if (!$data['sale_price']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '请输入售卖价格'));
            }

            if ($data['sale_price'] <= $data['dis_price']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '活动价不可高于售卖价'));
            }

            /*if (!$data['teacher_uid']) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请关联老师'));
            }*/

            if ($data['start_time'] > $data['end_time']) {
                $this->ajaxReturn(array('status' => false, 'msg' => '开始时间大于结束时间'));
            }

            //开启事务
            table('Article')->startTrans();

            $dataId = $this->defaults(); //保存主表
            //编辑
            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $dataId     = $id;
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
            }

            if (!$resultData) {
                table('Article')->rollback();
                $this->ajaxReturn(array('status' => false, 'msg' => '操作失败,请重新尝试'));
            }

            //保存课程表
            $schedule['startSyllabus'] = post('start_syllabus');
            $schedule['endSyllabus']   = post('end_syllabus');
            $schedule['credit']        = post('credit');
            $schedule['teacher_hour']  = post('teacher_hour');
            if (array_filter($schedule['startSyllabus'])) {
                //删除 课程表
                table('Article' . self::$dataTable . 'Schedule')->where('id', $dataId)->delete();

                //检测课程时间是否满足规则
                if (count($schedule['startSyllabus']) != count(array_unique($schedule['startSyllabus']))) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '课程开始时间存在相同时间，请修改'));
                }

                if (count($schedule['endSyllabus']) != count(array_unique($schedule['endSyllabus']))) {
                    $this->ajaxReturn(array('status' => false, 'msg' => '课程结束时间存在相同时间，请修改'));
                }

                foreach ($schedule['startSyllabus'] as $key => $value) {

                    if (strtotime($value) >= strtotime($schedule['endSyllabus'][$key])) {
                        $this->ajaxReturn(array('status' => false, 'msg' => '课程时间【' . $value . '】大于等于课程结束时间'));
                    }

                    if (date('Y-m-d', strtotime($value)) !== date('Y-m-d', strtotime($schedule['endSyllabus'][$key]))) {
                        $this->ajaxReturn(array('status' => false, 'msg' => '课程时间【' . $value . '】超过一天了'));
                    }
                }

                foreach ($schedule['startSyllabus'] as $key => $value) {
                    if ($value) {
                        $data = array();
                        $data = array(
                            'id'           => $dataId,
                            'start_time'   => strtotime($value),
                            'end_time'     => strtotime($schedule['endSyllabus'][$key]),
                            'credit'       => $schedule['credit'][$key],
                            'teacher_hour' => $schedule['teacher_hour'][$key],
                        );
                        $resultSchedule = table('Article' . self::$dataTable . 'Schedule')->add($data);
                        if (!$resultSchedule) {
                            table('Article')->rollback();
                            $this->ajaxReturn(array('status' => false, 'msg' => '课程信息保存失败,请重新尝试'));
                        }
                    }
                }
            }

            //创建直播间
            dao('YunwuRoom')->created(post('title', 'text', ''));

            table('Article')->commit();
            $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);

                //创建直播间
                dao('YunwuRoom')->created($rs['title']);
                //获取课程信息
                $schedule = table('Article' . self::$dataTable . 'Schedule')->where('id', $id)->order('start_time asc')->find('array');

            } else {
                $rs = array(
                    'is_show'      => 1,
                    'is_recommend' => 0,
                    'created'      => date('Y-m-d', TIME),
                    'model_id'     => self::$modelId,
                    'sale_price'   => 0.00,
                    'dis_price'    => 0.00,
                    'credit'       => 0,
                    'num'          => 0,
                    'base_orders'  => 0,
                    'class_type'   => 1,
                );
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'teacherHourType' => dao('Category')->getList(74),
                'featuredCopy'    => dao('Category')->getList(78),
                'columnListCopy'  => dao('Column', 'admin')->columnList(),
                'schedule'        => isset($schedule) ? $schedule : array(),
                'teacherList'     => table('User')->where(array('type' => 2, 'status' => 1))->field('id,real_name')->find('array'),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show(self::$tpl);
        }
    }

    //下载
    public function edit_4()
    {
        $id       = get('id', 'intval', 0);
        $columnId = get('column_id', 'intval', 0);

        if (IS_POST) {
            $data = post('info');

            $data['down_url'] = post('down_url', 'text', '');

            //开启事务
            table('Article')->startTrans();
            $dataId = $this->defaults(); //保存主表

            //编辑
            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $dataId     = $id;
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
            }

            if (!$resultData) {
                table('Article')->rollback();
                $this->ajaxReturn(array('status' => false, 'msg' => '操作失败,请重新尝试'));
            }

            table('Article')->commit();
            $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);
            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'admin.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
                'teacherList'    => table('User')->where(array('type' => 2, 'status' => 1))->field('id,real_name')->find('array'),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show(self::$tpl);
        }
    }

    //店铺
    public function edit_5()
    {
        $id       = get('id', 'intval', 0);
        $columnId = get('column_id', 'intval', 0);

        if (IS_POST) {
            $data = post('info');

            //开启事务
            table('Article')->startTrans();
            $dataId = $this->defaults(); //保存主表

            //编辑
            if ($dataId && $id) {
                $resultData = table('Article' . self::$dataTable)->where(array('id' => $id))->save($data);
                $dataId     = $id;
            } else {
                $data['id'] = $dataId;
                $resultData = table('Article' . self::$dataTable)->add($data);
            }

            if (!$resultData) {
                table('Article')->rollback();
                $this->ajaxReturn(array('status' => false, 'msg' => '操作失败,请重新尝试'));
            }

            table('Article')->commit();
            $this->ajaxReturn(array('status' => true, 'msg' => '操作成功'));

        } else {
            if ($id) {
                $rs = $this->getEditConent($id);
            } else {
                $rs              = array('is_show' => 1, 'is_recommend' => 0, 'created' => date('Y-m-d', TIME), 'model_id' => self::$modelId);
                $rs['column_id'] = $columnId;
            }

            $other = array(
                'tag'            => getVar('tags', 'admin.article'),
                'columnListCopy' => dao('Column', 'admin')->columnList(),
                'teacherList'    => table('User')->where(array('type' => 2, 'status' => 1))->field('id,real_name')->find('array'),
            );

            $this->assign('data', $rs);
            $this->assign('other', $other);
            $this->show(self::$tpl);
        }
    }

    public function delArticle()
    {
        $modelTable = getVar('model_table', 'admin.article');
        $id         = post('id', 'intval', 0);
        if (!$id) {
            $this->ajaxReturn(array('status' => false, 'msg' => '参数错误'));
        }

        $modelId = table('Article')->where('id', $id)->field('id')->find();
        if (!$modelId) {
            $this->ajaxReturn(array('status' => false, 'msg' => '信息不存在'));
        }

        $result = table('Article')->where('id', $id)->delete();
        if (!$result) {
            $this->ajaxReturn(array('status' => false, 'msg' => '删除失败了'));
        }

        table('Article' . $modelTable[self::$modelId])->where('id', $id)->delete();

        $this->ajaxReturn(array('status' => true, 'msg' => '删除成功'));
    }

    private function getEditConent($id = 0)
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
            Log::error('附属表异常');
        }

        $rs['created'] = date('Y-m-d', $rs['created']);

        return $rs;
    }
}
