<?php

namespace addons\shop\controller\api;

use addons\shop\model\OrderRenew;
use addons\shop\model\TemplateMsg;
use addons\shop\model\UserCoupon;
use addons\shop\model\Carts;
use addons\shop\model\Order as OrderModel;
use addons\shop\model\Address;
use addons\third\model\Third;
use addons\shop\model\OrderAction;
use addons\shop\model\OrderGoods;
use addons\shop\library\KdApiExpOrder;
use app\admin\model\shop\GoodsRenew;
use think\Cache;
use think\Db;

/**
 * 订单接口
 */
class Order extends Base
{
    protected $noNeedLogin = [];


    //计算邮费，判断商品
    public function carts()
    {
        $config = get_addon_config('shop');
        $cart_ids = $this->request->post('ids');
        $address_id = $this->request->post('address_id/d'); //地址id
        if (empty($cart_ids)) {
            $this->error('参数缺失');
        }
        $user_coupon_id = $this->request->post('user_coupon_id/d'); //优惠券
        $address = Address::get($address_id);
        $orderInfo = [
            'order_sn'    => '',
            'amount'      => 0, //订单金额（包含运费）
            'shippingfee' => 0, //运费
            'goodsprice'  => 0 //商品金额
        ];
        $goodsList = [];
        $area_id = !empty($address) ? $address->area_id : 0;
        try {
            list($orderItem, $goodsList) = OrderModel::computeCarts($orderInfo, $cart_ids, $this->auth->id, $area_id, $user_coupon_id);
            if (empty($goodsList)) {
                throw new \Exception("未找到商品");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        foreach ($goodsList as $item) {
            $item->category_id = $item->goods->category_id;
            $item->brand_id = $item->goods->brand_id;
            $item->goods->visible(explode(',', 'id,title,image,price,marketprice'));
        }
        //获取我的可以使用的优惠券
        $goods_ids = array_column($goodsList, 'goods_id');
        $category_ids = array_column($goodsList, 'category_id');
        $brand_ids = array_column($goodsList, 'brand_id');
        $this->success('获取成功', [
            'coupons'          => UserCoupon::myGoodsCoupon($this->auth->id, $goods_ids, $category_ids, $brand_ids),
            'goods_list'       => $goodsList,
            'order_info'       => $orderInfo,
            'couponTotalPrice' => floatval(!isset($config['shippingfeecoupon']) || $config['shippingfeecoupon'] == 0 ? $orderInfo['goodsprice'] : $orderInfo['amount'])
        ]);
    }

    //提交订单
    public function add()
    {
        $cart_ids = $this->request->post('ids'); // 购物车商品ID集合
        $address_id = $this->request->post('address_id/d'); // 地址ID
        $user_coupon_id = $this->request->post('user_coupon_id/d'); // 用户优惠券ID
        $memo = $this->request->post('memo', ''); // 用户备注

        // 新增：获取预约安装日期和时间段
        $appointmentDate = $this->request->post('date', ''); // 预约日期，例如 '2023-12-25'
        $appointmentTimeSlot = $this->request->post('time_slot', ''); // 预约时间段，例如 '09:00-12:00'

        if (empty($address_id)) {
            $this->error('请选择地址'); //错误：请选择地址
        }
        if (empty($cart_ids)) {
            $this->error('请选择商品'); //错误：请选择商品
        }
        // 校验购物车ID的合法性，确保它们属于当前用户
        $row = (new Carts)->where('id', 'IN', $cart_ids)->where('user_id', '<>', $this->auth->id)->find();
        if ($row) {
            $this->error('存在不合法购物车数据'); //错误：存在不合法的购物车数据
        }

        $order = null;
        try {
            // 调用模型创建订单，并传递新增的预约时间和日期参数
            $order = OrderModel::createOrder(
                $address_id,
                $this->auth->id,
                $cart_ids,
                $user_coupon_id,
                $memo,
                $appointmentDate,      // 新增参数
                $appointmentTimeSlot   // 新增参数
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage()); // 返回模型中抛出的具体错误信息
        }
        $this->success('下单成功！', $order); //成功：下单成功！
    }

    //订单详情
    public function detail()
    {
        $order_sn = $this->request->param('order_sn');
        if (empty($order_sn)) {
            $this->error('参数缺失');
        }
        $order = OrderModel::getDetail($order_sn, $this->auth->id);
        if (empty($order)) {
            $this->error('未找到订单');
        }
        $order->append(['status_text']);
        $order->hidden(explode(',', 'method,transactionid,updatetime,deletetime'));
        $order->expiretime = $order->expiretime - time();
        $this->success('', $order);
    }


    public function renewDetail(){
        $order_sn = $this->request->param('order_sn');
        if (empty($order_sn)) {
            $this->error('参数缺失');
        }
        $order = OrderModel::getDetail($order_sn, $this->auth->id);
        if (empty($order)) {
            $this->error('未找到订单');
        }
        //通过goods_id寻找
        $goods_renew = (new GoodsRenew())->getList($order->order_goods[0]['goods_id']);
        $order->renew = $goods_renew;
        //获取倒计时时间
        $secondsDifference  = $order->order_renew_time-time();
        $order->order_renew_time =$secondsDifference>0?$secondsDifference:0;
        $order->user_renew = (new OrderRenew())->getList($order->id,$this->auth->id,1,3);
        $order->append(['status_text']);
        $order->hidden(explode(',', 'method,transactionid,updatetime,deletetime'));
        $order->expiretime = $order->expiretime - time();
        $this->success('', $order);
    }

    //订单列表
    public function index()
    {
        $param = $this->request->param();
        $param['user_id'] = $this->auth->id;
        $list = OrderModel::tableList($param);
        foreach ($list as $item) {
            $item->append(['status_text']);
            //判断订单是否过期
            if($item->order_renew_status==20){
                $item->order_renew_time = date('Y-m-d H:i:s', $item->order_renew_time);
            }
            $field = 'id,order_sn,amount,expressno,expressname,install_status,order_renew_status,order_renew_time,saleamount,shippingfee,paystate,orderstate,shippingstate,order_goods,status,status_text';
            $item->visible(explode(',', $field));
        }
        $this->success('获取成功', $list);
    }

    //取消订单
    public function cancel()
    {
        $order_sn = $this->request->post('order_sn');
        if (!$order_sn) {
            $this->error('参数错误');
        }
        $order = OrderModel::getByOrderSn($order_sn);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('不能越权操作');
        }
        if ($order->status == 'hidden') {
            $this->error('订单已失效！');
        }
        //可以取消
        if (!$order->paystate && !$order->orderstate) {
            // 启动事务
            Db::startTrans();
            try {
                $order->orderstate = 1;
                $order->canceltime = time();
                $order->save();
                foreach ($order->order_goods as $item) {
                    $sku = $item->sku;
                    $goods = $item->goods;
                    //商品库存恢复
                    if ($sku) {
                        $sku->setInc('stocks', $item->nums);
                    }
                    if ($goods) {
                        $goods->setInc('stocks', $item->nums);
                    }
                }
                //恢复优惠券
                UserCoupon::resetUserCoupon($order->user_coupon_id, $order->order_sn);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error('订单取消失败');
            }
            //记录操作
            OrderAction::push($order->order_sn, '系统', '订单取消成功');
            $this->success('订单取消成功！', $order['status']);
        } else {
            $this->error('订单不允许取消');
        }
    }

    //续费支付
    public function renewPay(){
        $order_sn = $this->request->post('order_sn');
        $paytype = $this->request->post('paytype');
        $method = $this->request->post('method');
        $appid = $this->request->post('appid'); //为APP的应用id
        $returnurl = $this->request->post('returnurl', '', 'trim');
        $method = $this->request->post('method');
        $renew_id = $this->request->post('renew_id');
        $openid = '';
        //非H5 支付,非余额
        if (in_array($method, ['miniapp', 'mp']) && $paytype == 'wechat') {
            $info = get_addon_info('third');
            if (!$info || !$info['state']) {
                $this->error("请在后台安装第三方登录插件");
            }
            $third = Third::where('platform', 'wechat')
                ->where('apptype', $method)
                ->where('user_id', $this->auth->id)
                ->find();

            if (!$third) {
                $this->error("未找到微信授权的用户信息", 'bind');
            }
            $openid = $third['openid'];
        }
        $orderInfo = OrderModel::getByOrderSn($order_sn);
        if (!$orderInfo) {
            $this->error("未找到指定的订单");
        }
        //查询续费金额年份
        $renewinfo = GoodsRenew::get($renew_id);
        Db::startTrans();
        $response = null;
        //个人续费订单预下单
        try {
            //订单支付参数返回
            $renew = OrderRenew::checkRenew($orderInfo->id,$renew_id,$renewinfo->renew_year,$renewinfo->renew_price,$this->auth->id);
             \app\common\service\dealer\Dealer::setcalculateBrokerage($orderInfo->id,20,$this->auth->id,$renewinfo->renew_price);
             $response = OrderModel::payRenew($renew, $this->auth->id, $paytype, $method, $openid, '', $returnurl);
            Db::commit();
        } catch (\Exception $e) {
            $this->error("订单已失效");
        }
        $this->success("请求成功", $response);
    }

    //订单支付
    public function pay()
    {
        $order_sn = $this->request->post('order_sn');
        $paytype = $this->request->post('paytype');
        $method = $this->request->post('method');
        $appid = $this->request->post('appid'); //为APP的应用id
        $returnurl = $this->request->post('returnurl', '', 'trim');
        $openid = '';
        //非H5 支付,非余额
        if (in_array($method, ['miniapp', 'mp']) && $paytype == 'wechat') {
            $info = get_addon_info('third');
            if (!$info || !$info['state']) {
                $this->error("请在后台安装第三方登录插件");
            }
            $third = Third::where('platform', 'wechat')
                ->where('apptype', $method)
                ->where('user_id', $this->auth->id)
                ->find();

            if (!$third) {
                $this->error("未找到微信授权的用户信息", 'bind');
            }
            $openid = $third['openid'];
        }
        $orderInfo = OrderModel::getByOrderSn($order_sn);
        if (!$orderInfo) {
            $this->error("未找到指定的订单");
        }
        if ($orderInfo['paystate']) {
            $this->error("订单已经支付，请勿重复支付");
        }
        //订单过期
        if (!$orderInfo['orderstate'] && time() > $orderInfo['expiretime']) {
            // 启动事务
            Db::startTrans();
            try {
                $orderInfo->save(['orderstate' => 2]);
                //库存恢复
                OrderGoods::setGoodsStocksInc($orderInfo->order_sn);
                //恢复优惠券
                UserCoupon::resetUserCoupon($orderInfo->user_coupon_id, $orderInfo->order_sn);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
            $this->error("订单已失效");
        }
        $response = null;
        try {
            $response = OrderModel::pay($order_sn, $this->auth->id, $paytype, $method, $openid, '', $returnurl);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("请求成功", $response);
    }

    //确认收货
    public function takedelivery()
    {
        $order_sn = $this->request->post('order_sn');
        if (!$order_sn) {
            $this->error('参数错误');
        }
        $order = OrderModel::getByOrderSn($order_sn);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('不能越权操作');
        }
        if ($order->status == 'hidden') {
            $this->error('订单已失效！');
        }
        if ($order->paystate == 1 && !$order->orderstate && $order->shippingstate == 1) {
            $order->shippingstate = 2;
            $order->receivetime = time();
            $order->save();
            TemplateMsg::sendTempMsg('goods_order','order_success', $order->order_sn);
            //记录操作
            OrderAction::push($order->order_sn, '系统', '订单确认收货成功');
            $this->success('确认收货成功');
        }
        $this->error('未可确认收货');
    }

    //查询物流
    public function logistics()
    {
        $order_sn = $this->request->param('order_sn');
        if (empty($order_sn)) {
            $this->error('参数缺失');
        }
        $order = OrderModel::getDetail($order_sn, $this->auth->id);
        if (empty($order)) {
            $this->error('未找到订单');
        }
        if (!$order->shippingstate) {
            $this->error('订单未发货');
        }
        $electronics = Db::name('shop_order_electronics')->where('order_sn', $order_sn)->where('status', 0)->find();
        if (!$electronics) {
            $this->error('订单未发货');
        }
        $result = KdApiExpOrder::getLogisticsQuery([
            'order_sn'      => $order_sn,
            'logistic_code' => $electronics['logistic_code'],
            'shipper_code'  => $electronics['shipper_code']
        ]);
        if ($result['Success']) {
            $this->success('查询成功', $result['Traces']);
        }
        $this->error('查询失败');
    }
}
