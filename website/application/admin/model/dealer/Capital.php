<?php

namespace app\admin\model\dealer;

use app\api\model\zhidashop\WorkerApply;
use app\common\model\User;
use think\Model;


class Capital extends Model
{

    

    

    // 表名
    protected $name = 'dealer_capital';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text'
    ];
    

    



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function checkReferee($row){
        if($row->apply_status==20){
            //判断是否存在上级
            $agent_info = (new Agent())->where('uid',$row->user_id)->find();
            if(!$agent_info->referee_id){
                return false;
            }
            //判断上级是否为分销商
            $dealer_status = User::get($agent_info->referee_id);
            if(!$dealer_status->is_dealer){
                return  false;
            }
            //判断上级用户是否为师傅
            $worker =  new WorkerApply();
            $workerinfo =  $worker->checkUserWorker($agent_info->referee_id);
            $dealerConfig = get_addon_config('zhidashop')['dealer'];
            if($workerinfo){
                if($dealerConfig['user_spread_worker']){
                    $this->sendSpread($agent_info->referee_id,$row->user_id,$dealerConfig['user_spread_worker']);
                }
            }else{
                if($dealerConfig['worker_spread_worker']){
                    $this->sendSpread($agent_info->referee_id,$row->user_id,$dealerConfig['worker_spread_worker']);
                }
            }
        }
    }
    public function  sendSpread($referee_id,$user_id,$price){
        $refereeinfo = User::get($referee_id);
        $oldmoney =  $refereeinfo->dealer_price;
        $refereeinfo->dealer_price =  bcadd($refereeinfo->dealer_price,$price,2);
        $refereeinfo->save();
        \app\common\model\dealer\Capital::create([
            'user_id'=>$referee_id,
            'flow_type'=>10,
            'money'=>$price,
            'describe'=>'用户'.$user_id.'成为师傅,佣金由'.$oldmoney.'变为'.$refereeinfo->dealer_price,
            'create_time'=>time()
        ]);
    }
}
