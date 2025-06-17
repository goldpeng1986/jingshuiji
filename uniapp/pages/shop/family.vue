<template>
  <view class="container">
    <!-- 产品图片轮播 -->
    <view class="swiper-section">
      <u-swiper
        :list="productImages"
        :height="300"
        :interval="3000"
        :autoplay="true"
        indicatorMode="dot"
      ></u-swiper>
    </view>
    
    <!-- 产品标题和价格 -->
    <view class="product-header">
      <view class="product-title-section">
        <text class="product-title">{{ product.title }}</text>
        <u-tag :text="product.flag" :type="product.flag === 'hot' ? 'error' : 'primary'" size="mini" shape="circle"></u-tag>
      </view>
      <view class="price-section">
        <text class="price">¥{{ product.price }}</text>
        <text class="original-price">¥{{ product.marketprice }}</text>
        <text class="discount">{{ Math.floor((product.price/product.marketprice)*10) }}折</text>
      </view>
      <view class="sales-info">
        <text>月销 {{ product.monthlySales }}+</text>
        <text class="divider">|</text>
        <text>好评率 {{ product.goodRating }}%</text>
      </view>
    </view>
    
    <!-- 产品规格选择 -->
    <view class="specs-section">
      <view class="section-title">
        <u-icon name="tags" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">规格选择</text>
      </view>
      <view class="specs-options">
        <view 
          v-for="(item, index) in product.specs" 
          :key="index"
          :class="['spec-item', selectedSpec === index ? 'spec-selected' : '']"
          @click="selectSpec(index)"
        >
          {{ item.name }}
        </view>
      </view>
	  <view class="section-title">
	    <u-icon name="tags" size="18" color="#3c9cff"></u-icon>
	    <text class="title-text">房屋类型</text>
	  </view>
	  <view class="specs-options">
	    <view 
	      v-for="(item, index) in product.specs1" 
	      :key="index"
	      :class="['spec-item', selectedSpec1 === index ? 'spec-selected' : '']"
	      @click="selectSpec1(index)"
	    >
	      {{ item.name }}
	    </view>
	  </view>
    </view>
    
    <!-- 数量选择 -->
    <view class="quantity-section">
      <view class="section-title">
        <u-icon name="shopping-cart" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">购买数量</text>
      </view>
      <view class="quantity-selector">
        <u-number-box v-model="quantity" :min="1" :max="99"></u-number-box>
      </view>
    </view>
    
    <!-- 产品详情 -->
    <view class="detail-section">
      <view class="section-header">
        <view class="header-left">
          <u-icon name="file-text" size="24" color="#3c9cff"></u-icon>
          <text class="header-title">产品详情</text>
        </view>
      </view>
      <u-line color="#f3f4f6" margin="15rpx 0"></u-line>
      
     <!-- 产品特点 -->
      <!-- <view class="feature-list">
        <view class="feature-item" v-for="(item, index) in product.features" :key="index">
          <u-icon :name="item.icon" size="16" color="#3c9cff"></u-icon>
          <text class="ml-2">{{ item.text }}</text>
        </view>
      </view> -->
      
      <!-- 产品参数 -->
      <!-- <view class="params-list">
        <view class="param-item" v-for="(item, index) in product.params" :key="index">
          <text class="param-label">{{ item.label }}</text>
          <text class="param-value">{{ item.value }}</text>
        </view>
      </view> -->
     
      <!-- 产品描述 -->
      <view class="description">
        <text class="desc-text">{{ product.description }}</text>
      </view>
    </view>
	
	<!-- 底部购买栏 -->
	<view class="footer">
	  <view class="footer-right">
	    <u-button type="primary" text="下一步" size="medium" @click="buyNow"></u-button>
	  </view>
	</view>
  </view>
  
  
</template>

<script>
import { forEach } from 'core-js/stable/array';

