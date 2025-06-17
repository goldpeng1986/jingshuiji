<?php

namespace app\workman\controller;

use app\admin\model\shop\Order;
use think\Db;
use think\Exception;
use think\worker\Server;
use Workerman\Lib\Timer;

/**
 * 消息提醒
 */
class Notice extends Server
{
    protected $socket = 'websocket://127.0.0.1:7272';
    protected $processes = 1;
    protected $connection;
    protected $news_order=0;
    protected  $renew_order = 0;

    public function onMessage($connection, $data)
    {
        $connection->send('我收到你的信息了');
    }

    //当连接建立时触发的回调函数
    public function onConnect($connection)
    {
        $this->connection = $connection;
        //推送新订单提醒
        $this->news_order =  Timer::add('10',array($this,'taskOrderNew'));
        //推送过期订单提醒
        $this->renew_order =  Timer::add('10',array($this,'taskOrderRenew'));
    }

    public  function taskOrderNew(){
         $order = new Order();
         $orderinfo =  $order->getTaskNewOrder();
         if($orderinfo){
             $orderinfo->news_remind=2;
             $orderinfo->save();
             $this->connection->send(json_encode([
                 'name'=>'news_order'
             ]));
             echo '有新的订单';

         }
    }

    public function taskOrderRenew(){
        $order = new Order();
        $orderinfo =  $order->getTaskRenewOrder();
        if($orderinfo){
            $orderinfo->install_status_remind=2;
            $orderinfo->save();
            $this->connection->send(json_encode([
                'name'=>'renew_order'
            ]));
            echo '有即将过期的订单';

        }
    }
    //当连接断开时触发的回调函数
    public function onClose($connection)
    {
        //清除定时器
            Timer::del($this->news_order);
            Timer::del($this->renew_order);
    }

    //当客户端的连接上发生错误时触发
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }
    //每个进程启动
    public function onWorkerStart($worker)
    {
        //处理用户佣金发放
        Timer::add(60, array($this, 'dealerMoney'));
    }

    /**
     * @return void
     * 佣金结算通知
     */
    public function dealerMoney()
    {
        Db::startTrans();
        try {
            $order = model('app\common\model\dealer\Order');
            $list = $order->where('is_settled', 0)->where('is_invalid',0)->select();
            if($list){
                foreach ($list as $value) {
                    if($value){
                        //进行师傅订单发放
                        $dealerService =new \app\common\service\dealer\Dealer();
                        $dealerService-> grantMoney($value);
                    }
                }
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            print $e->getMessage();
        }
    }
}