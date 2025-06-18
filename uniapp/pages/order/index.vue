<template>
  <view class="container">


    <!-- Initial Loading State -->
    <view v-if="isLoading && orders.length === 0 && !errorLoading" class="loading-state state-container">
      <u-loading-icon text="正在加载订单..." size="24"></u-loading-icon>
    </view>

    <!-- Error State -->
    <view v-if="errorLoading && orders.length === 0" class="error-state state-container">
      <u-empty mode="network" text="订单加载失败"></u-empty>
      <u-button @click="fetchOrders(1)" type="primary" size="medium" text="点我重试" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button>
    </view>

    <!-- Empty State -->
    <view v-if="!isLoading && !errorLoading && orders.length === 0" class="empty-state state-container">
      <u-empty mode="order" text="您还没有相关订单"></u-empty>
      <!-- Optional: Button to browse products or something similar -->
      <!-- <u-button @click="goToShop" type="primary" plain text="去逛逛" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button> -->
    </view>

    <!-- 订单列表 -->
    <view class="order-list" v-if="orders.length > 0">
      <!-- 订单卡片 -->
      <view 
        v-for="(order, index) in orders"
        :key="order.id || order.order_sn || index" <!-- Ensure unique key -->
        class="order-card"
        @click="navigateToOrderDetail(order.id || order.order_sn)" <!-- Add navigation to detail page -->
      >
        <view class="order-header">
          <view class="order-title-container">
            <!-- Adjust status indicator based on API response -->
            <view class="order-indicator" :class="getOrderStatusClass(order.status_code || order.status)"></view>
            <view>
              <text class="order-number">订单编号：{{ order.order_sn || order.order_no || 'N/A' }}</text>
              <!-- Assuming a main product or title for the order, or generate one -->
              <text class="order-title">{{ order.title || (order.items && order.items.length > 0 ? order.items[0].product_name : '服务订单') }}</text>
            </view>
          </view>
          <view class="status-container">
             <!-- Adjust status icon and text based on API response -->
            <view class="status-icon" :class="getOrderStatusClass(order.status_code || order.status)">
              <u-icon :name="getOrderStatusIcon(order.status_code || order.status)" color="#ffffff" size="12"></u-icon>
            </view>
            <text class="status-text" :class="getOrderStatusTextColor(order.status_code || order.status)">{{ order.status_text || order.status_name || '未知状态' }}</text>
          </view>
        </view>
        
        <!-- Simplified details, or loop through order.items if that's the structure -->
        <view class="order-details">
          <view class="detail-item" v-if="order.create_time">
            <text class="detail-label">下单时间:</text>
            <text class="detail-value">{{ order.create_time }}</text>
          </view>
          <view class="detail-item" v-if="order.total_amount">
            <text class="detail-label">订单总额:</text>
            <text class="detail-value">¥{{ order.total_amount }}</text>
          </view>
          <!-- Example for displaying first product item if order.items exists -->
          <view class="detail-item" v-if="order.items && order.items.length > 0">
              <text class="detail-label">{{ order.items[0].product_name }}:</text>
              <text class="detail-value">x{{ order.items[0].quantity }}</text>
          </view>
        </view>
        
        <view class="order-footer">
          <text class="order-price">总费用 ¥{{ order.order_amount || order.total_fee || order.price || '0.00' }}</text>
          <u-button 
            :type="getPayStatusType(order.pay_status_code || order.pay_status)"
            size="mini" 
            shape="circle"
            @click.native.stop="handlePayment(order)" <!-- Add payment handling -->
          >{{ order.pay_status_text || order.pay_status_name || 'N/A' }}</u-button>
        </view>
      </view>
    </view>

    <!-- Load More / Loading state for pagination -->
    <view v-if="isLoading && orders.length > 0" class="loading-more state-container">
        <u-loadmore status="loading" loadingText="努力加载更多订单中..." />
    </view>
     <view v-if="!isLoading && currentPage >= totalPages && orders.length > 0" class="loading-more state-container">
        <u-loadmore status="nomore" nomoreText="没有更多订单了" />
    </view>

  </view>
</template>

<script>
import { getStorageSync } from '../../utils/utils'

