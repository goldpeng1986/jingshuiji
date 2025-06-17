<template>
  <view class="container">
    <!-- 顶部背景与用户信息 -->
    <view class="header-section">
      <view class="header-bg">
        <view class="wave-animation"></view>
      </view>
      <view class="user-profile">
        <view class="avatar-wrapper">
          <u-avatar
            :src="userInfo.avatar"
            size="140"
            :customStyle="{borderRadius: '28rpx', border: '4rpx solid rgba(255,255,255,0.8)'}"
          ></u-avatar>
          <view class="avatar-badge">
            <u-icon name="level" size="20" color="#ffffff"></u-icon>
          </view>
        </view>
        <view class="user-info">
          <view class="name-row">
            <text class="username">{{ userInfo.name }}</text>
            <u-tag
              text="普通会员"
              type="warning"
              size="mini"
              :customStyle="{marginLeft: '16rpx', borderRadius: '100rpx'}"
            ></u-tag>
          </view>
          <view class="user-id-badge">
            <u-icon name="account-fill" size="22" color="#3c9cff"></u-icon>
            <text class="id-text">ID: {{ userInfo.id }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 账户卡片 -->
    <view class="account-section">
      <view class="account-card">
        <view class="card-header">
          <view class="card-title">
            <u-icon name="rmb-circle-fill" size="32" color="#3c9cff"></u-icon>
            <text class="title-text">我的钱包</text>
          </view>
          <view class="card-action" @click="goToRecharge">
            <text class="action-text">充值</text>
            <u-icon name="arrow-right" size="24" color="#909399"></u-icon>
          </view>
        </view>
        <view class="balance-display">
          <text class="balance-label">账户余额</text>
          <text class="balance-amount">¥{{ accountInfo.balance }}</text>
        </view>
        <view class="expense-stats">
          <view class="stat-item">
            <text class="stat-value">¥{{ accountInfo.todayExpense }}</text>
            <text class="stat-label">今日支出</text>
          </view>
          <view class="stat-divider"></view>
          <view class="stat-item">
            <text class="stat-value">¥{{ accountInfo.monthExpense }}</text>
            <text class="stat-label">本月支出</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 功能区域 -->
    <view class="features-section">
      <view class="section-title">我的服务</view>
      <view class="feature-grid">
        <view 
          class="feature-item" 
          v-for="(item, index) in functionList" 
          :key="index"
          @click="handleFunctionClick(item)"
        >
          <view class="feature-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="36"></u-icon>
          </view>
          <text class="feature-name">{{ item.title }}</text>
          <text class="feature-desc">{{ item.subtitle }}</text>
        </view>
      </view>
    </view>

    <!-- 其他功能列表 -->
    <view class="other-section">
      <view class="section-title">更多服务</view>
      <view class="other-list">
        <view 
          class="other-item" 
          v-for="(item, index) in otherFunctionList" 
          :key="index"
          @click="handleOtherFunctionClick(item)"
        >
          <view class="item-left">
            <view class="item-icon" :style="{backgroundColor: item.bgColor}">
              <u-icon :name="item.icon" :color="item.iconColor" size="32"></u-icon>
            </view>
            <text class="item-name">{{ item.title }}</text>
          </view>
          <u-icon name="arrow-right" size="28" color="#c0c4cc"></u-icon>
        </view>
      </view>
    </view>

    <!-- 底部导航栏 -->
    <u-tabbar
      :value="3"
      :fixed="true"
      :safeAreaInsetBottom="true"
      :border="true"
      @change="tabbarChange"
    >
      <u-tabbar-item
        text="首页"
        icon="home"
        @click="goToPage('/pages/index/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="商城"
        icon="shopping-cart"
        @click="goToPage('/pages/shop/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="订单"
        icon="file-text"
        @click="goToPage('/pages/order/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="我的"
        icon="account"
        @click="goToPage('/pages/me/index')"
      ></u-tabbar-item>
    </u-tabbar>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '个人中心',
      userInfo: {
        name: '张三',
        id: '888888',
        avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-1.2.1&auto=format&fit=crop&w=80&h=80'
      },
      accountInfo: {
        balance: '50.00',
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
        {
          title: '使用记录',
          subtitle: '查看历史记录',
          icon: 'clock',
          iconColor: '#19be6b',
          bgColor: '#e5f7ef',
          url: '/pages/records/index'
        },
        {
          title: '我的钱包',
          subtitle: '管理资金',
          icon: 'rmb-circle',
          iconColor: '#ff9900',
          bgColor: '#fdf6ec',
          url: '/pages/wallet/index'
        },
        {
          title: '优惠券',
          subtitle: '3张可用',
          icon: 'coupon',
          iconColor: '#fa3534',
          bgColor: '#fef0f0',
          url: '/pages/coupons/index'
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
          url: '/pages/settings/index'
        }
      ]
    }
  },
  methods: {
    goToRecharge() {
      uni.navigateTo({
        url: '/pages/recharge/index'
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
  background-color: #f8f9fc;
  padding-bottom: 120rpx;
}

/* 顶部区域样式 */
.header-section {
  position: relative;
  height: 360rpx;
  overflow: hidden;
}

.header-bg {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #4a7aff 0%, #1d4ed8 100%);
  z-index: 1;
}

.wave-animation {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 120rpx;
  background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23f8f9fc" fill-opacity="1" d="M0,224L60,213.3C120,203,240,181,360,181.3C480,181,600,203,720,213.3C840,224,960,224,1080,208C1200,192,1320,160,1380,144L1440,128L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path></svg>');
  background-size: cover;
  background-repeat: no-repeat;
  z-index: 2;
}

.user-profile {
  position: relative;
  z-index: 3;
  display: flex;
  align-items: center;
  padding: 60rpx 40rpx 0;
}

.avatar-wrapper {
  position: relative;
}

.avatar-badge {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 40rpx;
  height: 40rpx;
  border-radius: 50%;
  background-color: #ff9900;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4rpx solid #ffffff;
}

.user-info {
  margin-left: 30rpx;
  flex: 1;
}

.name-row {
  display: flex;
  align-items: center;
  margin-bottom: 12rpx;
}

.username {
  font-size: 36rpx;
  font-weight: bold;
  color: #ffffff;
}

.user-id-badge {
  display: flex;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 100rpx;
  padding: 6rpx 20rpx;
  width: fit-content;
}

.id-text {
  font-size: 24rpx;
  color: #ffffff;
  margin-left: 8rpx;
}

/* 账户卡片样式 */
.account-section {
  padding: 0 30rpx;
  margin-top: -60rpx;
  position: relative;
  z-index: 10;
}

.account-card {
  background-color: #ffffff;
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 8rpx 30rpx rgba(0, 0, 0, 0.05);
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
}

.card-title {
  display: flex;
  align-items: center;
}

.title-text {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  margin-left: 12rpx;
}

.card-action {
  display: flex;
  align-items: center;
  background-color: #f5f7fa;
  border-radius: 100rpx;
  padding: 8rpx 20rpx;
}

.action-text {
  font-size: 26rpx;
  color: #606266;
  margin-right: 6rpx;
}

.balance-display {
  margin-bottom: 30rpx;
}

.balance-label {
  font-size: 26rpx;
  color: #909399;
  margin-bottom: 8rpx;
  display: block;
}

.balance-amount {
  font-size: 56rpx;
  font-weight: bold;
  color: #3c9cff;
  display: block;
}

.expense-stats {
  display: flex;
  align-items: center;
  background-color: #f5f7fa;
  border-radius: 16rpx;
  padding: 20rpx;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-size: 30rpx;
  font-weight: bold;
  color: #606266;
  margin-bottom: 6rpx;
}

.stat-label {
  font-size: 24rpx;
  color: #909399;
}

.stat-divider {
  width: 2rpx;
  height: 50rpx;
  background-color: #e4e7ed;
}

/* 功能区域样式 */
.features-section, .other-section {
  padding: 30rpx;
  margin-top: 30rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  margin-bottom: 24rpx;
  position: relative;
  padding-left: 20rpx;
  
  &::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 8rpx;
    height: 32rpx;
    background: linear-gradient(to bottom, #3c9cff, #5cadff);
    border-radius: 4rpx;
  }
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24rpx;
}

.feature-item {
  background-color: #ffffff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.03);
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.97);
  }
}

.feature-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16rpx;
}

.feature-name {
  font-size: 28rpx;
  font-weight: bold;
  color: #333333;
  margin-bottom: 8rpx;
  display: block;
}

.feature-desc {
  font-size: 24rpx;
  color: #909399;
  display: block;
}

/* 其他功能列表样式 */
.other-list {
  background-color: #ffffff;
  border-radius: 20rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.03);
}

.other-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx 24rpx;
  border-bottom: 2rpx solid #f5f7fa;
  
  &:last-child {
    border-bottom: none;
  }
  
  &:active {
    background-color: #f5f7fa;
  }
}

.item-left {
  display: flex;
  align-items: center;
}

.item-icon {
  width: 64rpx;
  height: 64rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20rpx;
}

.item-name {
  font-size: 28rpx;
  color: #333333;
}
</style>