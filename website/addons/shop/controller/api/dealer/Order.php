<?php

namespace addons\shop\controller\api\dealer;

use addons\shop\controller\api\Base;

use app\common\model\dealer\Referee;
class Order extends Base
{
    protected  $model;
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new  \app\common\model\dealer\Order();
    }
    public function getList(){
        //判断下级跟你有关系的用户id
        $referee = new Referee();
        $ids = $referee->where('dealer_id',$this->auth->id)->column('user_id');
        $param = $this->request->get();
        $list =  $this->model->getList($ids,$param['page'],$param['limit']);
        $count = $this->model->getlistCount($ids);
        $this->success('查询成功',compact('list','count'));
    }
}