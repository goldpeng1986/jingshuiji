<?php

namespace app\common\model\dealer;

use think\Model;

class Agent extends  Model
{
    protected $name='dealer_agent';
    protected $autoWriteTimestamp;


    public function checkReferee($user_id){
        return $this->where('uid',$user_id)->where('referee_id','<>',0)->find();
    }

    /**
     * 是否为分销商
     * @param $userId
     * @return bool
     */
    public static function isDealerUser($userId)
    {
        return !!(new static)->where('uid', '=', $userId)
            ->value('uid');
    }

}