<?php

namespace addons\shop\model;

use app\job\SubScribeJob;
use think\Model;
use addons\shop\model\Order;
use addons\third\model\Third;
use addons\shop\library\message\Service;
use addons\shop\model\SubscribeLog;
use think\Queue;

class TemplateMsg extends Model
{

    // 表名
    protected $name = 'shop_template_msg';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [];

    public static function getTplIds()
    {
        return self::where('switch', 1)->field('id,title,category,tpl_id')->where('type', 2)->select();
    }

    //获取发送模板消息的数据【付款成功】【商城发货通知】【退款通知】【售后回复】
    public static function sendTempMsg($category='',$title='', $order_sn='')
    {
        try {
            $config = get_addon_config('shop');
            if ($config['sendnoticemode'] == 'queue') {
                if (extension_loaded('redis') && class_exists('\think\Queue') && config('queue.connector') == 'redis') {
                    //使用队列发送
                    Queue::push('addons\shop\controller\queue\Subscribe', ['category' => $category,'title'=>$title, 'order_sn' => $order_sn], 'shopSubscribeQueue');
                }
            } elseif ($config['sendnoticemode'] == 'async') {
                //异步并发发送
                self::getSendOrderData($category,$title, $order_sn);
            }
        } catch (\Exception $e) {
        }
        return true;
    }

    /**
     * @ 获取数据发送
     * @param $event
     * @param $order_sn
     * @return bool
     */
    public static function getSendOrderData($category,$title, $order_sn)
    {
        try {
            //type 1=公众号,2=小程序,3=邮箱,4=短信
            $temps = self::where('category', $category)->where('title',$title)->where('switch', 1)->order('id asc')->column('*', 'type');
            //找订单
            $order = Order::field('o.*,u.mobile,u.email,u.nickname')
                ->alias('o')
                ->join('user u', 'u.id=o.user_id')
                ->where('order_sn', $order_sn)
                ->find();
            self::toSend($order, $temps);
        } catch (\Exception $e) {
            if (config('app_debug')) {
                \think\Log::write("Line:" . $e->getLine() . " Code:" . $e->getCode() . " Message:" . $e->getMessage() . " File:" . $e->getFile());
            }
            return false;
        }
        return true;
    }

    //去发送
    protected static function toSend($order, $temps)
    {
        $result = false;
        foreach ($temps as $tp) {
            switch ($tp['type']) {
                case 1:
                    //是否有openID
                    $order['openid'] = Third::where('user_id', $order['user_id'])->where('platform', 'wechat')->where('apptype', 'mp')->value('openid');
                    if (!empty($order['openid'])) {
                        $result = self::assembleMpData($order, $tp);
                    }
                    break;
                case 2:
                    //是否有openID
                    $order['openid'] = Third::where('user_id', $order['user_id'])->where('platform', 'wechat')->where('apptype', 'miniapp')->value('openid');
                    if (!empty($order['openid'])) {
                        $result = self::assembleMiniData($order, $tp);
                        //是否订阅有
                        $subscribe = SubscribeLog::where('order_sn', $order['order_sn'])->where('tpl_id', $tp['tpl_id'])->where('status', 0)->find();
                        if (!empty($subscribe)) {
                            $subscribe->status = 1;
                            $subscribe->save();
                            $result = self::assembleMiniData($order, $tp);
                        }
                    }
                    break;
                case 3:
                    if (!empty($order['email'])) {
                        $result = self::assembleMEData($order, $tp);
                    }
                    break;
                case 4:
                    if (!empty($order['mobile'])) {
                        $result = self::assembleMEData($order, $tp);
                    }
                    break;
            }
            $result && Service::send($tp['type'], $result);
        }
    }


    //组装公众号模板数据
    protected static function assembleMpData($param, $temp)
    {
        $data = [];
        $temp['content'] = is_array($temp['content']) ? $temp['content'] : (array)json_decode($temp['content'], true);
        foreach ($temp['content'] as $res) {
            $value = str_replace('.DATA}}', '', str_replace('{{', '', $res['value']));
            if ($value) {
                $data[$value] = [
                    'value' => $res['key'] != 'diy_text' && isset($param[$res['key']]) ? $param[$res['key']] : $res['def_val']
                ];
            }
        }
        $templateData = [
            'touser'      => $param['openid'],
            'template_id' => $temp['tpl_id'],
            'data'        => $data
        ];
        if (strpos($temp['page'], 'http') !== false) {
            $templateData['url'] = $temp['page'];
        } else {
            $config = get_addon_config('shop');
            $templateData['miniprogram'] = [
                "appid"    => $config['wx_appid'],
                "pagepath" => $temp['page']
            ];
        }
        return $templateData;
    }

