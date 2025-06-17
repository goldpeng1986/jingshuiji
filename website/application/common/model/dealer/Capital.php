<?php

namespace app\common\model\dealer;

use think\Model;

 /**
 * 分销商资金明细模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Capital extends Model
{
    protected $name = 'dealer_capital';
    protected  $autoWriteTimestamp = true;

    public function getCreateTimeAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }

    public function getUpdateTimeAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d h:i:s", $value) : $value;
    }
    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
    /**
     * 分销商资金明细
     * @param $data
     */
    public static function add($data)
    {
        $model = new static;
       return  $model->save($data);
    }
    public function getCapitalTimeData($time,$uid){
        if(!is_array($time)){
            switch ($time){
                case 'yesterday':
                    return $this->where('user_id',$uid)->whereTime('create_time', 'yesterday')->where('flow_type',10)->sum('money');
            }
        }
        return  '00.00';
    }
    public function getList($uid,$page,$limit){
        return $this->where('user_id',$uid)
            ->order('create_time','desc')
            ->page($page,$limit)->select();
    }
    public function getlistCount($uid){
        $add =  $this->where('user_id',$uid)->where('flow_type',10)->sum('money');
        $reduce = $this->where('user_id',$uid)->where('flow_type',20)->sum('money');
        return bcsub($add,$reduce,2);
    }

    public function getlistWithdrawCount($uid){
        return  $this->where('user_id',$uid)->where('flow_type',20)->sum('money');

    }
}