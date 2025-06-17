<template>
  <view class="container">

    <!-- 标签页 -->
    <view class="tabs-container">
      <u-tabs
        :list="tabList"
        :current="currentTab"
        @change="tabChange"
        :activeStyle="{
          color: '#3b82f6',
          fontWeight: 'bold'
        }"
        :inactiveStyle="{
          color: '#909399',
          fontWeight: 'normal'
        }"
        itemStyle="padding-left: 15px; padding-right: 15px; height: 90rpx;"
        lineColor="#3b82f6"
        :animation="true"
      ></u-tabs>
    </view>

    <!-- 优惠券列表 -->
    <view class="coupon-list">
      <!-- 可使用优惠券 -->
      <template v-if="currentTab === 0">
        <view 
          class="coupon-item" 
          v-for="(item, index) in availableCoupons" 
          :key="index"
          @click="useCoupon(item)"
        >
          <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
            <text class="coupon-amount">¥{{ item.amount }}</text>
            <text class="coupon-condition">{{ item.condition }}</text>
          </view>
          <view class="coupon-right">
            <view class="coupon-info">
              <view>
                <text class="coupon-name">{{ item.name }}</text>
                <text class="coupon-desc">{{ item.description }}</text>
              </view>
              <u-button 
                :type="item.buttonType" 
                size="mini" 
                shape="circle"
                @click.stop="useCoupon(item)"
              >立即使用</u-button>
            </view>
            <view class="coupon-footer">
              <text class="coupon-validity">有效期至: {{ item.validity }}</text>
              <text class="coupon-source">{{ item.source }}</text>
            </view>
          </view>
        </view>
      </template>

      <!-- 已使用优惠券 -->
      <template v-if="currentTab === 1">
        <view 
          class="coupon-item used" 
          v-for="(item, index) in usedCoupons" 
          :key="index"
        >
          <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
            <text class="coupon-amount">¥{{ item.amount }}</text>
            <text class="coupon-condition">{{ item.condition }}</text>
          </view>
          <view class="coupon-right">
            <view class="coupon-info">
              <view>
                <text class="coupon-name">{{ item.name }}</text>
                <text class="coupon-desc">{{ item.description }}</text>
              </view>
              <view class="coupon-used-tag">已使用</view>
            </view>
            <view class="coupon-footer">
              <text class="coupon-validity">使用时间: {{ item.usedTime }}</text>
              <text class="coupon-source">{{ item.source }}</text>
            </view>
          </view>
        </view>
      </template>

      <!-- 已过期优惠券 -->
      <template v-if="currentTab === 2">
        <view 
          class="coupon-item expired" 
          v-for="(item, index) in expiredCoupons" 
          :key="index"
        >
          <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
            <text class="coupon-amount">¥{{ item.amount }}</text>
            <text class="coupon-condition">{{ item.condition }}</text>
          </view>
          <view class="coupon-right">
            <view class="coupon-info">
              <view>
                <text class="coupon-name">{{ item.name }}</text>
                <text class="coupon-desc">{{ item.description }}</text>
              </view>
              <view class="coupon-expired-tag">已过期</view>
            </view>
            <view class="coupon-footer">
              <text class="coupon-validity">过期时间: {{ item.expiredTime }}</text>
              <text class="coupon-source">{{ item.source }}</text>
            </view>
          </view>
        </view>
      </template>

      <!-- 无优惠券提示 -->
      <view v-if="getCurrentCoupons.length === 0" class="empty-state">
        <u-empty
          mode="coupon"
          icon="http://cdn.uviewui.com/uview/empty/coupon.png"
          :text="emptyTips[currentTab]"
          textColor="#909399"
          iconSize="240"
        ></u-empty>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '我的优惠券',
      currentTab: 0,
      tabList: [
        { name: '可使用(3)' },
        { name: '已使用(2)' },
        { name: '已过期(1)' }
      ],
      emptyTips: [
        '暂无可用优惠券',
        '暂无已使用优惠券',
        '暂无已过期优惠券'
      ],
      availableCoupons: [
        {
          id: 1,
          name: '新用户专享券',
          description: '首次使用净水机可用',
          amount: '5',
          condition: '无门槛',
          validity: '2024-01-31',
          source: '森泽净水',
          bgColor: '#f43f5e',
          buttonType: 'error'
        },
        {
          id: 2,
          name: '周末特惠券',
          description: '周末使用净水机可用',
          amount: '3',
          condition: '满10元可用',
          validity: '2024-01-15',
          source: '森泽净水',
          bgColor: '#3b82f6',
          buttonType: 'primary'
        },
        {
          id: 3,
          name: '会员专享券',
          description: '会员每月赠送',
          amount: '2',
          condition: '无门槛',
          validity: '2024-01-20',
          source: '森泽净水',
          bgColor: '#10b981',
          buttonType: 'success'
        }
      ],
      usedCoupons: [
        {
          id: 4,
          name: '首次使用优惠券',
          description: '首次使用净水机可用',
          amount: '10',
          condition: '无门槛',
          usedTime: '2023-12-15',
          source: '森泽净水',
          bgColor: '#9ca3af'
        },
        {
          id: 5,
          name: '节日特惠券',
          description: '国庆节期间可用',
          amount: '5',
          condition: '满20元可用',
          usedTime: '2023-10-03',
          source: '森泽净水',
          bgColor: '#9ca3af'
        }
      ],
      expiredCoupons: [
        {
          id: 6,
          name: '618活动券',
          description: '618活动期间可用',
          amount: '8',
          condition: '满30元可用',
          expiredTime: '2023-06-20',
          source: '森泽净水',
          bgColor: '#9ca3af'
        }
      ]
    }
  },
  computed: {
    getCurrentCoupons() {
      if (this.currentTab === 0) return this.availableCoupons
      if (this.currentTab === 1) return this.usedCoupons
      if (this.currentTab === 2) return this.expiredCoupons
      return []
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
    tabChange(index) {
      this.currentTab = index
    },
    useCoupon(coupon) {
      uni.showToast({
        title: `使用优惠券: ${coupon.name}`,
        icon: 'none'
      })
    }
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

.tabs-container {
  background-color: #ffffff;
  margin-bottom: 20rpx;
}

.coupon-list {
  padding: 30rpx;
  padding-bottom: 50rpx;
}

.coupon-item {
  display: flex;
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  margin-bottom: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    left: -8rpx;
    top: 50%;
    width: 16rpx;
    height: 16rpx;
    background-color: #f5f7fa;
    border-radius: 50%;
    transform: translateY(-50%);
    z-index: 10;
  }
  
  &::after {
    content: '';
    position: absolute;
    right: -8rpx;
    top: 50%;
    width: 16rpx;
    height: 16rpx;
    background-color: #f5f7fa;
    border-radius: 50%;
    transform: translateY(-50%);
    z-index: 10;
  }
}

.coupon-left {
  width: 25%;
  padding: 30rpx 20rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    right: 0;
    top: 10%;
    bottom: 10%;
    border-right: 2rpx dashed rgba(0, 0, 0, 0.1);
  }
}

.coupon-amount {
  font-size: 48rpx;
  font-weight: bold;
  color: #ffffff;
}

.coupon-condition {
  font-size: 22rpx;
  color: rgba(255, 255, 255, 0.9);
  margin-top: 8rpx;
}

.coupon-right {
  flex: 1;
  padding: 30rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.coupon-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  .u-button{ width: 100px;padding: 10px;}
}

.coupon-name {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  display: block;
}

.coupon-desc {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.coupon-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 20rpx;
  font-size: 22rpx;
  color: #909399;
}

.coupon-used-tag, .coupon-expired-tag {
  padding: 6rpx 16rpx;
  border-radius: 30rpx;
  font-size: 22rpx;
  color: #ffffff;
}

.coupon-used-tag {
  background-color: #9ca3af;
}

.coupon-expired-tag {
  background-color: #9ca3af;
}

.used, .expired {
  filter: grayscale(1);
  opacity: 0.7;
}

.empty-state {
  padding: 100rpx 0;
}
</style>