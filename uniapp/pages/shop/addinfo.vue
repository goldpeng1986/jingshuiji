<template>
  <view class="container">
    <!-- 订单信息头部 -->
    <view class="order-header">
      <view class="header-content">
        <view class="header-left">
          <view class="header-title-section">
            <text class="header-title">订单信息</text>
          </view>
          <view class="order-info">
            <text class="product-spec">{{ productSpec }} x {{ quantity }}</text>
            <text class="divider">|</text>
            <text class="house-type">{{ houseType }}</text>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 用户信息表单 -->
    <view class="form-section">
      <view class="section-title">
        <u-icon name="account" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">联系人信息</text>
      </view>
      
      <view class="form-item">
        <text class="form-label">姓名</text>
        <u-input 
          v-model="formData.name" 
          placeholder="请输入您的姓名" 
          border="bottom"
          clearable
        ></u-input>
      </view>
      
      <view class="form-item">
        <text class="form-label">手机号码</text>
        <u-input 
          v-model="formData.phone" 
          placeholder="请输入您的手机号码" 
          border="bottom"
          type="number"
          clearable
        ></u-input>
      </view>
    </view>
    
    <!-- 地址信息 -->
    <view class="form-section">
      <view class="section-title">
        <u-icon name="map" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">安装地址</text>
      </view>
      
      <view class="form-item">
        <text class="form-label">所在地区</text>
        <view class="region-picker" @click="showRegionPicker">
          <text v-if="formData.region">{{ formData.region }}</text>
          <text v-else class="placeholder">请选择所在地区</text>
          <u-icon name="arrow-right" size="16" color="#c0c4cc"></u-icon>
        </view>
      </view>
      
      <view class="form-item">
        <text class="form-label">详细地址</text>
        <u-input 
          v-model="formData.address" 
          placeholder="请输入详细地址，如街道、门牌号等" 
          border="bottom"
          clearable
        ></u-input>
      </view>
    </view>
    
    <!-- 预约时间 -->
    <view class="form-section">
      <view class="section-title">
        <u-icon name="calendar" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">预约安装时间</text>
      </view>
      
      <view class="form-item">
        <text class="form-label">日期</text>
        <view class="date-picker" @click="showDatePicker">
          <text v-if="formData.date">{{ formData.date }}</text>
          <text v-else class="placeholder">请选择安装日期</text>
          <u-icon name="arrow-right" size="16" color="#c0c4cc"></u-icon>
        </view>
      </view>
      
      <view class="form-item">
        <text class="form-label">时间段</text>
        <view class="time-picker" @click="showTimePicker">
          <text v-if="formData.timeSlot">{{ formData.timeSlot }}</text>
          <text v-else class="placeholder">请选择时间段</text>
          <u-icon name="arrow-right" size="16" color="#c0c4cc"></u-icon>
        </view>
      </view>
    </view>
    
    <!-- 费用明细 -->
    <view class="fee-section">
      <view class="section-title">
        <u-icon name="rmb-circle" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">费用明细</text>
      </view>
      
      <view class="fee-item">
        <text class="fee-label">产品费用</text>
        <text class="fee-value">¥{{ productPrice }}</text>
      </view>
      
      <view class="fee-item">
        <text class="fee-label">上门安装费</text>
        <text class="fee-value">¥{{ installationFee }}</text>
      </view>
      
      <u-line color="#f3f4f6" margin="15rpx 0"></u-line>
      
      <view class="fee-item total">
        <text class="fee-label">合计</text>
        <text class="fee-value price">¥{{ totalPrice }}</text>
      </view>
    </view>
    
    <!-- 底部提交栏 -->
    <view class="footer">
      <view class="footer-left">
        <text class="total-text">合计：</text>
        <text class="total-price">¥{{ totalPrice }}</text>
      </view>
      <view class="footer-right">
        <u-button type="primary" text="立即下单" size="medium" @click="submitOrder"></u-button>
      </view>
    </view>
    
    <!-- 选择器组件 -->
    <u-picker 
      :show="showRegion" 
      :columns="regionColumns" 
      @confirm="confirmRegion" 
      @cancel="cancelRegion"
    ></u-picker>
    
    <u-calendar 
      :show="showDate" 
      mode="date" 
      @confirm="confirmDate" 
      @close="cancelDate"
    ></u-calendar>
    
    <u-picker 
      :show="showTime" 
      :columns="[timeSlots]" 
      @confirm="confirmTime" 
      @cancel="cancelTime"
    ></u-picker>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '填写订单信息',
      productSpec: '高端家用净水机 A8 标准版',
      houseType: '自有房',
      quantity: 1,
      productPrice: 1999,
      originalPrice: 2599,
      installationFee: 200,
      formData: {
        name: '',
        phone: '',
        region: '',
        address: '',
        date: '',
        timeSlot: ''
      },
      showRegion: false,
      showDate: false,
      showTime: false,
      regionColumns: [
        ['北京市', '上海市', '广州市', '深圳市', '杭州市'],
        ['朝阳区', '海淀区', '东城区', '西城区', '丰台区'],
        ['望京街道', '酒仙桥街道', '三里屯街道', '建外街道']
      ],
      timeSlots: ['上午 9:00-12:00', '下午 13:00-16:00', '晚上 17:00-20:00']
    }
  },
  computed: {
    totalPrice() {
      return this.productPrice + this.installationFee;
    }
  },
  methods: {
    showRegionPicker() {
      this.showRegion = true;
    },
    confirmRegion(e) {
      this.formData.region = e.value.join(' ');
      this.showRegion = false;
    },
    cancelRegion() {
      this.showRegion = false;
    },
    
    showDatePicker() {
      this.showDate = true;
    },
    confirmDate(e) {
      this.formData.date = this.$u.timeFormat(e.timestamp, 'yyyy-mm-dd');
      this.showDate = false;
    },
    cancelDate() {
      this.showDate = false;
    },
    
    showTimePicker() {
      this.showTime = true;
    },
    confirmTime(e) {
      this.formData.timeSlot = e.value[0];
      this.showTime = false;
    },
    cancelTime() {
      this.showTime = false;
    },
    
    submitOrder() {
      // 表单验证
      if (!this.formData.name) {
        return uni.showToast({
          title: '请输入姓名',
          icon: 'none'
        });
      }
      if (!this.formData.phone) {
        return uni.showToast({
          title: '请输入手机号码',
          icon: 'none'
        });
      }
      if (!this.formData.region || !this.formData.address) {
        return uni.showToast({
          title: '请完善安装地址',
          icon: 'none'
        });
      }
      if (!this.formData.date || !this.formData.timeSlot) {
        return uni.showToast({
          title: '请选择预约安装时间',
          icon: 'none'
        });
      }
      
      // 提交订单
      uni.showLoading({
        title: '提交中...'
      });
      
      // 模拟提交
      setTimeout(() => {
        uni.hideLoading();
        uni.showToast({
          title: '订单提交成功',
          icon: 'success'
        });
        // 跳转到订单页面
        setTimeout(() => {
          uni.navigateTo({
            url: '/pages/order/index'
          });
        }, 1500);
      }, 2000);
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

.order-header {
  background-color: #ffffff;
  padding: 30rpx;
  margin-bottom: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  flex: 1;
}

.header-right {
  text-align: right;
}

.header-title-section {
  margin-bottom: 16rpx;
}

.header-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  position: relative;
  padding-left: 20rpx;
}

.header-title::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 6rpx;
  height: 28rpx;
  background-color: #3c9cff;
  border-radius: 3rpx;
}

