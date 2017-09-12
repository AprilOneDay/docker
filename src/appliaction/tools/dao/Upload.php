<?php
namespace app\tools\dao;

/**
 * 通用的树型类，可以生成任何树型结构
 */
class Upload
{
    public function upBase64Img($data, $path)
    {
        if ($path) {
            $path = 'uploadfile' . DS . $path . DS;
        } else {
            $path = 'uploadfile' . DS;
        }

        //echo $path;die;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $match)) {
            $type = $match[2];
            if (!file_exists($path)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($path, 0700, true);
            }
            $fileName = md5(uniqid('', true)) . ".{$type}";
            $newFile  = $path . $fileName;

            if (file_put_contents($newFile, base64_decode(str_replace($match[1], '', $data)))) {
                return array('status' => true, 'msg' => '保存成功', 'data' => $fileName);
            } else {
                return array('status' => false, 'msg' => '保存失败');
            }
        }

        return array('status' => false, 'msg' => '参数错误');
    }
}
