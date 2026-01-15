<template>
  <view class="container">
    <!-- 全局加载提示 -->
    <u-loading-page :loading="isLoading" loading-text="正在努力加载设备..."></u-loading-page>

    <!-- 设备列表 -->
    <view class="device-list" v-if="!isLoading">
      <!-- 使用中设备 -->
      <view class="device-section" v-if="usingDevices.length > 0">
        <text class="section-title">使用中 ({{ usingDevices.length }})</text>
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item) in usingDevices"
            :key="item.order_id" <!-- 使用 order_id 作为 key -->
            @click="viewDeviceDetail(item)"
          >
            <!-- 设备图片，如果API提供 -->
            <view class="device-image-container" v-if="item.image">
              <u-image :src="item.image" width="100rpx" height="100rpx" radius="8rpx"></u-image>
            </view>
            <view class="device-icon-container" v-else> <!-- 如果没有图片，显示图标 -->
              <view class="device-icon" :style="{backgroundColor: item.iconBg || '#e7f1ff'}">
                <u-icon :name="item.icon || 'server-man'" :color="item.iconColor || '#3c9cff'" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.status_color || '#10b981'}">{{ item.status_text }}</text>
              <text class="remaining-text">{{ item.remaining_text }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 即将到期设备 -->
      <view class="device-section" v-if="expiringDevices.length > 0">
        <text class="section-title">即将到期 ({{ expiringDevices.length }})</text>
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item) in expiringDevices"
            :key="item.order_id"
            @click="viewDeviceDetail(item)"
          >
            <view class="device-image-container" v-if="item.image">
              <u-image :src="item.image" width="100rpx" height="100rpx" radius="8rpx"></u-image>
            </view>
            <view class="device-icon-container" v-else>
              <view class="device-icon" :style="{backgroundColor: item.iconBg || '#fdf6ec'}">
                <u-icon :name="item.icon || 'clock'" :color="item.iconColor || '#f59e0b'" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.status_color || '#f59e0b'}">{{ item.status_text }}</text>
              <text class="remaining-text">{{ item.remaining_text }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 已到期设备 -->
      <view class="device-section" v-if="expiredDevices.length > 0">
        <text class="section-title">已到期 ({{ expiredDevices.length }})</text>
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item) in expiredDevices"
            :key="item.order_id"
            @click="viewDeviceDetail(item)"
          >
            <view class="device-image-container" v-if="item.image">
              <u-image :src="item.image" width="100rpx" height="100rpx" radius="8rpx"></u-image>
            </view>
            <view class="device-icon-container" v-else>
              <view class="device-icon" :style="{backgroundColor: item.iconBg || '#fef0f0'}">
                <u-icon :name="item.icon || 'close-circle'" :color="item.iconColor || '#fa3534'" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.status_color || '#fa3534'}">{{ item.status_text }}</text>
              <text class="remaining-text">{{ item.remaining_text }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 全部列表为空时的提示 -->
      <view v-if="!isLoading && usingDevices.length === 0 && expiringDevices.length === 0 && expiredDevices.length === 0" class="empty-state">
        <u-empty mode="data" text="暂无设备信息" iconSize="120"></u-empty>
      </view>
    </view>
  </view>
</template>

<script>
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '我的设备',     // 页面标题
      usingDevices: [],     // 使用中设备列表
      expiringDevices: [],  // 即将到期设备列表
      expiredDevices: [],   // 已到期设备列表
      isLoading: false      // 加载状态标志，用于控制加载提示的显示
    };
  },
  onShow() {
    // 页面显示时调用获取设备数据的方法
    this.fetchDeviceData();
  },
  methods: {
    // 获取设备列表数据
    async fetchDeviceData() {
      this.isLoading = true; // 开始加载，显示加载提示
      try {
        const res = await this.$api.getDeviceList(); // 调用API获取设备列表
        if (res.code === 1 && res.data) {
          // 更新数据模型 (注意：API返回的字段名需要与模板中的绑定一致)
          // 后端返回的字段如: name, location, status_text, remaining_text, icon, iconBg, statusColor, order_id, image
          this.usingDevices = res.data.using_devices || [];
          this.expiringDevices = res.data.expiring_devices || [];
          this.expiredDevices = res.data.expired_devices || [];
        } else {
          toast(res.msg || '获取设备列表失败'); // 显示错误提示
          // 可选: 清空列表或保持旧数据
          this.usingDevices = [];
          this.expiringDevices = [];
          this.expiredDevices = [];
        }
      } catch (error) {
        console.error('fetchDeviceData error:', error);
        toast('网络请求设备列表出错');
        this.usingDevices = [];
        this.expiringDevices = [];
        this.expiredDevices = [];
      } finally {
        this.isLoading = false; // 加载完成，隐藏加载提示
      }
    },
    // 跳转到设备详情页
    viewDeviceDetail(device) {
      if (device && device.order_id) {
        uni.navigateTo({
          url: `/pages/devices/detail?order_id=${device.order_id}` // 传递order_id作为设备标识
        });
      } else {
        toast('无法查看设备详情，缺少设备ID');
      }
    },
    // goBack() { // uni-app页面通常有默认返回，除非是自定义导航栏
    //   uni.navigateBack()
    // },
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.header {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 30rpx;
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
  }
}

.header-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.back-btn {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title {
  font-size: 36rpx;
  font-weight: 600;
  color: #ffffff;
  margin-left: 20rpx;
}

.device-list {
  padding: 30rpx;
  padding-bottom: 50rpx;
}

.device-section {
  margin-bottom: 30rpx;
}

.section-title {
  font-size: 28rpx;
  color: #909399;
  margin-bottom: 16rpx;
  padding-left: 10rpx;
  display: block;
}

.device-card {
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.device-item {
  display: flex;
  align-items: center;
  padding: 30rpx;
  position: relative;
  
  &:active {
    background-color: #f9fafc;
  }
  
  &:not(:last-child) {
    border-bottom: 2rpx solid #f2f3f5;
  }
}

.device-icon-container {
  margin-right: 20rpx;
}

.device-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.device-info {
  flex: 1;
}

.device-name {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
  display: block;
}

.device-location {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.device-status {
  text-align: right;
}

.status-text {
  font-size: 28rpx;
  font-weight: 500;
  display: block;
}

.remaining-text {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.add-device-btn {
  margin-top: 50rpx;
}
</style>