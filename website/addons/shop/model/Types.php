<?php

namespace addons\shop\model;

use think\Cache;
use think\Db;
use think\Model;
use think\View;

/**
 * 区块模型
 */
class Types extends Model
{
    protected $name = "types";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';

    public static function getTypes($params)
    {
        $typesModel = self::where($params)
            ->order("sort asc")->select();
        return $typesModel;
    }
}