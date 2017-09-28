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
            //增加搜索记录
            dao('Search')->addLog(0, 1, $keyword);
        }

        $total = table('Article')->where($map)->count();
        $pages = new denha\Pages($total, $pageNo, $pageSize, url('index'));

        $field = 'id,tag,type,title,thumb,description,created,hot';
        $list  = table('Article')->where($map)->field($field)->limit($offer, $pageSize)->find('array');

        $class = table('Article')->where(array('is_show' => 1))->field('count(*) as num,tag')->group('tag')->find('array');
        foreach ($class as $key => $value) {
            $listClass[$value['tag']] = $value;
        }

        $this->assign('keyword', $keyword);
        $this->assign('listClass', $listClass);
        $this->assign('tagCopy', getVar('tags', 'console.article'));
        $this->assign('randList', $this->rank());
        $this->assign('page', $pages->loadPc());
        $this->assign('list', $list);
        $this->show();
    }

    /**
     * 博客详情
     * @date   2017-09-28T17:05:15+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function detail()
    {
        $id = get('id', 'intval', 0);

        $article     = table('Article')->tableName();
        $articleBlog = table('ArticleBlog')->tableName();

        $map[$article . '.is_show'] = 1;
        $map[$article . '.id']      = $id;

        $field = "$article.id,$article.title,$article.created,$article.description,$article.hot,$articleBlog.content";
        $data  = table('Article')->join($articleBlog, "$article.id = $articleBlog.id", 'left')->where($map)->field($field)->find();

        //获取分类
        $class = table('Article')->where(array('is_show' => 1))->field('count(*) as num,tag')->group('tag')->find('array');
        foreach ($class as $key => $value) {
            $listClass[$value['tag']] = $value;
        }

        //获取评论
        $comment = dao('VisitorComment', 'blog')->blogDetail($id);

        //增加阅读记录
        table('Article')->where(array('id' => $id))->save(array('hot' => array('add', 1)));

        $this->assign('comment', $comment);
        $this->assign('listClass', $listClass);
        $this->assign('tagCopy', getVar('tags', 'console.article'));
        $this->assign('randList', $this->rank());
        $this->assign('data', $data);
        $this->show();
    }

    /**
     * 排行榜
     * @date   2017-09-28T17:05:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    private function rank()
    {
        $map['is_show']      = 1;
        $map['is_recommend'] = 1;

        $field = 'id,title';
        $list  = table('Article')->where($map)->field($field)->limit(0, 10)->find('array');

        return $list;
    }
}
