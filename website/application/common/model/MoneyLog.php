<?php

namespace app\common\model;

use think\Model;

/**
 * 会员余额日志模型
 */
class MoneyLog extends Model
{

    // 表名
    protected $name = 'user_money_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'create_time_text'
    ];

    public  function getCreateTimeTextAttr($value,$data){
        if($data['createtime']){
            return date('Y-m-d',$data['createtime']);
        }
        return  '';
    }
    public  function getCreatetimeAttr($value){
        if($value){
            return date('Y-m-d H:i:s',$value);
        }
    }

    public function getList($uid,$page,$limit){
        return $this->where('user_id',$uid)
            ->order('createtime','desc')
            ->page($page,$limit)->select();
    }
    public function getlistCount($uid){
        return $this->where('user_id',$uid)->sum('money');
    }
}
