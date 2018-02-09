<?php
/**
 * 文件操作
 */
namespace app\tools\dao;

class File
{
    public function __construct()
    {
        //set_time_limit(0);
    }

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

    /**
     * xls文件导入数据库
     * @date   2018-02-07T17:12:53+0800
     * @author ChenMingjiang
     * @param  [type]                   $file      [文件]
     * @param  string                   $tableName [数据库名称]
     * @return [type]                              [description]
     */
    public function xlsImport($path)
    {
        ini_set('memory_limit', '2044M');
        //包含类文件
        require_once APP_PATH . 'tools' . DS . 'vendor' . DS . 'PHPExcel' . DS . 'Classes' . DS . 'PHPExcel' . DS . 'IOFactory.php';

        if (!is_file($path)) {
            return array('status' => false, 'msg' => '文件不存在');
        }

        $reader        = \PHPExcel_IOFactory::createReader('Excel5');
        $PHPExcel      = $reader->load($path); // 载入excel文件
        $sheet         = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow    = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数

        //var_dump($PHPExcel);
        //var_dump($highestRow);
        //var_dump($highestColumm);

        //行数是以第1行开始
        for ($row = 1; $row <= 1000; $row++) {
            $dataColumn = array();
            for ($column = 'A'; $column <= $highestColumm; $column++) {
                $dataColumn[] = $sheet->getCell($column . $row)->getValue();
            }

            $data[] = $dataColumn;
        }

        return $data;
    }
}
