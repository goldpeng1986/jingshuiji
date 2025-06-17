<?php

namespace app\common\model\dealer;

use app\common\model\User;
use think\Exception;
use think\Model;

class Withdraw extends Model
{
    // 表名
    protected $name = 'dealer_withdraw';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
        'pay_type_text',
        'apply_status_text',
        'create_time_text',
        'audit_time_text'
    ];



    public function getWithdrawTimeData($user_id)
    {
        return $this->where('user_id', $user_id)->sum('money');
    }

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    public function getAuditTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_time']) ? $data['audit_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    public function getPayTypeTextAttr($value, $data)
    {
        $status = [10=>'微信',20=>'支付宝',30=>'银行卡'];
        return $status[$data['pay_type']];
    }

    public function getApplyStatusTextAttr($value, $data)
    {
        $status = [10=>'待审核',20=>'待打款',30=>'驳回',40=>'已打款'];
        return $status[$data['apply_status']];
    }



    public function info($user)
    {
        //累计提现
        $capital = new Capital();
        $all_price = $capital->getlistWithdrawCount($user->id);
        return [
            'all_price' => $all_price,
            'withdraw_price' => $user->dealer_price,
            'withdraw_rate'=>get_addon_config('shop')['withdraw_rate'],
            'withdraw_min'=>get_addon_config('shop')['withdraw_min']
        ];
    }

    public function apply($param, $user)
    {
        $config = get_addon_config('shop');
        //判断当前用户余额是否充足
        if ((float)$user->dealer_price == 0) {
            throw new Exception('当前用户可提现金额不足');
        }
        if ((float)$user->dealer_price < $param['money']) {
            throw new Exception('提现金额不足');
        }
        if ( $param['money']<$config['withdraw_min']) {
            throw new Exception('提现金额最低为'.$config['withdraw_min']);
        }
        $money =  bcmul($param['money'],$config['withdraw_rate']/100,2);
        //整理需要提交的数据
        $list = $this->allowField(true)->save([
            'user_id' => $user->id,
            'money' => bcsub($param['money'],$money,2),
            'pay_type' => $param['pay_type'],
            'withdraw_rate'=>$config['withdraw_rate'],
            'rate_money'=>$money,
            'bank_name' => $param['bank_name'],
            'bank_number' => $param['bank_number'],
            'image' => $param['image']
        ]);
        //减少用户可提现金额
        $user = User::get($user->id);
        $old_price = $user->dealer_price;
        $user->dealer_price = bcsub($user->dealer_price, $param['money'], 2);
        $user->save();
        //新增提现金额
        $capital = new Capital();
        $capital->allowField(true)->save([
            'user_id' => $user->id,
            'flow_type' => 20,
            'money' => $param['money'],
            'describe' => "用户提现佣金由" . $old_price . '变为' . $user->dealer_price
        ]);
        return $list;
    }

    public function getList($uid, $page, $limit)
    {
        return $this->where('user_id', $uid)
            ->order('create_time', 'desc')
            ->page($page, $limit)->select();
    }

    public function getlistCount($uid)
    {
        $money = $this->where('user_id', $uid)->where('apply_status','<>',30)->sum('money');
        return $money;
    }


}