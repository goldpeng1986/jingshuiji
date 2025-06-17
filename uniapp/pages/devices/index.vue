<template>
  <view class="container">


    <!-- 设备列表 -->
    <view class="device-list">
      <!-- 使用中设备 -->
      <view class="device-section">
        
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item, index) in usingDevices" 
            :key="index"
            @click="viewDeviceDetail(item)"
          >
            <view class="device-icon-container">
              <view class="device-icon" :style="{backgroundColor: item.iconBg}">
                <u-icon :name="item.icon" :color="item.iconColor" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.statusColor}">{{ item.status }}</text>
              <text class="remaining-text">{{ item.remaining }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 即将到期设备 -->
      <view class="device-section">
       
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item, index) in expiringDevices" 
            :key="index"
            @click="viewDeviceDetail(item)"
          >
            <view class="device-icon-container">
              <view class="device-icon" :style="{backgroundColor: item.iconBg}">
                <u-icon :name="item.icon" :color="item.iconColor" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.statusColor}">{{ item.status }}</text>
              <text class="remaining-text">{{ item.remaining }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 已到期设备 -->
      <view class="device-section">
       
        <view class="device-card">
          <view 
            class="device-item" 
            v-for="(item, index) in expiredDevices" 
            :key="index"
            @click="viewDeviceDetail(item)"
          >
            <view class="device-icon-container">
              <view class="device-icon" :style="{backgroundColor: item.iconBg}">
                <u-icon :name="item.icon" :color="item.iconColor" size="24"></u-icon>
              </view>
            </view>
            <view class="device-info">
              <text class="device-name">{{ item.name }}</text>
              <text class="device-location">{{ item.location }}</text>
            </view>
            <view class="device-status">
              <text class="status-text" :style="{color: item.statusColor}">{{ item.status }}</text>
              <text class="remaining-text">{{ item.remaining }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '我的设备',
      usingDevices: [
        {
          id: 1,
          name: '森泽净水器 A-001',
          location: '北京市朝阳区建国路88号',
          status: '正常运行中',
          remaining: '剩余30天',
          icon: 'arrow-down',
          iconBg: '#e7f1ff',
          iconColor: '#3c9cff',
          statusColor: '#10b981'
        }
      ],
      expiringDevices: [
        {
          id: 2,
          name: '森泽净水器 B-002',
          location: '北京市海淀区中关村大街1号',
          status: '即将到期',
          remaining: '剩余5天',
          icon: 'arrow-down',
          iconBg: '#fdf6ec',
          iconColor: '#f59e0b',
          statusColor: '#f59e0b'
        }
      ],
      expiredDevices: [
        {
          id: 3,
          name: '森泽净水器 C-003',
          location: '北京市西城区西长安街1号',
          status: '已到期',
          remaining: '超期3天',
          icon: 'arrow-down',
          iconBg: '#fef0f0',
          iconColor: '#fa3534',
          statusColor: '#fa3534'
        }
      ]
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
    viewDeviceDetail(device) {
      uni.showToast({
        title: `查看设备: ${device.name}`,
        icon: 'none'
      })
    },
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