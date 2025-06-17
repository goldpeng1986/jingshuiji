<template>
  <view class="container">
    <!-- 推广信息卡片 -->
    <view class="promotion-card">
      <view class="promotion-header">
        <view class="promotion-title">
          <text class="title-text">我的推广码</text>
          <text class="subtitle-text">邀请好友注册，共享收益</text>
        </view>
        <view class="promotion-stats">
          <view class="stat-item income">
            <text class="stat-value">{{ promotionInfo.totalIncome }}</text>
            <text class="stat-label">累计收益(元)</text>
          </view>
          <view class="stat-item users">
            <text class="stat-value">{{ promotionInfo.totalUsers }}</text>
            <text class="stat-label">推广用户(人)</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 推广码展示区 -->
    <view class="qrcode-section">
      <view class="qrcode-card">
        <view class="qrcode-container">
          <image class="qrcode-image" :src="promotionInfo.qrcode" mode="aspectFit"></image>
        </view>
        <view class="qrcode-info">
          <text class="promotion-code">推广码：{{ promotionInfo.code }}</text>
          <u-button 
            type="primary" 
            text="复制推广码" 
            shape="circle"
            :customStyle="{width: '240rpx', height: '80rpx', background: 'linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%)', boxShadow: '0 4rpx 12rpx rgba(255, 71, 87, 0.2)', border: 'none'}"
            @click="copyPromotionCode"
          ></u-button>
        </view>
      </view>
    </view>

    <!-- 推广规则 -->
    <view class="rules-section">
      <view class="section-title">
        <u-icon name="info-circle" size="32" color="#ff6b6b"></u-icon>
        <text>推广规则</text>
      </view>
      <view class="rules-content">
        <view class="rule-item" v-for="(rule, index) in promotionRules" :key="index">
          <text class="rule-number">{{ index + 1 }}</text>
          <text class="rule-text">{{ rule }}</text>
        </view>
      </view>
    </view>

    <!-- 收益记录 -->
    <view class="income-section">
      <view class="section-title">
        <u-icon name="rmb-circle" size="32" color="#ff6b6b"></u-icon>
        <text>收益记录</text>
      </view>
      <view class="income-list">
        <view class="income-item" v-for="(item, index) in incomeRecords" :key="index">
          <view class="income-info">
            <text class="income-type">{{ item.type }}</text>
            <text class="income-time">{{ item.time }}</text>
          </view>
          <text class="income-amount">+{{ item.amount }}元</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      promotionInfo: {
        code: 'ABC123',
        qrcode: 'https://example.com/qrcode.png',
        totalIncome: '128.50',
        totalUsers: '12'
      },
      promotionRules: [
        '邀请好友注册并完成实名认证，即可获得推广奖励',
        '每成功邀请1位好友，可获得5元奖励',
        '好友首次充值满50元，额外获得10元奖励',
        '推广收益可直接提现到微信或支付宝',
        '推广奖励将在好友完成相应操作后24小时内发放'
      ],
      incomeRecords: [
        {
          type: '推广奖励',
          time: '2024-03-20 15:30',
          amount: '5.00'
        },
        {
          type: '首次充值奖励',
          time: '2024-03-19 10:15',
          amount: '10.00'
        },
        {
          type: '推广奖励',
          time: '2024-03-18 16:45',
          amount: '5.00'
        }
      ]
    }
  },
  methods: {
    copyPromotionCode() {
      uni.setClipboardData({
        data: this.promotionInfo.code,
        success: () => {
          uni.$u.toast('推广码已复制');
        }
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f8f9fa;
  padding-bottom: 120rpx;
}

.promotion-card {
  background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
  padding: 40rpx 30rpx;
  margin-bottom: 20rpx;
  color: #ffffff;
}

.promotion-header {
  .promotion-title {
    margin-bottom: 30rpx;
    
    .title-text {
      font-size: 36rpx;
      font-weight: bold;
      color: #ffffff;
      display: block;
    }
    
    .subtitle-text {
      font-size: 26rpx;
      color: rgba(255, 255, 255, 0.9);
      margin-top: 10rpx;
      display: block;
    }
  }
}

.promotion-stats {
  display: flex;
  justify-content: space-around;
  margin-top: 20rpx;
  
  .stat-item {
    text-align: center;
    padding: 20rpx 40rpx;
    border-radius: 16rpx;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10rpx);
    
    &.income {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    }
    
    &.users {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.2) 100%);
    }
    
    .stat-value {
      font-size: 40rpx;
      font-weight: bold;
      color: #ffffff;
      display: block;
    }
    
    .stat-label {
      font-size: 24rpx;
      color: rgba(255, 255, 255, 0.9);
      margin-top: 8rpx;
      display: block;
    }
  }
}

.qrcode-section {
  padding: 0 30rpx;
  margin-bottom: 20rpx;
}

.qrcode-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 24rpx;
  padding: 40rpx;
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.08);
  
  .qrcode-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30rpx;
    
    .qrcode-image {
      width: 400rpx;
      height: 400rpx;
      border-radius: 16rpx;
      box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
    }
  }
  
  .qrcode-info {
    text-align: center;
    
    .promotion-code {
      font-size: 32rpx;
      color: #333333;
      font-weight: bold;
      display: block;
      margin-bottom: 20rpx;
    }
  }
}

.rules-section, .income-section {
  margin: 20rpx 30rpx;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.08);
}

.section-title {
  display: flex;
  align-items: center;
  margin-bottom: 30rpx;
  
  text {
    font-size: 32rpx;
    font-weight: bold;
    color: #333333;
    margin-left: 16rpx;
  }
}

.rules-content {
  .rule-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20rpx;
    padding: 16rpx;
    border-radius: 12rpx;
    background: rgba(255, 107, 107, 0.05);
    
    .rule-number {
      width: 40rpx;
      height: 40rpx;
      background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24rpx;
      color: #ffffff;
      margin-right: 20rpx;
      flex-shrink: 0;
    }
    
    .rule-text {
      font-size: 26rpx;
      color: #666666;
      line-height: 1.6;
    }
  }
}

.income-list {
  .income-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx;
    margin-bottom: 10rpx;
    border-radius: 12rpx;
    background: rgba(255, 107, 107, 0.05);
    
    &:last-child {
      margin-bottom: 0;
    }
    
    .income-info {
      .income-type {
        font-size: 28rpx;
        color: #333333;
        display: block;
      }
      
      .income-time {
        font-size: 24rpx;
        color: #999999;
        margin-top: 8rpx;
        display: block;
      }
    }
    
    .income-amount {
      font-size: 32rpx;
      color: #ff4757;
      font-weight: bold;
    }
  }
}
</style> 