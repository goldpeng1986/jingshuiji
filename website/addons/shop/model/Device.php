<?php

namespace addons\shop\model;

use think\Model;
use think\Db;
use think\Config;

/**
 * Device Model
 */
class Device extends Model
{
    protected $name = 'shop_order'; // Using shop_order as the primary table for devices

    // No auto-write timestamp fields for this specific model logic if not directly saving to shop_order
    protected $autoWriteTimestamp = false;

    /**
     * Get User Device Summary
     *
     * @param int $userId
     * @return array
     */
    public static function getUserDeviceSummary($userId)
    {
        $now = time();
        $cdnUrl = Config::get('upload.cdnurl');
        $cdnUrl = $cdnUrl ? $cdnUrl : cdnurl('', true); // Ensure cdnurl utility is available

        // Common select fields for devices from orders and related goods
        $deviceFields = [
            'o.id as order_id',
            'g.title as name',
            'IF(g.image, CONCAT("' . $cdnUrl . '", g.image), "") as image',
            'o.address as location_raw', // Raw address data, formatting to be done in PHP
            'o.order_renew_time',
            'o.order_renew_status'
        ];

        // Base query for paid service/installation orders
        $baseQuery = Db::name('shop_order')
            ->alias('o')
            ->join('__SHOP_ORDER_GOODS__ og', 'o.id = og.order_id')
            ->join('__SHOP_GOODS__ g', 'og.goods_id = g.id')
            ->where('o.user_id', $userId)
            ->where('o.order_type', 20) // 预约订单 - service/installation order
            ->where('o.paystate', 1);    // Paid

        // Using Devices (includes expiring soon)
        $usingDevicesRaw = (clone $baseQuery)
            ->where('o.order_renew_status', '<>', 30) // Not manually marked as expired
            ->where(function ($query) use ($now) {
                $query->whereNull('o.order_renew_time') // No renew time means it's considered active indefinitely or managed by other means
                      ->whereOr('o.order_renew_time', '>=', $now);
            })
            ->field($deviceFields)
            ->select();

        $using_devices = [];
        $expiring_devices = [];

        foreach ($usingDevicesRaw as $device) {
            $remaining_days = null;
            if ($device['order_renew_time']) {
                $remaining_days = floor(($device['order_renew_time'] - $now) / (60 * 60 * 24));
            }

            $status_text = "正常运行中";
            $status_color = "success"; // Example color
            $remaining_text = $remaining_days !== null ? "剩余 {$remaining_days} 天" : "长期有效";
            $icon = "fa-check-circle"; // Example icon
            $iconBg = "bg-green";      // Example background

            $is_expiring_soon = false;
            if ($remaining_days !== null && $remaining_days <= 30) {
                $status_text = "即将到期";
                $status_color = "warning";
                $icon = "fa-exclamation-triangle";
                $iconBg = "bg-yellow";
                $is_expiring_soon = true;
            }

            // Basic address formatting (assuming address is a string like "Name,Phone,Region,Detail")
            $location_parts = explode(',', $device['location_raw']);
            $location_formatted = isset($location_parts[2]) && isset($location_parts[3]) ? trim($location_parts[2]) . ' ' . trim($location_parts[3]) : '地址未知';


            $formattedDevice = [
                'order_id' => $device['order_id'],
                'name' => $device['name'],
                'image' => $device['image'],
                'location' => $location_formatted,
                'status_text' => $status_text,
                'status_color' => $status_color,
                'remaining_text' => $remaining_text,
                'icon' => $icon,
                'iconBg' => $iconBg,
                'expire_date_raw' => $device['order_renew_time'] ? date('Y-m-d H:i:s', $device['order_renew_time']) : null,
                'is_expiring_soon' => $is_expiring_soon
            ];

            if ($is_expiring_soon) {
                $expiring_devices[] = $formattedDevice;
            }
            // All devices that are not expired, including those expiring soon, go into using_devices
            $using_devices[] = $formattedDevice;
        }

        // Expired Devices
        $expiredDevicesRaw = (clone $baseQuery)
            ->where(function ($query) use ($now) {
                $query->where('o.order_renew_status', 30) // Manually marked as expired
                      ->whereOr(function ($subQuery) use ($now) {
                          $subQuery->whereNotNull('o.order_renew_time')
                                   ->where('o.order_renew_time', '<', $now);
                      });
            })
            ->field($deviceFields)
            ->select();

        $expired_devices = [];
        foreach ($expiredDevicesRaw as $device) {
            $overdue_days = 0;
            if ($device['order_renew_time']) {
                $overdue_days = floor(($now - $device['order_renew_time']) / (60 * 60 * 24));
            }

            $location_parts = explode(',', $device['location_raw']);
            $location_formatted = isset($location_parts[2]) && isset($location_parts[3]) ? trim($location_parts[2]) . ' ' . trim($location_parts[3]) : '地址未知';

            $expired_devices[] = [
                'order_id' => $device['order_id'],
                'name' => $device['name'],
                'image' => $device['image'],
                'location' => $location_formatted,
                'status_text' => "已到期",
                'status_color' => "danger",
                'remaining_text' => $device['order_renew_time'] ? "已超期 {$overdue_days} 天" : "已到期",
                'icon' => "fa-times-circle",
                'iconBg' => "bg-red",
                'expire_date_raw' => $device['order_renew_time'] ? date('Y-m-d H:i:s', $device['order_renew_time']) : null,
            ];
        }

        // To avoid duplication if expiring_devices are just a filtered view of using_devices
        // We can send all using_devices and let frontend filter expiring_soon based on the flag.
        // Or, if the design strictly wants separate lists for "expiring" vs "normal using",
        // then using_devices should exclude expiring_devices.
        // For now, following the plan of three lists, let's filter `using_devices` to exclude `expiring_devices`.
        $true_using_devices = array_filter($using_devices, function($device){
            return !$device['is_expiring_soon'];
        });


        return [
            'using_devices'    => array_values($true_using_devices), // Re-index array
            'expiring_devices' => $expiring_devices,
            'expired_devices'  => $expired_devices,
        ];
    }