.price-section {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.price {
  font-size: 48rpx;
  font-weight: bold;
  color: #f56c6c;
  line-height: 1.2;
}

.original-price {
  font-size: 24rpx;
  color: #999;
  text-decoration: line-through;
  margin-top: 6rpx;
}

.order-info {
  display: flex;
  align-items: center;
}

.product-spec, .house-type {
  font-size: 26rpx;
  color: #666;
}

.divider {
  margin: 0 15rpx;
  color: #ddd;
}

.form-section, .fee-section {
  background-color: #ffffff;
  padding: 30rpx;
  margin-bottom: 20rpx;
}

.section-title {
  display: flex;
  align-items: center;
  margin-bottom: 30rpx;
}

.title-text {
  font-size: 28rpx;
  color: #333;
  margin-left: 10rpx;
  font-weight: 500;
}

.form-item {
  margin-bottom: 30rpx;
}

.form-label {
  font-size: 26rpx;
  color: #606266;
  margin-bottom: 10rpx;
  display: block;
}

.region-picker, .date-picker, .time-picker {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80rpx;
  border-bottom: 1px solid #e4e7ed;
  font-size: 28rpx;
  color: #303133;
}

.placeholder {
  color: #c0c4cc;
}

.fee-item {
  display: flex;
  justify-content: space-between;
  padding: 15rpx 0;
  font-size: 26rpx;
}

.fee-label {
  color: #606266;
}

.fee-value {
  color: #303133;
  font-weight: 500;
}

.total {
  font-size: 30rpx;
}

.footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100rpx;
  background-color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 30rpx;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
  z-index: 99;
  .u-button{
    padding: 10px;
  }
}

.footer-left {
  display: flex;
  align-items: center;
  flex: 1;
}

.total-text {
  font-size: 28rpx;
  color: #606266;
}

.total-price {
  font-size: 36rpx;
  font-weight: bold;
  color: #f56c6c;
}

.footer-right {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex: 1;
}
</style>