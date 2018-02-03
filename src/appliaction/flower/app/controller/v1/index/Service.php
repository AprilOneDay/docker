<?php
/**
 * 服务模块
 */
namespace app\flower\app\controller\v1\index;

use app\app\controller;
use app\flower\app\controller\v1\Init;
use denha\Start;

class Service extends Init
{

    /** 栏目分类 */
    public function menus()
    {
        $parentid = get('parentid', 'intval', 0);

        $map['web_type'] = 5;
        $map['parentid'] = $parentid;

        $field = 'name,bname';
        if ($this->lg != 'zh') {
            $field = "name_{$this->lg},bname_{$this->lg}";
        }
        $field .= ",id,thumb,is_show,parentid";

        $list = table('Column')->where($map)->field($field)->find('array');
        foreach ($list as $key => $value) {
            $list[$key]['finally_name']  = dao('Article')->getLgValue($value, 'name', $this->lg);
            $list[$key]['finally_bname'] = dao('Article')->getLgValue($value, 'bname', $this->lg);
            $list[$key]['thumb']         = $this->appImg($value['thumb'], 'column');
        }

        $data['list'] = (array) $list;

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 列表内容
     * @date   2017-12-04T10:46:16+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $columnId = get('cid', 'intval', 0);
        $keyword  = get('keyword', 'text', '');

        if (!$columnId) {
            $this->appReturn(array('stauts' => false, 'msg' => '参数错误'));
        }

        $map['column_id'] = $columnId;

        if ($keyword) {
            $field            = $this->lg != 'zh' ? 'title_' . $this->lg : 'title';
            $map[$field]      = array('instr', $keyword);
            $map['column_id'] = array('not in', '61,62,63,64,65');
        }

        $field = 'title,description';
        if ($this->lg != 'zh') {
            $field = "title_{$this->lg},description_{$this->lg}";
        }
        $field .= ",id,thumb,video,created";

        $data = dao('Article')->getList($map, $field, 1, $pageSize, $pageNo);

        $data['list'] = $data['list'] ? $data['list'] : array();

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['thumb'] = $this->appImg($value['thumb'], 'article');
            $data['list'][$key]['video'] = $value['video'] ? Start::$config['h5Url'] . $value['video'] : '';

            $data['list'][$key]['finally_title']       = dao('Article')->getLgValue($value, 'title', $this->lg);
            $data['list'][$key]['finally_description'] = dao('Article')->getLgValue($value, 'description', $this->lg);

            //获取栏目学习状态
            $status = 0;
            if (!$this->uid) {
                $status = 0;
            } else {
                $map              = array();
                $map['uid']       = $this->uid;
                $map['column_id'] = $value['id'];
                $status           = (int) table('ChdUser')->where($map)->field('status')->find('one');
            }
            $data['list'][$key]['user_status'] = $status;
        }

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 列表内容
     * @date   2017-12-04T10:46:16+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function lists_5()
    {
        $pageNo   = get('pageNo', 'intval', 1);
        $pageSize = get('pageSize', 'intval', 10);
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $columnId = get('cid', 'intval', 0);
        $keyword  = get('keyword', 'text', '');

        if (!$columnId) {
            $this->appReturn(array('stauts' => false, 'msg' => '参数错误'));
        }

        $map['column_id'] = $columnId;

        if ($keyword) {
            $field            = $this->lg != 'zh' ? 'title_' . $this->lg : 'title';
            $map[$field]      = array('instr', $keyword);
            $map['column_id'] = array('not in', '61,62,63,64,65');
        }

        $field = 'title,description,address';
        if ($this->lg != 'zh') {
            $field = "title_{$this->lg},description_{$this->lg},address_{$this->lg}";
        }
        $field .= ",id,created,lng,lat";

        $data = dao('Article')->getList($map, $field, 5, $pageSize, $pageNo);

        $data['list'] = $data['list'] ? $data['list'] : array();

        foreach ($data['list'] as $key => $value) {

            $point = baiduToTenxun($value['lat'], $value['lng']);

            $data['list'][$key]['lat'] = $point['lat'];
            $data['list'][$key]['lng'] = $point['lng'];

            $data['list'][$key]['finally_title']       = dao('Article')->getLgValue($value, 'title', $this->lg);
            $data['list'][$key]['finally_description'] = dao('Article')->getLgValue($value, 'description', $this->lg);
            $data['list'][$key]['finally_address']     = dao('Article')->getLgValue($value, 'address', $this->lg);

            //获取栏目学习状态
            $status = 0;
            if (!$this->uid) {
                $status = 0;
            } else {
                $map              = array();
                $map['uid']       = $this->uid;
                $map['column_id'] = $value['id'];
                $status           = (int) table('ChdUser')->where($map)->field('status')->find('one');
            }
            $data['list'][$key]['user_status'] = $status;
        }

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

    /**
     * 单页内容
     * @date   2017-12-04T10:46:07+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function detail()
    {
        $columnId = get('cid', 'intval', 0);
        $id       = get('id', 'intval', 0);

        if ($columnId) {
            $map['column_id'] = $columnId;
        }

        if ($id) {
            $map['id'] = $id;
        }

        $field = 'content,title,description';
        if ($this->lg != 'zh') {
            $field = "title_{$this->lg},content_{$this->lg},description_{$this->lg}";
        }
        $field .= ',created,column_id,id,btitle';

        $data = dao('Article')->getRowContent($map, $field, 1);

        $map              = array();
        $map['parentid']  = 58;
        $map['is_show']   = 1;
        $checkColumnArray = table('Column')->where($map)->field('id')->find('one', true);

        if (in_array($data['column_id'], $checkColumnArray)) {
            if (!$this->uid) {
                $this->appReturn(array('status' => false, 'msg' => '请登录', 'code' => 501));
            }

            //增加记录浏览记录
            $dataLog               = array();
            $dataLog['uid']        = $this->uid;
            $dataLog['article_id'] = $data['id'];
            $dataLog['column_id']  = $data['column_id'];
            $result                = table('ChdLog')->add($dataLog);

            //获取用户浏览当前栏目下的文章ID
            $map              = array();
            $map['column_id'] = $data['column_id'];
            $map['uid']       = $this->uid;
            $userLog          = table('ChdLog')->where($map)->field('article_id')->find('one', true);

            //检测是否浏览完所有文章
            $map               = array();
            $map['column_id']  = $data['column_id'];
            $map['is_review']  = 1;
            $map['is_show']    = 1;
            $checkArticleArray = table('Article')->where($map)->field('id')->find('one', true);
            $status            = !array_diff($userLog, $checkArticleArray) ? 2 : 1;

            //记录课件学习状态
            $map              = array();
            $map['uid']       = $this->uid;
            $map['column_id'] = $data['column_id'];
            $chdId            = table('ChdUser')->where($map)->field('id')->find('one');

            $dataLog              = array();
            $dataLog['uid']       = $this->uid;
            $dataLog['column_id'] = $data['column_id'];
            if ($chdId) {
                $result = table('ChdLog')->where('id', $chdId)->save($dataLog);
            } else {
                $result = table('ChdLog')->add($dataLog);
            }

        }

        $data['finally_content']     = dao('Article')->getLgValue($data, 'content', $this->lg);
        $data['finally_title']       = dao('Article')->getLgValue($data, 'title', $this->lg);
        $data['finally_description'] = dao('Article')->getLgValue($data, 'description', $this->lg);

        $data['content'] = str_replace('src="', 'src="' . Start::$config['h5Url'], $data['content']);
        $this->assign('data', $data);

        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));
    }

}
