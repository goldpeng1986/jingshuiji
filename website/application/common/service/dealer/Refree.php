<?php

namespace app\common\service\dealer;

use app\api\model\zhidashop\WorkerApply;
use app\common\model\dealer\Agent;
use app\common\model\dealer\Capital;
use app\common\model\dealer\Referee;
use app\common\model\User;

class Refree
{
    /**
     * 判断并绑定上下级关系 并判断推荐
     * @param $referee_id
     * @param $user_id
     * @return true
     * @throws \think\exception\DbException
     */

    public function checkReferee($userinfo)
    {
        $agentModel = new Agent();
        $referee_id = $userinfo->referee_id;
        //获取用户信息
        $userinfo->is_dealer = 1;
        $userinfo->save();
        //添加到分销员
        $agentModel::create([
            'uid' => $userinfo->id,
            'referee_id' => $referee_id,
            'create_time' => time(),
            'update_time' => time()
        ]);
        //绑定上下级关系
        $referee = new Referee();
        if ($referee_id) {
            //判断推荐人是否有上级
            $agent = $agentModel->checkReferee($referee_id);
            //判断推荐人身份
            $refereeinfo = User::get($referee_id);
            if (!$agent) {
                 $referee->checkReferee($referee_id, $userinfo->id, 1);
            }else{
                //整理有上级的记录
                $referee->insertAll([
                    [
                        'dealer_id' => $referee_id,
                        'user_id' => $userinfo->id,
                        'level' => 1,
                        'create_time'=>time()
                    ],
                    [
                        'dealer_id' => $agent->referee_id,
                        'user_id' => $userinfo->id,
                        'level' => 2,
                        'create_time'=>time()
                    ]
                ]);
            }
        }
        return true;
    }
    public function  sendSpread($referee_id,$user_id,$price){
        $refereeinfo = User::get($referee_id);
        $oldmoney =  $refereeinfo->dealer_price;
        $refereeinfo->dealer_price =  bcadd($refereeinfo->dealer_price,$price,2);
        $refereeinfo->save();
        Capital::create([
            'user_id'=>$referee_id,
            'flow_type'=>10,
            'money'=>$price,
            'describe'=>'用户推广id'.$user_id.',佣金由'.$oldmoney.'变为'.$refereeinfo->dealer_price,
            'create_time'=>time()
        ]);
    }

}