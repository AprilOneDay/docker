<?php
function GSF($array, $v)
{
    foreach ($array as $key => $value) {
        if (!is_array($key)) {
            gsc($key, $v);
        } else {
            gsf($key, $v);
        }

        if (!is_array($value)) {
            gsc($value, $v);
        } else {
            gsf($value, $v);
        }
    }
}

function GSC($str, $v)
{
    foreach ($v as $key => $value) {
        if ((preg_match('/' . $value . '/is', $str) == 1) || (preg_match('/' . $value . '/is', urlencode($str)) == 1)) {
            die('您的请求带有不合法参数!');
        }
    }
}

function GSS($value)
{
    $value = (is_array($value) ? array_map('GSS', $value) : stripslashes($value));
    return $value;
}

function parseName($name, $type = false)
{
    //下划线转大写
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    }
    //大写转下划线小写
    else {
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

//POST过滤
function post($name, $type = '', $default = '')
{
    if ($name == 'all') {
        foreach ($_POST as $key => $val) {
            $val        = trim($val);
            $data[$key] = !get_magic_quotes_gpc() ? htmlspecialchars(addslashes($val), ENT_QUOTES, 'UTF-8') : htmlspecialchars($val, ENT_QUOTES, 'UTF-8');

        }

    } else {
        $data = isset($_POST[$name]) ? $_POST[$name] : '';
    }

    if ($name != 'all' && !is_array($data)) {
        switch ($type) {
            case 'intval':
                $data = $data === '' ? intval($default) : intval($data);
                break;
            case 'float':
                $data = $data === '' ? floatval($default) : floatval($data);
                break;
            case 'text':
                $data = $data === '' ? strval($default) : strval($data);
                break;
            case 'trim':
                $data = $data === '' ? trim($default) : trim($data);
                break;
            case 'bool':
                $data = $data === '' ? (bool) $default : (bool) $data;
                break;
            case 'json':
                $data = $data === '' ? $default : json_decode($data, true);
                break;
                # code...
                break;
            default:
                # code...
                break;
        }
    }
    return $data;
}

//GET过滤
function get($name, $type = '', $default = '')
{
    $data = null;
    if ($name == 'all') {

        foreach ($_GET as $key => $val) {
            $val        = trim($val);
            $data[$key] = !get_magic_quotes_gpc() ? htmlspecialchars(addslashes($val), ENT_QUOTES, 'UTF-8') : htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }

    } else {
        $data = isset($_GET[$name]) ? $_GET[$name] : '';
    }

    if ($name != 'all' && !is_array($data)) {
        switch ($type) {
            case 'intval':
                $data = $data === '' ? intval($default) : intval($data);
                break;
            case 'float':
                $data = $data === '' ? floatval($default) : floatval($data);
                break;
            case 'text':
                $data = $data === '' ? strval($default) : strval($data);
                break;
            case 'trim':
                $data = $data === '' ? trim($default) : trim($data);
                break;
            case 'bool':
                $data = $data === '' ? (bool) $default : (bool) $data;
                break;
            case 'json':
                $data = $data === '' ? $default : json_decode($data, true);
                break;
            case 'jsonp':
                $data = $data === '' ? $default : get('callback') . '(' . json_encode($data, true) . ')';
            default:
                # code...
                break;
        }
    }
    return $data;
}

function files($name)
{
    if (isset($_FILES[$name])) {
        if (is_array($_FILES[$name]['name'])) {
            foreach ($_FILES[$name] as $key => $value) {
                foreach ($value as $k => $v) {
                    $data[$k][$key] = $v;
                }
            }
        } else {
            $data = $_FILES[$name];
        }
    } else {
        $data = null;
    }

    return $data;
}

//判断文件是否存在
function existsUrl($url)
{

    if ($url == '') {return false;}
    if (stripos($url, 'http') === false) {
        $http = $_SERVER['SERVER_NAME'];
        $url  = 'http://' . $http . '/' . $url;
    }
    $opts = array(
        'http' => array(
            'timeout' => 30,
        ),
    );

    $context = stream_context_create($opts);
    $rest    = @file_data_contents($url, false, $context);

    if ($rest) {
        return true;
    } else {
        return false;
    }
}

function table($name, $isTablepre = true)
{
    $do = denha\Mysqli::getInstance(); //单例实例化
    if ($name) {
        return $do->table($name, $isTablepre);
    }

    return $do;
}

function dao($name, $app = '')
{
    static $_dao = array();

    if (!$app) {
        $class = 'app\\tools\\dao\\' . $name;
    } else {
        $class = 'app\\' . $app . '\\tools\\dao\\' . $name;
    }

    $value = md5($class);

    if (isset($_dao[$value])) {
        return $_dao[$value];
    } else {
        if (class_exists($class)) {
            $_dao[$value] = new $class();
            return $_dao[$value];
        }
    }

    die('Dao方法：' . $class . '不存在');
}

//包含文件
function comprise($path)
{
    include VIEW_PATH . $path . '.html';
}

//获取配置常量
//getVar('tags','console.article') 获取 appliaction/console/tools/var/article文件中的 tags.$ext 文件
//getVar('tags','article') 获取 appliaction/tools/var/article文件中的 tags.$ext 文件
function getVar($filename, $path, $ext = EXT)
{
    static $_vars = array();

    if (!$filename) {
        return null;
    }

    $name = md5($filename . $path);
    if (isset($_vars[$name])) {
        return $name;
    } else {
        if (($length = stripos($path, '.')) === false) {
            $filePath = APP_PATH . 'tools' . DS . 'var' . DS . $path . DS . $filename . $ext;
        } else {
            $filePath = APP_PATH . substr($path, 0, $length) . DS . 'tools' . DS . 'var' . DS . substr(strstr($path, '.'), 1) . DS . $filename . $ext;
        }

        if (is_file($filePath)) {
            $_vars[$name] = include $filePath;
            return $_vars[$name];
        }
    }

    return null;
}

//获取config下配置文档
function getConfig($path = 'config', $name = '')
{
    static $_configData = array();

    if (!isset($_configData[$path])) {
        if (is_file(CONFIG_PATH . $path . '.php')) {
            $_configData[$path] = include CONFIG_PATH . $path . '.php';
        }

    }

    if (isset($_configData[$path])) {
        if ($name === '') {
            return $_configData[$path];
        }

        if (isset($_configData[$path][$name])) {
            return $_configData[$path][$name];
        }
    }

    return null;
}

//创建getUrl
function url($location = '', $params = array(), $isGet = false)
{
    $locationUrl = URL . '/' . MODULE;
    if ($location === '') {
        $locationUrl .= '/' . CONTROLLER . '/' . ACTION;
    } elseif (stripos($location, '/') === false && $location != '') {
        $locationUrl .= '/' . CONTROLLER . '/' . $location;
    } elseif (stripos($location, '/') != 1) {
        $locationUrl .= '/' . $location;
    } else {
        $locationUrl = $location;
    }

    $param = '';
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            if ($isGet) {
                if (key($params) === $key && stripos($locationUrl, '?') === false) {
                    $param = '?' . $key . '=' . $value;
                } else {
                    $param .= '&' . $key . '=' . $value;
                }
            } else {
                $param .= '/' . $key . '/' . $value;
            }

        }
    }

    // var_dump($param);

    return $locationUrl . $param;
}

