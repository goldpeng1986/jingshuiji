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
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '填写订单信息', // 页面标题
      productInfo: { // 从上一个页面接收的商品信息
        productId: null,
        productName: '商品加载中...',
        selectedSpecName: '',
        selectedSpec1Name: '', // 例如房屋类型
        quantity: 1,
        price: 0, // 单价
        image: ''
      },
      installationFee: 0, // 上门安装费，可以从配置或API获取，此处暂设为0或固定值
      formData: { // 表单数据
        name: '',       // 联系人姓名
        phone: '',      // 联系人手机
        region: '',     // 所在地区 (省市区字符串)
        address: '',    // 详细地址
        date: '',       // 预约安装日期
        timeSlot: '',   // 预约安装时间段
        memo: ''        // 用户备注 (新增)
      },
      // uView选择器相关的状态和数据
      showRegion: false,
      showDate: false,
      showTime: false,
      // 注意：regionColumns 应该从API获取或使用更完善的地区选择组件，这里仅为示例
      regionColumns: [
        ['北京市', '上海市', '广东省'],
        ['市辖区', '市辖区', '广州市', '深圳市'],
        ['东城区', '黄浦区', '天河区', '福田区']
      ],
      timeSlots: ['上午 09:00-12:00', '下午 13:00-16:00', '晚上 17:00-20:00'], // 可选时间段
      defaultAddress: null, // 存储默认地址信息
      isLoading: false, // 提交加载状态
    };
  },
  computed: {
    // 订单头部显示的产品规格信息
    productSpecDisplay() {
      let spec = this.productInfo.productName;
      if (this.productInfo.selectedSpecName) spec += ` ${this.productInfo.selectedSpecName}`;
      // if (this.productInfo.selectedSpec1Name) spec += ` ${this.productInfo.selectedSpec1Name}`; // 如果二级规格也显示
      return spec;
    },
    // 房屋类型显示 (如果二级规格作为房屋类型)
    houseTypeDisplay() {
        return this.productInfo.selectedSpec1Name || '未指定';
    },
    // 产品总费用
    productTotalPrice() {
      return parseFloat(this.productInfo.price) * this.productInfo.quantity;
    },
    // 订单总计费用
    totalPrice() {
      return this.productTotalPrice + parseFloat(this.installationFee);
    }
  },
  onLoad(options) {
    // 解析从上一个页面传递过来的商品信息
    if (options.productId) {
      this.productInfo.productId = options.productId;
      this.productInfo.productName = options.productName || '商品名称';
      this.productInfo.selectedSpecName = options.selectedSpecName || '';
      this.productInfo.selectedSpec1Name = options.selectedSpec1Name || ''; // 如房屋类型
      this.productInfo.quantity = parseInt(options.quantity) || 1;
      this.productInfo.price = parseFloat(options.price || 0).toFixed(2);
      this.productInfo.image = options.image || ''; // 商品图片
    } else {
      toast('商品信息加载失败');
      // uni.navigateBack(); // 如果没有商品信息，则返回
    }
    this.fetchDefaultAddress(); // 获取默认地址
    // TODO: 获取可选的安装日期和时间段 (如果需要从API获取)
    // this.fetchAvailableSlots();
  },
  methods: {
    // 获取默认收货地址
    async fetchDefaultAddress() {
        try {
            const res = await this.$api.defAddress();
            if (res.code === 1 && res.data) {
                this.defaultAddress = res.data;
                this.formData.name = res.data.name;
                this.formData.phone = res.data.mobile;
                this.formData.region = `${res.data.province || ''} ${res.data.city || ''} ${res.data.area || ''}`.trim();
                this.formData.address = res.data.address;
            }
        } catch (error) {
            console.error("fetchDefaultAddress error:", error);
            // 获取默认地址失败不强制提示，允许用户手动输入
        }
    },
    // 显示/隐藏地区选择器
    showRegionPicker() { this.showRegion = true; },
    confirmRegion(e) {
      this.formData.region = e.value.join(' ');
      this.showRegion = false;
    },
    cancelRegion() { this.showRegion = false; },
    
    // 显示/隐藏日期选择器
    showDatePicker() { this.showDate = true; },
    confirmDate(e) {
      this.formData.date = this.$u.timeFormat(e[0], 'yyyy-mm-dd'); // uView日历返回的是数组
      this.showDate = false;
    },
    cancelDate() { this.showDate = false; },
    
    // 显示/隐藏时间段选择器
    showTimePicker() { this.showTime = true; },
    confirmTime(e) {
      this.formData.timeSlot = e.value[0];
      this.showTime = false;
    },
    cancelTime() { this.showTime = false; },
    
    // 提交订单
    async submitOrder() {
      // 表单验证
      if (!this.formData.name) { return toast('请输入姓名');}
      if (!this.formData.phone) { return toast('请输入手机号码'); }
      if (!this.$u.test.mobile(this.formData.phone)) { return toast('手机号码格式不正确');}
      if (!this.formData.region) { return toast('请选择所在地区');}
      if (!this.formData.address) { return toast('请输入详细地址');}
      if (!this.formData.date) { return toast('请选择安装日期');}
      if (!this.formData.timeSlot) { return toast('请选择安装时间段');}
      
      this.isLoading = true; // 开始提交，显示加载状态
      uni.showLoading({ title: '订单提交中...' });

      // 构建提交给API的参数
      // 注意: 后端 addOrder API 需要能够处理直接购买的逻辑，
      // 它可能需要 cart_ids (此时为空或特定标识)，或者直接接收商品信息。
      // 此处我们假设后端能够通过 goods_info 或类似结构处理单个商品直接购买。
      const params = {
        // 购物车ID，对于直接购买可能为空或特殊处理
        // cart_ids: '', // 如果是从购物车结算，这里是购物车ID字符串，例如 "1,2,3"

        // 商品信息 (如果后端支持直接传递商品信息创建订单)
        // 这种方式需要后端接口支持，否则需要先将商品加入购物车再结算
        goods_info: [{
            goods_id: this.productInfo.productId,
            sku_id: this.productInfo.selectedSpecId || 0, // 假设有规格ID，若无则为0或不传
            // selected_spec_name: this.productInfo.selectedSpecName, // 规格名，供参考
            // selected_spec1_name: this.productInfo.selectedSpec1Name, // 二级规格名
            nums: this.productInfo.quantity
        }],

        address_id: this.defaultAddress ? this.defaultAddress.id : null, // 如果有默认地址ID则传递
        // 如果 address_id 为 null 或后端需要完整的地址信息，则传递以下字段
        name: this.formData.name,
        mobile: this.formData.phone,
        province: this.formData.region.split(' ')[0] || '',
        city: this.formData.region.split(' ')[1] || '',
        area: this.formData.region.split(' ')[2] || '',
        address: this.formData.address,

        memo: this.formData.memo, // 用户备注
        // 预约安装时间 (后端已计划将此信息存入memo或专用字段)
        date: this.formData.date,
        time_slot: this.formData.timeSlot,

        // 其他可能需要的参数，如优惠券ID等
        // user_coupon_id: null,
      };

      try {
        const res = await this.$api.addOrder(params);
        uni.hideLoading();
        this.isLoading = false;
        if (res.code === 1) {
          toast('订单提交成功', 'success');
          // 订单成功后，可以跳转到订单列表、订单详情或支付页面
          // 例如，如果需要支付：
          // uni.redirectTo({ url: `/pages/payment/pay?order_sn=${res.data.order_sn}` });
          // 否则跳转到订单列表或成功页：
          setTimeout(() => {
            uni.redirectTo({ // 使用redirectTo避免返回当前页
              url: '/pages/order/index' // 或订单成功提示页
            });
          }, 1500);
        } else {
          toast(res.msg || '订单提交失败');
        }
      } catch (error) {
        uni.hideLoading();
        this.isLoading = false;
        console.error('submitOrder error:', error);
        toast('网络请求失败，订单提交失败');
      }
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