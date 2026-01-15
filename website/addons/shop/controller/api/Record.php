<?php

namespace addons\shop\controller\api;

use addons\shop\model\Order as OrderModel; // 假设统计和列表逻辑放在Order模型中
use think\Db;

/**
 * 用户使用记录API
 * @ApiWeigh (10)
 */
class Record extends Base
{
    protected $noNeedLogin = []; // 所有方法都需要登录

    /**
     * 获取用户使用记录统计数据
     * @ApiMethod (POST)
     * @ApiParams (name="date_type", type="string", description="日期类型: 'current_month', 'last_month', 或 'YYYY-MM'", sample="current_month")
     */
    public function getStatistics()
    {
        $userId = $this->auth->id; // 获取当前认证的用户ID
        $dateType = $this->request->post('date_type', 'current_month'); // 默认为当月
        $customMonth = null;

        // 如果date_type是 YYYY-MM 格式，则将其视为customMonth
        if (preg_match('/^\d{4}-\d{2}$/', $dateType)) {
            $customMonth = $dateType;
            // 可以选择将dateType规范化，例如设为 'custom'，或者模型方法能直接处理 YYYY-MM
        }

        // 调用模型方法获取统计数据
        $statistics = OrderModel::getUserUsageStatistics($userId, $dateType, $customMonth);

        if ($statistics) {
            $this->success('获取统计数据成功', $statistics);
        } else {
            // 根据模型方法的返回值，这里可能需要调整，例如返回空数据结构或特定错误
            $this->success('暂无数据或获取失败', [
                'count' => 0,
                'amount' => 0.00,
                'volume' => 0
            ]);
        }
    }

    /**
     * 获取用户使用记录列表
     * @ApiMethod (POST)
     * @ApiParams (name="filter_type", type="string", description="筛选类型: 'all', 'current_month', 'last_month', 'custom_date_range'", sample="current_month")
     * @ApiParams (name="date_from", type="string", description="开始日期 (YYYY-MM-DD, 当 filter_type='custom_date_range')", sample="2023-01-01")
     * @ApiParams (name="date_to", type="string", description="结束日期 (YYYY-MM-DD, 当 filter_type='custom_date_range')", sample="2023-01-31")
     * @ApiParams (name="goods_id", type="integer", description="商品ID (可选, 用于按特定设备筛选)", sample="101")
     * @ApiParams (name="orderby", type="string", description="排序字段 (默认 'createtime')", sample="createtime")
     * @ApiParams (name="orderway", type="string", description="排序方式 ('asc' 或 'desc', 默认 'desc')", sample="desc")
     * @ApiParams (name="page", type="integer", description="页码", sample="1")
     * @ApiParams (name="limit", type="integer", description="每页数量", sample="10")
     */
    public function list()
    {
        $userId = $this->auth->id; // 获取用户ID
        $params = $this->request->post(); // 获取所有POST参数

        // 设置默认值
        $params['filter_type'] = isset($params['filter_type']) ? $params['filter_type'] : 'current_month';
        $params['orderby'] = isset($params['orderby']) ? $params['orderby'] : 'createtime';
        $params['orderway'] = isset($params['orderway']) ? $params['orderway'] : 'desc';
        $params['page'] = isset($params['page']) ? (int)$params['page'] : 1;
        $params['limit'] = isset($params['limit']) ? (int)$params['limit'] : 10;

        // 调用模型方法获取记录列表
        $list = OrderModel::getUserUsageRecords($userId, $params);

        $this->success('获取列表成功', $list);
    }

    /**
     * 获取单条使用记录详情
     * @ApiMethod (POST)
     * @ApiParams (name="id", type="integer", required=true, description="记录ID (即订单ID)")
     */
    public function detail()
    {
        $userId = $this->auth->id; // 获取当前认证的用户ID
        $orderId = $this->request->post('id/d'); // 获取订单ID，并确保是整数

        if (!$orderId) {
            $this->error(__('Invalid parameter: id is required.')); // 无效参数：id是必需的。
        }

        // 调用模型方法获取记录详情
        $recordDetail = OrderModel::getUsageRecordDetail($userId, $orderId);

        if ($recordDetail) {
            $this->success(__('Record detail retrieved successfully.'), $recordDetail); // 成功获取记录详情。
        } else {
            $this->error(__('Record not found or access denied.')); // 未找到记录或访问被拒绝。
        }
    }
}
