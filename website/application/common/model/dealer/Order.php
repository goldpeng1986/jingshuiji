<?php

namespace app\common\model\dealer;

use think\Hook;
use app\common\model\BaseModel;
use app\common\enum\OrderType as OrderTypeEnum;
use think\Model;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Order extends Model
{
    protected $name = 'dealer_order';

    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听分销商订单行为管理
        $static = new static;
        Hook::listen('DealerOrder', $static);
    }

    /**
     * 订单所属用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User','user_id');
    }
    /**
     * 订单所属用户
     * @return \think\model\relation\BelongsTo
     */
    public function referee()
    {
        return $this->belongsTo('app\common\model\dealer\Referee','user_id','user_id');
    }



    /**
     * 一级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function dealerFirst()
    {
        return $this->belongsTo('User', 'first_user_id');
    }

    /**
     * 二级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function dealerSecond()
    {
        return $this->belongsTo('User', 'second_user_id');
    }

    /**
     * 三级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function dealerThird()
    {
        return $this->belongsTo('User', 'third_user_id');
    }

    public function getCreateTimeAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }

    /**
     * 订单类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value)
    {
        $types = OrderTypeEnum::getTypeName();
        return ['text' => $types[$value], 'value' => $value];
    }
    /**
     * 订单详情
     * @param $where
     * @return Order|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return static::get($where);
    }
    /**
     * 订单详情
     * @param $orderId
     * @param $orderType
     * @return Order|null
     * @throws \think\exception\DbException
     */
    public static function getDetailByOrderId($orderId, $orderType)
    {
        return static::detail(['order_id' => $orderId, 'order_type' => $orderType]);
    }
    /**
     * 验证商品是否存在售后
     * @param $goods
     * @return bool
     */
    private function checkGoodsRefund($goods)
    {
        return !empty($goods['refund'])
            && $goods['refund']['type']['value'] == 10
            && $goods['refund']['is_agree']['value'] != 20;
    }
    public function getList($ids,$page,$limit){
        return $this
            ->where('user_id','in',$ids)
            ->with(['user','referee'])
            ->whereTime('create_time', 'month')
            ->page($page,$limit)
            ->order('user_id','desc')
            ->select();
    }
    public function getlistCount($ids){
        return $this
            ->where('user_id','in',$ids)
            ->with(['user'])
            ->whereTime('create_time', 'month')
            ->count();
    }
}
