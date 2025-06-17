<?php

namespace addons\shop\controller\api;

use addons\shop\model\Block as BlockModel; // 引入Block模型

/**
 * 通用区块/Banner API
 * @ApiWeigh (5) // 可选的API排序权重
 */
class Block extends Base
{
    /**
     * @var array 无需登录即可访问的方法列表
     */
    protected $noNeedLogin = ['getListByType'];

    /**
     * 根据类型获取区块/Banner列表
     * @ApiMethod (GET)
     * @ApiParams (name="type", type="string", required=true, description="区块类型标识", sample="uniapp_shop_banner")
     */
    public function getListByType()
    {
        $type = $this->request->param('type'); // 从请求中获取type参数

        if (!$type) {
            $this->error(__('Invalid parameters: type is required')); // 无效参数：type是必需的
        }

        // 调用模型方法获取数据
        $list = BlockModel::getBlockListByType($type);

        if ($list) {
            $this->success(__('Banners retrieved successfully'), $list); // 成功获取Banner列表
        } else {
            $this->success(__('No banners found for this type'), []); // 未找到该类型的Banner
        }
    }
}
