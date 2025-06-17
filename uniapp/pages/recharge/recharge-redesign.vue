<template>
  <view class="container">
    <!-- 顶部状态栏 -->
    <u-navbar
      title="账户充值"
      :autoBack="true"
      bgColor="#3b82f6"
      titleStyle="color: #ffffff; font-weight: 500"
      :fixed="true"
      :immersive="true"
    ></u-navbar>
    
    <!-- 余额卡片 -->
    <view class="balance-card">
      <view class="card-content">
        <view class="balance-info">
          <text class="balance-label">当前余额</text>
          <view class="balance-amount-row">
            <text class="currency">¥</text>
            <text class="balance-amount">{{ accountInfo.balance }}</text>
          </view>
        </view>
        <view class="card-decoration">
          <u-icon name="wallet" size="80" color="rgba(255,255,255,0.1)"></u-icon>
        </view>
      </view>
      <view class="card-footer">
        <view class="expense-item">
          <text class="expense-label">今日支出</text>
          <text class="expense-value">¥{{ accountInfo.todayExpense }}</text>
        </view>
        <view class="expense-divider"></view>
        <view class="expense-item">
          <text class="expense-label">本月支出</text>
          <text class="expense-value">¥{{ accountInfo.monthExpense }}</text>
        </view>
      </view>
    </view>
    
    <!-- 充值金额选择 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">选择充值金额</text>
        <text class="section-subtitle">选择或输入您想要充值的金额</text>
      </view>
      
      <view class="amount-grid">
        <view 
          class="amount-item" 
          v-for="(item, index) in amountOptions" 
          :key="index"
          :class="{active: selectedAmount === item.value}"
          @click="selectAmount(item.value)"
        >
          <text class="amount-value">¥{{ item.value }}</text>
          <text class="amount-desc">{{ item.desc }}</text>
        </view>
      </view>
      
      <view class="custom-amount-input">
        <view class="input-prefix">
          <text class="currency-symbol">¥</text>
        </view>
        <u-input
          v-model="customAmount"
          type="number"
          placeholder="自定义充值金额"
          :border="false"
          :clearable="true"
          @input="onCustomAmountInput"
        ></u-input>
      </view>
      <text class="input-tip">单次充值金额不低于1元，不超过1000元</text>
    </view>
    
    <!-- 支付方式选择 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">选择支付方式</text>
      </view>
      
      <view class="payment-options">
        <view 
          class="payment-option" 
          :class="{active: paymentMethod === 'wechat'}"
          @click="selectPayment('wechat')"
        >
          <view class="payment-icon wechat-icon">
            <u-icon name="weixin-fill" size="24" color="#07c160"></u-icon>
          </view>
          <text class="payment-name">微信支付</text>
        </view>
        
        <view 
          class="payment-option" 
          :class="{active: paymentMethod === 'alipay'}"
          @click="selectPayment('alipay')"
        >
          <view class="payment-icon alipay-icon">
            <u-icon name="zhifubao-circle-fill" size="24" color="#1677ff"></u-icon>
          </view>
          <text class="payment-name">支付宝</text>
        </view>
        
        <view 
          class="payment-option" 
          :class="{active: paymentMethod === 'balance'}"
          @click="selectPayment('balance')"
        >
          <view class="payment-icon balance-icon">
            <u-icon name="rmb-circle-fill" size="24" color="#f59e0b"></u-icon>
          </view>
          <text class="payment-name">余额支付</text>
        </view>
      </view>
    </view>
    
    <!-- 优惠券选择 -->
    <view class="coupon-selector" @click="showCouponList">
      <view class="selector-left">
        <u-icon name="coupon" size="20" color="#3b82f6"></u-icon>
        <text class="selector-label">优惠券</text>
      </view>
      <view class="selector-right">
        <text class="coupon-info">{{ selectedCoupon ? selectedCoupon.name : '无可用优惠券' }}</text>
        <u-icon name="arrow-right" size="16" color="#c0c4cc"></u-icon>
      </view>
    </view>
    
    <!-- 充值详情 -->
    <view class="recharge-summary">
      <view class="summary-item">
        <text class="summary-label">充值金额</text>
        <text class="summary-value">¥{{ finalAmount }}</text>
      </view>
      <view class="summary-item" v-if="selectedCoupon">
        <text class="summary-label">优惠券</text>
        <text class="summary-value">-¥{{ selectedCoupon.amount }}</text>
      </view>
      <view class="summary-item total">
        <text class="summary-label">实付金额</text>
        <text class="summary-value">¥{{ actualAmount }}</text>
      </view>
    </view>
    
    <!-- 充值按钮 -->
    <view class="recharge-btn-container">
      <u-button 
        type="primary" 
        shape="circle"
        :customStyle="{background: 'linear-gradient(to right, #3b82f6, #1d4ed8)'}"
        @click="confirmRecharge"
      >确认充值</u-button>
      <text class="agreement-text">点击充值即表示您同意<text class="agreement-link" @click="viewAgreement">《充值服务协议》</text></text>
    </view>
    
    <!-- 优惠券选择弹窗 -->
    <u-popup
      :show="showCoupons"
      @close="closeCouponList"
      mode="bottom"
      borderRadius="16"
      safeAreaInsetBottom
    >
      <view class="coupon-popup">
        <view class="popup-header">
          <text class="popup-title">选择优惠券</text>
          <u-icon name="close" size="20" color="#909399" @click="closeCouponList"></u-icon>
        </view>
        
        <scroll-view scroll-y class="coupon-list">
          <view 
            class="coupon-item" 
            v-for="(item, index) in availableCoupons" 
            :key="index"
            :class="{selected: selectedCouponId === item.id}"
            @click="selectCoupon(item)"
          >
            <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
              <text class="coupon-amount">¥{{ item.amount }}</text>
              <text class="coupon-condition">{{ item.condition }}</text>
            </view>
            <view class="coupon-right">
              <view class="coupon-info">
                <text class="coupon-name">{{ item.name }}</text>
                <text class="coupon-desc">{{ item.description }}</text>
              </view>
              <view class="coupon-validity">
                <text>有效期至: {{ item.validity }}</text>
              </view>
            </view>
            <view class="coupon-check">
              <u-icon v-if="selectedCouponId === item.id" name="checkmark-circle" size="20" color="#3b82f6"></u-icon>
              <view v-else class="check-circle"></view>
            </view>
          </view>
          
          <view class="no-coupon-item" @click="selectCoupon(null)">
            <text class="no-coupon-text">不使用优惠券</text>
            <view class="coupon-check">
              <u-icon v-if="selectedCouponId === null" name="checkmark-circle" size="20" color="#3b82f6"></u-icon>
              <view v-else class="check-circle"></view>
            </view>
          </view>
        </scroll-view>
        
        <view class="popup-footer">
          <u-button 
            type="primary" 
            shape="circle"
            @click="confirmCouponSelection"
          >确认</u-button>
        </view>
      </view>
    </u-popup>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '账户充值',
      accountInfo: {
        balance: '50.00',
        todayExpense: '0.00',
        monthExpense: '10.00'
      },
      amountOptions: [
        { value: '10', desc: '充值10元' },
        { value: '20', desc: '充值20元' },
        { value: '50', desc: '充值50元' },
        { value: '100', desc: '充值100元' },
        { value: '200', desc: '充值200元' },
        { value: '500', desc: '充值500元' }
      ],
      selectedAmount: '50',
      customAmount: '',
      paymentMethod: 'wechat',
      showCoupons: false,
      selectedCouponId: null,
      selectedCoupon: null,
      availableCoupons: [
        {
          id: 1,
          name: '新用户专享券',
          description: '首次充值可用',
          amount: '5',
          condition: '满50元可用',
          validity: '2024-01-31',
          bgColor: '#f43f5e'
        },
        {
          id: 2,
          name: '周末特惠券',
          description: '周末充值可用',
          amount: '3',
          condition: '满30元可用',
          validity: '2024-01-15',
          bgColor: '#3b82f6'
        }
      ]
    }
  },
  computed: {
    finalAmount() {
      return this.selectedAmount === '0' ? (this.customAmount || '0') : this.selectedAmount
    },
    actualAmount() {
      const amount = parseFloat(this.finalAmount)
      const discount = this.selectedCoupon ? parseFloat(this.selectedCoupon.amount) : 0
      return Math.max(amount - discount, 0).toFixed(2)
    }
  },
  methods: {
    selectAmount(amount) {
      this.selectedAmount = amount
      if (amount !== '0') {
        this.customAmount = ''
      }
    },
    onCustomAmountInput(value) {
      if (value) {
        this.selectedAmount = '0'
      }
    },
    selectPayment(method) {
      this.paymentMethod = method
    },
    showCouponList() {
      this.showCoupons = true
    },
    closeCouponList() {
      this.showCoupons = false
    },
    selectCoupon(coupon) {
      this.selectedCouponId = coupon ? coupon.id : null
    },
    confirmCouponSelection() {
      this.selectedCoupon = this.availableCoupons.find(c => c.id === this.selectedCouponId) || null
      this.closeCouponList()
    },
    confirmRecharge() {
      const amount = this.finalAmount
      if (amount === '0' || !amount) {
        uni.showToast({
          title: '请选择或输入充值金额',
          icon: 'none'
        })
        return
      }
      
      uni.showLoading({
        title: '处理中...'
      })
      
      // 模拟充值过程
      setTimeout(() => {
        uni.hideLoading()
        uni.showToast({
          title: `充值 ¥${this.actualAmount} 成功`,
          icon: 'success'
        })
        
        // 模拟余额更新
        this.accountInfo.balance = (parseFloat(this.accountInfo.balance) + parseFloat(this.actualAmount)).toFixed(2)
        
        // 延迟返回上一页
        setTimeout(() => {
          uni.navigateBack()
        }, 1500)
      }, 1500)
    },
    viewAgreement() {
      uni.showToast({
        title: '查看充值服务协议',
        icon: 'none'
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f8f9fc;
  padding-bottom: 50rpx;
}

.balance-card {
  margin: 100rpx 30rpx 30rpx;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  border-radius: 20rpx;
  overflow: hidden;
  box-shadow: 0 8rpx 30rpx rgba(59, 130, 246, 0.3);
}

.card-content {
  padding: 30rpx;
  position: relative;
  overflow: hidden;
}

.balance-info {
  position: relative;
  z-index: 2;
}

.balance-label {
  font-size: 28rpx;
  color: rgba(255, 255, 255, 0.8);
  display: block;
}

.balance-amount-row {
  display: flex;
  align-items: baseline;
  margin-top: 10rpx;
}

.currency {
  font-size: 36rpx;
  color: #ffffff;
  font-weight: 500;
}

.balance-amount {
  font-size: 60rpx;
  font-weight: bold;
  color: #ffffff;
  margin-left: 8rpx;
}

.card-decoration {
  position: absolute;
  right: 20rpx;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.5;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  padding: 20rpx 30rpx;
  background-color: rgba(0, 0, 0, 0.1);
}

.expense-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.expense-label {
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 6rpx;
}

.expense-value {
  font-size: 28rpx;
  color: #ffffff;
  font-weight: 500;
}

.expense-divider {
  width: 2rpx;
  height: 40rpx;
  background-color: rgba(255, 255, 255, 0.2);
}

.section {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.section-header {
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333333;
  display: block;
}

.section-subtitle {
  font-size: 24rpx;
  color: #909399;
  margin-top: 6rpx;
  display: block;
}

.amount-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20rpx;
  margin-bottom: 30rpx;
}

.amount-item {
  background-color: #f5f7fa;
  border-radius: 12rpx;
  padding: 20rpx 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
  
  &.active {
    background-color: #e7f1ff;
    border: 2rpx solid #3b82f6;
    
    .amount-value {
      color: #3b82f6;
    }
  }
}

.amount-value {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
}

.amount-desc {
  font-size: 22rpx;
  color: #909399;
  margin-top: 8rpx;
}

.custom-amount-input {
  display: flex;
  align-items: center;
  background-color: #f5f7fa;
  border-radius: 12rpx;
  padding: 0 20rpx;
  margin-bottom: 10rpx;
}

.input-prefix {
  margin-right: 10rpx;
}

.currency-symbol {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
}

.input-tip {
  font-size: 22rpx;
  color: #909399;
  padding-left: 10rpx;
}

.payment-options {
  display: flex;
  justify-content: space-between;
}

.payment-option {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx;
  border-radius: 12rpx;
  transition: all 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
  
  &.active {
    background-color: #e7f1ff;
  }
}

.payment-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10rpx;
}

.wechat-icon {
  background-color: #e7f8ef;
}

.alipay-icon {
  background-color: #e7f1ff;
}

.balance-icon {
  background-color: #fdf6ec;
}

.payment-name {
  font-size: 24rpx;
  color: #606266;
}

.coupon-selector {
  margin: 0 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.selector-left {
  display: flex;
  align-items: center;
}

.selector-label {
  font-size: 28rpx;
  color: #333333;
  margin-left: 10rpx;
}

.selector-right {
  display: flex;
  align-items: center;
}

.coupon-info {
  font-size: 26rpx;
  color: #909399;
  margin-right: 10rpx;
}

.recharge-summary {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20rpx;
  
  &:last-child {
    margin-bottom: 0;
  }
  
  &.total {
    padding-top: 20rpx;
    border-top: 2rpx dashed #e5e7eb;
    
    .summary-label, .summary-value {
      font-weight: bold;
      color: #333333;
    }
    
    .summary-value {
      color: #f43f5e;
    }
  }
}

.summary-label {
  font-size: 28rpx;
  color: #606266;
}

.summary-value {
  font-size: 28rpx;
  color: #333333;
}

.recharge-btn-container {
  margin: 50rpx 30rpx;
}

.agreement-text {
  font-size: 24rpx;
  color: #909399;
  text-align: center;
  margin-top: 20rpx;
  display: block;
}

.agreement-link {
  color: #3b82f6;
}

.coupon-popup {
  background-color: #ffffff;
  border-radius: 16rpx 16rpx 0 0;
  overflow: hidden;
  height: 800rpx;
  display: flex;
  flex-direction: column;
}

.popup-header {
  padding: 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2rpx solid #f2f3f5;
}

.popup-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333333;
}

.coupon-list {
  flex: 1;
  padding: 30rpx;
}

.coupon-item {
  display: flex;
  background-color: #ffffff;
  border: 2rpx solid #e5e7eb;
  border-radius: 16rpx;
  overflow: hidden;
  margin-bottom: 20rpx;
  position: relative;
  transition: all 0.2s;
  
  &.selected {
    border-color: #3b82f6;
    background-color: #e7f1ff;
  }
}

.coupon-left {
  width: 200rpx;
  padding: 30rpx 20rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.coupon-amount {
  font-size: 40rpx;
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
  padding: 20rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.coupon-name {
  font-size: 28rpx;
  font-weight: bold;
  color: #333333;
  display: block;
}

.coupon-desc {
  font-size: 24rpx;
  color: #909399;
  margin-top: 6rpx;
  display: block;
}

.coupon-validity {
  font-size: 22rpx;
  color: #909399;
  margin-top: 10rpx;
}

.coupon-check {
  position: absolute;
  right: 20rpx;
  top: 20rpx;
}

.check-circle {
  width: 40rpx;
  height: 40rpx;
  border-radius: 50%;
  border: 2rpx solid #e5e7eb;
}

.no-coupon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx;
  background-color: #ffffff;
  border: 2rpx solid #e5e7eb;
  border-radius: 16rpx;
  margin-bottom: 20rpx;
  
  &.selected {
    border-color: #3b82f6;
    background-color: #e7f1ff;
  }
}

.no-coupon-text {
  font-size: 28rpx;
  color: #333333;
}

.popup-footer {
  padding: 20rpx 30rpx 50rpx;
  border-top: 2rpx solid #f2f3f5;
}
</style>