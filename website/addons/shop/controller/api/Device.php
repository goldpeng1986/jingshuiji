<?php

namespace addons\shop\controller\api;

use addons\shop\model\Device as DeviceModel;

/**
 * User Devices
 */
class Device extends Base
{

    protected $noNeedLogin = [];

    /**
     * Get user's device list, categorized by status
     */
    public function list()
    {
        $userId = $this->auth->id;
        $deviceSummary = DeviceModel::getUserDeviceSummary($userId);
        $this->success('获取成功', $deviceSummary);
    }

    /**
     * Get detailed information for a specific user's device
     */
    public function detail()
    {
        $userId = $this->auth->id;
        $orderId = $this->request->param('order_id');

        if (!$orderId) {
            $this->error(__('Invalid parameters'));
        }

        $deviceDetails = DeviceModel::getDeviceDetails($userId, $orderId);

        if ($deviceDetails) {
            $this->success('获取成功', $deviceDetails);
        } else {
            $this->error(__('No device found or access denied.'));
        }
    }
}
