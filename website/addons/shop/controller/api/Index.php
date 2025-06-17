<?php

namespace addons\shop\controller\api;

use app\common\library\Auth;
use app\common\library\Sms;
use app\common\library\Ems;
use fast\Random;
use think\Validate;
use fast\Http;
use addons\third\library\Service;
use think\Config;
use think\Session;
use addons\shop\model\Block;
use addons\shop\model\Types;
use addons\shop\model\Card;

class Index extends Base
{

    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isGet()) {
            $this->error('请求错误');
        }

//        Auth::instance()->setAllowFields(array_merge(['username'], $this->allowFields));
    }
    //首页
    public function index(){

        $data=[];
        //标题
        $config = get_addon_config('shop');
        $data["title"]=$config["title"];
        //焦点图
        $bannerList = [];
        $list = Block::getBlockListByName('uniappfocus', 5);
        foreach ($list as $index => $item) {
            $bannerList[] = ['image' => cdnurl($item['image'], true), 'url' => $item['url'], 'title' => $item['title']];
        }
        $data['bannerList']=$bannerList;
        //菜单图
        $menuList=[];
        $typeList=Types::getTypes(["type"=>1]);
        foreach ($typeList as $index => $item){
            $menuList[]=[
                'id'=>$item['id'],
                'title'=>$item['type_name'],
                'url'=>$item['type_value'],
                'icon'=>$item['type_desc'],
                'color'=>$item['type_other'],
                'bgClass'=>$item['type_other2']
            ];
        }
        $data['menuList']=$menuList;
        //计费图
        $homeDeviceInfo=Card::get(["id"=>1]);
        $data['homeDeviceInfo']=$homeDeviceInfo["content"];
        $this->success('success',$data);
    }
}
