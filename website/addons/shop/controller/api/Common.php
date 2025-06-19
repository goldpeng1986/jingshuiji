<?php

namespace addons\shop\controller\api;

use addons\shop\library\Theme;
use think\Config;
use think\Hook;
use addons\shop\model\Navigation;
use addons\shop\model\Area;
use addons\shop\model\Block;
use addons\shop\model\SearchLog;

/**
 * 公共
 */
class Common extends Base
{
    // 将 aboutus 添加到 noNeedLogin 数组
    protected $noNeedLogin = ['init', 'area', 'aboutus'];

    /**
     * 初始化
     */
    public function init()
    {
        //配置信息
        $upload = Config::get('upload');

        //如果非服务端中转模式需要修改为中转
        if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
            //临时修改上传模式为服务端中转
            set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

            $upload = \app\common\model\Config::upload();
            // 上传信息配置后
            Hook::listen("upload_config_init", $upload);

            $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
        }

        $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
        $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

        $data = [
            'upload'    => $upload,
            '__token__' => $this->request->token()
        ];

        //焦点图
        $bannerList = [];
        $list = Block::getBlockListByName('uniappfocus', 5);
        foreach ($list as $index => $item) {
            $bannerList[] = ['image' => cdnurl($item['image'], true), 'url' => $item['url'], 'title' => $item['title']];
        }
        $data['swiper'] = $bannerList;

        $data['order_timeout'] = Config::get('shop.order_timeout');
        $data['sitename'] = Config::get('shop.sitename');
        $data['notice'] = Config::get('shop.notice');
        $data['phone'] = Config::get('shop.phone');
        $data['logisticstype'] = Config::get('shop.logisticstype');

        $data['category_mode'] = Config::get('shop.category_mode');
        $data['money_score'] = Config::get('shop.money_score');
        $data['comment_score'] = Config::get('shop.comment_score');

        $data['renew_notes'] = Config::get('shop.renew_notes');
        $data['xufei_nodes'] = Config::get('shop.xufei_nodes');
        $data['racharge_nodes'] = Config::get('shop.racharge_nodes');


        $data['defaultpaytype'] = Config::get('shop.defaultpaytype');
        $data['default_goods_img'] = cdnurl(Config::get('shop.default_goods_img'), true);
        $data['default_category_img'] = cdnurl(Config::get('shop.default_category_img'), true);

        $data['navigate'] = Navigation::tableList();
        $data['brands'] = \addons\shop\model\Brand::field('id,name')->order('weigh desc')->select();

        //消息订阅模板id
        $data['tpl_ids'] = \addons\shop\model\TemplateMsg::getTplIds();
        //热门搜索关键词
        $data['hot_keyword'] = SearchLog::order('nums desc')->limit(10)->column('keywords');

        //合并主题样式，判断是否预览模式
        $isPreview = stripos($this->request->SERVER("HTTP_REFERER"), "mode=preview") !== false;
        $themeConfig = $isPreview && \think\Session::get("previewtheme-shop") ? \think\Session::get("previewtheme-shop") : Theme::get();

        $themeConfig = Theme::render($themeConfig);
        $data = array_merge($data, $themeConfig);

        $this->success('', $data);
    }

    /**
     * 读取省市区数据,联动列表
     */
    public function area()
    {

        $province = $this->request->param('province', '');
        $city = $this->request->param('city', '');
        $where = ['pid' => 0, 'level' => 1];
        $provincelist = null;
        if ($province !== '') {
            $where['pid'] = $province;
            $where['level'] = 2;
        }
        if ($city !== '') {
            $where['pid'] = $city;
            $where['level'] = 3;
        }
        $provincelist = Area::where($where)->field('id as value,name as label')->where('status', 'normal')->select();
        $this->success('', $provincelist);
    }

    /**
     * 关于我们
     * @ApiMethod (GET)
     */
    public function aboutus()
    {
        // 实际项目中，这些数据可能来自数据库的配置表或特定表
        // 例如: $config = 	hink\Config::get('shop_about_us');
        // 或者: $aboutModel = new ddons\shop\model\AboutUs(); $info = $aboutModel->find();

        // 模拟数据
        $siteConfig = Config::get('site'); // 尝试从全局站点配置获取信息
        $shopConfig = Config::get('shop'); // 尝试从插件配置获取信息

        $appName = isset($shopConfig['name']) && !empty($shopConfig['name']) ? $shopConfig['name'] : (isset($siteConfig['name']) ? $siteConfig['name'] : '森泽共享净水机');
        $defaultLogo = '/assets/addons/shop/img/logo.png'; // 假设的默认logo路径
        // cdnurl 需要确保在没有上下文时也能正确生成URL，或者提供一个完整的URL
        $logoUrl = isset($shopConfig['logo']) && !empty($shopConfig['logo']) ? cdnurl($shopConfig['logo'], true) : cdnurl($defaultLogo, true);


        $aboutData = [
            'appName'        => $appName,
            'version'        => isset($shopConfig['version']) ? $shopConfig['version'] : '1.0.1', // 示例版本号
            'logoUrl'        => $logoUrl,
            'description'    => isset($shopConfig['description']) && !empty($shopConfig['description']) ? $shopConfig['description'] : '森泽共享净水机致力于提供便捷、高效、经济的共享净水服务，让每个人都能轻松享受健康饮水。我们采用先进的物联网技术和多级过滤系统，确保水质安全纯净。',
            'features'       => [ // 功能特点 - 示例
                '便捷扫码，即开即用',
                '多重过滤，水质纯净',
                '智能监控，安全可靠',
                '多种套餐，经济实惠',
                '在线充值，方便快捷'
            ],
            'companyName'    => isset($shopConfig['company_name']) && !empty($shopConfig['company_name']) ? $shopConfig['company_name'] : '森泽科技有限公司',
            'websiteUrl'     => isset($shopConfig['website_url']) && !empty($shopConfig['website_url']) ? $shopConfig['website_url'] : 'www.senzejingshui.com',
            'contactPhone'   => isset($shopConfig['contact_phone']) && !empty($shopConfig['contact_phone']) ? $shopConfig['contact_phone'] : '400-000-0001',
            'contactEmail'   => isset($shopConfig['contact_email']) && !empty($shopConfig['contact_email']) ? $shopConfig['contact_email'] : 'service@senzejingshui.com',
            'companyAddress' => isset($shopConfig['company_address']) && !empty($shopConfig['company_address']) ? $shopConfig['company_address'] : '中国北京市朝阳区建国路88号SOHO现代城A座1201室',
            'copyright'      => isset($shopConfig['copyright']) && !empty($shopConfig['copyright']) ? $shopConfig['copyright'] : ('© ' . date('Y') . ' ' . $appName . ' 版权所有')
        ];

        // 兼容前端可能期望数据在 'info' 或 'data' 键下
        $this->success('获取成功', ['info' => $aboutData, 'data' => $aboutData]);
    }
}