export default {
  data() {
    return {
      title: '家用净水机详情',
      selectedSpec: 0,
      selectedSpec1: 0,
      quantity: 1,
      product: {
        id: 1,
        title: '高端家用净水机 A8',
        flagName: '热销',
        flag: 'hot',
        price: '1999',
        marketprice: '2599',
        discount: '7.7',
        monthlySales: 328,
        goodRating: 98,
        specs: [
          { name: '标准版', price: '1999' },
          { name: '豪华版', price: '2499' },
          { name: '智能版', price: '2999' }
        ],
		specs1: [
		  { name: '自有房', price: '1999' },
		  { name: '出租房', price: '2499' },
		],
        /* features: [
          { icon: 'checkbox-mark', text: '五级过滤系统，深度净化水质' },
          { icon: 'checkbox-mark', text: '智能控制，一键操作简单方便' },
          { icon: 'checkbox-mark', text: '超静音设计，运行噪音低至45分贝' },
          { icon: 'checkbox-mark', text: '智能水质监测，实时掌握水质状况' },
          { icon: 'checkbox-mark', text: '滤芯寿命提醒，到期自动通知更换' }
        ],
        params: [
          { label: '产品型号', value: 'SZ-A8' },
          { label: '额定功率', value: '25W' },
          { label: '过滤精度', value: '0.01微米' },
          { label: '适用水压', value: '0.1-0.4MPa' },
          { label: '水流量', value: '1.5L/min' },
          { label: '使用寿命', value: '3-5年' },
          { label: '滤芯寿命', value: '6-12个月' },
          { label: '产品尺寸', value: '25×15×40cm' }
        ], */
        description: '森泽高端家用净水机A8采用先进的RO反渗透技术，能有效去除水中的重金属、细菌、病毒等有害物质，为您家人提供健康安全的饮用水。智能化操作系统，一键启动，简单方便。超静音设计，不打扰您的生活。配备智能水质监测系统，实时掌握水质状况。'
      },
      productImages: [
       /* { image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600', title: '高端家用净水机' },
        { image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600', title: '产品细节展示' },
        { image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600', title: '使用场景' }
     */ ]
    }
  },
  onLoad() {
  	// 获取商品数据
  	this.getShopData();
  },
  methods: {
	getShopData(id){
		  this.$api.getHomeProduct({id:1}).then(res => {
		  	// console.log(res)
		  	if (res.code == 1) {
		  		this.product=res.data;
				
				this.productImages=res.data.images;
				
		  	}else{
		  		this.$refs.paging.complete(false);
		  	}
		  })
	},
    selectSpec(index) {
      this.selectedSpec = index;
      this.product.price = this.product.specs[index].price;
    },
	selectSpec1(index) {
	  this.selectedSpec1 = index;
	},
    addToCart() {
      uni.showToast({
        title: `已添加${this.quantity}台${this.product.specs[this.selectedSpec].name}到购物车`,
        icon: 'none'
      });
    },
    buyNow() {
      uni.showToast({
        title: `正在购买${this.quantity}台${this.product.specs[this.selectedSpec].name}`,
        icon: 'none'
      });
      // 这里可以跳转到订单确认页面
      uni.navigateTo({
        url: '/pages/shop/addinfo'
      });
    },
    goToHome() {
      uni.switchTab({
        url: '/pages/index/index'
      });
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

.swiper-section {
  width: 100%;
  background-color: #fff;
}

.product-header {
  background-color: #ffffff;
  padding: 30rpx;
  margin-bottom: 20rpx;
}

.product-title-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.product-title {
  font-size: 36rpx;
  font-weight: bold;
  color: #333;
  flex: 1;
}

.price-section {
  display: flex;
  align-items: baseline;
  margin-bottom: 15rpx;
}

.price {
  font-size: 48rpx;
  font-weight: bold;
  color: #f56c6c;
}

.original-price {
  font-size: 28rpx;
  color: #999;
  text-decoration: line-through;
  margin-left: 15rpx;
}

.discount {
  font-size: 24rpx;
  color: #fff;
  background-color: #f56c6c;
  padding: 4rpx 10rpx;
  border-radius: 8rpx;
  margin-left: 15rpx;
}

.sales-info {
  font-size: 24rpx;
  color: #999;
  display: flex;
  align-items: center;
}

.divider {
  margin: 0 15rpx;
}

.specs-section, .quantity-section, .detail-section {
  background-color: #ffffff;
  padding: 30rpx;
  margin-bottom: 20rpx;
}

.section-title {
  display: flex;
  align-items: center;
  margin-bottom: 20rpx;
}

.title-text {
  font-size: 28rpx;
  color: #333;
  margin-left: 10rpx;
  font-weight: 500;
}

.specs-options {
  display: flex;
  flex-wrap: wrap;
}

.spec-item {
  padding: 15rpx 30rpx;
  background-color: #f5f7fa;
  border-radius: 8rpx;
  margin-right: 20rpx;
  margin-bottom: 20rpx;
  font-size: 26rpx;
  color: #606266;
  transition: all 0.3s;
}

.spec-selected {
  background-color: #ecf5ff;
  color: #3c9cff;
  border: 1px solid #3c9cff;
}

.quantity-selector {
  display: flex;
  align-items: center;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16rpx;
}

.header-title {
  font-size: 30rpx;
  color: #303133;
  font-weight: 600;
}

.feature-list {
  margin: 30rpx 0;
}

.feature-item {
  display: flex;
  align-items: center;
  margin-bottom: 20rpx;
  font-size: 26rpx;
  color: #606266;
}

.params-list {
  margin: 30rpx 0;
  background-color: #f5f7fa;
  border-radius: 12rpx;
  padding: 20rpx;
}

.param-item {
  display: flex;
  justify-content: space-between;
  padding: 15rpx 0;
  font-size: 26rpx;
  border-bottom: 1px solid #ebeef5;
}

.param-item:last-child {
  border-bottom: none;
}

.param-label {
  color: #909399;
  width: 30%;
}

.param-value {
  color: #606266;
  width: 70%;
  text-align: right;
}

.description {
  margin-top: 30rpx;
}

.desc-text {
  font-size: 26rpx;
  color: #606266;
  line-height: 1.8;
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

.footer-right {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex: 1;
}
.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-right: 40rpx;
}

.action-text {
  font-size: 20rpx;
  color: #606266;
  margin-top: 5rpx;
}

.footer-right {
  display: flex;
  align-items: center;
  gap: 20rpx;
}

/* 辅助类 */
.ml-2 {
  margin-left: 10rpx;
}
</style>