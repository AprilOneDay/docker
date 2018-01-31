<?php
/**
 * 百度地图
 */
namespace app\tools\dao;

class BaiduMap
{
    /** 保存地址信息 */
    public function saveGeolocation($lng = 0, $lat = 0)
    {
        if ($lng && $lat) {
            session('lng', $lng);
            session('lat', $lat);
        }

        return true;
    }
}