//保存Cookie
function cookie($name = '', $value = '', $expire = '86400', $encode = false)
{
    if (!$name) {
        return false;
    }

    //加密
    $value = $encode ? auth($value) : $value;

    setcookie($name, $value, time() + $expire, '/');

}

//获取Cookie
function getCookie($name, $encode = false)
{
    $data = '';
    if (isset($_COOKIE[$name])) {
        $data = $_COOKIE[$name];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $encode ? auth($value, 'DECODE') : $value;
            }
        } else {
            $data = $encode ? auth($data, 'DECODE') : $data;
        }
    }

    return $data;
}

//获取上传图片地址
function imgUrl($name, $path = '', $size = 0, $host = false)
{

    if (stripos($name, ',') !== false) {
        $imgName = explode(',', $name);
    } else {
        $imgName = is_array($name) ? $name : (array) $name;
    }

    foreach ($imgName as $key => $value) {
        if ($path) {
            $url = '/uploadfile/' . $path . '/' . $value;
        } else {
            $url = '/uploadfile/' . $value;
        }

        $url = !$host ? URL . $url : $host . $url;

        //这块有点影响网速 设置超时 后续会改为检测数据库
        $opts = array(
            'http' => array(
                'method'  => "GET",
                'timeout' => 1, //单位秒
            ),
        );

        if (!file_get_contents($url, false, stream_context_create($opts))) {
            $url = '/ststic/console/images/nd.jpg';
            $url = !$host ? URL . $url : $host . $url;
        }

        $data[] = $url;
    }

    $data = count($data) > 1 ? $data : current($data);
    return $data;
}