    /**
     * Get Detailed Device Information for a User
     *
     * @param int $userId
     * @param int $orderId
     * @return array|null
     */
    public static function getDeviceDetails($userId, $orderId)
    {
        $cdnUrl = Config::get('upload.cdnurl');
        $cdnUrl = $cdnUrl ? $cdnUrl : cdnurl('', true);

        $order = Db::name('shop_order')
            ->alias('o')
            ->join('__SHOP_ORDER_GOODS__ og', 'o.id = og.order_id', 'LEFT') // Use order_id for join
            ->join('__SHOP_GOODS__ g', 'og.goods_id = g.id', 'LEFT')
            // Attempt to join with shop_address IF address_id is present and valid
            // However, the primary address details seem to be stored in o.address as a string
            // ->join('__SHOP_ADDRESS__ a', 'o.address_id = a.id', 'LEFT')
            ->where('o.id', $orderId)
            ->where('o.user_id', $userId)
            ->where('o.order_type', 20) // Ensure it's a service/installation type order
            ->field([
                'o.id as order_id',
                'o.order_sn',
                'o.address as full_address_string', // Raw address string from order
                'o.receiver',
                'o.mobile',
                'o.order_renew_status',
                'o.order_renew_time',
                'o.createtime as order_createtime',
                'og.title as goods_title_in_order',
                'og.nums as quantity',
                'og.price as price_per_item_in_order',
                'og.attrdata as specification_in_order',
                'g.title as product_name',
                'IF(g.image, CONCAT("' . $cdnUrl . '", g.image), "") as product_image',
                'g.content as product_description', // This might be very large, consider if summary is better
                'g.marketprice as product_marketprice',
                'g.price as product_current_price',
                // Address components from o.address if possible, or from joined 'a' if implemented
                // 'a.name as address_receiver_name', // Example if joining fa_shop_address
                // 'a.mobile as address_mobile',      // Example
                // 'a.province as address_province',  // Example
                // 'a.city as address_city',          // Example
                // 'a.area as address_area',          // Example
                // 'a.address as address_detail',     // Example
            ])
            ->find();

        if (!$order) {
            return null;
        }

        // Process address string: "Name,Phone,Region,Detailed Address"
        $address_parts = explode(',', $order['full_address_string'] ?? '');
        $order['parsed_address'] = [
            'name'    => $order['receiver'] ?? ($address_parts[0] ?? ''), // Prioritize o.receiver
            'phone'   => $order['mobile'] ?? ($address_parts[1] ?? ''),   // Prioritize o.mobile
            'region'  => $address_parts[2] ?? '',
            'detail'  => $address_parts[3] ?? '',
            'full'    => str_replace(',', ' ', $order['full_address_string'] ?? '')
        ];
        // Remove raw address string after parsing if preferred
        // unset($order['full_address_string']);


        // Calculate status and remaining time
        $now = time();
        $status_text = "未知";
        $remaining_text = "N/A";
        $is_expired = false;

        if ($order['order_renew_status'] == 30 || ($order['order_renew_time'] && $order['order_renew_time'] < $now)) {
            $status_text = "已到期";
            $is_expired = true;
            if ($order['order_renew_time']) {
                $overdue_days = floor(($now - $order['order_renew_time']) / (60 * 60 * 24));
                $remaining_text = "已超期 {$overdue_days} 天";
            } else {
                $remaining_text = "已到期 (无续约时间)";
            }
        } else {
            $status_text = "正常运行中";
            if ($order['order_renew_time']) {
                $remaining_days = floor(($order['order_renew_time'] - $now) / (60 * 60 * 24));
                if ($remaining_days <= 30) {
                    $status_text = "即将到期";
                }
                $remaining_text = "剩余 {$remaining_days} 天";
            } else {
                // No renew time, but not explicitly expired status (30)
                // This case might mean "active indefinitely" or depends on other business logic
                $status_text = "持续有效";
                $remaining_text = "长期有效";
            }
        }

        $order['status_text'] = $status_text;
        $order['remaining_text'] = $remaining_text;
        $order['is_expired_flag'] = $is_expired;
        $order['expire_date_raw'] = $order['order_renew_time'] ? date('Y-m-d H:i:s', $order['order_renew_time']) : null;
        $order['installation_date'] = date('Y-m-d H:i:s', $order['order_createtime']);

        // Ensure product_description is handled (e.g. strip tags or truncate if too long for a summary view)
        if (!empty($order['product_description'])) {
            $order['product_description'] = strip_tags($order['product_description']);
            // Optionally truncate:
            // $order['product_description'] = mb_substr($order['product_description'], 0, 100) . (mb_strlen($order['product_description']) > 100 ? '...' : '');
        }


        return $order;
    }
}
