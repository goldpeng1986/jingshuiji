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
    protected $noNeedLogin = ['init', 'area'];

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
}
