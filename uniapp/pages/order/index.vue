<template>
  <view class="container">
    <!-- 全局加载状态 -->
    <u-loading-page :loading="isLoading" loading-text="正在加载订单..."></u-loading-page>

    <!-- 订单列表 -->
    <view class="order-list" v-if="!isLoading && orderList.length > 0">
      <!-- 订单卡片 -->
      <view 
        v-for="(item) in orderList"
        :key="item.id" <!-- 使用订单ID作为key -->
        class="order-card"
        @click="viewOrderDetail(item)" <!-- 点击卡片查看详情 -->
      >
        <view class="order-header">
          <view class="order-title-container">
            <!-- 订单状态指示器，可以根据 item.status (原始状态码) 或 item.status_name 进行判断 -->
            <view class="order-indicator" :class="getOrderIndicatorClass(item)"></view>
            <view>
              <text class="order-number">订单编号：{{ item.orderNumber }}</text>
              <text class="order-title">{{ item.title }}</text>
            </view>
          </view>
          <view class="status-container">
            <!-- 状态图标和文本 -->
            <view class="status-icon" :class="getOrderIndicatorClass(item)">
              <u-icon :name="getOrderStatusIcon(item)" color="#ffffff" size="12"></u-icon>
            </view>
            <text class="status-text" :style="{ color: getOrderStatusColor(item) }">{{ item.status_name }}</text>
          </view>
        </view>
        
        <view class="order-details">
          <view class="detail-item" v-for="(detail, dIndex) in item.details" :key="dIndex">
            <text class="detail-label">{{ detail.label }}:</text> <!-- 标签后加冒号 -->
            <text class="detail-value">{{ detail.value }}</text>
          </view>
           <view class="detail-item"> <!-- 单独显示创建时间 -->
             <text class="detail-label">创建时间:</text>
             <text class="detail-value">{{ item.createtime }}</text>
           </view>
        </view>
        
        <view class="order-footer">
          <text class="order-price">费用 ¥{{ item.price }}</text>
          <!-- 支付状态按钮，可以根据实际需要添加点击事件，例如去支付 -->
          <u-button 
            :type="getPayStatusButtonType(item.payStatus)"
            size="mini" 
            shape="circle"
            :disabled="item.payStatus !== '未支付'"
            @click.stop="goToPay(item)" <!-- 阻止事件冒泡到父级viewOrderDetail -->
          >{{ item.payStatus }}</u-button>
        </view>
      </view>
      <!-- 加载更多提示 -->
      <u-loadmore :status="loadStatus" @loadmore="fetchOrderList(true)" v-if="orderList.length > 0" />
    </view>

    <!-- 空状态提示 -->
    <u-empty
      v-if="!isLoading && orderList.length === 0"
      mode="order"
      text="暂无相关订单"
      marginTop="100"
      iconSize="120"
    ></u-empty>
  </view>
</template>

<script>
import { getStorageSync, toast } from '../../utils/utils'; // 引入toast

