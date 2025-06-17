<template>
  <view class="container">


    <!-- 订单列表 -->
    <view class="order-list">
      <!-- 订单卡片 -->
      <view 
        v-for="(item, index) in orderList" 
        :key="index"
        class="order-card"
      >
        <view class="order-header">
          <view class="order-title-container">
            <view class="order-indicator" :class="item.status==1? 'status-installed' : 'status-not-installed'"></view>
            <view>
              <text class="order-number">订单编号：{{ item.orderNumber }}</text>
              <text class="order-title">{{ item.title }}</text>
            </view>
          </view>
          <view class="status-container">
            <view class="status-icon" :class="item.status==1? 'status-installed' : 'status-not-installed'">
              <u-icon :name="item.status === 1 ? 'checkmark' : 'tools'" color="#ffffff" size="12"></u-icon>
            </view>
            <text class="status-text" :class="item.status_name === '已安装' ? 'text-green' : 'text-yellow'">{{ item.status_name }}</text>
          </view>
        </view>
        
        <view class="order-details">
          <view class="detail-item" v-for="(detail, dIndex) in item.details" :key="dIndex">
            <text class="detail-label">{{ detail.label }}</text>
            <text class="detail-value">{{ detail.value }}</text>
          </view>
        </view>
        
        <view class="order-footer">
          <text class="order-price">费用 ¥{{ item.price }}</text>
          <u-button 
            :type="item.payStatus === '未支付' ? 'warning' : 'success'" 
            size="mini" 
            shape="circle"
          >{{ item.payStatus }}</u-button>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { getStorageSync } from '../../utils/utils'

export default {
  data() {
    return {
      title: '我的订单',
      orderList: [
        {
          orderNumber: '12232324455551',
          title: '安装服务费',
          status_name: '已安装',
		  status:1,
          details: [
            { label: '设备序列号', value: '2XD041503963' },
            { label: '设备类型', value: '家用' },
            { label: '支付方式', value: '微信支付' },
            { label: '使用时间', value: '2021-01-02 14:50:10' }
          ],
          price: '200',
          payStatus: '未支付'
        },
        {
          orderNumber: '12232324455551',
          title: '首次购买家用净水机',
          status_name: '已安装',
          status:1,
          details: [
            { label: '设备序列号', value: '2XD041503963' },
            { label: '设备类型', value: '家用' },
            { label: '支付方式', value: '微信支付' },
            { label: '使用时间', value: '2021-01-02 14:50:10' }
          ],
          price: '199',
          payStatus: '已支付'
        }
      ]
    }
  },
  onLoad(e) {
	var userInfo=getStorageSync('userInfo').id;
	console.log(userInfo);
  	this.$api.orderList({'token':getStorageSync('token'),'user_id':3}).then(res=>{
  		console.log(res)
  	})
  },
  methods: {
    goBack() {
      uni.navigateBack()
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