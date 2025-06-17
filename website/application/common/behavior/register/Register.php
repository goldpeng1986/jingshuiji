<?php

namespace app\common\behavior\register;

use app\common\service\dealer\Refree;

class Register
{

    /**
     * @param $params
     * @return void
     * @throws \think\exception\DbException
     * 用户注册成功行为
     */
    public function run ($params){
        //并且自身成为分销员
        (new Refree())->checkReferee($params);
    }

}