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

    /**
     * File批量上传图片
     * @date   2017-09-14T10:20:29+0800
     * @author ChenMingjiang
     * @param  [type]                   $files [files数组]
     * @param  [type]                   $path  [保存地址]
     * @param  integer                  $size  [最大上传M]
     * @param  string                   $type  [上传类型限制]
     * @return [type]                          [保存后的图片路径 数组]
     */
    public function uploadfile($files, $path, $size = 10, $type = '')
    {
        if (!$files) {
            return array('status' => false, 'msg' => '上传信息为空');
        }

        if (count($files) == count($files, 1)) {
            foreach ($files as $key => $value) {
                unset($files[$key]);
                $files[0][$key] = $value;
            }
        }

        $type ?: $type = 'jpg,png,gif,jpeg';

        $path = PUBLIC_PATH . 'uploadfile' . DS . $path . DS;
        is_dir($path) ? '' : mkdir($path, 0077, true);

        foreach ($files as $key => $value) {
            if ($value['size'] >= $size * 1024 * 1024) {
                return array('status' => false, 'msg' => '请上传小于' . $size . 'M的文件');
            }

            $ext = ltrim($value['type'], substr($value['type'], 0, stripos($value['type'], '/') + 1));
            //$ext = end(pathinfo($value['tmp_name']));

            if (stripos($type, $ext) === false) {
                return array('status' => false, 'msg' => $ext . '文件禁止上传');
            }

            //保存文件
            //$fileName = time() . '.' . $ext;
            //$result   = move_uploaded_file($value['tmp_name'], $path . $fileName);
            $move[$key]['tmp_name'] = $value['tmp_name'];
            $move[$key]['name']     = time() . rand(10000, 99999) . '.' . $ext;

        }

        //上传文件
        foreach ($move as $key => $value) {
            $result = move_uploaded_file($value['tmp_name'], $path . $value['name']);

            if ($result) {
                $data['name'][$key] = $value['name'];
            }
        }

        return array('status' => true, 'msg' => '上传成功', 'data' => $data);
    }
}