    //组装小程序模板数据
    protected static function assembleMiniData($param, $temp)
    {
        $data = [];
        $temp['content'] = is_array($temp['content']) ? $temp['content'] : (array)json_decode($temp['content'], true);
         $goods_name = (new OrderGoods())->where('order_sn',$param->order_sn)->column('title');
         //查询续费信息
         if($param->order_type==20){
             $renew = (new OrderRenew())->where('order_id',$param->id)->order('create_time','desc')->find();
         }
         foreach ($temp['content'] as $res) {
            $value = str_replace('.DATA}}', '', str_replace('{{', '', $res['value']));
            if ($value) {
                switch ($res['def_val']){
                    //订单号 order_sn
                    case 'order_sn':
                        $data[$value] = [
                            'value' =>$param->order_sn
                        ];
                        break;
                        //商品名称 goods_name
                    case 'goods_name':
                        $data[$value] = [
                            'value' =>implode(',',$goods_name)
                        ];
                        break;
                        //支付金额 zhifu_price
                    case 'zhifu_price':
                        $data[$value] = [
                            'value' =>$param->payamount
                        ];
                        break;
                        //下单时间 xiadan_time
                    case 'xiadan_time':
                        $data[$value] = [
                            'value' => date('Y-m-d H:i:s',$param->createtime)
                        ];
                        break;
                      //收货地址 address
                    case 'address':
                        $data[$value] = [
                            'value' => $param->address
                        ];
                        break;
                    //订单公司  expressname
                    case 'expressname':
                        $data[$value] = [
                            'value' => $param->expressname?$param->expressname:'未填写'
                        ];
                        break;

                    //订单号  expressno
                    case 'expressno':
                        $data[$value] = [
                            'value' =>$param->expressno?$param->expressno:1111
                        ];
                        break;


                        //完成时间
                    case 'receivetime':
                        $data[$value] = [
                            'value' => date('Y-m-d H:i:s',$param->receivetime)
                        ];
                        break;

                    //订单号  tips

                    case 'tips':
                        $data[$value] = [
                            'value' => "您的订单已经发货！"
                        ];
                        break;
                    //订单号  tips

                    case 'receive_tips':
                        $data[$value] = [
                            'value' => "您的订单已完成！"
                        ];
                        break;


                    case 'paytime':
                        $data[$value] = [
                            'value' =>date('Y-m-d H:i:s',$param->paytime)
                        ];
                        break;

                    case 'payamount':
                        $data[$value] = [
                            'value' =>$param->payamount
                        ];
                        break;
                    case 'nickname':
                        $data[$value] = [
                            'value' =>$param->nickname
                        ];
                        break;
                    case 'renew_status':
                        $data[$value] = [
                            'value' => '准备上门'
                        ];
                        break;
                    case 'renew_time':
                        $data[$value] = [
                            'value' => date('Y-m-d H:i:s',time())
                        ];
                    case 'store':
                        $data[$value] = [
                            'value' => get_addon_config('shop')['sitename']
                        ];
                    case 'renew_state':
                        $data[$value] = [
                            'value' => '待续费'
                        ];
                    case 'renew_tips':
                        $data[$value] = [
                            'value' => '请前往小程序进行续费，否则产品将无法使用'
                        ];
                    case 'renew_price':
                        $data[$value] = [
                            'value' => $renew->year
                        ];
                    case 'renew_year':
                        $data[$value] = [
                            'value' => $renew->price
                        ];
                    case 'renew_become':
                        $data[$value] = [
                            'value' =>date('Y-m-d H:i:s', $param->order_renew_time)
                        ];
                    case 'renew_number':
                        $data[$value] = [
                            'value' =>get_addon_config('shop')['renew_time']
                        ];
                    case 'renew_notes':
                        $data[$value] = [
                            'value' =>'为了您的产品继续使用,请即时续费'
                        ];


                }
            }
        }
        return [
            'touser'      => $param['openid'],
            'template_id' => $temp['tpl_id'],
            'page'        => $temp['page'],
            'data'        => $data
        ];
    }

    //组装邮箱，短信模板数据
    protected static function assembleMEData($param, $temp)
    {
        $msg = $temp['extend'];
        $temp['content'] = is_array($temp['content']) ? $temp['content'] : (array)json_decode($temp['content'], true);
        $data = [];
        foreach ($temp['content'] as $res) {
            $value = $res['value'];
            if ($value) {
                $data[$value] = $res['key'] != 'diy_text' && isset($param[$res['key']]) ? $param[$res['key']] : $res['def_val'];
            }
        }
        //替换内容中的变量 ${变量名}
        $msg = preg_replace_callback('/\$\{(.*?)\}/i', function ($matches) use ($data) {
            return isset($data[$matches[1]]) ? $data[$matches[1]] : '';
        }, $msg);
        return [
            'template_id' => $temp['tpl_id'],
            'mobile'      => $param['mobile'],
            'email'       => $param['email'],
            'nickname'    => $param['nickname'],
            'title'       => $temp['title'],
            'message'     => $msg,
            'data'        => $data,
        ];
    }
}
