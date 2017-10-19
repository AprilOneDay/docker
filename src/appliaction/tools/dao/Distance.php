<?php
/*
 *根据经纬度计算距离
 */
namespace app\tools\dao;

class Distance
{

    public function range($lat, $lng, $list)
    {
        /*
         *lat 用户纬度
         *lng 用户经度
         *list sql语句
         */
        if (!empty($lat) && !empty($lng)) {
            foreach ($list as $row) {
                $row['km'] = $this->nearbyDistance($lat, $lng, $row['lat'], $row['lng']);
                $row['km'] = round($row['km'], 1);
                $res[]     = $row;
            }
            if (!empty($res)) {
                foreach ($res as $user) {
                    $ages[] = $user['km'];
                }
                array_multisort($ages, SORT_ASC, $res);
                return $res;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //计算经纬度两点之间的距离
    public function nearbyDistance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;
        $radLat1      = $this->rad($lat1);
        $radLat2      = $this->rad($lat2);
        $a            = $radLat1 - $radLat2;
        $b            = $this->rad($lng1) - $this->rad($lng2);
        $s            = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s1           = $s * $EARTH_RADIUS;
        $s2           = round($s1 * 10000) / 10000;
        return $s2;
        //print_r($s2);
    }

    private function rad($d)
    {
        return $d * 3.1415926535898 / 180.0;
    }
}
