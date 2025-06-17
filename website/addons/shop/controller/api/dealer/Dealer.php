<?php

namespace addons\shop\controller\api\dealer;


use addons\shop\controller\api\Base;
use app\common\service\dealer\Poster;

class Dealer extends Base
{
    protected $noNeedRight = '*';

    protected  $model;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new  \app\common\service\dealer\Dealer();
    }
    //用户分销数据
    public function spead_user(){
        //整理数据返回
        $list =  $this->model->speadUser($this->auth);
        $this->success('查询成功',$list);
    }
    /**
     * @return void
     * @throws \Exception
     * 获取海报
     */
    public function getQrcode(){
        $model = new Poster($this->auth);
        $qrcode = $model->getImage();
        $this->success('查询成功',compact('qrcode'));
    }


}