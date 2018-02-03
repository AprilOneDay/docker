<?php
/**
 * 物流模块模块
 */
namespace app\fastgo\tools\dao;

use app\fastgo\app\controller\v1\Init;

class Logistics extends Init
{
    public function detail($orderSn)
    {
        if (!$orderSn) {
            return '';
        }

        $data = table('Logistics')->where('order_sn', $orderSn)->find();

        if (!$data) {
            return array();
        }

        //入库中转信息
        $data['storage_transport'] = array();
        if ($data['storage_transport_id']) {
            $data['storage_transport'] = array(
                'transport_sn'           => $data['storage_transport_sn'],
                'transport_company'      => dao('Category')->getName($data['storage_transport_id'], $this->lg),
                'transport_company_type' => dao('Category')->getBname($data['storage_transport_id']),
                'transport_id'           => $data['storage_transport_id'],
            );
        }

        //出库中转信息
        $data['outbound_transport'] = array();
        if ($data['outbound_transport_id']) {
            $data['outbound_transport'] = array(
                'transport_sn'           => $data['outbound_transport_sn'],
                'transport_company'      => dao('Category')->getName($data['outbound_transport_id'], $this->lg),
                'transport_company_type' => dao('Category')->getBname($data['outbound_transport_id']),
                'transport_id'           => $data['outbound_transport_id'],
            );
        }

        //获取仓库详细信息
        $data['warehouse_copy'] = '';

        if ($data['warehouse_id']) {
            $data['warehouse_copy'] = (string) dao('Depot', 'fastgo')->getName($data['warehouse_id'], $this->lg);
            $warehouseInfo          = table('WarehouseInfo')->where('category_id', $data['warehouse_id'])->find();
            $data['warehouseInfo']  = $warehouseInfo;
        }

        $data['logistics_back_code']     = $this->appImg($data['logistics_back_code'], 'code');
        $data['logistics_positive_code'] = $this->appImg($data['logistics_positive_code'], 'code');

        $data['user_ablum']    = $this->appImg($data['user_ablum'], 'logistics');
        $data['console_ablum'] = $this->appImg($data['console_ablum'], 'logistics');

        //发货地址信息
        if (!$data['ship_address_id']) {
            $fastgoAddress = dao('Orders', 'fastgo')->fastgoAddress();
            $data          = array_merge($data, $fastgoAddress);
        }

        return $data;
    }

}
