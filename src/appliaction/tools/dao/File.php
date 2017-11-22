<?php
/**
 * 文件操作
 */
namespace app\tools\dao;

class File
{
    /**
     * 将多个文件压缩成一个zip文件的函数
     * @date   2017-11-15T10:12:02+0800
     * @author ChenMingjiang
     * @param  array                    $files       [description]
     * @param  string                   $path        [目标文件的路径 如""]
     * @param  string                   $zipName     [压缩后生成的名称]
     * @param  boolean                  $overwrite   [是否为覆盖与目标文件相同的文件]
     * @return [type]                                [description]
     */
    public function zip($files = array(), $path = '', $zipName = '', $overwrite = false)
    {

        //创建文件夹
        $dirPath = PUBLIC_PATH . 'uploadfile' . DS . $path . DS;
        is_dir($dirPath) ? '' : mkdir($dirPath, 0755, true);

        $data        = DS . 'uploadfile' . DS . $path . DS . $zipName . '.zip';
        $destination = PUBLIC_PATH . 'uploadfile' . DS . $path . DS . $zipName . '.zip';

        //window转gbk 防止中文乱码
        if (strripos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false) {
            $destination = iconv('UTF-8', 'GBK', $destination);
        }

        //如果zip文件已经存在并且设置为不重写返回false
        if (file_exists($destination) && !$overwrite) {
            return array('status' => true, 'msg' => '文件已存在', 'data' => $data);
        }
        //vars
        $validFiles = array();
        //获取到真实有效的文件名
        $files = is_array($files) ? $files : (array) $files;
        foreach ($files as $file) {
            $file = PUBLIC_PATH . str_replace('/', DS, $file);
            if (file_exists($file)) {
                $validFiles[] = $file;
            }
        }

        if (!count($validFiles)) {
            return array('status' => false, 'msg' => '需要压缩文件不存在');
        }

        $zip = new \ZipArchive();
        //打开文件       如果文件已经存在则覆盖，如果没有则创建
        if ($zip->open($destination, $overwrite ? \ZipArchive::OVERWRITE : \ZipArchive::CREATE) !== true) {
            return array('status' => false, 'msg' => 'zip创建失败');
        }
        //向压缩文件中添加文件
        foreach ($validFiles as $file) {
            $fileInfoArr = pathinfo($file);
            $filename    = $fileInfoArr['basename'];
            $zip->addFile($file, $filename);
        }

        //debug
        //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
        //close the zip -- done!

        //关闭文件
        $zip->close();

        //检测文件是否存在
        return array('status' => file_exists($destination), 'msg' => '操作完成', 'data' => $data);

    }
}
