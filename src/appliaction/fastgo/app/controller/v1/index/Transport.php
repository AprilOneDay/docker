<?php
/**
 * 车友圈模块
 */
namespace app\fastgo\app\controller\v1\index;

use app\app\controller;
use app\fastgo\app\controller\v1\Init;

class Transport extends Init
{
    /*获取中转地址*/
    public function transferAddress()
    {

        $cityId = get('city_id', 'text', '');
        $data   = table('WarehouseInfo')->where('category_id', $cityId)->field('id,name,mobile,address,zip_code')->find();
        if (empty($data)) {
            $this->appReturn(array('status' => false, 'msg' => '暂无地址信息'));
        }

        $data['name'] = isset($this->uid) ? $data['name'] . '|' . $this->uid : $data['name'];

        $transInfo = array('转运包裹先寄到Fastgo中转仓,分别拆包转运哦-收件人、仓库地址是Fastgo收录包裹的唯一标识,不可更改!', '亲,邮寄物品分国际管制和国内管制哦，"禁运物品"寄到Fastgo中转仓也没有办法帮您转运出去呢！为了给您更好的转运体验，请跟随我一起了解"禁运物品"有哪些把');

        $data['info'] = $transInfo;
        $this->appReturn(array('msg' => '获取数据成功', 'data' => $data));

        if ($this->getMaxDim($goodsArray) != 2) {
            $this->appReturn(array('status' => false, 'msg' => 'goods结构只能有两层', 'data' => $goodsArray));
        }
    }
}
