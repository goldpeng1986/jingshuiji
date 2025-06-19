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

    <!-- 充值记录区域 -->
    <view class="section-title records-title">充值记录</view>

    <!-- 记录加载状态 -->
    <view v-if="isLoading && records.length === 0 && !errorLoading" class="state-container">
      <u-loading-icon text="正在加载记录..." size="20"></u-loading-icon>
    </view>

    <!-- 记录错误状态 -->
    <view v-if="errorLoading && records.length === 0" class="state-container">
      <u-empty mode="network" text="记录加载失败">
        <u-button slot="bottom" type="primary" size="mini" @click="fetchRechargeRecords(1)" text="点我重试" :customStyle="{marginTop: '10px'}"></u-button>
      </u-empty>
    </view>

    <!-- 记录列表 -->
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

    <!-- 记录空状态 -->
    <view v-if="!isLoading && !errorLoading && records.length === 0" class="state-container">
      <u-empty mode="history" text="暂无充值记录"></u-empty>
    </view>

    <!-- 加载更多 -->
    <u-loadmore v-if="records.length > 0 || isLoading" :status="loadMoreStatus" @loadmore="fetchRechargeRecords(currentPage + 1)" iconType="circle" class="load-more-component"/>

  </view>
</template>

<script>
import { getRechargeRecords } from '../../api/api'; // 引入获取充值记录的API方法
export default {
  data() {
    return {
      title: '账户充值',
      accountInfo: {
        balance: '50.00' // 账户余额，实际应用中也可能来自API
      },
      amountOptions: [ // 预设充值金额选项
        { value: '10', desc: '充值10元' },
        { value: '20', desc: '充值20元' },
        { value: '50', desc: '充值50元' },
        { value: '100', desc: '充值100元' },
        { value: '200', desc: '充值200元' },
        { value: '0', desc: '自定义' }
      ],
      selectedAmount: '50', // 当前选中的预设金额
      customAmount: '', // 自定义输入的金额
      paymentMethod: 'wechat', // 当前选择的支付方式

      // --- 充值记录相关数据属性 ---
      records: [], // 存储充值记录列表
      isLoading: true, // 记录列表的加载状态，默认为true，初始进入页面即加载
      errorLoading: false, // 记录列表的错误状态
      currentPage: 1, // 当前页码
      totalPages: 1, // 总页数
    }
  },
  computed: {
    finalAmount() {
      // 计算最终确认充值的金额
      return this.selectedAmount === '0' ? this.customAmount || '0' : this.selectedAmount
    },
    loadMoreStatus() {
      // 计算 u-loadmore 组件的状态
      if (this.isLoading && this.currentPage > 0 && this.records.length > 0 && this.currentPage < this.totalPages) return 'loading'; // 加载中（非首次）
      if (this.currentPage >= this.totalPages && this.records.length > 0) return 'nomore'; // 没有更多
      if (this.isLoading && this.records.length === 0) return 'loading'; // 首次加载中
      // 如果非加载中且无记录，将由empty状态处理，u-loadmore不应显示
      return 'loadmore'; // 默认状态，理论上在正确逻辑下不应显示此状态
    }
  },
  onLoad() { // uni-app 页面生命周期钩子，页面加载时触发
    this.fetchRechargeRecords(); // 获取充值记录
    // 此处也可能需要获取账户余额
    // this.fetchAccountBalance();
  },
  onReachBottom() {
    // 页面滚动到底部时触发，用于上拉加载更多
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchRechargeRecords(this.currentPage + 1);
    }
  },
  methods: {
    getRecordStatusClass(status) {
        // 示例：status 可能为 0 表示处理中, 1 表示成功, 2 表示失败
        if (status === 1 || status === 'SUCCESS' || status === 'completed' || status === '已完成') {
            return 'status-success'; // 成功状态的CSS类
        } else if (status === 0 || status === 'PENDING' || status === 'pending' || status === '处理中') {
            return 'status-pending'; // 处理中状态的CSS类
        } else if (status === 2 || status === 'FAILED' || status === 'failed' || status === '已失败') {
            return 'status-failed'; // 失败状态的CSS类
        }
        return 'status-unknown'; // 未知状态的CSS类
    },
    async fetchRechargeRecords(page = 1) {
      this.isLoading = true;
      // 仅在实际发生错误时设置 errorLoading 为 true，因此此处无需重置为 false
      // this.errorLoading = false;
      try {
        // 调用API获取充值记录
        const res = await getRechargeRecords({ page: page, limit: 15 });

        if (res && res.data && res.data.list) { // 检查API响应是否符合预期结构
          if (page === 1) {
            this.records = res.data.list; // 第一页数据，直接覆盖
          } else {
            this.records = this.records.concat(res.data.list); // 后续页数据，追加到现有列表
          }
          // 更新当前页码和总页数，如果API未返回则使用当前值作为回退
          this.currentPage = parseInt(res.data.current_page || this.currentPage);
          this.totalPages = parseInt(res.data.last_page || this.totalPages);

           // 如果第一页返回空列表，说明没有更多页
           if (res.data.list.length === 0 && page === 1) {
             this.totalPages = 1;
           }
           // 如果当前页返回的记录数小于每页限制数，则认为这是最后一页
           else if (res.data.list.length < 15 && page >=1 ){
             this.totalPages = this.currentPage;
           }

        } else {
          // API响应为空或数据格式不符合预期
          console.warn('充值记录数据未找到或格式不正确:', res);
           if (page === 1) this.records = []; // 第一页加载失败则清空记录
           this.totalPages = page; // 如果响应错误，则阻止后续加载尝试
        }
      } catch (err) {
        console.error('获取充值记录时发生错误:', err);
        this.errorLoading = true; // 仅在捕获到错误时设置错误状态
        if (page === 1) this.records = []; // 第一页加载失败则清空记录
        // Toast提示是好的，但UI中的错误状态也会显示给用户
        // uni.showToast({ title: '记录加载失败', icon: 'none' });
      } finally {
        this.isLoading = false; // 无论成功或失败，结束加载状态
      }
    },
    // fetchAccountBalance() { /* 获取账户余额的方法 */ },
    goBack() {
      uni.navigateBack(); // 返回上一页
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