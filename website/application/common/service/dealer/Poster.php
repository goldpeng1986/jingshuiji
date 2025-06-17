<?php
namespace app\common\service\dealer;

use app\common\service\dealer\wechat\Qrcode;
use Grafika\Color;
use Grafika\Grafika;

class Poster
{
    private $dealer;
    private $config;

    /**
     * 构造方法
     * Poster constructor.
     * @param $dealer
     * @throws \Exception
     */
    public function __construct($dealer)
    {
        $this->dealer = $dealer;
        $this->config = get_addon_config('shop');
    }

    public function getImage(){

        //获取海报封面图
        $poster =$this->config['poster'];
        //下载背景图
        $posterImage =$this->saveTempImage(request()->domain().$poster, 'backdrop');
        //下载小程序码
        $wxQrcode = $this->saveQrcode('uid:' . $this->dealer->id);
        // 4. 拼接海报图
        return $this->savePoster($posterImage, $wxQrcode);

    }

    /**
     * 获取网络图片到临时目录
     * @param $wxappId
     * @param $url
     * @param string $mark
     * @return string
     */
    protected function saveTempImage( $url, $mark = 'temp')
    {
        $dirPath = RUNTIME_PATH . 'image' ;
        !is_dir($dirPath) && mkdir($dirPath, 0755, true);
        $savePath = $dirPath . '/' . $mark . '_' . md5($url) . '.png';
        if (file_exists($savePath)) return $savePath;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $img = curl_exec($ch);
        curl_close($ch);
        $fp = fopen($savePath, 'w');
        fwrite($fp, $img);
        fclose($fp);
        return $savePath;
    }

    /**
     * 保存小程序码到文件
     * @param $wxappId
     * @param $scene
     * @param null $page
     * @return string
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    protected function saveQrcode( $scene, $page = null)
    {
        // 文件目录
        $dirPath = RUNTIME_PATH . 'image';
        !is_dir($dirPath) && mkdir($dirPath, 0755, true);
        // 文件名称
        $fileName = 'qrcode_' . md5( $scene . $page) . '.png';
        // 文件路径
        $savePath = "{$dirPath}/{$fileName}";
        if (file_exists($savePath)) return $savePath;
        // 请求api获取小程序码
        $Qrcode = new Qrcode($this->config['wx_appid'],$this->config['wx_app_secret']);
        $content = $Qrcode->getQrcode($scene, $page);
        // 保存到文件
        file_put_contents($savePath, $content);
        return $savePath;
    }

    /**
     * 海报图文件路径
     * @return string
     */
    private function getPosterPath($mark = 'dealer')
    {
        // 保存路径
        $dirPath = $_SERVER['DOCUMENT_ROOT'].'/'. 'dealer' . '/' ;
        !is_dir($dirPath) && mkdir($dirPath, 0755, true);
        return $dirPath . $this->getPosterName();
    }

    /**
     * 海报图文件名称
     * @return string
     */
    private function getPosterName()
    {
        return md5('poster_' . $this->dealer->id) . '.png';
    }

    /**
     * 海报图url
     * @return string
     */
    private function getPosterUrl()
    {

        return  request()->domain(). '/dealer/' . $this->getPosterName() . '?t=' . time();
    }
    /**
     * 拼接海报图
     * @param $backdrop
     * @param $avatarUrl
     * @param $qrcode
     * @return string
     * @throws \Exception
     */
    private function savePoster($backdrop,$qrcode)
    {
        // 实例化图像编辑器
        $editor = Grafika::createEditor(['Gd']);
        // 打开海报背景图
        $editor->open($backdropImage, $backdrop);
        // 生成圆形小程序码
//        $this->circular($qrcode, $qrcode);
        // 打开小程序码
        $editor->open($qrcodeImage, $qrcode);
        $qrcodeWidth = 120;
        $editor->resizeExact($qrcodeImage, $qrcodeWidth, $qrcodeWidth);
        // 小程序码添加到背景图
        $qrcodeX = 300;
        $qrcodeY = 690;
        $editor->blend($backdropImage, $qrcodeImage, 'normal', 1.0, 'top-left', $qrcodeX, $qrcodeY);
        // 写入用户昵称
        $fontSize = 12 * 2 * 0.76;
        $fontX = 50;
        $fontY =  720;
        $Color = new Color('#05429f');
        $fontPath = Grafika::fontsDir() . DS . 'st-heiti-light.ttc';
        $editor->text($backdropImage, $this->dealer->nickname, $fontSize, $fontX, $fontY, $Color, $fontPath);
        $fontSize = 14 * 2 * 0.76;
        $fontX = 50;
        $fontY =  760;
        $Color = new Color('#05429f');
        $fontPath = Grafika::fontsDir() . DS . 'st-heiti-light.ttc';
        $editor->text($backdropImage,'邀请你来赚钱', $fontSize, $fontX, $fontY, $Color, $fontPath);

        // 保存图片
        $editor->save($backdropImage, $this->getPosterPath());
        return $this->getPosterUrl();
    }

}