function imgFetch($path)
{
    (!$path && stripos($path, 'nd.jpg') === false) ?: (string) ltrim($param['thumb'], substr($param['thumb'], 0, strripos($param['thumb'], '/') + 1));
}

//保存Session
function session($name = '', $value = '')
{
    isset($_SESSION) ?: session_start();
    //删除
    if ($value === null) {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }
    //保存
    else {
        // 数组
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $_SESSION[$k] = $v;
            }
        }
        //二维数组
        elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $_SESSION[$name][$k] = $v;
            }
        } else {
            $_SESSION[$name] = $value;
        }
    }

    //关闭session 可防止高并发下死锁问题
    session_write_close();
}

//判断是否存在session
function issetSession($name)
{
    isset($_SESSION) ?: session_start();
    if (isset($_SESSION[$name])) {
        return true;
    }
    session_write_close(); //关闭session
    return false;
}

//读取Session
function getSession($name)
{
    isset($_SESSION) ?: session_start();
    $data = isset($_SESSION[$name]) ? $_SESSION[$name] : '';
    if (is_object($data)) {
        $data = (array) $data;
    }
    session_write_close(); //关闭session
    return $data;
}

//删除session
function delSession($name)
{
    isset($_SESSION) ?: session_start();
    if (isset($_SESSION[$name])) {
        unset($_SESSION[$name]);
    }
    session_write_close(); //关闭session
    return true;
}

/**
 * 转换其他编码成Unicode编码
 * @date   2017-10-10T15:24:33+0800
 * @author ChenMingjiang
 * @param  [type]                   $name [需要转换的内容]
 * @param  string                   $code [当前编码]
 * @return [type]                         [description]
 */
function enUnicode($name, $code = 'UTF-8')
{
    $name = iconv($code, 'UCS-2', $name);
    $len  = strlen($name);
    $str  = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2) {
        $c  = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) {
            //两个字节的文字
            $str .= '\u' . base_convert(ord($c), 10, 16) . str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            //$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
        } else {
            $str .= '\u' . str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            //$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
        }
    }
    $str = strtoupper($str); //转换为大写
    return $str;
}

/**
 * 转换Unicode编码为其他编码
 * @date   2017-10-10T15:24:54+0800
 * @author ChenMingjiang
 * @param  [type]                   $name [需要转换的内容]
 * @param  string                   $code [需要转换成的编码]
 * @return [type]                         [description]
 */
function deUnicode($name, $code = 'UTF-8')
{
    $name = strtolower($name);
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)) {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code  = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c     = chr($code) . chr($code2);
                $c     = iconv('UCS-2', $code, $c);
                $name .= $c;
            } else {
                $name .= $str;
            }
        }
    }
    return $name;
}

/**
 * 编码转换
 * @date   2017-08-27T16:07:41+0800
 * @author ChenMingjiang
 * @param  string                   $content  [需要转码的内容]
 * @param  string                   $mbEncode [需要转换成的编码]
 * @return [type]                             [description]
 */
function mbDetectEncoding($content = '', $mbEncode = "UTF-8")
{
    $encode = mb_detect_encoding($content, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "EUC-CN"));
    if ($encode != $mbEncode) {
        $encode  = $encode == "EUC-CN" ? "GB2312" : $encode;
        $content = mb_convert_encoding($content, $mbEncode, $encode);
    }

    return $content;
}

/**
 * 字符串加密、解密函数
 *
 *
 * @param    string    $txt        字符串
 * @param    string    $operation    ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
 * @param    string    $key        密钥：数字、字母、下划线
 * @param    string    $expiry        过期时间
 * @return    string
 */
function auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key         = md5($key != '' ? $key : getConfig('config', 'authKey'));
    $keya        = md5(substr($key, 0, 16));
    $keyb        = md5(substr($key, 16, 16));
    $keyc        = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(strtr(substr($string, $ckey_length), '-_', '+/')) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp     = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a       = ($a + 1) % 256;
        $j       = ($j + $box[$a]) % 256;
        $tmp     = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
    }
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function toGuidString($mix)
{
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

//唯一id
function guid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid   = chr(123) // "{"
         . substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12)
        . chr(125); // "}"
        return $uuid;
    }
}

//获取真实IP地址
function getIP()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');

    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//百度转腾讯坐标转换
function baiduToTenxun($lat, $lng)
{
    $x_pi  = 3.14159265358979324 * 3000.0 / 180.0;
    $x     = $lng - 0.0065;
    $y     = $lat - 0.006;
    $z     = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $lng   = $z * cos($theta);
    $lat   = $z * sin($theta);
    return array('lng' => $lng, 'lat' => $lat);
}

//腾讯转百度坐标转换
function tenxunToBaidu($lat, $lng)
{
    $x_pi  = 3.14159265358979324 * 3000.0 / 180.0;
    $x     = $lng;
    $y     = $lat;
    $z     = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $lng   = $z * cos($theta) + 0.0065;
    $lat   = $z * sin($theta) + 0.006;
    return array('lng' => $lng, 'lat' => $lat);

}

//获取汉字首字母
function getFirstCharter($str)
{
    header("content-Type: text/html; charset=GB2312");
    if (empty($str)) {
        return '';
    }

    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) {
        return strtoupper($str{0});
    }

    //$s1 = iconv("UTF-8", "gb2312//IGNORE", $str);
    //$s2 = iconv("gb2312", "UTF-8//IGNORE", $s1);

    $s1 = mb_convert_encoding($str, "GBK", "UTF-8");
    $s2 = mb_convert_encoding($s1, "UTF-8", "GBK");

    $s = $s2 == $str ? $s1 : $str;

    $asc = current(unpack('N', "\xff\xff$s"));
    //$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;

    if ($asc >= -20319 && $asc <= -20284) {
        return 'A';
    }

    if ($asc >= -20283 && $asc <= -19776) {
        return 'B';
    }

    if ($asc >= -19775 && $asc <= -19219) {
        return 'C';
    }

    if ($asc >= -19218 && $asc <= -18711) {
        return 'D';
    }

    if ($asc >= -18710 && $asc <= -18527) {
        return 'E';
    }

    if ($asc >= -18526 && $asc <= -18240) {
        return 'F';
    }

    if ($asc >= -18239 && $asc <= -17923) {
        return 'G';
    }

    if ($asc >= -17922 && $asc <= -17418) {
        return 'H';
    }

    if ($asc >= -17417 && $asc <= -16475) {
        return 'J';
    }

    if ($asc >= -16474 && $asc <= -16213) {
        return 'K';
    }

    if ($asc >= -16212 && $asc <= -15641) {
        return 'L';
    }

    if ($asc >= -15640 && $asc <= -15166) {
        return 'M';
    }

    if ($asc >= -15165 && $asc <= -14923) {
        return 'N';
    }

    if ($asc >= -14922 && $asc <= -14915) {
        return 'O';
    }

    if ($asc >= -14914 && $asc <= -14631) {
        return 'P';
    }

    if ($asc >= -14630 && $asc <= -14150) {
        return 'Q';
    }

    if ($asc >= -14149 && $asc <= -14091) {
        return 'R';
    }

    if ($asc >= -14090 && $asc <= -13319) {
        return 'S';
    }

    if ($asc >= -13318 && $asc <= -12839) {
        return 'T';
    }

    if ($asc >= -12838 && $asc <= -12557) {
        return 'W';
    }

    if ($asc >= -12556 && $asc <= -11848) {
        return 'X';
    }

    if ($asc >= -11847 && $asc <= -11056) {
        return 'Y';
    }

    if ($asc >= -11055 && $asc <= -10247) {
        return 'Z';
    }

    if ($asc == -9559) {
        return 'O';
    }

    return null;
}
