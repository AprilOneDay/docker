<?php
/**
 * 广告管理
 */
namespace app\tools\dao;

class Banner
{
    public function getBannerList($bannerId)
    {
        $list = table('BannerData')->where(array('banner_id' => $bannerId))->find('array');
        return $list;
    }
}
