<template>
  <view class="container">


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
    <view class="order-list" v-if="orders.length > 0">
      <!-- 订单卡片 -->
      <view 
        v-for="(order, index) in orders"
        :key="order.id || order.order_sn || index" <!-- 确保key的唯一性 -->
        class="order-card"
        @click="navigateToOrderDetail(order.id || order.order_sn)" <!-- 添加跳转到订单详情页的事件 -->
      >
        <view class="order-header">
          <view class="order-title-container">
            <!-- 根据API响应调整状态指示器 -->
            <view class="order-indicator" :class="getOrderStatusClass(order.status_code || order.status)"></view>
            <view>
              <text class="order-number">订单编号：{{ order.order_sn || order.order_no || 'N/A' }}</text>
              <!-- 假设订单有一个主要商品或标题，或动态生成一个 -->
              <text class="order-title">{{ order.title || (order.items && order.items.length > 0 ? order.items[0].product_name : '服务订单') }}</text>
            </view>
          </view>
          <view class="status-container">
             <!-- 根据API响应调整状态图标和文本 -->
            <view class="status-icon" :class="getOrderStatusClass(order.status_code || order.status)">
              <u-icon :name="getOrderStatusIcon(order.status_code || order.status)" color="#ffffff" size="12"></u-icon>
            </view>
            <text class="status-text" :class="getOrderStatusTextColor(order.status_code || order.status)">{{ order.status_text || order.status_name || '未知状态' }}</text>
          </view>
        </view>
        
        <!-- 简化版详情，或如果API返回 order.items 结构则循环显示 -->
        <view class="order-details">
          <view class="detail-item" v-if="order.create_time">
            <text class="detail-label">下单时间:</text>
            <text class="detail-value">{{ order.create_time }}</text>
          </view>
          <view class="detail-item" v-if="order.total_amount">
            <text class="detail-label">订单总额:</text>
            <text class="detail-value">¥{{ order.total_amount }}</text>
          </view>
          <!-- 如果 order.items 存在，则显示第一个商品信息的示例 -->
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
            @click.native.stop="handlePayment(order)" <!-- 添加支付处理事件 -->
          >{{ order.pay_status_text || order.pay_status_name || 'N/A' }}</u-button>
        </view>
      </view>
    </view>

    <!-- 加载更多 / 分页加载状态 -->
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
      orders: [], // 从 orderList 重命名并初始化为空数组
      isLoading: true, // 初始加载状态设置为 true
      errorLoading: false, // 错误加载状态
      currentPage: 1, // 当前页码
      totalPages: 1, // 总页数
      apiToken: null, // 用于存储token
      apiUserId: null, // 用于存储用户ID
    }
  },
  onLoad(e) {
    // 存储token和用户ID，然后获取订单
    this.apiToken = getStorageSync('token');
    const userInfo = getStorageSync('userInfo');
    this.apiUserId = userInfo ? userInfo.id : null; // 假设 userInfo 对象有 id 属性

    if (!this.apiToken || !this.apiUserId) {
      console.error("Token 或用户 ID 缺失，无法获取订单。");
      this.isLoading = false;
      this.errorLoading = true;
      uni.showToast({
        title: '认证信息缺失',
        icon: 'error',
        duration: 2000
      });
      // 如果token/用户ID是关键信息，可以选择重定向到登录页
      // uni.redirectTo({ url: '/pages/login/index' });
      return;
    }

    this.fetchOrders(1); // 获取初始页数据
  },
  onReachBottom() {
    // 页面触底加载更多
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchOrders(this.currentPage + 1);
    } else if (this.currentPage >= this.totalPages && !this.isLoading) {
      // 此处无需提示，u-loadmore 组件会显示 "没有更多了" 的状态
    }
  },
  methods: {
    navigateToOrderDetail(orderId) {
      // 跳转到订单详情页
      if (!orderId) {
        console.warn('调用 navigateToOrderDetail 时 orderId 为空');
        uni.showToast({ title: '无效订单', icon: 'none' });
        return;
      }
      uni.navigateTo({
        url: `/pages/order/detail?id=${orderId}` // 假设订单详情页路径为 order/detail
      });
    },
    getOrderStatusClass(status) {
      // 示例：根据实际的状态码或值进行调整
      // 这些可能是API返回的数字代码或字符串标识符
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'status-installed'; // 绿色系
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'status-pending-payment'; // 蓝色系或橙色系
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'status-processing'; // 黄色系
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'status-cancelled'; //灰色系
      }
      return 'status-not-installed'; // 默认或其他状态
    },
    getOrderStatusIcon(status) {
      // 示例：根据状态返回对应的uView图标名称
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'checkmark-circle-fill';
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'clock-fill';
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'hourglass-half-fill';
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'close-circle-fill';
      }
      return 'question-circle-fill'; // 未知状态的默认图标
    },
    getOrderStatusTextColor(status) {
      // 示例：根据状态返回对应的文本颜色class
      if (status === 'completed' || status === 1 || status === '已安装' || status === '已完成') {
        return 'text-green';
      } if (status === 'pending_payment' || status === '待支付' || status === 2) {
        return 'text-blue'; // 或 text-orange
      } if (status === 'processing' || status === '处理中' || status === 3) {
        return 'text-yellow';
      } if (status === 'cancelled' || status === '已取消' || status === 0 || status === 'canceled') {
        return 'text-grey';
      }
      return 'text-grey';
    },
    getPayStatusType(payStatus) {
      // 示例：支付状态可能为 'UNPAID', 'PAID', 'REFUNDED' 等字符串或数字代码
      if (payStatus === 'UNPAID' || payStatus === 0 || payStatus === '未支付') {
        return 'warning'; // uView按钮类型
      } if (payStatus === 'PAID' || payStatus === 1 || payStatus === '已支付') {
        return 'success';
      } if (payStatus === 'REFUNDED' || payStatus === 2 || payStatus === '已退款') {
        return 'info';
      }
      return 'default';
    },
    handlePayment(order) {
      // 处理支付逻辑
      if (!order) return;
      // 示例：跳转到支付页面或发起支付请求
      // 在继续前检查订单是否未支付
      const isUnpaid = order.pay_status_code === 0 || order.pay_status === 'UNPAID' || order.pay_status_text === '未支付';
      if (isUnpaid) {
        uni.showToast({ title: `处理订单 ${order.order_sn || order.id} 的支付...`, icon: 'loading' });
        // uni.navigateTo({ url: `/pages/payment/index?orderId=${order.id || order.order_sn}` }); // 跳转到支付页示例
      } else {
        uni.showToast({ title: `订单 ${order.pay_status_text || order.pay_status_name || '状态未知'}`, icon: 'none' });
      }
    },
    goToShop() { // 空状态按钮的可选方法
        uni.switchTab({ // 假设有一个商店/首页的tab
            url: '/pages/index/index'
        });
    },
    fetchOrders(page = 1) {
      // 获取订单列表
      if (!this.apiToken || !this.apiUserId) {
        console.warn("调用 fetchOrders 时 token 或 userId 为空。");
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
        limit: 10 // 假设每页限制10条数据
      };

      this.$api.orderList(params).then(res => {
        if (res && res.data && res.data.list) { // 根据你的API响应结构调整
          if (page === 1) {
            this.orders = res.data.list; // 第一页，直接赋值
          } else {
            this.orders = this.orders.concat(res.data.list); // 后续页，追加数据
          }
          // API 可能使用 'page' 或 'current_page'
          this.currentPage = parseInt(res.data.current_page || res.data.page || 1);
          // API 可能使用 'last_page' 或 'total_pages'
          this.totalPages = parseInt(res.data.last_page || res.data.total_pages || 1);
        } else {
          console.warn("未找到订单数据或数据格式不符合预期:", res);
          if (page === 1) this.orders = []; // 如果是第一页且获取数据失败，则清空列表
          // 如果列表为空但响应正常，考虑将总页数设为当前页
          if (res && res.data && !res.data.list) {
             this.totalPages = this.currentPage;
          }
        }
        this.isLoading = false;
      }).catch(error => {
        console.error("获取订单时发生错误:", error);
        this.isLoading = false;
        this.errorLoading = true;
        if (page === 1) this.orders = []; // 如果是第一页且发生错误，则清空订单
         uni.showToast({
            title: '订单加载失败',
            icon: 'none'
        });
      });
    },
    goBack() {
      uni.navigateBack(); // 返回上一页
    },
    tabbarChange(index) {
      // 处理底部导航切换逻辑（如果适用）
    },
    goToPage(url) {
      // 跳转到指定tab页面
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