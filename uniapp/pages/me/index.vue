<template>
  <view class="container">
    <!-- 用户信息卡片 -->
    <view class="user-info-card">
      <view class="user-info-content">
        <view class="avatar-container">
          <u-avatar
            :src="userInfo.avatar"
            size="80"
            mode="square"
            :customStyle="{borderRadius: '24rpx', boxShadow: '0 4rpx 8rpx rgba(0, 0, 0, 0.1)'}"
          ></u-avatar>
        </view>
        <view class="user-details">
          <text class="username">{{ userInfo.nickname }}</text>
          <view class="user-id">
            <u-icon name="account" size="24" color="#333333"></u-icon>
            <text class="id-text">ID: {{ userInfo.id }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 账户信息卡片 -->
    <view class="account-card">
      <view class="account-info">
        <view class="account-money">
          <text class="account-label">分销佣金</text>
          <text class="account-balance">¥{{ userInfo.money }}</text>
        </view>
        <u-button 
          type="primary" 
          shape="circle"
          :customStyle="{width: '200rpx', height: '90rpx', background: 'linear-gradient(135deg, #4a7aff 0%, #1d4ed8 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none'}"
          @click="goToRecharge"
        >
          <view class="btn-content">
            <text>充值</text>
          </view>
		  
        </u-button>
      </view>
      <view class="expense-info">
        <text>今日收入: ¥{{ accountInfo.todayExpense }}</text>
        <text>本月收入: ¥{{ accountInfo.monthExpense }}</text>
      </view>
    </view>

    <!-- 功能列表 -->
    <view class="function-list">
      <u-cell-group>
        <u-cell 
          v-for="(item, index) in functionList" 
          :key="index"
          :title="item.title"
          :label="item.subtitle"
          :isLink="true"
          @click="handleFunctionClick(item)"
        >
          <view slot="icon" class="cell-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="36"></u-icon>
          </view>
        </u-cell>
      </u-cell-group>
    </view>

    <!-- 其他功能 -->
    <view class="other-functions">
      <u-cell-group>
        <u-cell 
          v-for="(item, index) in otherFunctionList" 
          :key="index"
          :title="item.title"
          :isLink="true"
          @click="handleOtherFunctionClick(item)"
        >
          <view slot="icon" class="cell-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="36"></u-icon>
          </view>
        </u-cell>
      </u-cell-group>
    </view>
  </view>
</template>

<script>
	import {toast, clearStorageSync, setStorageSync,getStorageSync, useRouter} from '@/utils/utils.js'
export default {
  data() {
    return {
      title: '个人中心',
      userInfo: {
        nickname: '张三',
        id: '888888',
        avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-1.2.1&auto=format&fit=crop&w=80&h=80'
      },
      accountInfo: {
        dealer_price: '50.00',
        todayExpense: '0.00',
        monthExpense: '10.00'
      },
      functionList: [
        {
          title: '我的设备',
          subtitle: '2台设备',
          icon: 'level',
          iconColor: '#3c9cff',
          bgColor: '#e7f1ff',
          url: '/pages/devices/index'
        },
        // {
        //   title: '使用记录',
        //   subtitle: '查看历史记录',
        //   icon: 'clock',
        //   iconColor: '#19be6b',
        //   bgColor: '#e5f7ef',
        //   url: '/pages/records/index'
        // },
        {
          title: '订单记录',
          subtitle: '管理资金',
          icon: 'order',
          iconColor: '#ff9900',
          bgColor: '#fdf6ec',
          url: '/pages/order/index'
        },
        {
          title: '优惠券',
          subtitle: '3张可用',
          icon: 'coupon',
          iconColor: '#fa3534',
          bgColor: '#fef0f0',
          url: '/pages/coupons/index'
        },
		{
		  title: '我的推广码',
		  subtitle: '共享赚佣金',
		  icon: 'coupon',
		  iconColor: '#fa3534',
		  bgColor: '#fef0f0',
		  url: '/pages/me/promotion'
		}
      ],
      otherFunctionList: [
        {
          title: '帮助中心',
          icon: 'question-circle',
          iconColor: '#9a6ee8',
          bgColor: '#f3effa',
          url: '/pages/help/index'
        },
        {
          title: '设置',
          icon: 'setting',
          iconColor: '#909399',
          bgColor: '#f3f4f6',
          url: '/pages/set/index'
        }
      ]
    }
  },
  onLoad() {
    this.userInfo=getStorageSync('userInfo');
  },
  methods: {
    goToRecharge() {
      uni.navigateTo({
        url: '/pages/recharge/recharge-redesign'
      })
    },
    handleFunctionClick(item) {
      uni.navigateTo({
        url: item.url
      })
    },
    handleOtherFunctionClick(item) {
      uni.navigateTo({
        url: item.url
      })
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
  background-color: #f5f5f5;
  padding-bottom: 120rpx;
}

.user-info-card {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 60rpx 30rpx 60rpx;
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

.user-info-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.avatar-container {
  position: relative;
}

.user-details {
  margin-left: 30rpx;
}

.username {
  font-size: 34rpx;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 10rpx;
}

.user-id {
  display: flex;
  align-items: center;
  background-color: #f8f8f8;
  border-radius: 50rpx;
  padding: 6rpx 20rpx;
  margin-top: 10px;
}

.id-text {
  font-size: 22rpx;
  color: #333333;
  margin-left: 10rpx;
}

.account-card {
  margin: -30rpx 30rpx 0;
  background-color: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20rpx);
  border-radius: 24rpx;
  padding: 24rpx 30rpx;
  box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 10;
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
}

.account-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16rpx;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 10rpx;
  .text{
	  margin-left: 12rpx; font-weight: 500; color: #ffffff;
  }
}

.account-label {
  font-size: 26rpx;
  color: #666666;
  display: block;
}
.account-money{
	width: 70%;
}
.account-balance {
  font-size: 48rpx;
  font-weight: bold;
  color: #3c9cff;
  display: block;
  margin-top: 6rpx;
}

.expense-info {
  display: flex;
  justify-content: space-between;
  font-size: 22rpx;
  color: #999999;
}

.function-list, .other-functions {
  margin: 24rpx 30rpx;
  border-radius: 24rpx;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.cell-icon {
  width: 72rpx;
  height: 72rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16rpx;
  margin-right: 20rpx;
}
</style>