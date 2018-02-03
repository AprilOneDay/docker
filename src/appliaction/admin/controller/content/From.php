<?php
/**
 * 车友圈模块
 */
namespace app\admin\controller\content;

use app\admin\controller\Init;

class From extends Init
{
    public function lists()
    {
        $list = table('FromJoin')->field('id,name,join_city,open_shop_time')->find('array');

        foreach ($list as $key => $value) {
            $list[$key]['name'] = $value['name'] ? $value['name'] : '游客';
        }

        $this->assign('list', $list);

        $this->show();
        //select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='表名' and table_schema='数据库名'
    }

    public function detail()
    {
        $id = get('id', 'intval', 0);

        $fromData = table('FromJoin')->getField('column_name,column_comment');

        $data = table('FromJoin')->find();

        $this->assign('data', $data);
        $this->assign('fromData', $fromData);
        $this->show();

    }

}
