<?php
/**
 * 前台用户管理
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;
use denha\Pages;

class Feedback extends Init
{
    public function lists()
    {

        $pageNo = get('pageNo', 'intval', 0);

        $field   = get('field', 'text', '');
        $keyword = get('keyword', 'text', '');

        $param = get('param');

        $pageSize = 20;
        $offer    = max(($pageNo - 1), 0) * $pageSize;

        $param['field']    = 'id';

        if ($param['type']) {
            $map['type'] = $param['type'];
        }

        if (isset($param['status']) and $param['status'] !== '') {
            $map['status'] = $param['status'];
        }
		if(!empty($keyword)){
			
			$map['description'] = array('like', '%' . $keyword . '%');
			
		}
        $list  = table('userFeedback')->where($map)->limit($offer, $pageSize)->find('array');
        $total = table('userFeedback')->where($map)->count();
        $page  = new Pages($total, $pageNo, $pageSize, url('lists', $param));
		
		foreach($list as $k => $v){
			
			$list[$k]['type'] = $this -> modelField($v['type'],'Category','name');
			$list[$k]['priority'] = $this -> modelField($v['priority'],'Category','name');
			$list[$k]['fullname'] = $this -> modelField($v['uid'],'user','nickname');
			
		}

        $other = array(
            'typeCopy'   => dao('Category')->getList(606),
            'statusCopy' => array('0' => '待回复', '1' => '已回复'),
        );

        $this->assign('other', $other);
        $this->assign('list', $list);
        $this->assign('pages', $page->loadConsole());
        $this->assign('param', $param);
        $this->show();
    }

    public function editPost()
    {
        $id = get('id', 'intval', 0);

        //回复
        if ($id) {
            $data['result']    = post('result', 'text', '');
            $data['update_time']  = time();
            $data['status'] = 1;
			
			if(empty($data['result'])){
				
				$this->ajaxReturn(array('status' => false, 'msg' => '请输入回复内容'));
				
			}
			
			$result            = table('userFeedback')->where('id', $id)->save($data);
			
			if (!$result) {
				$this->ajaxReturn(array('status' => false, 'msg' => '操作失败'));
			}
			
        }

        $this->ajaxReturn(array('msg' => '操作成功'));
    }

    public function edit()
    {
        $id = get('id', 'intval', 0);

        if ($id) {
            $data = table('userFeedback')->where('id', $id)->find();
			$data['type'] = $this -> modelField($data['type'],'Category','name');
			$data['fullname'] = $this -> modelField($data['uid'],'user','nickname');
        } 
        
        $this->assign('data', $data);
        $this->show();
    }
}
