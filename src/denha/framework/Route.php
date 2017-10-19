<?php
namespace denha;

class Route
{
    public static $path;
    public static $class;

    //后台快捷路由
    public static function admin()
    {
        $uri   = self::parseUri();
        $array = explode('/', $uri);

    }

    //app 路由结构
    //v1/user/index/index/2/ be appliaction/app/controller/v1/user/Index_2.php 中 index
    public static function app()
    {
        $uri   = self::parseUri();
        $array = explode('/', $uri);

        if (count($array) >= 3) {
            $version = $array[0];
            define('MODULE', $array[1]);
            define('CONTROLLER', $array[2]);

            //index方法 默认
            if (is_numeric($array[3])) {
                define('ACTION', 'index');
            } else {
                define('ACTION', $array[3]);
            }

            self::$path  = APP . DS;
            self::$class = 'app\\' . APP . '\\' . 'controller\\' . $array[0] . '\\' . parsename(MODULE, false) . '\\' . parsename(CONTROLLER, true);

            //切换小版本
            if (is_numeric($array[3])) {
                $version = $array[0] . '.' . $array[3];
                self::$class .= '_' . $array[3];
            } elseif (isset($array[4]) && is_numeric($array[4])) {
                $version = $array[0] . '.' . $array[4];
                self::$class .= '_' . $array[4];
            }

            define('APP_VERSION', $version);
        }

    }

    public static function mca()
    {
        if (!isset($_GET['module']) && isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI'])) {
            $uri = self::parseUri();

            if ($uri) {
                $array = explode('/', $uri);

                if (isset($array[0]) && $array[0]) {
                    $_GET['module'] = $array[0];
                    if (isset($array[1]) && $array[1]) {
                        if (is_numeric($array[1])) {
                            $_GET['controller'] = 'detail';
                            $_GET['action']     = 'index';
                            $_GET['id']         = $array[1];
                        } else {
                            $_GET['controller'] = $array[1];
                        }

                        if (isset($array[2]) && $array[2]) {
                            if (is_numeric($array[2])) {
                                $_GET['action'] = 'detail';
                                $_GET['id']     = $array[2];
                            } else {
                                $_GET['action'] = $array[2];
                            }
                        }

                        //静态化
                        $total = count($array);
                        if ($total >= 4) {
                            for ($i = 3; $i < $total;) {
                                $_GET[$array[$i]] = $array[$i + 1];
                                $i += 2;
                            }
                        }
                    }
                }
            }
        }

        $module     = self::initValue('module', 'index');
        $controller = self::initValue('controller', 'index');
        $action     = self::initValue('action', 'index');
        define('MODULE', $module);
        define('CONTROLLER', $controller);
        define('ACTION', $action);

        self::$path  = APP ? APP . DS : '';
        self::$class = 'app\\' . APP . '\\' . 'controller\\' . parsename(MODULE) . '\\' . parsename(CONTROLLER, true);
    }

    public static function ca()
    {
        if (!isset($_GET['controller']) && isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI'])) {
            $uri = self::parseUri();

            if ($uri) {
                $array = explode('/', $uri);
                if (isset($array[0]) && $array[0]) {
                    $_GET['controller'] = $array[0];
                    if (isset($array[1]) && $array[1]) {
                        if (is_numeric($array[1])) {
                            $_GET['action'] = 'detail';
                            $_GET['id']     = $array[1];
                        } else {
                            $_GET['action'] = $array[1];
                        }

                        //静态化
                        $total = count($array);
                        if ($total >= 4) {
                            for ($i = 3; $i < $total;) {
                                $_GET[$array[$i]] = $array[$i + 1];
                                $i += 2;
                            }
                        }
                    }
                }
            }
        }

        $controller = self::initValue('controller', 'index');
        $action     = self::initValue('action', 'index');

        define('CONTROLLER', $controller);
        define('ACTION', $action);
        self::$path  = APP . DS;
        self::$class = 'app\\' . APP . '\\' . 'controller\\' . parsename(CONTROLLER, true);
    }

    //获取直接参数
    private static function initValue($flag, $value)
    {
        $res = (isset($_GET[$flag]) && $_GET[$flag] ? strip_tags($_GET[$flag]) : $value);
        return $res;
    }

    //解析路由
    private static function parseUri()
    {
        $uri = urldecode($_SERVER['REQUEST_URI']);

        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }

        $uri = trim($uri, '/');

        if (!$uri) {
            return false;
        }

        $pos = strpos($uri, '?');

        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        if ($uri) {
            return $uri;
        } else {
            return false;
        }
    }

}
