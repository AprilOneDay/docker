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
    public $isSend; //是否发送消息推送
    public $atLists;

    public function __construct()
    {

        //@上传者对应手机号
        $this->atLists = array(
            'cmj' => '15923882847',
            'tjw' => '15095909535',
            'dyp' => '15223777043',
            'll'  => '13647602430',
            'ghl' => '13647602430',
            'xy'  => '15922610927',
            'lsr' => '18306062832',
            'wys' => '18228270586',
        );

        $options = getopt('n:v:d:a:');

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
        $this->isSend      = $options['a'] ? $options['a'] : false;

        $this->bulidPath = $this->homePath . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . $this->projectName . DIRECTORY_SEPARATOR . 'builds' . DIRECTORY_SEPARATOR . $this->buildId . DIRECTORY_SEPARATOR . 'changelog.xml';

        //项目目录
        $this->workPath = $this->homePath . DIRECTORY_SEPARATOR . 'workspace' . DIRECTORY_SEPARATOR . $this->projectName;

        //存放增量代码目录
        $this->sourcePath = $this->homePath . DIRECTORY_SEPARATOR . 'workspace' . DIRECTORY_SEPARATOR . 'tmp_' . $this->projectName . '_pakage';

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

            if (key($this->fileArr['logentry']) == '0') {
                $list = $this->fileArr['logentry'];
            } else {
                $list[] = $this->fileArr;
            }

            // print_r($this->fileArr);
            // var_dump(key($this->fileArr['logentry']));
            // print_r($list);
            // die;

            foreach ($list as $file) {

                $file = isset($file['logentry']) ? $file['logentry'] : $file;

                echo '上传人 : ' . $file['author'] . ' SVN版本 : ' . implode(',', $file['@attributes']) . ' 备注 : ' . implode(',', (array) $file['msg']) . PHP_EOL;

                //发送推送信息
                if ($this->isSend) {
                    echo $this->sendDD($file);
                }

                if (!empty($file['paths']['path'])) {
                    foreach ((array) $file['paths']['path'] as $value) {

                        $path   = $this->workPath . $value;
                        $tmpDir = $this->sourcePath . pathinfo($value, PATHINFO_DIRNAME);

                        //复制文件
                        if (is_file($path)) {

                            if (!is_dir($tmpDir)) {
                                mkdir($tmpDir, 0755, true);
                            }

                            copy($path, $this->sourcePath . $value);
                            echo 'change file :' . $value . PHP_EOL;

                        }
                        //复制文件夹
                        elseif (is_dir($this->workPath . $value)) {
                            $this->copyDir($this->workPath . $value, $this->sourcePath . $value);
                            echo 'change dir :' . $value . PHP_EOL;
                        }
                        //需要删除的文件夹/文件
                        else {
                            if ($value != '/') {
                                $notFiles[] = $value;
                            }
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

    /** 创建删除Shell脚本 */
    private function createDelSh($file, $path)
    {
        $fileName = $this->sourcePath . DIRECTORY_SEPARATOR . 'del.sh';

        $content = '#!/bin/bash' . PHP_EOL;
        foreach ($file as $key => $value) {
            $content .= 'rm -rf $1' . $path . $value . PHP_EOL;
        }

        $file = fopen($fileName, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    /** 创建删除php脚本 */
    private function createDelPHP()
    {
        $fileName = $this->sourcePath . DIRECTORY_SEPARATOR . 'jekninsDel.php';
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

    /**
     * 复制文件夹
     * @date   2018-06-07T11:32:57+0800
     * @author ChenMingjiang
     * @param  [type]                   $source [原文件夹]
     * @param  [type]                   $dest   [复制文件夹]
     * @return [type]                           [description]
     */
    private function copyDir($source, $dest)
    {
        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }

        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $_source = $source . '/' . $item;
            $_dest   = $dest . '/' . $item;
            if (is_file($_source)) {
                copy($_source, $_dest);
            }

            if (is_dir($_source)) {
                $this->copyDir($_source, $_dest);
            }

        }
        closedir($handle);
    }

    /** curl模拟 */
    public function sendMsg($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /** 发送钉钉推送信息 */
    public function sendDD($params)
    {

        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=7a9e5d4da8e0c34f8d2d02b78891915a4f00ec294b42b16a9eadf06ffc740a59";
        $message = '上传人 : ' . $params['author'] . ' SVN版本 : ' . implode(',', $params['@attributes']) . ' 备注 : ' . implode(',', (array) $params['msg']) . ' 更新完毕' . PHP_EOL;

        $at = '';
        if (isset($this->atLists[$params['author']])) {
            $message = $message . '@' . $this->atLists[$params['author']];
            $at      = $this->atLists[$params['author']];
        }

        $jsonString = '{"msgtype": "text","text": {"content": "' . $message . '"},"at": {"atMobiles": ["' . $at . '"], "isAtAll": false}}';

        $curl = 'curl -H "Content-Type:application/json" -d \'' . $jsonString . '\' ' . $webhook;
        system($curl);
    }

}

$fileClass = new FileStatic();
$fileClass->makePackege();
