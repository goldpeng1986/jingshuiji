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
      <u-loading-icon v-if="isLoadingAccountInfo && accountInfo.balance === '0.00'" mode="circle" color="#ffffff" size="20" text="余额加载中..." inactive-color="#ffffff" text-color="#ffffff"></u-loading-icon>
      <template v-else>
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
      </template>
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
        <u-loading-icon v-if="isLoadingCoupons && availableCoupons.length === 0" mode="circle" size="16"></u-loading-icon>
        <text class="coupon-info" v-else>{{ selectedCoupon ? selectedCoupon.name : (availableCoupons.length > 0 ? `${availableCoupons.length}张可用` : '无可用优惠券') }}</text>
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
        :loading="isSubmitting"
        :disabled="isSubmitting"
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
        
        <scroll-view scroll-y class="coupon-list-scroll"> <!-- 修改class名以区分样式 -->
          <u-loading-icon v-if="isLoadingCoupons && availableCoupons.length === 0" mode="circle" size="28" text="优惠券加载中..."></u-loading-icon>
          <template v-if="!isLoadingCoupons && availableCoupons.length > 0">
            <view
              class="coupon-item-popup" <!-- 修改class名以区分样式 -->
              v-for="(item) in availableCoupons"
              :key="item.id" <!-- 使用优惠券唯一ID -->
              :class="{selected: selectedCouponId === item.id}"
              @click="selectCoupon(item)"
            >
              <view class="coupon-left" :style="{backgroundColor: item.bgColor || '#f43f5e'}">
                <text class="coupon-amount">¥{{ item.amount }}</text>
                <text class="coupon-condition">{{ item.condition }}</text>
              </view>
              <view class="coupon-right">
                <view class="coupon-info">
                  <text class="coupon-name">{{ item.name }}</text>
                  <text class="coupon-desc" v-if="item.description">{{ item.description }}</text>
                </view>
                <view class="coupon-validity">
                  <text>有效期至: {{ item.validity }}</text>
                </view>
              </view>
              <view class="coupon-check">
                <u-icon v-if="selectedCouponId === item.id" name="checkmark-circle-fill" size="22" color="#3b82f6"></u-icon> <!-- 修改图标 -->
                <view v-else class="check-circle"></view>
              </view>
            </view>
          </template>
          <u-empty mode="coupon" text="暂无可用优惠券" v-if="!isLoadingCoupons && availableCoupons.length === 0"></u-empty>
          
          <!-- 不使用优惠券选项 -->
          <view class="no-coupon-item" @click="selectCoupon(null)" :class="{selected: selectedCouponId === null}">
            <text class="no-coupon-text">不使用优惠券</text>
            <view class="coupon-check">
              <u-icon v-if="selectedCouponId === null" name="checkmark-circle-fill" size="22" color="#3b82f6"></u-icon>
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
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '账户充值', // 页面标题
      accountInfo: { // 账户信息，将由API填充
        balance: '0.00',        // 当前余额
        todayExpense: '0.00',   // 今日支出
        monthExpense: '0.00'  // 本月支出
      },
      amountOptions: [ // 充值档位选项 (可保持静态或未来从API获取)
        { value: '10', desc: '充值10元' },
        { value: '20', desc: '充值20元' },
        { value: '50', desc: '充值50元' },
        { value: '100', desc: '充值100元' },
        { value: '200', desc: '充值200元' },
        { value: '500', desc: '充值500元' }
      ],
      selectedAmount: '50', // 默认选中的充值档位金额
      customAmount: '',     // 自定义充值金额
      paymentMethod: 'wechat', // 默认支付方式: 'wechat', 'alipay', 'balance' (余额支付理论上不用于充值自身)

      availableCoupons: [], // 可用优惠券列表，将由API填充
      selectedCoupon: null, // 当前选中的优惠券对象
      selectedCouponId: null,// 当前选中的优惠券ID (用于弹窗中高亮)
      showCoupons: false,   // 控制优惠券弹窗的显示状态

      isLoadingAccountInfo: false, // 账户信息加载状态
      isLoadingCoupons: false,     // 优惠券加载状态
      isSubmitting: false          // 充值提交状态
    };
  },
  computed: {
    // 最终选择的充值金额 (档位或自定义)
    finalAmount() {
      return this.selectedAmount === '0' ? (this.customAmount || '0') : this.selectedAmount;
    },
    // 实付金额 (充值金额 - 优惠券抵扣)
    actualAmount() {
      const amount = parseFloat(this.finalAmount);
      // 确保 selectedCoupon 和 selectedCoupon.amount 存在且为有效数字
      const discount = this.selectedCoupon && !isNaN(parseFloat(this.selectedCoupon.amount))
                       ? parseFloat(this.selectedCoupon.amount)
                       : 0;
      return Math.max(amount - discount, 0).toFixed(2); // 确保不为负数
    }
  },
  onShow() { // 使用onShow确保每次进入页面都刷新数据
    this.fetchAccountInfo();
    this.fetchAvailableCoupons();
  },
  methods: {
    // 获取账户信息 (余额、支出等)
    async fetchAccountInfo() {
      this.isLoadingAccountInfo = true;
      try {
        const res = await this.$api.getUserIndex(); // 此API已包含余额和支出
        if (res.code === 1 && res.data && res.data.userInfo) {
          const userInfo = res.data.userInfo;
          this.accountInfo.balance = parseFloat(userInfo.balance || 0).toFixed(2); // API返回的是 balance 字段
          this.accountInfo.todayExpense = parseFloat(userInfo.today_expense || 0).toFixed(2);
          this.accountInfo.monthExpense = parseFloat(userInfo.month_expense || 0).toFixed(2);
        } else {
          toast(res.msg || '获取账户信息失败');
        }
      } catch (error) {
        console.error('fetchAccountInfo error:', error);
        toast('网络请求账户信息出错');
      } finally {
        this.isLoadingAccountInfo = false;
      }
    },
    // 获取可用优惠券列表
    async fetchAvailableCoupons() {
      this.isLoadingCoupons = true;
      this.availableCoupons = []; // 清空旧数据
      this.selectedCoupon = null; // 清空已选
      this.selectedCouponId = null;
      try {
        // TODO: 确认API是否支持按场景(如'recharge')筛选优惠券，或前端自行根据优惠券类型/适用商品判断
        const res = await this.$api.myCouponList({ status: 'available' });
        if (res.code === 1 && res.data && res.data.data) {
          // 适配API返回的数据结构
          this.availableCoupons = res.data.data.map(item => {
            const couponDetails = item.coupon || {};
            // 假设充值优惠券是固定金额的 (mode: FIXED)
            // 并且其 result_data.price 是优惠金额
            // **重要**: 此处需要根据实际优惠券数据结构进行调整，特别是判断哪些优惠券适用于充值场景
            if (couponDetails.mode === 'FIXED' && couponDetails.result_data && couponDetails.result_data.price) {
                 return {
                    id: item.id, // 这是 fa_shop_user_coupon.id，即用户优惠券ID
                    user_coupon_id: item.id, // 明确一下用户优惠券ID
                    coupon_id: couponDetails.id, // 这是 fa_shop_coupon.id，即优惠券模板ID
                    name: couponDetails.name || '未知券名',
                    description: `满${parseFloat(couponDetails.result_data.enough || 0).toFixed(2)}减${parseFloat(couponDetails.result_data.price).toFixed(2)}`, // 描述
                    amount: parseFloat(couponDetails.result_data.price).toFixed(2), // 优惠金额
                    condition: `满${parseFloat(couponDetails.result_data.enough || 0).toFixed(2)}可用`, // 使用条件
                    validity: item.expire_time ? this.$u.timeFormat(item.expire_time, 'yyyy-mm-dd') : '长期有效',
                    bgColor: '#f43f5e' // 示例颜色，可根据类型调整
                 };
            }
            return null; // 如果不适用或结构不符，则过滤掉
          }).filter(Boolean); // 移除null项
        } else {
          toast(res.msg || '获取可用优惠券失败');
        }
      } catch (error) {
        console.error('fetchAvailableCoupons error:', error);
        toast('网络请求可用优惠券出错');
      } finally {
        this.isLoadingCoupons = false;
      }
    },
    // 选择充值档位
    selectAmount(amount) {
      this.selectedAmount = amount;
      if (amount !== '0') { // '0' 代表自定义金额输入框被激活
        this.customAmount = ''; // 清空自定义金额
      }
    },
    // 自定义金额输入处理
    onCustomAmountInput(value) {
      if (value) {
        this.selectedAmount = '0'; // 标记为自定义金额
      }
      // 可以在此处添加输入校验逻辑，例如最大最小值
      const numValue = parseFloat(value);
      if (isNaN(numValue) || numValue < 1) {
          // this.customAmount = '1'; // 或清空，或提示
      } else if (numValue > 1000) {
          this.customAmount = '1000';
          toast('单次充值不超过1000元');
      }
    },
    // 选择支付方式
    selectPayment(method) {
      this.paymentMethod = method;
    },
    // 显示优惠券弹窗
    showCouponList() {
      if (this.availableCoupons.length === 0 && !this.isLoadingCoupons) {
        toast('暂无可用优惠券');
        return;
      }
      this.showCoupons = true;
    },
    // 关闭优惠券弹窗
    closeCouponList() {
      this.showCoupons = false;
    },
    // 在弹窗中选择优惠券
    selectCoupon(coupon) {
      // coupon 为 null 表示选择 "不使用优惠券"
      this.selectedCouponId = coupon ? coupon.id : null;
    },
    // 确认优惠券选择
    confirmCouponSelection() {
      this.selectedCoupon = this.availableCoupons.find(c => c.id === this.selectedCouponId) || null;
      this.closeCouponList();
    },
    // 确认充值
    async confirmRecharge() {
      const finalRechargeAmount = parseFloat(this.finalAmount); // 最终选择的充值金额
      const actualPaymentAmount = parseFloat(this.actualAmount); // 实际需要支付的金额 (可能已扣除优惠券)

      // 1. 表单验证
      if (isNaN(finalRechargeAmount) || finalRechargeAmount <= 0) {
        toast('请输入或选择有效的充值金额');
        return;
      }
      if (finalRechargeAmount < 1 || finalRechargeAmount > 1000) { // 与输入提示一致
        toast('单次充值金额需在1元至1000元之间');
        return;
      }
      if (!this.paymentMethod) {
        toast('请选择支付方式');
        return;
      }

      this.isSubmitting = true; // 开始提交，禁用按钮等
      uni.showLoading({ title: '正在创建充值订单...' });

      // 2. 构建API参数
      // **重点**: 后端 userMoney API 需要修改以接收 paytype 和 user_coupon_id
      // 并返回调起支付所需的参数，而不是直接完成充值。
      const params = {
        money: actualPaymentAmount, // 提交给后端的是实际应支付金额
        paytype: this.paymentMethod,
        // 如果选择了优惠券，传递用户优惠券ID (fa_shop_user_coupon.id)
        user_coupon_id: this.selectedCoupon ? this.selectedCoupon.user_coupon_id : null
      };

      try {
        // 3. 调用创建充值订单API (即 User.php -> userMoney)
        const res = await this.$api.confirmMoney(params); // confirmMoney 即 userMoney
        
        if (res.code === 1 && res.data) {
          // 4. 根据返回的支付参数调起支付
          //    res.data 应包含类似微信的 jsApiParameters 或支付宝的 trade_no/order_string
          //    此处为模拟支付流程，实际需根据支付网关返回的具体参数进行处理

          // 示例：微信支付 (需要后端返回正确的支付参数)
          if (this.paymentMethod === 'wechat' && res.data.jsApiParameters) {
            uni.hideLoading(); // 先隐藏创建订单的loading
            uni.requestPayment({
              provider: 'wxpay', // 微信小程序/APP是'wxpay', H5可能是公众号支付
              ...res.data.jsApiParameters, // 后端返回的支付参数
              success: (payRes) => {
                toast('支付成功', 'success');
                this.handleRechargeSuccess();
              },
              fail: (payErr) => {
                console.error('uni.requestPayment fail:', payErr);
                toast('支付失败或取消');
              },
              complete: () => {
                this.isSubmitting = false;
              }
            });
          } else if (this.paymentMethod === 'alipay' && res.data.trade_no) { // 示例：支付宝支付
            uni.hideLoading();
            uni.requestPayment({
              provider: 'alipay',
              orderInfo: res.data.trade_no, // 或后端返回的完整orderStr
              success: (payRes) => {
                toast('支付成功', 'success');
                this.handleRechargeSuccess();
              },
              fail: (payErr) => {
                console.error('uni.requestPayment fail:', payErr);
                toast('支付失败或取消');
              },
              complete: () => {
                this.isSubmitting = false;
              }
            });
          } else if (res.data.is_paid_directly) { // 如果后端直接处理了支付（例如余额充值或金额为0）
             uni.hideLoading();
             toast('充值成功', 'success');
             this.handleRechargeSuccess();
          } else {
            uni.hideLoading();
            toast('获取支付参数失败，请稍后再试');
            this.isSubmitting = false;
          }
        } else {
          uni.hideLoading();
          toast(res.msg || '创建充值订单失败');
          this.isSubmitting = false;
        }
      } catch (error) {
        uni.hideLoading();
        this.isSubmitting = false;
        console.error('confirmRecharge error:', error);
        toast('网络请求失败，请稍后再试');
      }
    },
    // 充值成功后的处理
    handleRechargeSuccess() {
        this.isSubmitting = false;
        // 刷新账户信息
        this.fetchAccountInfo();
        // 清空选择的优惠券和金额 (可选)
        this.selectedCoupon = null;
        this.selectedCouponId = null;
        // this.selectedAmount = '50'; // 重置为默认档位
        // this.customAmount = '';
        // 提示并延时返回或跳转到其他页面
        setTimeout(() => {
          uni.navigateBack(); // 或跳转到指定页面
        }, 1500);
    },
    // 查看充值协议
    viewAgreement() {
      toast('查看充值服务协议 (功能待实现)');
      // uni.navigateTo({ url: '/pages/public/webview?url=协议地址' });
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