export default {
  data() {
    return {
      title: '我的订单', // 页面标题
      orderList: [],    // 订单列表，将从API获取
      isLoading: false, // 页面初次加载状态
      page: 1,          // 当前页码
      limit: 10,        // 每页数量
      loadStatus: 'loadmore' // 加载更多状态: 'loadmore', 'loading', 'nomore'
    };
  },
  onLoad(e) {
    // 页面加载时获取初始订单数据
    this.fetchOrderList(false);
  },
  onShow() {
    // 页面显示时也可以考虑刷新数据，特别是如果其他页面操作可能影响订单列表
    // this.fetchOrderList(false); // 取消注释以在onShow时刷新
  },
  onReachBottom() {
    // 页面滚动到底部时加载更多订单
    if (this.loadStatus === 'loadmore') {
      this.fetchOrderList(true);
    }
  },
  methods: {
    // 获取订单列表数据
    async fetchOrderList(isLoadMore = false) {
      if (!isLoadMore) { // 如果是首次加载或下拉刷新
        this.page = 1;
        this.orderList = [];
        this.loadStatus = 'loadmore';
        this.isLoading = true; // 仅在首次加载时显示页面级loading
      } else { // 如果是加载更多
        if (this.loadStatus === 'loading' || this.loadStatus === 'nomore') {
          return; // 防止重复加载或已无更多时加载
        }
        this.loadStatus = 'loading';
      }

      try {
        // API参数，可以根据需要添加筛选条件，如订单类型
        // 假设后端 orderList API 接受 page 和 limit, 并返回 FastAdmin 标准分页结构
        const params = {
          page: this.page,
          limit: this.limit,
          // order_type: 10, // 如果需要筛选特定类型的订单，例如商品订单
          // token: getStorageSync('token'), // API如果需要token，request工具通常会自动处理
          // user_id: getStorageSync('userInfo') ? getStorageSync('userInfo').id : null // 用户ID，同上
        };

        const res = await this.$api.orderList(params);

        if (res.code === 1 && res.data && res.data.data) {
          const newOrders = res.data.data.map(order => {
            // 数据适配与格式化 (非常重要，确保与模板绑定一致)
            return {
              id: order.id, // 订单ID
              orderNumber: order.order_sn, // 订单编号
              // title: order.title || (order.order_goods && order.order_goods[0] ? order.order_goods[0].title : '订单商品'), // 订单标题
              title: order.order_type === 20 && order.order_goods && order.order_goods.length > 0 ? order.order_goods[0].title : (order.order_type_text || '商品购买'), // 根据订单类型显示标题
              status: order.orderstate, // 订单状态 (原始状态码，可用于逻辑判断)
              status_name: order.status_text || '未知状态', // 订单状态文本 (后端已提供)
              payStatus: order.paystate_text || '未知', // 支付状态文本 (后端已提供)
              price: parseFloat(order.payamount || 0).toFixed(2), // 支付金额
              createtime: order.createtime ? this.$u.timeFormat(order.createtime, 'yyyy-mm-dd hh:MM') : '未知时间', // 创建时间
              // 订单详情项 (details) - 需要根据API返回的 order_goods 结构来构建
              details: this.formatOrderDetails(order),
              // 其他可能需要的字段，如 order_type, paytype_text 等
              order_type_text: order.order_type_text || '', // 订单类型文本
              paytype_text: order.paytype_text || '',     // 支付方式文本
            };
          });

          if (isLoadMore) {
            this.orderList = this.orderList.concat(newOrders); // 追加数据
          } else {
            this.orderList = newOrders; // 替换数据
          }

          this.page++; // 页码自增
          this.loadStatus = newOrders.length < this.limit ? 'nomore' : 'loadmore'; // 更新加载状态
        } else {
          if (!isLoadMore) this.orderList = []; // 首次加载失败则清空
          this.loadStatus = isLoadMore ? 'loadmore' : 'nomore'; // 加载更多失败时允许重试，或标记为nomore
          toast(res.msg || '获取订单列表失败');
        }
      } catch (error) {
        console.error('fetchOrderList error:', error);
        if (!isLoadMore) this.orderList = [];
        this.loadStatus = 'loadmore'; // 网络错误时允许重试
        toast('网络请求订单列表出错');
      } finally {
        if (!isLoadMore) this.isLoading = false; // 结束页面级loading
        if (isLoadMore) this.loadStatus = (this.loadStatus === 'loading' ? 'loadmore' : this.loadStatus); // 如果是loading中出错，恢复为loadmore
      }
    },

    // 格式化订单详情显示 (辅助方法)
    formatOrderDetails(order) {
        let details = [];
        // details.push({ label: '订单类型', value: order.order_type_text || '普通订单' });
        if (order.order_goods && order.order_goods.length > 0) {
            // 如果是商品订单，可以显示第一个商品的部分信息或数量等
            // details.push({ label: '商品数量', value: order.order_goods.reduce((sum, item) => sum + item.nums, 0) + '件' });
        }
        if (order.paytype_text) {
            details.push({ label: '支付方式', value: order.paytype_text });
        }
        if (order.createtime) { // 已在外部格式化，这里用格式化后的
            details.push({ label: '下单时间', value: this.$u.timeFormat(order.createtime, 'yyyy-mm-dd hh:MM') });
        }
        // 根据需要添加更多从 order 对象中提取的详情
        // 例如，如果是服务订单，可以显示服务相关信息
        if (order.order_type === 20 && order.appointment_date) { // 假设服务订单有预约日期
             details.push({ label: '预约日期', value: order.appointment_date });
        }
         if (order.memo && order.memo.includes("预约日期")) { // 从备注解析预约信息
            const memoParts = order.memo.split(" ");
            const datePart = memoParts.find(part => part.startsWith("预约日期："));
            const timeSlotPart = memoParts.find(part => part.startsWith("时间段："));
            if (datePart) details.push({ label: '预约信息', value: datePart.replace("预约日期：","") + (timeSlotPart ? " " + timeSlotPart.replace("时间段：","") : "") });
        }


        return details;
    },

    // 跳转到订单详情页 (如果需要)
    viewOrderDetail(order) {
      // uni.navigateTo({ url: `/pages/order/detail?order_sn=${order.orderNumber}` });
      toast(`查看订单详情: ${order.orderNumber} (功能待实现)`);
    },

    // goBack() { // 如果未使用自定义导航栏，此方法通常不需要
    //   uni.navigateBack()
    // },
    // tabbarChange(index) {
    //   // 处理底部导航切换 (如果此页面是Tab页的一部分)
    // },
    // goToPage(url) {
    //   uni.switchTab({
    //     url: url
    //   })
    // }

    // 根据订单状态决定指示器样式类
    getOrderIndicatorClass(order) {
      // 假设 status_name 包含“完成”或“已安装”等表示积极状态的词
      if (order.status_name && (order.status_name.includes('完成') || order.status_name.includes('已安装') || order.status_name.includes('已支付'))) {
        return 'indicator-installed'; // 绿色或蓝色系
      }
      // 可以根据 order.status (原始状态码) 添加更具体的逻辑
      if (order.status_name && (order.status_name.includes('取消') || order.status_name.includes('失效') || order.status_name.includes('退款'))) {
          return 'indicator-cancelled'; // 灰色或红色系
      }
      return 'indicator-not-installed'; // 黄色或橙色系 (进行中)
    },

    // 根据订单状态获取状态图标
    getOrderStatusIcon(order) {
      if (order.status_name && (order.status_name.includes('完成') || order.status_name.includes('已安装') || order.status_name.includes('已支付'))) {
        return 'checkmark-circle-fill';
      }
      if (order.status_name && (order.status_name.includes('取消') || order.status_name.includes('失效') || order.status_name.includes('退款'))) {
          return 'close-circle-fill';
      }
      return 'more-circle-fill'; // 进行中或其他状态
    },

    // 根据订单状态获取状态文本颜色
    getOrderStatusColor(order) {
      if (order.status_name && (order.status_name.includes('完成') || order.status_name.includes('已安装') || order.status_name.includes('已支付'))) {
        return '#10b981'; // 绿色
      }
       if (order.status_name && (order.status_name.includes('取消') || order.status_name.includes('失效') || order.status_name.includes('退款'))) {
          return '#fa3534'; // 红色
      }
      return '#f59e0b'; // 黄色/橙色
    },

    // 根据支付状态获取按钮类型
    getPayStatusButtonType(payStatus) {
        if (payStatus === '未支付') return 'warning';
        if (payStatus === '已支付') return 'success';
        return 'info'; // 其他状态，如退款中等
    },

    // 去支付 (如果订单未支付)
    goToPay(order) {
        if (order.payStatus === '未支付') {
            // uni.navigateTo({ url: `/pages/payment/pay?order_sn=${order.orderNumber}` });
            toast(`订单 ${order.orderNumber} 去支付 (功能待实现)`);
        }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
  padding-bottom: 120rpx;
}

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