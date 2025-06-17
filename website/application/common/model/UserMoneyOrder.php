<?php

namespace app\common\model;

use think\Model;

class UserMoneyOrder extends Model
{
    // 表名
    protected $name = 'user_money_order';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];




    public static function setted($order_sn,$payamount,$transaction_id){
        $model = new static();
        $order = $model->where('order_sn',$order_sn)->find();
        if(!$order){
            return false;
        }
        if($order->pay_state==1){
            return false;
        }
        if($order->state==2){
            return false;
        }
        if($payamount!=$order->money){
            return false;
        }
        $order->transaction_id = $transaction_id;
        $order->pay_state=1;
        $order->state=2;
        $order->pay_time = time();
        $order->save();
        User::money($order->money,$order->user_id,'用户充值余额');
        return  true;
    }
}