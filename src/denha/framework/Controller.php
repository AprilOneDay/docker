<?php
namespace denha;

class Controller
{
    public $assign;

    /**
     * 赋值
     * @date   2017-05-14T21:30:23+0800
     * @author ChenMingjiang
     * @param  [type]                   $field [description]
     * @param  [type]                   $value [description]
     * @return [type]                          [description]
     */
    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->assign = array_merge($this->assign, $name);
        } else {
            $this->assign[$name] = $value;
        }
    }

    /**
     * 视图显示
     * @date   2017-05-07T21:08:44+0800
     * @author ChenMingjiang
     * @param  string                   $viewPath [description]
     * @param  boolean                  $peg      [true 自定义路径]
     * @return [type]                             [description]
     */
    protected function show($viewPath = '', $peg = false)
    {

        if ($this->assign) {
            // 模板阵列变量分解成为独立变量
            extract($this->assign, EXTR_OVERWRITE);
        }

        if (get('all')) {
            extract(get('all'), EXTR_OVERWRITE);
        }

        if (!$peg) {
            if (!$viewPath) {
                $path = APP_PATH . APP . DS . 'view' . DS . MODULE . DS . CONTROLLER . DS . ACTION . '.html';
            }
            //绝对路径
            elseif (stripos($viewPath, '/') === 0) {
                $path = APP_PATH . APP . DS . 'view' . DS . MODULE . DS . substr($viewPath, 1) . '.html';
            }
            //相对路径
            else {
                $path = APP_PATH . APP . DS . 'view' . DS . MODULE . DS . $viewPath . '.html';
            }
        } else {
            $path = $viewPath;
        }

        if (!is_file($path)) {
            throw new Exception('视图地址' . $path . '不存在');
        }

        $cachePath = DATA_PATH . md5($path) . '.php';
        if (is_file($cachePath) && filemtime($path) == filemtime($cachePath)) {
            include $cachePath;
        } else {
            //处理视图模板
            $template = new Template($path);
            $template->getContent();
            include $template->loadPath;
        }
    }

    /**
     * ajax返回
     * @date   2017-06-13T22:48:29+0800
     * @author ChenMingjiang
     * @param  [type]                   $value [description]
     * @return [type]                          [description]
     */
    protected function ajaxReturn($value)
    {
        header("Content-Type:application/json; charset=utf-8");
        $array = array(
            'status' => true,
            'data'   => array(),
            'msg'    => '操作成功',
        );
        $value = array_merge($array, $value);
        exit(json_encode($value));
    }

    protected function appReturn($value)
    {
        header("Content-Type:application/json; charset=utf-8");
        $array = array(
            'code'   => 200,
            'status' => true,
            'data'   => array(),
            'msg'    => '获取数据成功',
        );
        $value = array_merge($array, $value);
        exit(json_encode($value));
    }

    /**
     * jsonpReturn返回
     * @date   2017-08-07T10:41:59+0800
     * @author ChenMingjiang
     * @param  array                    $value    [description]
     * @param  string                   $callback [description]
     * @return [type]                             [description]
     */
    protected function jsonpReturn(array $value, $callback = '')
    {
        if ($callback) {
            exit($callback . '(' . json_encode($value) . ')');
        } else {
            $this->ajaxReturn($value);
        }

    }
}
