<?php

namespace app\common\model;

use addons\epay\library\Service;
use addons\third\model\Third;
use app\common\model\dealer\Capital;
use think\Db;
use think\Exception;
use think\Model;
use Yansongda\Pay\Exceptions\GatewayException;

/**
 * 会员模型
 */
class User extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array  $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function money($money, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                //$after = $user->money + $money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->save(['money' => $after]);
                //写入日志
                MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 变更会员积分
     * @param int    $score   积分
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function score($score, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $score != 0) {
                $before = $user->score;
                $after = $user->score + $score;
                $level = self::nextlevel($after);
                //更新会员信息
                $user->save(['score' => $after, 'level' => $level]);
                //写入日志
                ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }


    public function payRacharge($money,$user_id){
        $epay = get_addon_info('epay');
        $third = Third::where('platform', 'wechat')
            ->where('apptype', 'miniapp')
            ->where('user_id', $user_id)
            ->find();

        if (!$third) {
           throw new Exception('微信用户获取失败');
        }
        $order_sn = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);

        // 启动事务
        Db::startTrans();
        try {
             UserMoneyOrder::create([
                 'user_id'=>$user_id,
                 'order_sn'=>$order_sn,
                 'pay_state'=>0,
                 'pay_time'=>0,
                 'state'=>1,
                 'money'=>$money,
                 'transaction_id'=>0,
                 'createtime'=>time()
             ]);
            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
        $response = '';
        $openid = $third['openid'];
        $money = (float)$money;
        if ($epay && $epay['state']) {
            $notifyurl =  request()->root(true) . '/addons/shop/order/racharge/type/notify/paytype/wechat';
            //保证取出的金额一致，不一致将导致订单重复错误
            $amount = sprintf("%.2f", $money);
            $params = [
                'amount'    => $amount,
                'orderid'   => $order_sn,
                'type'      => 'wechat',
                'title'     => "支付{$amount}元",
                'notifyurl' => $notifyurl,
                'returnurl' => '',
                'method'    => 'miniapp',
                'openid'    => $openid
            ];
            try {
                $response = Service::submitOrder($params);
            } catch (GatewayException $e) {
                throw new \Exception(config('app_debug') ? $e->getMessage() : "支付失败，请稍后重试");
            }
        } else {
            throw new \Exception("请在后台安装配置微信支付宝整合插件");
        }
        return $response;
    }

    public static function rachargeSetted($order_no){

    }

}
