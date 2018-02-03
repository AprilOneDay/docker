<?php
/**
 * 分组管理模块
 */
namespace app\admin\controller\setting;

use app\admin\controller\Init;

class Website extends Init
{
    public function config()
    {

        $data = getVar('config', 'admin.website');

        $other = array(
            'siteCopy' => getVar('site', 'admin.website'),
            'lgCopy'   => getVar('lg', 'admin.website'),
        );

        $this->assign('data', $data);
        $this->assign('other', $other);
        $this->show();
    }

    public function configPost()
    {

        $lg     = post('lg', 'text');
        $siteId = post('site_id', 'intval', 0);

        $data['lg']      = implode(',', $lg);
        $data['site_id'] = $siteId;

        if (!$lg) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请选择语言'));
        }

        if (!$siteId) {
            $this->ajaxReturn(array('status' => false, 'msg' => '请选择网站'));
        }

        $this->setPhpFile(var_export($data, true) . ';');

        $this->ajaxReturn(array('status' => true, 'msg' => '保存成功'));
    }

    /**
     * 保存文件
     * @date   2017-11-22T19:42:16+0800
     * @author ChenMingjiang
     * @param  [type]                   $filename [description]
     * @param  [type]                   $content  [description]
     */
    private function setPhpFile($content)
    {
        $filename = APP_PATH . APP . DS . 'tools' . DS . 'var' . DS . 'website' . DS . 'config.php';
        $fp       = fopen($filename, "w");
        fwrite($fp, "<?php return " . $content);
        fclose($fp);
    }
}