export default {
  data() {
    return {
      title: '我的订单',
      orders: [], // Renamed from orderList and initialized as empty
      isLoading: true, // Start with loading true
      errorLoading: false,
      currentPage: 1,
      totalPages: 1,
      apiToken: null, // To store the token
      apiUserId: null, // To store user_id
    }
  },
  onLoad(e) {
    // Store token and user_id, then fetch orders
    this.apiToken = getStorageSync('token');
    const userInfo = getStorageSync('userInfo');
    this.apiUserId = userInfo ? userInfo.id : null; // Assuming userInfo has an id property

    if (!this.apiToken || !this.apiUserId) {
      console.error("Token or User ID is missing. Cannot fetch orders.");
      this.isLoading = false;
      this.errorLoading = true;
      uni.showToast({
        title: '认证信息缺失',
        icon: 'error',
        duration: 2000
      });
      // Optionally redirect to login if token/user_id is critical
      // uni.redirectTo({ url: '/pages/login/index' });
      return;
    }

    this.fetchOrders(1); // Fetch initial page
  },
  onReachBottom() {
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchOrders(this.currentPage + 1);
    } else if (this.currentPage >= this.totalPages && !this.isLoading) {
      // No toast here, u-loadmore component shows "no more" state if used in template for it
    }
  },
  methods: {
    navigateToOrderDetail(orderId) {
      if (!orderId) {
        console.warn('navigateToOrderDetail called without orderId');
        uni.showToast({ title: '无效订单', icon: 'none' });
        return;
      }
      uni.navigateTo({
        url: `/pages/order/detail?id=${orderId}` // Assuming detail page is order/detail
      });
    },
    getOrderStatusClass(status) {
      // Example: Adjust based on your actual status codes/values
      // These might be numeric codes or string identifiers from your API
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'status-installed'; // Greenish
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'status-pending-payment'; // Bluish or Orangeish
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'status-processing'; // Yellowish
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'status-cancelled'; // Greyish
      }
      return 'status-not-installed'; // Default or other states
    },
    getOrderStatusIcon(status) {
      // Example:
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'checkmark-circle-fill';
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'clock-fill';
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'hourglass-half-fill';
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'close-circle-fill';
      }
      return 'question-circle-fill'; // Default for unknown states
    },
    getOrderStatusTextColor(status) {
      // Example:
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'text-green';
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'text-blue'; // Or text-orange
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'text-yellow';
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'text-grey';
      }
      return 'text-grey';
    },
    getPayStatusType(payStatus) {
      // Example: payStatus might be a string like 'UNPAID', 'PAID', 'REFUNDED' or numeric codes
      if (payStatus === 'UNPAID' || payStatus === 0 || payStatus === '未支付') {
        return 'warning'; // uView type for button
      } if (payStatus === 'PAID' || payStatus === 1 || payStatus === '已支付') {
        return 'success';
      } if (payStatus === 'REFUNDED' || payStatus === 2 || payStatus === '已退款') {
        return 'info';
      }
      return 'default';
    },
    handlePayment(order) {
      if (!order) return;
      // Example: Navigate to a payment page or initiate payment
      // Check if order is unpaid before proceeding
      const isUnpaid = order.pay_status_code === 0 || order.pay_status === 'UNPAID' || order.pay_status_text === '未支付';
      if (isUnpaid) {
        uni.showToast({ title: `处理订单 ${order.order_sn || order.id} 的支付...`, icon: 'loading' });
        // uni.navigateTo({ url: `/pages/payment/index?orderId=${order.id || order.order_sn}` });
      } else {
        uni.showToast({ title: `订单 ${order.pay_status_text || order.pay_status_name || '状态未知'}`, icon: 'none' });
      }
    },
    goToShop() { // Optional method for empty state button
        uni.switchTab({ // Assuming you have a tab for shop/home
            url: '/pages/index/index'
        });
    },
    fetchOrders(page = 1) {
      if (!this.apiToken || !this.apiUserId) {
        console.warn("fetchOrders called without token or userId.");
        this.isLoading = false;
        this.errorLoading = true;
        return;
      }
      this.isLoading = true;
      this.errorLoading = false;

      const params = {
        token: this.apiToken,
        user_id: this.apiUserId,
        page: page,
        limit: 10 // Assuming a limit of 10 items per page
      };

      this.$api.orderList(params).then(res => {
        if (res && res.data && res.data.list) { // Adjust based on your API response structure
          if (page === 1) {
            this.orders = res.data.list;
          } else {
            this.orders = this.orders.concat(res.data.list);
          }
          this.currentPage = parseInt(res.data.current_page || res.data.page || 1); // API might use 'page' or 'current_page'
          this.totalPages = parseInt(res.data.last_page || res.data.total_pages || 1); // API might use 'last_page' or 'total_pages'
        } else {
          console.warn("Orders data not found or in unexpected format:", res);
          if (page === 1) this.orders = []; // Clear if first page load fails to get data
          // Consider setting totalPages to currentPage if list is empty but response is otherwise ok
          if (res && res.data && !res.data.list) {
             this.totalPages = this.currentPage;
          }
        }
        this.isLoading = false;
      }).catch(error => {
        console.error("Error fetching orders:", error);
        this.isLoading = false;
        this.errorLoading = true;
        if (page === 1) this.orders = []; // Clear orders on error for the first page
         uni.showToast({
            title: '订单加载失败',
            icon: 'none'
        });
      });
    },
    goBack() {
      uni.navigateBack();
    },
    tabbarChange(index) {
      // 处理底部导航切换
    },
    goToPage(url) {
      uni.switchTab({
        url: url
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
  padding-bottom: 120rpx; // Adjust if you have a tabbar
}

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 30rpx; // Standardized padding
  text-align: center;
}

// Specific styles for status indicators if needed, complementing uView's text color classes
.status-installed { background-color: #10b981; /* Green */ } // For the little vertical bar and icon bg
.status-pending-payment { background-color: #3c9cff; /* Blue */ }
.status-processing { background-color: #f59e0b; /* Yellow/Orange */ }
.status-cancelled { background-color: #c8c9cc; /* Grey */ }
.status-not-installed { background-color: #f59e0b; /* Default/fallback color for vertical bar */ }


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