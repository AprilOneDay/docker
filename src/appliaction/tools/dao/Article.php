<?php
/**
 * 百度抓取
 */
namespace app\tools\dao;

class Article
{

    private $dataTable;
    private $modelId;
    private $article;
    private $articleData;
    private $map   = array();
    private $field = '';

    /**
     * 获取单条文章记录
     * @date   2017-11-02T16:25:29+0800
     * @author ChenMingjiang
     * @param  [type]                   $nativeMap   [查询条件]
     * @param  [type]                   $nativeField [查询字段]
     * @param  [type]                   $modelId     [模型id]
     * @return [type]                                [description]
     */
    public function getRowContent($nativeMap, $nativeField, $modelId)
    {

        $this->getMapField($nativeMap, $nativeField, $modelId);

        $rs = table('Article')->join($this->articleData)->where($this->map)->field($this->field)->order($this->article . '.id desc')->find();
        return $rs;
    }

    /**
     * 获取列表
     * @date   2017-11-02T20:02:54+0800
     * @author ChenMingjiang
     * @param  [type]                   $nativeMap   [查询条件]
     * @param  [type]                   $nativeField [查询字段]
     * @param  [type]                   $modelId     [模型id]
     * @param  integer                  $pageSize    [分页数量]
     * @param  integer                  $pageNo      [分页数]
     * @return [type]                                [description]
     */
    public function getList($nativeMap, $nativeField, $modelId, $pageSize = 99, $pageNo = 1)
    {
        $offer = max(($pageNo - 1), 0) * $pageSize;
        $this->getMapField($nativeMap, $nativeField, $modelId);
        $total         = table('Article')->join($this->articleData)->where($this->map)->count();
        $list          = table('Article')->join($this->articleData)->where($this->map)->field($this->field)->limit($offer, $pageSize)->order($this->article . '.id desc')->find('array');
        $data['total'] = $total;
        $data['list']  = $list;

        return $data;
    }

    /**
     * 根据模型id获取对于附属表 自动整合map field
     * @date   2017-11-02T20:03:55+0800
     * @author ChenMingjiang
     * @param  [type]                   $nativeMap   [description]
     * @param  [type]                   $nativeField [description]
     * @return [type]                                [description]
     */
    private function getMapField($nativeMap, $nativeField, $modelId)
    {

        $modelTable = getVar('model_table', 'admin.article');

        $this->dataTable = $modelTable[$modelId];

        $this->article     = table('Article')->tableName();
        $this->articleData = table('Article' . $this->dataTable)->tableName();

        //主表字段
        $fieldArray = table('Article')->getField();

        $this->map = '';
        if ($nativeMap) {
            foreach ($nativeMap as $key => $value) {
                if (in_array($key, $fieldArray)) {
                    $this->map[$this->article . '.' . $key] = $value;
                } else {
                    $this->map[$this->articleData . '.' . $key] = $value;
                }
            }
        }
        $this->map[$this->article . '.model_id'] = $modelId;

        $this->field = '';
        $nativeField = explode(',', $nativeField);
        $nativeField = array_filter($nativeField);
        if ($nativeField) {
            foreach ($nativeField as $value) {
                if (in_array($value, $fieldArray)) {
                    $this->field .= $this->article . '.' . $value . ',';
                } else {
                    $this->field .= $this->articleData . '.' . $value . ',';
                }
            }
        }

        !$this->field ?: $this->field = substr($this->field, 0, -1);

    }

}
