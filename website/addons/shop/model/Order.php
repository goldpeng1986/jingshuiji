<?php

namespace addons\shop\model;

use app\admin\model\shop\OrderAction;
use app\admin\model\shop\Reneworder;
use app\job\Brokerage;
use think\Db;
use think\Exception;
use think\Model;
use Yansongda\Pay\Exceptions\GatewayException;
use addons\epay\library\Service;
use traits\model\SoftDelete;

/**
 * 模型
 */
class Order extends Model
{

    use SoftDelete;
    // 表名
    protected $name = 'shop_order';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    // 追加属性
    protected $append = [
        'url'
    ];
    protected static $config = [];

    protected static $tagCount = 0;

    protected static function init()
    {
        $config = get_addon_config('shop');
        self::$config = $config;
    }

    public function getUrlAttr($value, $data)
    {
        return url('shop.order/detail', ['orderid' => $data['order_sn']]);
    }

    public function getPayurlAttr($value, $data)
    {
        return addon_url('shop/payment/index') . '?orderid=' . $data['order_sn'];
    }

    public function getCommenturlAttr($value, $data)
    {
        return url('shop.comment/post') . '?orderid=' . $data['order_sn'];
    }

    /**
     * 获取快递查询URL
     */
    public function getLogisticsurlAttr($value, $data)
    {
        $url = self::$config['logisticstype'] == 'kdnapi' ? url('shop.order/logistics') . '?orderid=' . $data['order_sn'] : "https://www.kuaidi100.com/chaxun?com={$data['expressname']}&nu={$data['expressno']}";
        return $url;
    }


    public function getOrderstateList()
    {
        return ['0' => __('Orderstate 0'), '1' => __('Orderstate 1'), '2' => __('Orderstate 2'), '3' => __('Orderstate 3'), '4' => __('Orderstate 4'), '5' => __('Orderstate 5')];
    }

    public function getShippingstateList()
    {
        return ['0' => __('Shippingstate 0'), '1' => __('Shippingstate 1'), '2' => __('Shippingstate 2'), '3' => __('Shippingstate 3')];
    }

    public function getPaystateList()
    {
        return ['0' => __('Paystate 0'), '1' => __('Paystate 1')];
    }



