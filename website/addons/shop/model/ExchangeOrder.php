<?php

namespace addons\shop\model;

use think\Model;


class ExchangeOrder extends Model
{

    // 表名
    protected $name = 'shop_exchange_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];

    public function getTypeList()
    {
        return ['virtual' => __('Type virtual'), 'reality' => __('Type reality')];
    }

    public function getStatusList()
    {
        return ['created' => __('待兑换'), 'inprogress' => __('处理中'), 'rejected' => __('已拒绝'), 'delivered' => __('已发货'), 'completed' => __('已完成')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    //常见兑换订单
    public static function createOrder($data)
    {
        $data['orderid'] = date("Ymdhis") . sprintf("%06d", $data['user_id']) . mt_rand(1000, 9999);
        $data['ip'] = request()->ip();
        $data['useragent'] = substr(request()->server('HTTP_USER_AGENT'), 0, 255);
        return (new self)->save($data);
    }


    //获取列表
    public static function tableList($param)
    {
        $pageNum = 10;
        if (isset($param['num']) && !empty($param['num'])) {
            $pageNum = $param['num'];
        }
        return self::with(['Exchange' => function ($query) {
            $query->field('id,title,image');
        }])->field('id,status,nums,score,type,exchange_id,reason,expressname,expressno,createtime')->where(function ($query) use ($param) {
            if (isset($param['type']) && !empty($param['type'])) {
                $query->where('type', $param['type']);
            }
            if (isset($param['status']) && !empty($param['status'])) {
                $query->where('status', $param['status']);
            }
            if (isset($param['user_id']) && !empty($param['user_id'])) {
                $query->where('user_id', $param['user_id']);
            }
        })->order('createtime desc')->paginate($pageNum);
    }

    public function Exchange()
    {
        return $this->hasOne('Exchange', 'id', 'exchange_id');
    }
}
