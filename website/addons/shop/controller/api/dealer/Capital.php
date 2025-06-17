<?php

namespace addons\shop\controller\api\dealer;
use addons\shop\controller\api\Base;


class Capital extends Base
{
    protected  $model;
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new  \app\common\model\dealer\Capital();
    }
    public function getList(){
        $param = $this->request->get();
        $list =  $this->model->getList($this->auth->id,$param['page'],$param['limit']);
        $count = $this->model->getlistCount($this->auth->id);
        $this->success('查询成功',compact('list','count'));
    }
}