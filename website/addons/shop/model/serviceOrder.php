<?php

namespace addons\shop\model;

use addons\epay\library\Service;
use addons\third\model\Third;
use think\Model;
use traits\model\SoftDelete;
use Yansongda\Pay\Exceptions\GatewayException;

/**
 * 模型
 */
class serviceOrder extends Model
{
    // 表名
    protected $name = 'shop_service_order';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 追加属性
    protected $append = [
        'order_state_text'
    ];

    public function getImageAttr($value){
        if($value){
            $images = [];
            foreach (explode(',',$value) as $list){
                $images[] = cdnurl($list,true);
            }
            return  $images;
        }
        return [];
    }

    public function getOrderStateTextAttr($value,$data){
        $list = [ '待审核','审核通过','拒接','已取消','已完成'];
        return $list[$data['order_state']];
    }

    public function createOrder($param,$user_id){
        $order_sn = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
        $param['user_id'] = $user_id;
        $param['order_sn'] = $order_sn;
       return $this->allowField(true)->save($param);
    }

    public function getList($page,$limit,$order_state,$user_id,$pay_state){
        return $this->where('user_id',$user_id)
            ->where(function ($query)use($order_state,$pay_state){
                 if(isset($order_state)&&$order_state!='all'){
                     $query->where('order_state',$order_state);
                 }
                if(isset($pay_state)&&$pay_state!=0){
                    $query->where('pay_state',$pay_state);
                }
            })
            ->page($page,$limit)
            ->order('create_time','desc')
            ->select();
    }

    public function editOrder($id,$user_id,$where){
        return $this->where('user_id',$user_id)->where('id',$id)->update($where);
    }
    public function pay($id,$user_id,$paytype){
     //执行微信参数
        $order = $this->where('user_id',$user_id)->find($id);
        //创建微信分销订单
        \app\common\service\dealer\Dealer::setcalculateBrokerage($id,30,$user_id,$order->pay_price);
        if($paytype=='money'){
            $usermodel = new \app\common\model\User();
            $userinfo = $usermodel::get($user_id);
            if((float)$userinfo->money<(float)$order->pay_price){
                throw new \Exception("当前余额不足,请充值");
            }
            $usermodel::money(-$order->pay_price,$user_id,"用户维修订单消费");
            ( new serviceOrder())->payCallback($order->order_sn);
            return true;
        }
        $info = get_addon_info('third');
        if (!$info || !$info['state']) {
            $this->error("请在后台安装第三方登录插件");
        }
        $third = Third::where('platform', 'wechat')
            ->where('apptype', 'miniapp')
            ->where('user_id', $user_id)
            ->find();

        if (!$third) {
            $this->error("未找到微信授权的用户信息", 'bind');
        }
        $openid = $third['openid'];

        $response = null;
        $epay = get_addon_info('epay');

        if ($epay && $epay['state']) {
            $notifyurl =  request()->root(true) . '/addons/shop/order/service';
            //保证取出的金额一致，不一致将导致订单重复错误
            $amount = sprintf("%.2f", $order->pay_price);
            $params = [
                'amount'    => $amount,
                'orderid'   => $order->order_sn,
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

    public function payCallback($order_sn){
        $order = $this->where('order_sn',$order_sn)->find();
        if(!$order||$order->pay_state==20){
            return false;
        }
        $order->pay_state=20;
        $order->pay_time = time();
        $order->order_state = 4;
        $order->save();
        return true;
    }
}
