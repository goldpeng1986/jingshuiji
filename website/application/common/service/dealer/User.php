<?php

namespace app\common\service\dealer;
use app\common\model\dealer\Referee;

class User
{

    public function getList($page,$limit,$user,$level,$keyword){
         $model = new Referee();
         $list =   $model->getList($page,$limit,$user->id,$level,$keyword);
         return $list;
    }

    public function getlistCount($user){
        $model = new Referee();
        $list =   $model->getListCount($user->id);
        return $list;
    }

}