<?php

namespace addons\shop\controller\api\dealer;

use addons\zhidashop\library\WanlPay\WanlPay;
use addons\shop\controller\api\Base;

use app\common\service\Dealer;
use think\Db;
use think\Exception;

class Withdraw extends Base
{
    protected  $model;
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\dealer\Withdraw();
    }

    /**
     * @return void
     * 提现记录
     */
    public function getList(){
        $param = $this->request->get();
        $list =  $this->model->getList($this->auth->id,$param['page'],$param['limit']);
        $count = $this->model->getlistCount($this->auth->id);
        $this->success('查询成功',compact('list','count'));
    }

    public function apply(){
        $param = $this->request->post();
        $result =   $this->model->apply($param,$this->auth);
        if($result){
            return $this->success('申请成功',[]);
        }
        return $this->success('申请失败',[]);

    }

    public function info(){
        $list =   $this->model->info($this->auth);
        return $this->success('获取成功',$list);
    }

}