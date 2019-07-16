<?php
/**
 * 制作增量包
 */

class FileStatic
{

    public $fileArr; //需要打包文件日志
    public $deleteFile; //删除的文件
    public $projectName; //项目名称
    public $sourcePath; //代码源
    public $workPath; //项目目录
    public $homePath; //主目录
    public $bulidPath; //构建日志目录
    public $buildId; //构建日志ID
    public $delPath;

    public function __construct()
    {
        $options = getopt('n:v:d:');

        if (empty($options['n'])) {
            die('Fail : options not find projectName' . PHP_EOL);
        }

        if (empty($options['n'])) {
            die('Fail : options not find buildId' . PHP_EOL);
        }

        $this->homePath    = dirname(__DIR__);
        $this->projectName = $options['n'];
        $this->buildId     = $options['v'];
        $this->delPath     = isset($options['d']) ? $options['d'] : '';

        $this->bulidPath = $this->homePath . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . $this->projectName . DIRECTORY_SEPARATOR . 'builds' . DIRECTORY_SEPARATOR . $this->buildId . DIRECTORY_SEPARATOR . 'changelog.xml';

        //项目目录
        $this->workPath = $this->homePath . DIRECTORY_SEPARATOR . 'workspace' . DIRECTORY_SEPARATOR . $this->projectName;

        //存放增量代码目录
        $this->sourcePath = $this->homePath . DIRECTORY_SEPARATOR . 'workspace' . DIRECTORY_SEPARATOR . 'tmpPakage';

        if (!is_dir($this->sourcePath)) {
            mkdir($this->sourcePath, 0755, true);
        }

        if (!is_file($this->bulidPath)) {
            die('Fail : changelog.xml not find Path : ' . $this->bulidPath . PHP_EOL);
        }

    }

    //打包
    public function makePackege()
    {
        //解析xml
        $xml = file_get_contents($this->bulidPath);
        libxml_disable_entity_loader(true);
        $this->fileArr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        //处理增量文件
        if ($this->buildId !== 1 && !empty($this->fileArr)) {

            foreach ($this->fileArr as $file) {

                $file = isset($file['logentry']) ? $file['logentry'] : $file;

                echo 'author : ' . $file['author'] . ' version : ' . implode(',', $file['@attributes']) . ' msg : ' . $file['msg'] . PHP_EOL;

                if (!empty($file['paths']['path'])) {
                    foreach ($file['paths']['path'] as $value) {

                        $path   = $this->workPath . $value;
                        $tmpDir = $this->sourcePath . pathinfo($value, PATHINFO_DIRNAME);

                        if (is_file($path)) {

                            if (!is_dir($tmpDir)) {
                                mkdir($tmpDir, 0755, true);
                            }

                            copy($path, $this->sourcePath . $value);
                            echo 'change file :' . $value . PHP_EOL;

                        } else {
                            $notFiles[] = $value;
                        }

                    }

                }

            }

            if (!empty($notFiles)) {
                echo 'Not Find Files' . PHP_EOL;
                echo implode(PHP_EOL, $notFiles) . PHP_EOL;

                $this->createDelSh($notFiles, $this->delPath);
            }

        }
    }

    /** 创建删除脚本 */
    private function createDelSh($file, $path)
    {
        $fileName = $this->sourcePath . DIRECTORY_SEPARATOR . 'del.sh';

        $content = '#!/bin/bash' . PHP_EOL;
        foreach ($file as $key => $value) {
            $content .= 'rm -rf ' . $path . $value . PHP_EOL;
        }

        $file = fopen($fileName, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * 创建目录
     */
    private function mkdirs($dir, $mode = 0775)
    {
        if (is_dir($dir) || mkdir($dir, $mode)) {
            return true;
        }

        if (!$this->mkdirs(dirname($dir), $mode)) {
            return false;
        }

        return mkdir($dir, $mode);
    }

}

$fileClass = new FileStatic();
$fileClass->makePackege();
