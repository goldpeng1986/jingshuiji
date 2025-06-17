<?php

namespace addons\shop\model;

use addons\shop\model\Order as OrderModel;
use app\admin\model\shop\OrderAction;
use think\Db;
use think\Exception;
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
        return date('Y-m-d',$value);
    }

    public function getBecomeTimeAttr($value){
        return date('Y-m-d',$value);
    }


    public function getList($order_id,$user_id,$page=1,$limit=20){
        return $this->where('order_id',$order_id)->where('user_id',$user_id)->where('status',20)->order('become_time','desc')->page($page,$limit)->select();
    }

    /**
     * @return void
     * 续费订单预下单
     */
    public static function checkRenew($order_id,$renew_id,$renew_year,$renew_price,$user_id){
         $model = new static();
        $order_sn = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
       return $model::create([
             'order_id'=>$order_id,
             'order_sn'=>$order_sn,
             'user_id'=>$user_id,
             'renew_id'=>$renew_id,
             'year'=>$renew_year,
             'price'=>$renew_price,
         ]);
    }

    /**
     * @ DateTime 2021-06-01
     * @ 订单结算
     * @param string $order_sn      订单号
     * @param float  $payamount     支付金额
     * @param string $transactionid 流水号
     * @return bool
     */
    public static function settle($order_sn, $payamount, $transactionid = '')
    {
        $order = self::where('order_sn', $order_sn)->find();
        if (!$order || $order->pay_status == 20) {
            return false;
        }
        if ($payamount != $order->price) {
            \think\Log::write("[shop][pay][{$order_sn}][订单支付金额不一致]");
            return false;
        }

        // 启动事务
        Db::startTrans();
        try {
            $order->pay_status = 20;
            $order->transactionid = $transactionid;
            $order->pay_time = time();
            $order->save();
            OrderRenew::calculateRenew($order_sn);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        //发送通知
        TemplateMsg::sendTempMsg('goods_order','order_success', $order->order_sn);
        return true;
    }

    /**
     * 根据订单号查询数据
     * @return void
     */
    public static  function orderSn($order_sn){
        $model = new static();
        return $model->where('order_sn',$order_sn)->find();
    }

    /**
     * @return void
     * 支付回调修改预下单并计算时间
     */
    public static function  calculateRenew($order_sn){
        $model = new static();
        $orderInfo = $model::orderSn($order_sn);
        //判断当前订单是否已经支付
        if($orderInfo->status==20){
           return true;
        }
        //获取当前订单时间
        $order = OrderModel::get($orderInfo->order_id);
        if($order->order_renew_status==20){
            $orderInfo->pay_time = $order->order_renew_time;
            $order->order_renew_time = strtotime(date("Y-m-d H:i:s",strtotime("+$orderInfo->year year",$order->order_renew_time)));
            $orderInfo->become_time = $order->order_renew_time;
            $order->order_renew_status=20;
            $order->save();
        }else{
            $order->order_renew_time =strtotime( date("Y-m-d H:i:s",strtotime("+$orderInfo->year year")));
            $orderInfo->become_time = $order->order_renew_time;
            $order->order_renew_status=20;
            $orderInfo->pay_time = time();
            $order->save();
        }
        $orderInfo->status = 20;
      return  $orderInfo->save();
    }


}

