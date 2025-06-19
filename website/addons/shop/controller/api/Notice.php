<?php

namespace addons\shop\controller\api;

use addons\shop\model\Notice as NoticeModel; // 假设模型存在或后续创建
use think\Db; // 根据实际情况，可能需要使用Dbファサード

class Notice extends Base
{
    protected $noNeedLogin = ['list', 'detail']; // 允许未登录访问列表和详情

    public function _initialize()
    {
        parent::_initialize();
        // 可以移除请求类型检查，或根据需要调整
        // if (!$this->request->isGet()) {
        //     $this->error('请求错误');
        // }
    }

    /**
     * 通知列表
     */
    public function list()
    {
        // TODO: 实现从数据库获取数据和分页逻辑
        // 模拟数据
        $list = [
            [
                'id' => 1,
                'title' => '【模拟】系统维护通知',
                'content' => '我们将于2023年12月15日凌晨2:00-4:00进行系统维护...',
                'summary' => '我们将于2023年12月15日凌晨2:00-4:00进行系统维护...',
                'publish_time' => '2023-12-10 10:00:00',
                'create_time' => '2023-12-10 10:00:00',
                'publisher' => '系统管理员',
                'type' => '系统通知',
                'category' => '维护',
                'icon' => 'info-circle',
                'tagBgColor' => '#fef0f0',
                'iconColor' => '#f56c6c',

            ],
            [
                'id' => 2,
                'title' => '【模拟】新功能上线公告',
                'content' => 'APP新版本V2.6.0已上线，新增多项功能...',
                'summary' => 'APP新版本V2.6.0已上线，新增多项功能...',
                'publish_time' => '2023-12-09 14:30:00',
                'create_time' => '2023-12-09 14:30:00',
                'publisher' => '产品团队',
                'type' => '功能更新',
                'category' => '新功能',
                'icon' => 'checkbox-mark',
                'tagBgColor' => '#e5f7ef',
                'iconColor' => '#5ac725',
            ]
        ];
        $total = count($list); // 模拟总数
        $page = $this->request->request('page/d', 1);
        $limit = $this->request->request('limit/d', 10);

        // 模拟分页
        $offset = ($page - 1) * $limit;
        $paginatedList = array_slice($list, $offset, $limit);

        $result = [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit),
            'data' => $paginatedList, // 在实际应用中，这里应该是模型返回的数据
            'list' => $paginatedList // 兼容前端可能使用的 list 字段
        ];
        $this->success('获取成功', $result);
    }

    /**
     * 通知详情
     */
    public function detail()
    {
        $id = $this->request->request('id/d');
        if (!$id) {
            $this->error('参数错误：缺少ID');
        }
        // TODO: 实现从数据库获取特定ID的通知详情
        // 模拟数据
        $detail = null;
        if ($id == 1) {
            $detail = [
                'id' => 1,
                'title' => '【模拟】系统维护通知',
                'content' => '尊敬的用户：\n\n为了给您提供更好的服务体验，我们将于2023年12月15日凌晨2:00-4:00进行系统维护升级。在此期间，森泽共享净水机APP及相关服务将暂时不可用。\n\n维护内容：\n1. 系统性能优化\n2. 安全性提升\n3. 新功能上线准备\n\n我们建议您提前做好相关安排，避免在维护时段使用我们的服务。系统维护结束后，您可能需要重新登录账号。\n\n感谢您的理解与支持！\n\n森泽共享净水机团队\n2023年12月10日',
                'publish_time' => '2023-12-10 10:00:00',
                'create_time' => '2023-12-10 10:00:00',
                'publisher' => '系统管理员',
                'type' => '系统通知',
                'category' => '维护',
                'icon' => 'info-circle',
                'tagBgColor' => '#fef0f0',
                'iconColor' => '#f56c6c',
            ];
        } elseif ($id == 2) {
            $detail = [
                'id' => 2,
                'title' => '【模拟】新功能上线公告',
                'content' => '亲爱的用户：\n\n我们很高兴地通知您，森泽共享净水机APP新版本V2.6.0现已正式上线！此次更新带来了多项实用新功能和体验优化...（此处省略完整内容）',
                'publish_time' => '2023-12-09 14:30:00',
                'create_time' => '2023-12-09 14:30:00',
                'publisher' => '产品团队',
                'type' => '功能更新',
                'category' => '新功能',
                'icon' => 'checkbox-mark',
                'tagBgColor' => '#e5f7ef',
                'iconColor' => '#5ac725',
            ];
        }

        if ($detail) {
            // 兼容前端可能使用的不同包装层级
            $this->success('获取成功', ['info' => $detail, 'detail' => $detail, 'data' => $detail]);
        } else {
            $this->error('通知未找到');
        }
    }
}
