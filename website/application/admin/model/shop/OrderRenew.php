<?php

namespace app\admin\model\shop;

use think\Model;

class OrderRenew extends Model
{
    // 表名
    protected $name = 'shop_order_renew';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    protected $hidden = [
        'updatetime'
    ];
    public function getPayTimeAttr($value){
        return date('Y-m-d H:i:s',$value);
    }

    public function getBecomeTimeAttr($value){
        if(!$value){
            return  '';
        }
        return date('Y-m-d H:i:s',$value);
    }

}

