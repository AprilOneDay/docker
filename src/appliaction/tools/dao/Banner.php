<?php
namespace app\tools\dao;

class Banner
{
    public function getBannerList($bannerId)
    {
        $list = table('BannerData')->where(array('banner_id' => $bannerId))->find('array');

        return $list;
    }
}
