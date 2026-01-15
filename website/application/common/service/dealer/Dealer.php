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
        $dealerCapitalModel = new Capital();
        // $dealerwithdraw = new Withdraw(); // This seems to be for withdrawals, not total income.

        // 计算累计总收益 (佣金收入)
        // 假设 flow_type = 10 代表订单佣金结算收入 (根据sengrantMoney方法中的Capital::add调用)
        // 假设 flow_type = 20 如果有其他类型的佣金收入，也需要包含，或者明确10就是所有佣金收入
        $total_income = $dealerCapitalModel
            ->where('user_id', $user->id)
            ->where('flow_type', 10) // 10 是 sengrantMoney 中记录的类型
            ->sum('money');
        $total_income = $total_income ? floatval($total_income) : 0.00;

        // 计算推广用户总数 (直接推荐的用户)
        // fa_dealer_referee 表中 dealer_id 是推荐人, user_id 是被推荐人
        $total_users = (new Referee())
            ->where('dealer_id', $user->id)
            // ->where('level', 1) // 如果只统计一级用户，取消此行注释
            ->count();
        $total_users = intval($total_users);

        // 推广码 (用户ID)
        $promotion_code = $user->id;

        // 当前可提现佣金 (即用户表中 'money' 字段，因为佣金直接发放到这里)
        $current_commission_balance = floatval($user->money);

        return [
            'promotion_code'           => $promotion_code, // 推广码 (用户ID)
            'total_income'             => $total_income, // 累计总收益
            'total_users'              => $total_users, // 推广用户总数
            'dealer_rule'              => $this->config['dealer_rule'], // 推广规则 (来自配置)
            'current_commission_balance' => $current_commission_balance, // 当前可提现佣金
            'is_dealer'                => $user->is_dealer, // 是否是分销商
            // 'yesday_price' => $dealerCapitalModel->getCapitalTimeData('yesterday',$user->id), // 昨日收益，如果前端需要可以保留
            // 'all_price' => $dealerwithdraw->getWithdrawTimeData($user->id), // 累计提现，如果前端需要可以保留
        ];
    }

    /**
     * 设置计算佣金的静态入口方法
     * @param int $order_id 订单ID
     * @param int $dealer_type 订单类型 (10=商品订单, 20=服务订单等)
     * @param int $user_id 下单用户ID
     * @param float $price 参与计算佣金的金额
     * @return bool|false|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function setcalculateBrokerage($order_id,$dealer_type,$user_id,$price){
        if(!$price){
          return  false;
        }
        $model = new self();
        return $model->calculateBrokerage($order_id,$dealer_type,$user_id,$price);
    }

    /**
     * 计算实际佣金 (核心佣金计算逻辑)
     * @param int $order_id 订单ID
     * @param int $dealer_type 订单类型
     * @param int $user_id 下单用户ID
     * @param float $price 参与计算佣金的金额
     * @return bool|false|\think\Model
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function calculateBrokerage($order_id,$dealer_type=10,$user_id,$price){
        // 分销是否开启
        if(!$this->config['is_dealer']){
            return false; // 分销未开启，不进行计算
        }
        // 获取分销商上级
        $orderModel = new Order(); // 实例化分销订单模型
        $refereeInfo = $this->getDealerUserId($user_id,2); // 获取上两级推荐人
        if($refereeInfo['first_user_id']==0){ // 如果没有一级推荐人，则不进行佣金计算
            return  false;
        }
        $calculatedCapital = $this->getCapitalByOrder($price); // 计算各级佣金金额
        $data = array_merge($refereeInfo,$calculatedCapital,[
            'order_price'   => $calculatedCapital['orderPrice'], // 订单价格 (用于计算佣金的基数)
            'user_id'       => $user_id, // 下单用户ID
            'dealer_type'   => $dealer_type, // 订单类型
            'order_id'      => $order_id, // 原始订单ID
            'create_time'   => time(), // 创建时间
            'is_invalid'    => 0, // 是否失效 (0=否, 1=是)
            'is_settled'    => 0  // 是否已结算 (0=否, 1=是)
        ]);

        unset($data['orderPrice']); // orderPrice 仅用于计算，不存入分销订单表本身
        return $orderModel::create($data); // 创建分销订单记录
    }


    /**
     * 获取当前买家的所有上级分销商用户ID
     * @param int $user_id 当前下单用户ID
     * @param int $level 需要获取的上级层数 (例如2代表获取上两级)
     * @return array 包含各级推荐人ID的数组
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getDealerUserId($user_id, $level)
    {
        $dealerUser = [
            'first_user_id'  => $level >= 1 ? Referee::getRefereeUserId($user_id, 1, true) : 0, // 一级推荐人ID
            'second_user_id' => $level >= 2 ? Referee::getRefereeUserId($user_id, 2, true) : 0, // 二级推荐人ID
            'third_user_id'  => $level == 3 ? Referee::getRefereeUserId($user_id, 3, true) : 0  // 三级推荐人ID (如果配置了三级分销)
        ];
         // 判断二级推荐人是否为员工身份且不能获得佣金 (根据原有逻辑)
        $secondUser = $dealerUser['second_user_id'] ? User::get($dealerUser['second_user_id']) : null;
        if($secondUser && $secondUser->is_dealer == 1){ // 假设 is_dealer = 1 代表特殊身份不能获取二级佣金
            $dealerUser['second_user_id'] = 0; // 置为0，使其不能获得佣金
        }
        return $dealerUser;
    }

    /**
     * 根据订单价格计算各级分销佣金
     * @param float $price 用于计算佣金的订单金额
     * @param int $level 分销层级 (默认2级)
     * @return array 包含各级佣金金额及原始订单价格
     */
    protected function getCapitalByOrder($price,$level=2)
    {
        // 分销订单佣金数据结构初始化
        $capital = [
            'orderPrice'   => $price,    // 订单总金额(不含运费, 用于计算佣金的基数)
            'first_money'  => 0.00,     // 一级分销佣金
            'second_money' => 0.00,     // 二级分销佣金
            'third_money'  => 0.00      // 三级分销佣金 (如果支持)
        ];
        // 计算商品实际佣金 (基于配置的百分比)
        $goodsCapital = $this->calculateGoodsCapital($price);
        // 累积分销佣金到对应层级
        if ($level >= 1) $capital['first_money'] = bcadd($capital['first_money'], $goodsCapital['first_money'], 2);
        if ($level >= 2) $capital['second_money'] = bcadd($capital['second_money'], $goodsCapital['second_money'], 2);
        // if ($level >= 3) $capital['third_money'] = bcadd($capital['third_money'], $goodsCapital['third_money'], 2); // 如果支持三级
        return $capital;
    }

    /**
     * 计算商品实际产生的各级佣金（基于配置的百分比）
     * @param float $goodsPrice 商品价格（或订单中参与分销计算的总价）
     * @return array 包含各级佣金金额
     */
    private function calculateGoodsCapital($goodsPrice)
    {
        // 从配置中获取各级佣金比例
        $first_rate  = isset($this->config['first_money']) ? $this->config['first_money'] * 0.01 : 0;
        $second_rate = isset($this->config['second_money']) ? $this->config['second_money'] * 0.01 : 0;
        // $third_rate  = isset($this->config['third_money']) ? $this->config['third_money'] * 0.01 : 0; // 如果支持三级

        return [
            'first_money'  => bcmul($goodsPrice, $first_rate, 2),  // 一级佣金
            'second_money' => bcmul($goodsPrice, $second_rate, 2), // 二级佣金
            // 'third_money'  => bcmul($goodsPrice, $third_rate, 2)    // 三级佣金
        ];
    }

    /**
     * 发放分销订单佣金给推荐人
     * @param object $value 分销订单记录 (fa_dealer_order 表的记录对象)
     * @return bool 是否成功处理
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function grantMoney($value)
    {
        $price = 0; // 初始化用于计算佣金的订单金额
        // 根据订单类型，从不同的订单表获取已完成的订单信息和支付金额
        if($value->dealer_type == 10){ // 普通商品订单
            $orderinfo = model('addons\shop\model\Order')
                            ->where('order_type',10)
                            ->where('id',$value->order_id)
                            ->where('orderstate',3) // 已完成
                            ->find();
            if(!$orderinfo) return false; // 订单未完成或未找到
            $price = $orderinfo->payamount; // 支付金额
        } elseif ($value->dealer_type == 20){ // 预约订单 (假设与普通订单表结构和状态类似)
             $orderinfo = model('addons\shop\model\Order') // 假设也是用这个Order模型
                            ->where('order_type',20) // 区分订单类型
                            ->where('id',$value->order_id)
                            ->where('orderstate',3) // 已完成 (具体状态可能不同)
                            ->find();
            if(!$orderinfo) return false;
            $price = $orderinfo->payamount; // 支付金额
        } elseif ($value->dealer_type == 30){ // 服务订单
            $orderinfo = model('addons\shop\model\serviceOrder') // 服务订单模型
                            ->where('id',$value->order_id)
                            ->where('order_state',1) // 已完成 (具体状态可能不同)
                            ->where('pay_state',20)  // 已支付 (具体状态可能不同)
                            ->find();
            if(!$orderinfo) return false;
            $price = $orderinfo->pay_price; // 支付金额
        } else {
            return false; // 未知订单类型
        }

        if ($price <= 0) return false; // 订单金额为0或负，不发佣金

        // 重新计算分销佣金 (基于当前配置和实际支付金额)
        $capital = $this->getCapitalByOrder($price);

        // 发放一级分销商佣金
        if ($value->first_user_id > 0 && $capital['first_money'] > 0) {
            $this->sengrantMoney($value->first_user_id, $capital['first_money'], $value->order_id, $value->dealer_type, 1);
        }
        // 发放二级分销商佣金
        if ($value->second_user_id > 0 && $capital['second_money'] > 0) {
            $this->sengrantMoney($value->second_user_id, $capital['second_money'], $value->order_id, $value->dealer_type, 2);
        }
        // 发放三级分销商佣金 (如果支持)
        // if ($value->third_user_id > 0 && $capital['third_money'] > 0) {
        //     $this->sengrantMoney($value->third_user_id, $capital['third_money'], $value->order_id, $value->dealer_type, 3);
        // }

        // 更新分销订单记录状态为已结算
        $value->save([
            'order_price'  => $capital['orderPrice'], // 更新用于计算的订单金额
            'first_money'  => $capital['first_money'], // 更新一级佣金
            'second_money' => $capital['second_money'],// 更新二级佣金
            // 'third_money'  => $capital['third_money'], // 更新三级佣金
            'is_settled'   => 1, // 标记为已结算
            'settle_time'  => time() // 结算时间
        ]);
        echo "分销订单ID: {$value->id} 的佣金已结算。\n"; // 日志或命令行输出
        return true;
    }

    /**
     * 发放佣金给单个用户，并记录资金流水
     * @param int $user_id 用户ID
     * @param float $money 发放的佣金金额
     * @param int $order_id 关联的原始订单ID
     * @param int $dealer_order_type 分销订单类型
     * @param int $level 佣金层级 (1, 2, 3)
     * @return bool
     * @throws \think\Exception
     */
    public  function sengrantMoney($user_id, $money, $order_id = 0, $dealer_order_type = 0, $level = 0){
        // 获取分销商用户模型
        $userModel = User::get($user_id);
        if (!$userModel) {
            echo "错误：未找到用户ID: {$user_id}。\n";
            return false; // 用户不存在
        }
        if ($money <= 0) {
            echo "信息：用户ID: {$user_id} 的佣金金额为0或负，不发放。\n";
            return false; // 佣金为0或负，不处理
        }

        $original_money = $userModel->money; // 用户操作前余额
        // 累加用户可提现佣金 (即余额)
        $userModel->setInc('money', $money);

        // 构造描述信息
        $describe = "订单(ID:{$order_id}, 类型:{$dealer_order_type}, 层级:{$level})佣金结算. ";
        $describe .= "佣金由{$original_money}变为{$userModel->money}.";

        // 记录分销商资金明细 (fa_dealer_capital)
        Capital::add([
            'user_id'     => $user_id,
            'flow_type'   => 10, // 10 = 订单佣金结算 (根据speadUser中的统计逻辑)
            'money'       => $money, // 本次操作金额
            'describe'    => $describe, // 描述
            'create_time' => time()  // 创建时间
        ]);
        // 记录通用用户余额变动日志 (fa_money_log)
        MoneyLog::create([
            'user_id' => $user_id,
            'money'   => $money, // 本次操作金额
            'before'  => $original_money, // 操作前余额
            'after'   => $userModel->money, // 操作后余额
            'memo'    => $describe // 备注
        ]);
        echo "成功：用户ID: {$user_id} 获得佣金 {$money} 元。 {$describe}\n";
        return true;
    }
}