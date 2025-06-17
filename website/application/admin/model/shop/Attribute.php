<?php

namespace app\admin\model\shop;

use think\Model;


class Attribute extends Model
{


    // 表名
    protected $name = 'shop_attribute';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];


    public function getTypeList()
    {
        return ['radio' => __('Type radio'), 'checkbox' => __('Type checkbox')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function AttributeValue()
    {
        return $this->hasMany('AttributeValue', 'attribute_id', 'id');
    }
}
