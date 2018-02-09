<?php
/**
 * 上传模块
 */
namespace app\tools\dao;

/**
 * 通用的树型类，可以生成任何树型结构
 */
class Upload
{
    private $path;

    public function upBase64Img($data, $path)
    {
        if ($path) {
            $path = 'uploadfile' . DS . $path . DS;
        } else {
            $path = 'uploadfile' . DS;
        }

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $match)) {
            $type = $match[2];
            if (!file_exists($path)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($path, 0755, true);
            }
            $fileName = md5(uniqid('', true)) . ".{$type}";
            $newFile  = $path . $fileName;

            if (file_put_contents($newFile, base64_decode(str_replace($match[1], '', $data)))) {
                return array('status' => true, 'msg' => '保存成功', 'data' => $fileName);
            } else {
                return array('status' => false, 'msg' => '保存失败');
            }
        }

        return array('status' => false, 'msg' => '请上传正确的图片');
    }

    /**
     * File批量上传图片
     * @date   2017-09-14T10:20:29+0800
     * @author ChenMingjiang
     * @param  [type]                   $files [files数组]
     * @param  [type]                   $path  [保存地址]
     * @param  integer                  $maxSize  [最大上传M]
     * @param  string                   $type  [上传类型限制]
     * @return [type]                          [保存后的图片路径 数组]
     */
    public function uploadfile($files, $path, $maxSize = 10, $type = '')
    {
        set_time_limit(0);

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

        $filePath = PUBLIC_PATH . 'uploadfile' . DS . $path . DS;
        is_dir($filePath) ? '' : mkdir($filePath, 0755, true);

        //获取最近附件记录id
        $uploadLog = table('UploadLog')->fieldStatus('Auto_increment');
        $id        = $uploadLog['Auto_increment'];

        foreach ($files as $key => $value) {
            if ($value['size'] >= $maxSize * 1024 * 1024) {
                return array('status' => false, 'msg' => '请上传小于' . $maxSize . 'M的文件');
            }

            $ext = str_replace(substr($value['type'], 0, stripos($value['type'], '/') + 1), '', $value['type']);

            !($ext == 'et-stream' || $ext == 'octet-stream') ?: $ext = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));

            if (stripos($type, $ext) === false) {
                return array('status' => false, 'msg' => $ext . '文件禁止上传');
            }

            $move[$key]['tmp_name'] = $value['tmp_name'];
            $move[$key]['name']     = time() . rand(100, 999) . '_' . $id++ . '.' . $ext;
            $move[$key]['old_name'] = $value['name'];
            $move[$key]['size']     = $value['size'];
        }

        //上传文件
        foreach ($move as $key => $value) {

            $result = move_uploaded_file($value['tmp_name'], $filePath . $value['name']);

            if ($result) {
                $data['name'][$key] = $value['name'];
                //保存日志记录
                $this->saveLog($value, $path);
            }

        }

        return array('status' => true, 'msg' => '上传成功', 'data' => $data);
    }

    /** 保存临时文件 */
    public function uploadfileTmp($files)
    {

    }

    /**
     * 合并分片上传数据
     * @date   2017-12-12T11:23:00+0800
     * @author ChenMingjiang
     * @param  [type]                   $name [description]
     * @param  [type]                   $path [description]
     * @param  [type]                   $max  [description]
     * @return [type]                         [description]
     */
    public function uploadfileMerge($name, $path, $max)
    {

    }

    /** 保存附件记录 */
    public function saveLog($param = array(), $path = '')
    {

        $data['name']    = $param['old_name'];
        $data['size']    = $param['size'];
        $data['path']    = $path;
        $data['ext']     = pathinfo($param['old_name'], PATHINFO_EXTENSION);
        $data['url']     = $param['name'];
        $data['created'] = TIME;

        $result = table('UploadLog')->add($data);

        return $result;
    }

}
