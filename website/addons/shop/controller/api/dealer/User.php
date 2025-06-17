<?php

namespace addons\shop\controller\api\dealer;

use app\common\service\dealer\User as UserService;
use addons\shop\controller\api\Base;

class User extends Base
{
    protected  $model;
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new  UserService();
    }
    public function getList(){
       $level = $this->request->get('level');
        $page = $this->request->get('page');
        $limit = $this->request->get('limit');
        $keyword = $this->request->get('keyword');

        $list =  $this->model->getList($page,$limit,$this->auth,$level,$keyword);
       $count = $this->model->getlistCount($this->auth);
       $this->success('查询成功',compact('list','count'));
    }
}