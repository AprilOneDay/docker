<?php
namespace app\admin\controller\common;

use denha;

class Upload extends denha\Controller
{
    //上传base64图片
    public function upBase64Img()
    {
        max_execution_time();

        $img  = post('data', 'text', '');
        $path = post('path', 'text', '');

        $reslut = dao('Upload')->upBase64Img($img, $path);

        if ($reslut['status']) {
            $reslut['data'] = imgUrl($reslut['data'], $path);
        }
        $this->ajaxReturn($reslut);
    }

    //上传文件
    public function upFile()
    {
        $files   = files('file');
        $path    = post('path', 'text', '');
        $maxSize = post('max_size', 'intval', 30);

        $reslut = dao('Upload')->uploadfile($files, $path, $maxSize, $type = 'apk,mp4,mp3,doc,docx,flv,zip,rar,jpg,gif,png,jpeg');

        $this->ajaxReturn($reslut);
    }
}
