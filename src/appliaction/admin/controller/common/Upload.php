<?php
namespace app\admin\controller\common;

use denha;

class Upload extends denha\Controller
{
    public function upBase64Img()
    {
        $img  = post('data', 'text', '');
        $path = post('path', 'text', '');

        $reslut = dao('Upload')->upBase64Img($img, $path);

        if ($reslut['status']) {
            $reslut['data'] = imgUrl($reslut['data'], $path);
        }
        $this->ajaxReturn($reslut);
    }
}
