<?php

namespace app\admin\model\shop;

use think\Model;


class TemplateMsg extends Model
{


    // 表名
    protected $name = 'shop_template_msg';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'category_text'
    ];


    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3'), '4' => __('Type 4')];
    }

    public function getCategoryTextAttr($value, $data)
    {
       $list =   [
           'goods_order'    => '商品订单',
           'renew_order'    => '安装订单',
           'service_order'  => '维修订单'
       ];
        return $list[$data['category']];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }



}
