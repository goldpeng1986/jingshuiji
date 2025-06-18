<template>
  <view class="container">


    <!-- 账户信息卡片 -->
    <view class="account-card">
      <view class="balance-info">
        <text class="balance-label">当前余额</text>
        <text class="balance-amount">¥{{ accountInfo.balance }}</text>
      </view>
     
    </view>

    <!-- 充值金额选择 -->
    <view class="section-title">选择充值金额</view>
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

    <!-- 自定义金额输入 -->
    <view class="custom-amount-section">
      <text class="input-label">自定义充值金额</text>
      <view class="input-container">
        <text class="currency-symbol">¥</text>
        <u-input
          v-model="customAmount"
          type="number"
          placeholder="请输入充值金额"
          :border="false"
          :clearable="true"
          @input="onCustomAmountInput"
        ></u-input>
      </view>
      <text class="input-tip">单次充值金额不低于1元，不超过1000元</text>
    </view>

    <!-- 支付方式 -->
    <view class="section-title">选择支付方式</view>
    <view class="payment-card">
      <view class="payment-item" @click="selectPayment('wechat')">
        <view class="payment-left">
          <view class="payment-icon wechat-bg">
            <u-icon name="weixin-fill" size="24" color="#07c160"></u-icon>
          </view>
          <text class="payment-name">微信支付</text>
        </view>
        <u-radio 
          :checked="paymentMethod === 'wechat'"
          activeColor="#3b82f6"
          @change="selectPayment('wechat')"
        ></u-radio>
      </view>
      <view class="payment-item" @click="selectPayment('alipay')">
        <view class="payment-left">
          <view class="payment-icon alipay-bg">
            <u-icon name="zhifubao-circle-fill" size="24" color="#1677ff"></u-icon>
          </view>
          <text class="payment-name">支付宝</text>
        </view>
        <u-radio 
          :checked="paymentMethod === 'alipay'"
          activeColor="#3b82f6"
          @change="selectPayment('alipay')"
        ></u-radio>
      </view>
    </view>

    <!-- 充值按钮 -->
    <view class="recharge-btn-container">
      <u-button 
        type="primary" 
        shape="circle"
        @click="confirmRecharge"
      >确认充值 ¥{{ finalAmount }}</u-button>
      <text class="agreement-text">点击充值即表示您同意<text class="agreement-link" @click="viewAgreement">《充值服务协议》</text></text>
    </view>

    <!-- Recharge Records Section -->
    <view class="section-title records-title">充值记录</view>

    <!-- Loading State for Records -->
    <view v-if="isLoading && records.length === 0 && !errorLoading" class="state-container">
      <u-loading-icon text="正在加载记录..." size="20"></u-loading-icon>
    </view>

    <!-- Error State for Records -->
    <view v-if="errorLoading && records.length === 0" class="state-container">
      <u-empty mode="network" text="记录加载失败">
        <u-button slot="bottom" type="primary" size="mini" @click="fetchRechargeRecords(1)" text="点我重试" :customStyle="{marginTop: '10px'}"></u-button>
      </u-empty>
    </view>

    <!-- Records List -->
    <view class="records-list" v-if="!errorLoading && records.length > 0">
      <view class="record-item" v-for="record in records" :key="record.id || record.trade_no">
        <view class="record-info">
          <text class="record-amount">充值金额: ¥{{ record.amount || '0.00' }}</text>
          <text class="record-status" :class="getRecordStatusClass(record.status_code || record.status)">{{ record.status_text || record.status || '未知' }}</text>
        </view>
        <text class="record-time">{{ record.recharge_time || record.created_at || 'N/A' }}</text>
        <text class="record-trade-no" v-if="record.trade_no">交易号: {{record.trade_no}}</text>
      </view>
    </view>

    <!-- Empty State for Records -->
    <view v-if="!isLoading && !errorLoading && records.length === 0" class="state-container">
      <u-empty mode="history" text="暂无充值记录"></u-empty>
    </view>

    <!-- Load More -->
    <u-loadmore v-if="records.length > 0 || isLoading" :status="loadMoreStatus" @loadmore="fetchRechargeRecords(currentPage + 1)" iconType="circle" class="load-more-component"/>

  </view>
</template>

