<?php

namespace app\admin\model\shop;

use think\Model;

class GoodsRenew extends  Model
{
    // 表名
    protected $name = 'shop_goods_renew';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;


    public function getList($goods_id,$page=1,$limit=20){
        return $this
            ->field('id,renew_year,renew_price')
            ->where('goods_id',$goods_id)
            ->page($page,$limit)
            ->order('renew_year','asc')
            ->where('switch',1)
            ->select();
    }
}