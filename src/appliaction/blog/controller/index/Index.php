<?php
namespace app\blog\controller\index;

use denha;

class Index extends denha\Controller
{
    public function index()
    {
        $pageNo   = get('pageNo', 'intval', 0);
        $tag      = get('tag', 'intval', 0);
        $keyword  = get('keyword', 'text', '');
        $pageSize = 10;

        $offer = max(($pageNo - 1), 0) * $pageSize;

        $map['is_show'] = 1;

        if ($tag) {
            $map['tag'] = $tag;
        }

        if ($keyword) {
            $map['title'] = array('like', '%' . $keyword . '%');
        }

        $total = table('Article')->where($map)->count();
        $pages = new denha\pages($total, $pageNo, $pageSize, url('index'));

        $field = 'id,tag,type,title,thumb,description,created';
        $list  = table('Article')->where($map)->field($field)->limit($offer, $pageSize)->find('array');

        $class = table('Article')->where(array('is_show' => 1))->field('count(*) as num,tag')->group('tag')->find('array');
        foreach ($class as $key => $value) {
            $listClass[$value['tag']] = $value;
        }

        //print_r($listClass);

        $this->assign('keyword', $keyword);
        $this->assign('listClass', $listClass);
        $this->assign('tagCopy', getVar('tags', 'console.article'));
        $this->assign('randList', $this->rank());
        $this->assign('page', $pages->loadPc());
        $this->assign('list', $list);
        $this->show();
    }

    public function detail()
    {
        $id = get('id', 'intval', 0);

        $article     = table('Article')->tableName();
        $articleBlog = table('ArticleBlog')->tableName();

        $map[$article . '.is_show'] = 1;
        $map[$article . '.id']      = $id;

        $field = "$article.title,$article.created,$article.description,$articleBlog.content";
        $data  = table('Article')->join($articleBlog, "$article.id = $articleBlog.id", 'left')->where($map)->field($field)->find();

        $class = table('Article')->where(array('is_show' => 1))->field('count(*) as num,tag')->group('tag')->find('array');
        foreach ($class as $key => $value) {
            $listClass[$value['tag']] = $value;
        }

        $this->assign('keyword', $keyword);
        $this->assign('listClass', $listClass);
        $this->assign('tagCopy', getVar('tags', 'console.article'));
        $this->assign('randList', $this->rank());
        $this->assign('data', $data);
        $this->show();
    }

    private function rank()
    {
        $map['is_show']      = 1;
        $map['is_recommend'] = 1;

        $field = 'id,title';
        $list  = table('Article')->where($map)->field($field)->limit(0, 10)->find('array');

        return $list;
    }
}