<script>
import { getRechargeRecords } from '../../api/api'; // Added import
export default {
  data() {
    return {
      title: '账户充值',
      accountInfo: {
        balance: '50.00' // This might also come from an API in a real app
      },
      amountOptions: [
        { value: '10', desc: '充值10元' },
        { value: '20', desc: '充值20元' },
        { value: '50', desc: '充值50元' },
        { value: '100', desc: '充值100元' },
        { value: '200', desc: '充值200元' },
        { value: '0', desc: '自定义' }
      ],
      selectedAmount: '50',
      customAmount: '',
      paymentMethod: 'wechat',

      // New data properties for recharge records
      records: [],
      isLoading: true, // For records list
      errorLoading: false, // For records list
      currentPage: 1,
      totalPages: 1,
    }
  },
  computed: {
    finalAmount() {
      return this.selectedAmount === '0' ? this.customAmount || '0' : this.selectedAmount
    },
    loadMoreStatus() {
      if (this.isLoading && this.currentPage > 0 && this.records.length > 0 && this.currentPage < this.totalPages) return 'loading';
      if (this.currentPage >= this.totalPages && this.records.length > 0) return 'nomore';
      if (this.isLoading && this.records.length === 0) return 'loading'; // Initial load
      // If not loading and no records, it will be handled by empty state, not u-loadmore
      return 'loadmore'; // Default, should ideally not be seen if logic is right
    }
  },
  onLoad() { // Changed from mounted for uni-app page lifecycle
    this.fetchRechargeRecords();
    // Potentially fetch accountInfo.balance here too
    // this.fetchAccountBalance();
  },
  onReachBottom() {
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchRechargeRecords(this.currentPage + 1);
    }
  },
  methods: {
    getRecordStatusClass(status) {
        // Example: status might be 0 for pending, 1 for success, 2 for failed
        if (status === 1 || status === 'SUCCESS' || status === 'completed' || status === '已完成') {
            return 'status-success';
        } else if (status === 0 || status === 'PENDING' || status === 'pending' || status === '处理中') {
            return 'status-pending';
        } else if (status === 2 || status === 'FAILED' || status === 'failed' || status === '已失败') {
            return 'status-failed';
        }
        return 'status-unknown';
    },
    async fetchRechargeRecords(page = 1) {
      this.isLoading = true;
      // No need to set errorLoading to false here if it's only set on actual error
      // this.errorLoading = false;
      try {
        const res = await getRechargeRecords({ page: page, limit: 15 });

        if (res && res.data && res.data.list) {
          if (page === 1) {
            this.records = res.data.list;
          } else {
            this.records = this.records.concat(res.data.list);
          }
          this.currentPage = parseInt(res.data.current_page || this.currentPage); // Fallback to current page if not provided
          this.totalPages = parseInt(res.data.last_page || this.totalPages); // Fallback to current totalPages
           if (res.data.list.length === 0 && page === 1) { // If first page returns no items
             this.totalPages = 1; // No more pages
           } else if (res.data.list.length < 15 && page >=1 ){ // If current page has less than limit items
             this.totalPages = this.currentPage; // Assume this is the last page
           }

        } else {
          console.warn('Recharge records data not found or in unexpected format:', res);
           if (page === 1) this.records = [];
           this.totalPages = page; // Prevent further loading attempts if response is bad
        }
      } catch (err) {
        console.error('Error fetching recharge records:', err);
        this.errorLoading = true; // Set error only on catch
        if (page === 1) this.records = [];
        // Toast is good, but error state in UI will also be visible
        // uni.showToast({ title: '记录加载失败', icon: 'none' });
      } finally {
        this.isLoading = false;
      }
    },
    // fetchAccountBalance() { /* Method to fetch account balance */ },
    goBack() {
      uni.navigateBack();
    },
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
          title: `充值 ¥${amount} 成功`,
          icon: 'success'
        })
        
        // 模拟余额更新
        this.accountInfo.balance = (parseFloat(this.accountInfo.balance) + parseFloat(amount)).toFixed(2)
        
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
  background-color: #f5f7fa;
  padding-bottom: 50rpx;
}

.state-container {
  padding: 40rpx 0;
  display: flex;
  justify-content: center;
  align-items: center;
}

.records-title {
  margin-top: 40rpx !important; // Add some space above records list
  border-top: 1rpx solid #eee;
  padding-top: 30rpx;
}

.records-list {
  margin: 0 30rpx;
}

.record-item {
  background-color: #ffffff;
  border-radius: 12rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.04);
}

.record-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12rpx;
}

.record-amount {
  font-size: 30rpx;
  font-weight: 500;
  color: #333;
}

.record-status {
  font-size: 26rpx;
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
  color: #fff;
}

.status-success { background-color: #19be6b; } // Green
.status-pending { background-color: #ff9900; } // Orange
.status-failed { background-color: #fa3534; }   // Red
.status-unknown { background-color: #909399; } // Grey

.record-time {
  font-size: 24rpx;
  color: #909399;
  display: block;
  margin-bottom: 8rpx;
}
.record-trade-no {
  font-size: 22rpx;
  color: #c0c4cc;
  display: block;
}

.load-more-component {
  margin: 30rpx 0;
}


/* Existing styles from here, ensure no conflicts */
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

.account-card {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
}

.balance-label {
  font-size: 26rpx;
  color: #909399;
  display: block;
}

.balance-amount {
  font-size: 48rpx;
  font-weight: bold;
  color: #3b82f6;
  margin-top: 8rpx;
  display: block;
}

.account-tag {
  background-color: #e7f1ff;
  border-radius: 30rpx;
  padding: 8rpx 20rpx;
  display: flex;
  align-items: center;
}

.tag-text {
  font-size: 24rpx;
  color: #3b82f6;
  margin-left: 8rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333333;
  margin: 30rpx 30rpx 20rpx;
}

.amount-grid {
  padding: 0 30rpx;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20rpx;
}

.amount-item {
  background-color: #ffffff;
  border: 2rpx solid #e5e7eb;
  border-radius: 16rpx;
  padding: 24rpx 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
  
  &.active {
    background-color: #3b82f6;
    border-color: #3b82f6;
    
    .amount-value, .amount-desc {
      color: #ffffff;
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

.custom-amount-section {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 20rpx 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.input-label {
  font-size: 26rpx;
  color: #909399;
  margin-bottom: 16rpx;
  display: block;
}

.input-container {
  display: flex;
  align-items: center;
  border-bottom: 2rpx solid #e5e7eb;
  padding-bottom: 16rpx;
  margin-bottom: 16rpx;
}

.currency-symbol {
  font-size: 36rpx;
  font-weight: bold;
  color: #333333;
  margin-right: 16rpx;
}

.input-tip {
  font-size: 22rpx;
  color: #909399;
}

.payment-card {
  margin: 0 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.payment-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx;
  
  &:active {
    background-color: #f9fafc;
  }
  
  &:not(:last-child) {
    border-bottom: 2rpx solid #f2f3f5;
  }
}

.payment-left {
  display: flex;
  align-items: center;
}

.payment-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 16rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20rpx;
}

.wechat-bg {
  background-color: #e7f8ef;
}

.alipay-bg {
  background-color: #e7f1ff;
}

.payment-name {
  font-size: 30rpx;
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
</style>