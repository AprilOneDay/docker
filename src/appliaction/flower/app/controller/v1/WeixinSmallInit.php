<?php
namespace app\flower\app\controller\v1;

use denha\Controller;
use denha\Start;

class WeixinSmallInit extends Controller
{
    public $token = '';
    public $uid   = 0;
    public $version;
    public $group;
    public $lg = 'zh'; //返回提示信息语言版本
    public $familySn;

    public function __construct()
    {
        $token = get('token', 'text', '');
        if ($token) {
            $this->group    = 1;
            $this->uid      = auth($token, 'DECODE');
            $this->familySn = table('BillFamily')->where('uid', $this->uid)->field('family_sn')->find('one');
            if (!$this->familySn) {
                $this->appReturn(array('status' => false, 'msg' => '账户信息异常', 'code' => 501));
            }
        }
    }

    /**
     * 验证用户组权限
     * @date   2017-11-23T16:45:03+0800
     * @author ChenMingjiang
     * @param  integer                  $group [description]
     * @return [type]                          [description]
     */
    public function checkIndividual($group = 1)
    {
        if (strpos($group, ',') !== false) {
            $group = explode(',', $group);
        }

        $group = !is_array($group) ? (array) $group : $group;

        if (!$this->uid) {
            $this->appReturn(array('status' => false, 'msg' => '请登录', 'code' => 501));
        }

        if (!in_array($this->group, $group)) {
            $this->appReturn(array('status' => false, 'msg' => '权限不足'));
        }
    }

    /**
     * 商户用户必须通过认证
     * @date   2017-09-21T10:38:24+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function checkIde()
    {
        $isIde = table('UserShop')->where(array('uid' => $this->uid))->field('is_ide')->find('one');
        if ($isIde == 2) {
            $this->appReturn(array('status' => false, 'msg' => '认证未通过,请在店铺资料修改中修改后重新提交'));
        } elseif ($isIde == 0) {
            $this->appReturn(array('status' => false, 'msg' => '请先申请认证,或等待认证通过后操作'));
        } elseif ($isIde == 3) {
            $this->appReturn(array('status' => false, 'msg' => '认证审核中请耐心等待，或联系管理员'));
        }
    }

    protected function appReturn($value)
    {
        parent::appReturn($value, $this->lg);
    }

    /**
     * 上传图片并替换对于旧图片
     * @date   2017-09-14T11:54:39+0800
     * @author ChenMingjiang
     * @param  [type]                   $files [上传相册]
     * @param  string                   $merge [需要合并的相册]
     * @param  string                   $path  [保存文件]
     * @return [type]                          [description]
     */
    public function appUpload($files, $merge = '', $path = '')
    {
        $data = '';
        if ($files) {
            $reslut = dao('Upload')->uploadfile($files, $path);
            if (!$reslut['status']) {
                $this->appReturn($reslut);
            }
        } else {
            $reslut['data']['name'] = array();
        }

        if (is_array($merge)) {
            foreach ($merge as $key => $value) {
                $url         = array();
                $url         = pathinfo($value);
                $merge[$key] = $url['basename'];
            }
            //替换数组
            $data = implode(',', array_filter(array_replace($merge, $reslut['data']['name'])));
        } else {
            $data = implode(',', $reslut['data']['name']);
        }

        return $data;
    }

    /**
     * 转换一维数组成二维数组
     * @date   2017-09-15T09:31:28+0800
     * @author ChenMingjiang
     * @param  [type]                   $data [description]
     * @return [type]                         [description]
     */
    public function appArray($data)
    {
        foreach ($data as $key => $value) {
            $listTemp[] = array('id' => $key, 'value' => $value);
        }

        $listTemp = isset($listTemp) ? $listTemp : array();

        return $listTemp;
    }

    /**
     * app切割图片
     * @date   2017-09-18T11:21:31+0800
     * @author ChenMingjiang
     * @param  string                   $data [description]
     * @param  [type]                   $path [description]
     * @param  integer                  $size [description]
     * @return [type]                         [description]
     */
    public function appImgArray($data = '', $path = '', $size = 0)
    {
        $data = $data ? (array) imgUrl($data, $path, 0, Start::$config['imgUrl']) : array();
        return (array) $data;
    }

    public function appImg($data = '', $path = '', $size = 0)
    {

        $data = imgUrl($data, $path, 0, Start::$config['imgUrl']);
        return (string) $data;
    }
}
