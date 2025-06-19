<template>
  <view class="container">
    <!-- 全局加载状态 -->
    <u-loading-page :loading="isLoading" loading-text="正在加载订单..."></u-loading-page>

    <!-- 初始加载状态 -->
    <view v-if="isLoading && orders.length === 0 && !errorLoading" class="loading-state state-container">
      <u-loading-icon text="正在加载订单..." size="24"></u-loading-icon>
    </view>

    <!-- 错误状态 -->
    <view v-if="errorLoading && orders.length === 0" class="error-state state-container">
      <u-empty mode="network" text="订单加载失败"></u-empty>
      <u-button @click="fetchOrders(1)" type="primary" size="medium" text="点我重试" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button>
    </view>

    <!-- 空状态 -->
    <view v-if="!isLoading && !errorLoading && orders.length === 0" class="empty-state state-container">
      <u-empty mode="order" text="您还没有相关订单"></u-empty>
      <!-- 可选: 浏览商品或类似操作的按钮 -->
      <!-- <u-button @click="goToShop" type="primary" plain text="去逛逛" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button> -->
    </view>

    <!-- 订单列表 -->

            <view>
              <text class="order-number">订单编号：{{ order.order_sn || order.order_no || 'N/A' }}</text>
              <!-- 假设订单有一个主要商品或标题，或动态生成一个 -->
              <text class="order-title">{{ order.title || (order.items && order.items.length > 0 ? order.items[0].product_name : '服务订单') }}</text>
            </view>
          </view>
          <view class="status-container">
          </view>
        </view>
        
        <!-- 简化版详情，或如果API返回 order.items 结构则循环显示 -->
        <view class="order-details">
          </view>
           <view class="detail-item"> <!-- 单独显示创建时间 -->
             <text class="detail-label">创建时间:</text>
             <text class="detail-value">{{ item.createtime }}</text>
           </view>
        </view>
        
        <view class="order-footer">
        </view>
      </view>
      <!-- 加载更多提示 -->
      <u-loadmore :status="loadStatus" @loadmore="fetchOrderList(true)" v-if="orderList.length > 0" />
    </view>
  </view>
</template>

<script>
import { getStorageSync, toast } from '../../utils/utils'; // 引入toast

export default {
  data() {
    return {
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
  padding-bottom: 120rpx; // 如果有底部导航栏，调整此值
}

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 30rpx; // 标准化内边距
  text-align: center;
}

// 特定状态指示器的样式（如果需要），补充uView的文本颜色类
.status-installed { background-color: #10b981; /* 绿色 */ } /* 用于小的垂直条和图标背景 */
.status-pending-payment { background-color: #3c9cff; /* 蓝色 */ }
.status-processing { background-color: #f59e0b; /* 黄色/橙色 */ }
.status-cancelled { background-color: #c8c9cc; /* 灰色 */ }
.status-not-installed { background-color: #f59e0b; /* 垂直条的默认/回退颜色 */ }


.text-green { color: #10b981; }
.text-blue { color: #3c9cff; }
.text-yellow { color: #f59e0b; }
.text-grey { color: #909399; }


.header {
  background-color: #ffffff;
  padding: 30rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.back-btn, .more-btn {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title {
  font-size: 36rpx;
  font-weight: 600;
  color: #333333;
}

.order-list {
  padding: 30rpx;
}

.order-card {
  background-color: #ffffff;
  border-radius: 24rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  transition: transform 0.3s;
  
  &:active {
    transform: translateY(-2rpx);
  }
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24rpx;
}

.order-title-container {
  display: flex;
  align-items: flex-start;
}

.order-indicator {
  width: 4rpx;
  height: 80rpx;
  border-radius: 4rpx;
  margin-right: 20rpx;
}

.indicator-installed {
  background-color: #3b82f6;
}

.indicator-not-installed {
  background-color: #d1d5db;
}

.order-number {
  font-size: 24rpx;
  color: #909399;
  display: block;
}

.order-title {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
  margin-top: 8rpx;
  display: block;
}

.status-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.status-icon {
  width: 48rpx;
  height: 48rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10rpx;
}

.status-installed {
  background-color: #10b981;
}

.status-not-installed {
  background-color: #f59e0b;
}

.status-text {
  font-size: 24rpx;
}

.text-green {
  color: #10b981;
}

.text-yellow {
  color: #f59e0b;
}

.order-details {
  margin-bottom: 24rpx;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12rpx;
}

.detail-label {
  font-size: 26rpx;
  color: #909399;
}

.detail-value {
  font-size: 26rpx;
  color: #606266;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20rpx;
  border-top: 2rpx solid #f2f3f5;
  .u-button{ padding: 20px; width: 100px;}
}

.order-price {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
  width: 80%;
}
</style>