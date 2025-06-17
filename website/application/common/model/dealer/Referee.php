<?php

namespace app\common\model\dealer;

use app\common\model\User;
use think\Model;

/**
 * 分销商推荐关系模型
 * Class Referee
 * @package app\common\model\dealer
 */
class Referee extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $name = 'dealer_referee';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User');
    }


    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function order()
    {
        return $this->hasMany('app\common\model\dealer\Order', 'user_id', 'user_id');
    }

    /**
     * 关联分销商用户表
     * @return \think\model\relation\BelongsTo
     */
    public function dealer()
    {
        return $this->belongsTo('Agent');
    }

    public function getCreateTimeAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * 获取上级用户id
     * @param $user_id
     * @param $level
     * @param bool $is_dealer 必须是分销商
     * @return bool|mixed
     * @throws \think\exception\DbException
     */
    public static function getRefereeUserId($user_id, $level, $is_dealer = false)
    {
        $dealer_id = (new self)->where(compact('user_id', 'level'))
            ->value('dealer_id');
        if (!$dealer_id) return 0;

        return $is_dealer ? (Agent::isDealerUser($dealer_id) ? $dealer_id : 0) : $dealer_id;
    }

    public function checkReferee($dealer_uid, $user_id, $level)
    {
        //新增绑定关系
        return $this->allowField(true)->save([
            'dealer_id' => $dealer_uid,
            'user_id' => $user_id,
            'level' => $level
        ]);
    }

    /**
     * 获取我的团队列表
     * @param $user_id
     * @param int $level
     * @throws \think\exception\DbException
     */
    public function getList($page,$limit,$user_id, $level = -1,$keyword)
    {
        $level > -1 && $this->where('referee.level', '=', $level);
        return $this->with(['dealer', 'user'])
            ->alias('referee')
            ->field('referee.*')
            ->withSum(['order' => function ($query) {
                $query->where('is_settled', 1);
            }], true, 'order_price')
            ->withCount(['order' => function ($query) {
                $query->where('is_settled', 1);
            }], false)
            ->where('referee.dealer_id', '=', $user_id)
            ->where(function ($query)use($keyword){
                 if(!empty($query)){
                     $user = new User();
                     $ids =  $user->where('nickname','like',"%$keyword%")->column('id');
                    $query->where('user_id','in',$ids);
                 }
            })
            ->order('referee.create_time', 'desc')
            ->page($page,$limit)
            ->select();
    }

    public function getListCount($user_id){
        return $this
            ->where('dealer_id', '=', $user_id)
            ->count();
    }
}