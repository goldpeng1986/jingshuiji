<?php

namespace app\common\service\dealer;

use app\common\model\dealer\Order;
use app\common\model\dealer\Referee;
use app\common\model\dealer\Withdraw;
use app\common\model\dealer\Agent;
use app\common\model\dealer\Capital;
use app\common\model\MoneyLog;
use app\common\model\User;

class Dealer
{
    public $model;

    protected $config;

    public function __construct()
    {
        $this->model = new Agent();
        $this->config = get_addon_config('shop');
    }
    public function speadUser($user)
    {
        $dealerCapital = new Capital();
        $dealerwithdraw = new Withdraw();
        $yesday_price = $dealerCapital->getCapitalTimeData('yesterday',$user->id);
        $all_price = $dealerwithdraw->getWithdrawTimeData($user->id);
        return [
            'yesday_price' => $yesday_price,
            'all_price' => $all_price,
            'dealer_rule'=>$this->config['dealer_rule'],
            'dealer_price'=>$user->money,
            'is_dealer'=>$user->is_dealer
        ];
    }
    public static function setcalculateBrokerage($order_id,$dealer_type,$user_id,$price){
        if(!$price){
          return  false;
        }
        $model = new self();
        return $model->calculateBrokerage($order_id,$dealer_type,$user_id,$price);
    }

    public function calculateBrokerage($order_id,$dealer_type=10,$user_id,$price){
        //分销是否开启
        if(!$this->config['is_dealer']){
            return false;
        }
        //获取分销商上级
        $order = new Order();
        $referee = $this->getDealerUserId($user_id,2);
        if($referee['first_user_id']==0){
            return  false;
        }
        $capital = $this->getCapitalByOrder($price);
        $data = array_merge($referee,$capital,[
            'order_price'=>$capital['orderPrice'],
            'user_id'=>$user_id,
            'dealer_type'=>$dealer_type,
            'order_id'=>$order_id,
            'create_time'=>time(),
            'is_invalid'=>0,
            'is_settled'=>0
        ]);

        unset($data['orderPrice']);
        return $order::create($data);
    }


    /**
     * 获取当前买家的所有上级分销商用户id
     * @param $user_id
     * @param $level
     * @param $self_buy
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getDealerUserId($user_id, $level)
    {
        $dealerUser = [
            'first_user_id' => $level >= 1 ? Referee::getRefereeUserId($user_id, 1, true) : 0,
            'second_user_id' => $level >= 2 ? Referee::getRefereeUserId($user_id, 2, true) : 0,
            'third_user_id' => $level == 3 ? Referee::getRefereeUserId($user_id, 3, true) : 0
        ];
         //判断一下二级是否为员工
        $user_dealer = $dealerUser['second_user_id']?User::get($dealerUser['second_user_id'])->is_dealer:0;
        if($user_dealer==1){
            $dealerUser['second_user_id'] = 0;
        }
        return $dealerUser;
    }

    /**
     * 计算订单分销佣金
     * @param $order
     * @return array
     */
    protected function getCapitalByOrder($price,$level=2)
    {
        // 分销订单佣金数据
        $capital = [
            // 订单总金额(不含运费)
            'orderPrice' => $price,
            // 一级分销佣金
            'first_money' => 0.00,
            // 二级分销佣金
            'second_money' => 0.00,
            'third_money'=>0.00
        ];
        // 计算商品实际佣金
        $goodsCapital = $this->calculateGoodsCapital($price);
        // 累积分销佣金
        $level >= 1 && $capital['first_money'] += $goodsCapital['first_money'];
        $level >= 2 && $capital['second_money'] += $goodsCapital['second_money'];
        return $capital;
    }

    /**
     * 计算商品实际佣金
     * @param $setting
     * @param $goods
     * @param $goodsPrice
     * @return array
     */
    private function calculateGoodsCapital($goodsPrice)
    {
        return [
            'first_money' => bcmul($goodsPrice , ($this->config['first_money']*0.01),2),
            'second_money' => bcmul($goodsPrice ,($this->config['second_money'] *0.01 ),2)
        ];

    }

    /**
     * 发放分销订单佣金
     * @param array|\think\Model $order 订单详情
     * @param int $orderType 订单类型
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public  function grantMoney($value)
    {
        // 订单是否已完成
        if($value->dealer_type==10){
            $orderinfo =   model('addons\shop\model\Order')->where('order_type',10)->where('id',$value->order_id)->where('orderstate ',3)->find();
            if(!$orderinfo){
                return  false;
            }
            $price = $orderinfo->payamount;
        }
        if($value->dealer_type==20){
            $orderinfo =   model('addons\shop\model\Order')->where('id',$value->order_id)->where('orderstate ',3)->find();
            if(!$orderinfo){
                return  false;
            }
            $price = $orderinfo->payamount;

        }
        if($value->dealer_type==30){
            $orderinfo =   model('addons\shop\model\serviceOrder')->where('id',$value->order_id)->where('order_state ',1)->where('pay_state',20)->find();
            if(!$orderinfo){
                return  false;
            }
            $price = $orderinfo->pay_price;

        }
        // 重新计算分销佣金
        $capital = $this->getCapitalByOrder($price);
        // 发放一级分销商佣金
        $value['first_user_id'] > 0 &&$this->sengrantMoney($value['first_user_id'], $capital['first_money']);
        // 发放二级分销商佣金
        $value['second_user_id'] > 0 && $this->sengrantMoney($value['second_user_id'], $capital['second_money']);
        // 发放三级分销商佣金
        $value['third_user_id'] > 0 && $this->sengrantMoney($value['third_user_id'], $capital['third_money']);
        // 更新分销订单记录
         $value->save([
            'order_price' => $capital['orderPrice'],
            'first_money' => $capital['first_money'],
            'second_money' => $capital['second_money'],
            'third_money' => $capital['third_money'],
            'is_settled' => 1,
            'settle_time' => time()
        ]);
        echo $value->id.'佣金订单结算成功';
    }

    public  function sengrantMoney($user_id,$money){
        // 分销商详情
        $model = User::get($user_id);
        if (!$model) {
            return false;
        }
        $dealer_price_old = $model->money;
        // 累积分销商可提现佣金
        $model->setInc('money', $money);
        // 记录分销商资金明细
        Capital::add([
            'user_id' => $user_id,
            'flow_type' => 10,
            'money' => $money,
            'describe' => '订单佣金结算,'.'佣金由'.$dealer_price_old.'变为'.$model->money,
            'create_time'=>time()
        ]);
        MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $dealer_price_old, 'after' => $model->money, 'memo' => '订单佣金结算,'.'佣金由'.$dealer_price_old.'变为'.$model->money]);
        return true;
    }

}