    public function getOrderstateTextAttr($value, $data)
    {
        $value = $value ? $value : $data['orderstate'];
        $list = $this->getOrderstateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getShippingstateTextAttr($value, $data)
    {
        $value = $value ? $value : $data['shippingstate'];
        $list = $this->getShippingstateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPaystateTextAttr($value, $data)
    {
        $value = $value ? $value : $data['paystate'];
        $list = $this->getPaystateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
//          if((strtotime($data['order_renew_time'])-strtotime("+".get_addon_config('shop')['renew_time']))>0){
//              return  '即将到期';
//          }
        if ($data['orderstate'] == 0) {
            if ($data['paystate'] == 0) {
                return '待付款';
            }
            if ($data['shippingstate'] == 0) {
                return '待发货';
            } else if ($data['shippingstate'] == 1) {
                return '待收货';
            } else if ($data['shippingstate'] == 2) {
                return '待评价';
            }
        } else if ($data['orderstate'] == 1) {
            return '已取消';
        } else if ($data['orderstate'] == 2) {
            return '已失效';
        } else if ($data['orderstate'] == 3) {
            return '已完成';
        } else if ($data['orderstate'] == 4) {
            return '退货/退款中';
        }
        return '未知';
    }

    //获取订单剩余有效时长
    public function getRemainsecondsAttr($value, $data)
    {
        return max(0, $data['expiretime'] - time());
    }

    //计算购物车商品
    public static function computeCarts(&$orderInfo, $cart_ids, $user_id, $area_id, $user_coupon_id = '')
    {
        $config = get_addon_config('shop');
        $goodsList = Carts::getGoodsList($cart_ids, $user_id);

        if (empty($goodsList)) {
            throw new \Exception("未找到商品");
        }
        $orderInfo['amount'] = 0;
        $orderInfo['goodsprice'] = 0;
        $orderInfo['shippingfee'] = 0;
        $orderInfo['discount'] = 0;
        $orderItem = [];
        $shippingTemp = [];
        $userCoupon = null;
        //校验领取和是否可使用
        if ($user_coupon_id) {
            $userCouponModel = new UserCoupon();
            $userCoupon = $userCouponModel->checkUserOrUse($user_coupon_id, $user_id);
            $orderInfo['user_coupon_id'] = $user_coupon_id;
        }
        //判断商品库存和状态
        foreach ($goodsList as $item) {
            $goodsItem = [];
            $goodsItem['goods_type'] = $item->goods->goods_type;
            if (empty($item->goods) && empty($item->sku)) {
                throw new \Exception("商品已下架");
            }
            //规格
            if ($item->goods_sku_id && empty($item->sku)) {
                throw new \Exception("商品规格不存在");
            }
            if (!empty($item->sku)) { //规格计算
                if ($item->sku->stocks < $item->nums) {
                    throw new \Exception("有商品库存不足,请返回购物车重新修改");
                }
                $goodsItem['image'] = !empty($item->sku->image) ? $item->sku->image : $item->goods->image;
                $goodsItem['price'] = $item->sku->price;
                $goodsItem['marketprice'] = $item->sku->marketprice;
                $goodsItem['goods_sn'] = $item->sku->goods_sn;
                $amount = bcmul($item->sku->price, $item->nums, 2);
            } else { //商品默认计算
                if ($item->goods->stocks < $item->nums) {
                    throw new \Exception("有商品库存不足,请返回购物车重新修改");
                }
                $goodsItem['image'] = !empty($item->sku->image) ? $item->sku->image : $item->goods->image;
                $goodsItem['price'] = $item->goods->price;
                $goodsItem['marketprice'] = $item->goods->marketprice;
                $goodsItem['goods_sn'] = $item->goods->goods_sn;
                $amount = bcmul($item->goods->price, $item->nums, 2);
            }
            $goodsItem['amount'] = $amount;
            //订单总价
            $orderInfo['amount'] = bcadd($orderInfo['amount'], $amount, 2);
            //商品总价
            $orderInfo['goodsprice'] = bcadd($orderInfo['goodsprice'], $amount, 2);

            $freight_id = $item->goods->freight_id;
            //计算邮费【合并运费模板】
            if (!isset($shippingTemp[$freight_id])) {
                $shippingTemp[$freight_id] = [
                    'nums'   => $item->nums,
                    'weight' => $item->goods->weight,
                    'amount' => $amount
                ];
            } else {
                $shippingTemp[$freight_id] = [
                    'nums'   => bcadd($shippingTemp[$freight_id]['nums'], $item->nums, 2),
                    'weight' => bcadd($shippingTemp[$freight_id]['weight'], $item->goods->weight, 2),
                    'amount' => bcadd($shippingTemp[$freight_id]['amount'], $amount, 2)
                ];
            }
            //创建订单商品数据
            $orderItem[] = array_merge($goodsItem, [
                'order_sn'     => $orderInfo['order_sn'],
                'goods_id'     => $item->goods_id,
                'title'        => $item->goods->title,
                'url'          => $item->goods->url,
                'nums'         => $item->nums,
                'goods_sku_id' => $item->goods_sku_id,
                'attrdata'     => $item->sku_attr,
                'weight'       => $item->goods->weight,
                'category_id'  => $item->goods->category_id,
                'brand_id'     => $item->goods->brand_id,
            ]);
        }
        //按运费模板计算
        foreach ($shippingTemp as $key => $item) {
            $shippingfee = Freight::calculate($key, $area_id, $item['nums'], $item['weight'], $item['amount']);
            $orderInfo['shippingfee'] = bcadd($orderInfo['shippingfee'], $shippingfee, 2);
        }

        //订单总价(含邮费)
        $orderInfo['amount'] = bcadd($orderInfo['goodsprice'], $orderInfo['shippingfee'], 2);

        if (!empty($userCoupon)) {
            //校验优惠券
            $goods_ids = array_column($orderItem, 'goods_id');
            $category_ids = array_column($orderItem, 'category_id');
            $brand_ids = array_column($orderItem, 'brand_id');
            $couponModel = new Coupon();
            $coupon = $couponModel->getCoupon($userCoupon['coupon_id'])
                ->checkCoupon()
                ->checkOpen()
                ->checkUseTime($userCoupon['createtime'])
                ->checkConditionGoods($goods_ids, $user_id, $category_ids, $brand_ids);

            //计算折扣金额，判断是使用不含运费，还是含运费的金额
            $amount = !isset($config['shippingfeecoupon']) || $config['shippingfeecoupon'] == 0 ? $orderInfo['goodsprice'] : $orderInfo['amount'];
            list($new_money, $coupon_money) = $coupon->doBuy($amount);

            //判断优惠金额是否超出总价，超出则直接设定优惠金额为总价
            $orderInfo['discount'] = $coupon_money > $amount ? $amount : $coupon_money;
        }

        //计算订单的应付金额【减去折扣】
        $orderInfo['saleamount'] = max(0, bcsub($orderInfo['amount'], $orderInfo['discount'], 2));
        $orderInfo['discount'] = bcadd($orderInfo['discount'], 0, 2);

        return [
            $orderItem,
            $goodsList,
            $userCoupon
        ];
    }

    /**
     * @ DateTime 2021-05-28
     * @ 创建订单
     * @param int    $address_id
     * @param int    $user_id
     * @param mixed  $cart_ids
     * @param string $memo 用户备注
     * @param string $appointmentDate 预约安装日期 (可选)
     * @param string $appointmentTimeSlot 预约安装时间段 (可选)
     * @return Order|null 创建成功返回订单对象，否则抛出异常或返回null
     * @throws \Exception
     */
    public static function createOrder(
        $address_id,
        $user_id,
        $cart_ids,
        $user_coupon_id,
        $memo,
        $appointmentDate = null, // 新增参数：预约日期
        $appointmentTimeSlot = null // 新增参数：预约时间段
    ) {
        $address = Address::get($address_id);
        if (!$address || $address['user_id'] != $user_id) {
            throw new \Exception("地址未找到");
        }
        $config = get_addon_config('shop');
        $order_sn = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
        //订单主表
        $orderInfo = [
            'user_id'     => $user_id,
            'order_sn'    => $order_sn,
            'address_id'  => $address->id,
            'province_id' => $address->province_id,
            'city_id'     => $address->city_id,
            'area_id'     => $address->area_id,
            'receiver'    => $address->receiver,
            'mobile'      => $address->mobile,
            'address'     => $address->address,
            'zipcode'     => $address->zipcode,
            'order_type'=>10,
            'goodsprice'  => 0, //商品金额 (不含运费)
            'amount'      => 0, //总金额 (含运费)
            'shippingfee' => 0, //运费
            'discount'    => 0, //优惠金额
            'saleamount'  => 0,
            'memo'        => $memo,
            'expiretime'  => time() + $config['order_timeout'], //订单失效
            'status'      => 'normal',
            // 假设数据库中已有这些字段，如果没有，需要先通过数据库迁移添加
            // 'appointment_date' => $appointmentDate,
            // 'appointment_timeslot' => $appointmentTimeSlot,
        ];

        // 处理预约时间：如果数据库没有专用字段，则附加到备注中
        // **重要提示**: 最佳实践是为预约日期和时间段在 `fa_shop_order` 表中创建专用字段。
        // 以下代码作为一种临时措施，如果字段不存在，则将信息存入备注。
        $appointmentInfoString = '';
        if (!empty($appointmentDate)) {
            $appointmentInfoString .= " 预约日期：" . trim($appointmentDate);
        }
        if (!empty($appointmentTimeSlot)) {
            $appointmentInfoString .= " 时间段：" . trim($appointmentTimeSlot);
        }

        if (!empty($appointmentInfoString)) {
            // 检查 fa_shop_order 表是否有 appointment_date 和 appointment_timeslot 字段
            // 这里我们不能直接检查，所以按计划写入备注，并提醒用户。
            // 更好的方式是检查 $orderInfo 模型（或其父模型）的字段列表，但为简化，直接附加到memo
            $orderInfo['memo'] = trim($memo . $appointmentInfoString);
            // 如果有专用字段，则应如下赋值:
            // $orderInfo['appointment_date'] = $appointmentDate;
            // $orderInfo['appointment_timeslot'] = $appointmentTimeSlot;
            // 并在上方 $orderInfo 定义中取消注释相应行。
            // **开发者注意**: 请检查 `fa_shop_order` 表结构。如果缺少 `appointment_date` (DATE) 和
            // `appointment_timeslot` (VARCHAR) 字段，请添加它们以正确存储预约信息。
            // 当前实现会将预约信息附加到订单备注中。
        }


        //订单详细表
        list($orderItem, $goodsList, $userCoupon) = self::computeCarts($orderInfo, $cart_ids, $user_id, $address->area_id, $user_coupon_id);
        if(count($orderItem)==1){ // 如果订单只有一个商品
           if($orderItem[0]['goods_type']==20){ // 且该商品类型为20 (假设为服务类或需要安装的商品)
               $orderInfo['order_type']=20; // 则将订单类型也标记为20
           }
        }
        $order = null;
        Db::startTrans(); // 启动数据库事务
        try {
            //创建订单
            $order = Order::create($orderInfo, true);
            //减库存
            foreach ($goodsList as $index => $item) {
                if ($item->sku) {
                    $item->sku->setDec('stocks', $item->nums);
                }
                $item->goods->setDec("stocks", $item->nums);
            }
            //计算单个商品折扣后的价格
            $saleamount = bcsub($order['saleamount'], $order['shippingfee'], 2);
            $saleratio = bcdiv($saleamount, $order['goodsprice'], 10);
            $saleremains = $saleamount;
            foreach ($orderItem as $index => &$item) {
                if (!isset($orderItem[$index + 1])) {
                    $saleprice = $saleremains;
                } else {
                    $saleprice = $order['discount'] == 0 ? bcmul($item['price'], $item['nums'], 2) : bcmul(bcmul($item['price'], $item['nums'], 2), $saleratio, 2);
                }
                $saleremains = bcsub($saleremains, $saleprice, 2);
                $item['realprice'] = $saleprice;
            }
            unset($item);
            //创建订单商品数据
            foreach ($orderItem as $index => $item) {
                OrderGoods::create($item, true);
            }
            //修改地址使用次数
            $address->setInc('usednums');
            //优惠券已使用
            if (!empty($userCoupon)) {
                $userCoupon->save(['is_used' => 2]);
            }
            //只有商品订单结算余额
            if($order->order_type==10){
                \app\common\service\dealer\Dealer::setcalculateBrokerage($order->id,$order->order_type,$order->user_id,$order->saleamount);
            }
            //计算分销商订单
            //提交事务
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        //清空购物车
        Carts::clear($cart_ids);
        //记录操作
        OrderAction::push($order_sn, '系统', '订单创建成功');
        //订单应付金额为0时直接结算
        if ($order['saleamount'] == 0) {
            self::settle($order->order_sn, 0);
        }
        return $order;
    }

    /**
     * @ DateTime 2021-05-31
     * @ 订单信息
     * @param $order_sn
     * @param $user_id
     * @return array|false|\PDOStatement|string|Model
     */
    public static function getDetail($order_sn, $user_id)
    {
        return self::with(['orderGoods'])->where('order_sn', $order_sn)->where('user_id', $user_id)->find();
    }

    /**
     * @ 支付
     * @param string $orderid
     * @param int    $user_id
     * @param string $paytype
     * @param string $method
     * @param string $openid
     * @param string $notifyurl
     * @param string $returnurl
     * @return \addons\epay\library\Collection|\addons\epay\library\RedirectResponse|\addons\epay\library\Response|null
     * @throws \Exception
     */
    public static function payRenew($order, $user_id, $paytype, $method = 'web', $openid = '', $notifyurl = null, $returnurl = null)
    {
        $request = \think\Request::instance();
        if (!$order) {
            throw new \Exception('订单不存在！');
        }
        $order_sn = $order->order_sn;

        if($paytype=='money'){
            $usermodel = new \app\common\model\User();
            $userinfo = $usermodel::get($user_id);
            if((float)$userinfo->money<(float)$order->price){
                throw new \Exception("当前余额不足,请充值");
            }
            $usermodel::money(-$order->price,$user_id,"用户续费订单消费");
            \addons\shop\model\OrderRenew::settle($order_sn, $order->price, '');
           return  true;
        }
        $response = null;
        $epay = get_addon_info('epay');
        if ($epay && $epay['state']) {
            $notifyurl = $notifyurl ? $notifyurl : $request->root(true) . '/addons/shop/order/renew/type/notify/paytype/' . $paytype;
            //保证取出的金额一致，不一致将导致订单重复错误
            $amount = sprintf("%.2f", $order->price);
            $params = [
                'amount'    => $amount,
                'orderid'   => $order_sn,
                'type'      => $paytype,
                'title'     => "支付{$amount}元",
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
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


    /**
     * @ 支付
     * @param string $orderid
     * @param int    $user_id
     * @param string $paytype
     * @param string $method
     * @param string $openid
     * @param string $notifyurl
     * @param string $returnurl
     * @return \addons\epay\library\Collection|\addons\epay\library\RedirectResponse|\addons\epay\library\Response|null
     * @throws \Exception
     */
    public static function pay($orderid, $user_id, $paytype, $method = 'web', $openid = '', $notifyurl = null, $returnurl = null)
    {
        $request = \think\Request::instance();
        $order = self::getDetail($orderid, $user_id);
        if (!$order) {
            throw new \Exception('订单不存在！');
        }
        if ($order->paystate) {
            throw new \Exception('订单已支付！');
        }
        if ($order->orderstate) {
            throw new \Exception('订单已失效！');
        }
        //支付金额为0，无需支付
        if ($order->saleamount == 0) {
            throw new \Exception('无需支付！');
        }
        $order_sn = $order->order_sn;

        // 启动事务
        Db::startTrans();
        try {

            //支付方式变更
            if (($order['method'] && $order['paytype'] == $paytype && $order['method'] != $method)) {
                $order_sn = date("Ymdhis") . sprintf("%08d", $user_id) . mt_rand(1000, 9999);
                //更新电子面单
                $electronics = $order->order_electronics;
                foreach ($electronics as $aftersales) {
                    $aftersales->order_sn = $order_sn;
                    $aftersales->save();
                }
                //更新操作日志
                $orderAction = $order->order_action;
                foreach ($orderAction as $action) {
                    $action->order_sn = $order_sn;
                    $action->save();
                }
                $order->save(['order_sn' => $order_sn]);
                //更新订单明细
                foreach ($order->order_goods as $item) {
                    $item->order_sn = $order_sn;
                    $item->save();
                }
            }
            //更新支付类型和方法
            $order->save(['paytype' => $paytype, 'method' => $method]);
            //提交事务
            Db::commit();
        } catch (\Exception $e) {

            // 回滚事务
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
        //判断是否为余额支付
        $response = null;
       if($paytype=='money'){
          //判断用户余额是否存在
           $usermodel = new \app\common\model\User();
           $userinfo = $usermodel::get($user_id);
           if((float)$userinfo->money<(float)$order->saleamount){
               throw new \Exception("当前余额不足,请充值");
           }
           $usermodel::money(-$order->saleamount,$user_id,"用户购买商品");
           self::settle($order->order_sn, $order->saleamount);
       }else{
           $epay = get_addon_info('epay');

           if ($epay && $epay['state']) {

               $notifyurl = $notifyurl ? $notifyurl : $request->root(true) . '/addons/shop/order/epay/type/notify/paytype/' . $paytype;
               $returnurl = $returnurl ? $returnurl : $request->root(true) . '/addons/shop/order/epay/type/return/paytype/' . $paytype . '/order_sn/' . $order_sn;

               //保证取出的金额一致，不一致将导致订单重复错误
               $amount = sprintf("%.2f", $order->saleamount);

               $params = [
                   'amount'    => $amount,
                   'orderid'   => $order_sn,
                   'type'      => $paytype,
                   'title'     => "支付{$amount}元",
                   'notifyurl' => $notifyurl,
                   'returnurl' => $returnurl,
                   'method'    => $method,
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
       }
        $response['order_type'] =   $order->order_type;
        return $response;
    }


    /**
     * 订单列表
     *
     * @param $param
     * @return \think\Paginator
     */
    public static function tableList($param)
    {
        $pageNum = 15;
        if (isset($param['num']) && !empty($param['num'])) {
            $pageNum = $param['num'];
        }
        return self::with(['orderGoods'])
            ->where(function ($query) use ($param) {
                $query->where('status', 'normal');
                if (isset($param['user_id']) && !empty($param['user_id'])) {
                    $query->where('user_id', $param['user_id']);
                }
                if (isset($param['orderstate']) && $param['orderstate'] != '') {
                    $query->where('orderstate', $param['orderstate']);
                }
                if (isset($param['shippingstate']) && $param['shippingstate'] != '') {
                    $query->where('shippingstate', $param['shippingstate']);
                }
                if (isset($param['paystate']) && $param['paystate'] != '') {
                    $query->where('paystate', $param['paystate']);
                }
                if(isset($param['order_type']) && $param['order_type'] != ''){
                    $query->where('order_type', $param['order_type']);
                }
                if(isset($param['order_renew_status']) && $param['order_renew_status'] != ''){
                    $query->where('order_renew_status', $param['order_renew_status']);
                }
                if(isset($param['order_refus']) && $param['order_refus'] != ''){
                    $query->whereTime('order_renew_time','between',[date('Y-m-d H:i:s'),date('Y-m-d H:i:s',strtotime("+".get_addon_config('shop')['renew_time']."day"))]);
                }
                if (isset($param['q']) && $param['q'] != '') {
                    $query->where('order_sn', 'in', function ($query) use ($param) {
                        return $query->name('shop_order_goods')->where('order_sn|title', 'like', '%' . $param['q'] . '%')->field('order_sn');
                    });
                }
            })
            ->order('createtime desc')->paginate($pageNum, false, ['query' => request()->get()]);
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
        $order = self::with(['orderGoods'])->where('order_sn', $order_sn)->find();
        if (!$order || $order->paystate == 1) {
            return false;
        }

        if ($payamount != $order->saleamount) {
            \think\Log::write("[shop][pay][{$order_sn}][订单支付金额不一致]");
            return false;
        }

        // 启动事务
        Db::startTrans();
        try {
            $order->paystate = 1;
            $order->transactionid = $transactionid;
            $order->payamount = $payamount;
            $order->paytime = time();
            $order->paytype = !$order->paytype ? 'system' : $order->paytype;
            $order->method = !$order->method ? 'system' : $order->method;
            $order->save();
            if ($order->payamount == $order->saleamount) {
                //支付完成后,商品销量+1
                foreach ($order->order_goods as $item) {
                    $goods = $item->goods;
                    $sku = $item->sku;
                    if ($goods) {
                        $goods->setInc('sales', $item->nums);
                    }
                    if ($sku) {
                        $sku->setInc('sales', $item->nums);
                    }
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        //记录操作
        OrderAction::push($order_sn, '系统', '订单支付成功');
        //发送通知
        TemplateMsg::sendTempMsg('goods_order','order_success', $order->order_sn);
        return true;
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function address()
    {
        return $this->belongsTo('Address', 'address_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function orderGoods()
    {
        return $this->hasMany('OrderGoods', 'order_sn', 'order_sn');
    }

    public function orderElectronics()
    {
        return $this->hasMany('OrderElectronics', 'order_sn', 'order_sn');
    }

    public function orderAction()
    {
        return $this->hasMany('OrderAction', 'order_sn', 'order_sn');
    }

    /**
     * Get User's Today and Current Month Expenses
     *
     * @param int $userId
     * @return array
     */
    public static function getUserExpenses($userId)
    {
        $today_start = strtotime('today');
        $today_end = strtotime('today +1 day') - 1;

        $month_start = strtotime(date('Y-m-01'));
        $month_end = strtotime(date('Y-m-01', strtotime('+1 month'))) - 1;

        $today_expense = Db::name('shop_order')
            ->where('user_id', $userId)
            ->where('paystate', 1) // Paid
            ->where('createtime', '>=', $today_start)
            ->where('createtime', '<=', $today_end)
            ->sum('payamount');

        $month_expense = Db::name('shop_order')
            ->where('user_id', $userId)
            ->where('paystate', 1) // Paid
            ->where('createtime', '>=', $month_start)
            ->where('createtime', '<=', $month_end)
            ->sum('payamount');

        return [
            'today_expense' => $today_expense ? floatval($today_expense) : 0.00,
            'month_expense' => $month_expense ? floatval($month_expense) : 0.00,
        ];
    }

    /**
     * 获取用户使用记录统计
     * @param int $userId 用户ID
     * @param string $dateType 日期类型: 'current_month', 'last_month', 'YYYY-MM'
     * @param string|null $customMonth 自定义月份 (格式 'YYYY-MM'), 当 $dateType 为 'YYYY-MM' 或类似值时使用
     * @return array 包含 count, amount, volume 的数组
     */
    public static function getUserUsageStatistics($userId, $dateType = 'current_month', $customMonth = null)
    {
        $query = Db::name('shop_order')
            ->alias('o')
            ->join('__SHOP_ORDER_GOODS__ og', 'o.order_sn = og.order_sn', 'LEFT') // LEFT JOIN 获取商品信息
            ->where('o.user_id', $userId)
            ->where('o.paystate', 1); // 已支付的订单

        // 处理日期范围
        $beginTime = null;
        $endTime = null;

        if ($customMonth && preg_match('/^\d{4}-\d{2}$/', $customMonth)) { // 直接使用 YYYY-MM 作为 dateType
            $beginTime = strtotime($customMonth . '-01');
            $endTime = strtotime('+1 month', $beginTime) - 1;
        } elseif ($dateType === 'current_month') {
            $beginTime = strtotime(date('Y-m-01'));
            $endTime = strtotime(date('Y-m-01', strtotime('+1 month'))) - 1;
        } elseif ($dateType === 'last_month') {
            $beginTime = strtotime(date('Y-m-01', strtotime('-1 month')));
            $endTime = strtotime(date('Y-m-01')) - 1;
        }
        // 如果 $dateType 是其他值且 $customMonth 未能解析，则可能不应用时间过滤或根据需求调整

        if ($beginTime && $endTime) {
            $query->whereBetween('o.createtime', [$beginTime, $endTime]);
        }

        // 计算统计数据
        // 使用 distinct o.id 避免因 join shop_order_goods 导致订单重复计算总数和总金额
        $count = $query->count('DISTINCT o.id');
        // 总金额也需要基于去重后的订单
        $amount = Db::name('shop_order') // 单独查询总金额以确保准确性
            ->alias('o_sum')
            ->where('o_sum.user_id', $userId)
            ->where('o_sum.paystate', 1);
        if ($beginTime && $endTime) {
             $amount->whereBetween('o_sum.createtime', [$beginTime, $endTime]);
        }
        $totalAmount = $amount->sum('payamount');

        // 计算 volume (所有订单商品数量总和)
        // 这里的 $query 已经包含了 JOIN 和时间过滤
        $totalVolume = (clone $query)->sum('og.nums');


        return [
            'count'  => $count ? intval($count) : 0, // 订单数量
            'amount' => $totalAmount ? floatval($totalAmount) : 0.00, // 支付总金额
            'volume' => $totalVolume ? intval($totalVolume) : 0, // 商品总件数 (作为 volume)
        ];
    }

    /**
     * 获取用户使用记录列表 (分页)
     * @param int $userId 用户ID
     * @param array $params 包含筛选和分页参数的数组
     * @return \think\Paginator
     */
    public static function getUserUsageRecords($userId, $params)
    {
        $query = self::alias('o') // 使用 self::alias 可以在当前模型上操作
            ->join('__SHOP_ORDER_GOODS__ og', 'o.order_sn = og.order_sn', 'LEFT')
            ->join('__SHOP_GOODS__ g', 'og.goods_id = g.id', 'LEFT')
            ->where('o.user_id', $userId)
            ->where('o.paystate', 1); // 已支付

        // 处理时间范围筛选
        if (isset($params['filter_type'])) {
            $beginTime = null;
            $endTime = null;
            switch ($params['filter_type']) {
                case 'current_month':
                    $beginTime = strtotime(date('Y-m-01'));
                    $endTime = strtotime(date('Y-m-01', strtotime('+1 month'))) - 1;
                    break;
                case 'last_month':
                    $beginTime = strtotime(date('Y-m-01', strtotime('-1 month')));
                    $endTime = strtotime(date('Y-m-01')) - 1;
                    break;
                case 'custom_date_range':
                    if (!empty($params['date_from'])) {
                        $beginTime = strtotime($params['date_from']);
                    }
                    if (!empty($params['date_to'])) {
                        $endTime = strtotime($params['date_to'] . ' 23:59:59'); // 包含当天
                    }
                    break;
            }
            if ($beginTime && $endTime) {
                $query->whereBetween('o.createtime', [$beginTime, $endTime]);
            } elseif ($beginTime) {
                $query->where('o.createtime', '>=', $beginTime);
            } elseif ($endTime) {
                $query->where('o.createtime', '<=', $endTime);
            }
        }

        // 按特定商品ID筛选
        if (!empty($params['goods_id'])) {
            $query->where('og.goods_id', $params['goods_id']);
        }

        // 排序
        $orderby = isset($params['orderby']) && in_array($params['orderby'], ['createtime', 'payamount']) ? 'o.' . $params['orderby'] : 'o.createtime';
        $orderway = isset($params['orderway']) && in_array(strtolower($params['orderway']), ['asc', 'desc']) ? $params['orderway'] : 'desc';
        $query->order($orderby, $orderway);

        // 选择需要的字段，注意处理可能的重复 (如果一个订单有多个商品，这里取第一个商品作为代表)
        // 使用 group by o.id 确保每个订单只出现一次，同时获取其第一个商品的代表信息
        // 注意：MySQL的GROUP BY默认行为可能不符合预期，最好明确聚合函数或使用子查询/窗口函数，但简单场景下可以
        $query->group('o.id');
        $query->field([
            'o.id as record_id',
            'o.order_sn',
            'o.createtime as time',
            'o.payamount as amount',
            'o.address',
            'MIN(og.title) as device_name', // 取第一个商品标题
            'MIN(og.nums) as volume',       // 取第一个商品数量
            'MIN(g.image) as image_raw'     // 取第一个商品图片（原始路径）
        ]);


        // 分页参数
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 10;

        $list = $query->paginate($limit, false, ['page' => $page]);

        // 数据后处理
        foreach ($list as $item) {
            $item->time = date('Y-m-d H:i:s', $item->time); // 格式化时间
            $item->image = $item->image_raw ? cdnurl($item->image_raw, true) : ''; // 处理图片URL
            unset($item->image_raw); // 移除原始图片路径字段

            // 解析地址 (简单示例，可能需要更复杂的解析逻辑)
            if ($item->address) {
                $addressParts = explode(',', $item->address);
                // 假设地址格式是 "姓名,电话,省市区,详细地址"
                // 或者 "省市区 详细地址" (如果o.address直接是这样存的)
                // 这里仅作一个简单展示，实际可能需要根据存储格式调整
                $item->location = isset($addressParts[2]) ? str_replace(' ', '', $addressParts[2]) : $item->address;
            } else {
                $item->location = '未知';
            }
        }
        return $list;
    }

    /**
     * 获取单条用户使用记录详情
     * @param int $userId 用户ID
     * @param int $orderId 订单ID
     * @return array|null 订单详情数组，或null（如果未找到或无权访问）
     */
    public static function getUsageRecordDetail($userId, $orderId)
    {
        // 查询主订单信息
        $order = self::alias('o')
            ->where('o.id', $orderId)
            ->where('o.user_id', $userId)
            ->where('o.paystate', 1) // 确保是已支付的订单
            ->field([ // 选择订单表中的核心字段
                'o.id as record_id',
                'o.order_sn',
                'o.createtime', // 将作为 'time' 字段
                'o.payamount as amount',
                'o.paytype',
                'o.address', // 原始地址字符串
                'o.memo',    // 备注，可能包含预约信息
                'o.orderstate', // 用于获取订单状态文本
                'o.shippingstate', // 用于获取订单状态文本 (如果适用)
            ])
            ->find();

        if (!$order) {
            return null; // 订单不存在或不属于该用户或未支付
        }

        // 查询订单关联的商品详情
        $goodsDetails = Db::name('shop_order_goods')
            ->alias('og')
            ->join('__SHOP_GOODS__ g', 'og.goods_id = g.id', 'LEFT')
            ->where('og.order_sn', $order['order_sn'])
            ->field([
                'og.title as product_name', // 商品在订单中的标题
                'og.nums as quantity',
                'og.price as price_per_item', // 商品在订单中的单价
                'g.image as product_image_raw' // 商品主图原始路径
            ])
            ->select();

        $totalVolume = 0;
        $processedGoodsDetails = [];
        foreach ($goodsDetails as $good) {
            $totalVolume += $good['quantity'];
            $processedGoodsDetails[] = [
                'product_name'     => $good['product_name'],
                'quantity'         => $good['quantity'],
                'price_per_item'   => floatval($good['price_per_item']),
                'product_image'    => $good['product_image_raw'] ? cdnurl($good['product_image_raw'], true) : '',
            ];
        }

        // 准备返回的数据结构
        $result = $order->toArray(); // 将主订单信息转为数组
        $result['time'] = date('Y-m-d H:i:s', $order['createtime']); // 格式化时间
        $result['pay_type_text'] = self::getPayTypeText($order['paytype']); // 获取支付方式文本

        // 订单状态文本 (复用模型中已有的 getStatusTextAttr 逻辑，但需要模拟数据结构)
        $statusData = [
            'orderstate'    => $order['orderstate'],
            'paystate'      => 1, // 因为我们查询时已限定 paystate = 1
            'shippingstate' => $order['shippingstate']
        ];
        $result['status_text'] = (new self())->getStatusTextAttr(null, $statusData);


        // 解析地址
        if ($result['address']) {
            $addressParts = explode(',', $result['address']);
            // 假设地址格式 "姓名,电话,省市区,详细地址"
            $result['location'] = isset($addressParts[2]) ? trim($addressParts[2]) . (isset($addressParts[3]) ? ' ' . trim($addressParts[3]) : '') : $result['address'];
        } else {
            $result['location'] = '未知';
        }

        // device_name: 可以用第一个商品名作为代表，或根据业务逻辑调整
        $result['device_name'] = !empty($processedGoodsDetails) ? $processedGoodsDetails[0]['product_name'] : '未知设备';

        // volume: 订单中所有商品的总数量
        $result['volume'] = $totalVolume;

        $result['goods_details'] = $processedGoodsDetails; // 包含处理后的商品列表

        // 移除不再需要的原始字段
        unset($result['createtime'], $result['orderstate'], $result['shippingstate']);

        return $result;
    }

    /**
     * 获取支付方式文本 (辅助方法)
     * @param string $payType 支付类型标识 (e.g., 'wechat', 'alipay', 'money')
     * @return string 支付方式的文本描述
     */
    private static function getPayTypeText($payType)
    {
        // 这里可以根据实际的支付类型标识返回对应的中文名称
        // 例如，在配置文件或语言包中定义这些映射
        $payTypeMap = [
            'wechat' => __('Wechat'), // 微信支付
            'alipay' => __('Alipay'), // 支付宝支付
            'money'  => __('Balance'), // 余额支付
            'system' => __('System Pay'), // 系统支付 (例如金额为0时)
            // 添加其他支付方式...
        ];
        return isset($payTypeMap[$payType]) ? $payTypeMap[$payType] : __('Unknown'); // 未知
    }
}
