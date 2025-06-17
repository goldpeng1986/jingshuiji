<?php

namespace addons\shop\controller\api\service;

use addons\shop\controller\api\Base;
use addons\shop\model\serviceOrder;
use addons\shop\model\Order as OrderModel;
use think\Cache;

class Order extends Base
{
    public function create(){
        $param = $this->request->post();
        $model = new serviceOrder();
        $result =  $model->createOrder($param,$this->auth->id);
        if($result){
            $this->success('等待审核');
        }
        $this->error('添加失败',[]);
    }

    public function list(){
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $order_state = $this->request->get('order_state');
        $pay_state = $this->request->get('pay_state','');

        $model = new serviceOrder();
        $list =   $model->getList($page,$limit,$order_state,$this->auth->id,$pay_state);
        $this->success('查询成功',$list);
    }

    public function detail(){
        $id = $this->request->get('id');
        $user_id = $this->auth->id;
        $model = new serviceOrder();
        $list =   $model->get(['id'=>$id,'user_id'=>$user_id]);
        $this->success('查询成功',$list);

    }

    public function cancel(){
        $id = $this->request->post('id');
        $user_id = $this->auth->id;
        $where = [
            'order_state'=>3
        ];
        $model = new serviceOrder();
        $result =  $model->editOrder($id,$user_id,$where);
        if($result){
            $this->success('取消成功');
        }
        $this->error('取消失败',[]);

    }

    public function payParams(){
        $id = $this->request->post('id');
        $paytype = $this->request->post('paytype');
        $user_id = $this->auth->id;
        $model = new serviceOrder();
        $result =  $model->pay($id,$user_id,$paytype);
        if($result){
            $this->success('微信支付参数获取成功',$result);
        }
        $this->error('获取微信参数失败',[]);
    }

    public function ordercount(){
        $model = new serviceOrder();

        $info = [
            'renew'=>[
                'daizhifu' => OrderModel::where('user_id', $this->auth->id)->where('order_type',20)->where('orderstate', 0)->where('paystate', 0)->count(),
                'daiqueren' => OrderModel::where('user_id', $this->auth->id)->where('order_type',20)->where('orderstate', 0)->where('paystate', 1)->where('install_status',1)->count(),
                'daianzhuang' => OrderModel::where('user_id', $this->auth->id)->where('order_type',20)->where('orderstate', 0)->where('paystate', 1)->where('install_status',2)->count(),
                'daixufei' => OrderModel::where('user_id', $this->auth->id)->where('order_type',20)->where('orderstate', 3)->where('paystate', 1)->where('order_renew_status',10)->where('install_status',2)->count(),
                'daoqi' => OrderModel::where('user_id', $this->auth->id)
                    ->where('order_type',20)->where('orderstate', 3)
                    ->where('paystate', 1)->where('install_status',2)
                    ->whereTime('order_renew_time','between',[date('Y-m-d H:i:s'),date('Y-m-d H:i:s',strtotime("+".get_addon_config('shop')['renew_time']."day"))])
                    ->count(),
                'yiwancheng' => OrderModel::where('user_id', $this->auth->id)->where('order_type',20)->where('orderstate', 3)->where('paystate', 1)->where('order_renew_status',20)->where('install_status',2)->count(),
            ],
            'service'=>[
                  'daishenhe'=>$model->where('user_id', $this->auth->id)->where('pay_state',10)->where('order_state',0)->count(),
                'daizhifu'=>$model->where('user_id', $this->auth->id)->where('pay_state',10)->count(),
                'yiwancheng'=>$model->where('user_id', $this->auth->id)->where('pay_state',20)->where('order_state',4)->count()
            ]
        ];
        $this->success('查询成功',$info);
    }

}