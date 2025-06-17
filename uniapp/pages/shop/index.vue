<template>
  <view class="container">
    
    <!-- 商品列表 -->
    <view class="product-list">
      <!-- 商品卡片 -->
      <view 
        v-for="(item, index) in productList" 
        :key="index"
        class="product-card"
        @click="viewProduct(item)"
      >
        <view class="product-image-container">
          <u-image 
            :src="item.image" 
            width="100%" 
            height="100%"
            mode="aspectFill"
            :radius="12"
          ></u-image>
          <view class="tag" :class="'tag-' + item.tagType">
            {{ item.tag }}
          </view>
        </view>
        <view class="product-info">
          <view>
            <text class="product-title">{{ item.title }}</text>
            <text class="product-desc">{{ item.description }}</text>
          </view>
          <view class="product-footer">
            <view class="price-container">
              <text class="price">¥{{ item.price }}</text>
              <text class="unit">{{ item.unit }}</text>
            </view>
            <u-button 
              type="primary" 
              size="mini" 
              shape="circle"
              @click.stop="buyNow(item)"
            >立即购买</u-button>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '商城',
      productList: [
        {
          id: 1,
          title: '高端家用净水机',
          description: '五级过滤系统，智能控制，超静音设计，智能水质监测',
          price: '1999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '热销',
          tagType: 'hot'
        },
        {
          id: 2,
          title: '高效复合滤芯',
          description: '有效过滤杂质，持久耐用，智能提醒更换，安装便捷',
          price: '299',
          unit: '/个',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '新品',
          tagType: 'new'
        },
        {
          id: 3,
          title: '商用大流量净水机',
          description: '适用于商业场所，大流量供水系统，智能运维管理',
          price: '3999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '限时特惠',
          tagType: 'discount'
        },
        {
          id: 4,
          title: '净水机配件套装',
          description: '管道接头+扳手+密封圈，安装维护必备，品质保证',
          price: '159',
          unit: '/套',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '套装优惠',
          tagType: 'package'
        }
      ]
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
    viewProduct(item) {
      uni.showToast({
        title: `查看商品: ${item.title}`,
        icon: 'none'
      })
    },
    buyNow(item) {
      uni.showToast({
        title: `购买商品: ${item.title}`,
        icon: 'none'
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
  min-height: 50vh;
  background-color: #f5f7fa;
  padding-bottom: 120rpx;
}
.header-content {
  display: flex;
  align-items: center;
}

.back-btn {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title {
  font-size: 32rpx;
  font-weight: 500;
  margin-left: 20rpx;
}

.product-list {
  padding: 30rpx;
}
.u-button{
	width: 100px;
	padding: 15px 0px;
}
.product-card {
  display: flex;
  background-color: #ffffff;
  border-radius: 24rpx;
  overflow: hidden;
  margin-bottom: 30rpx;
  box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
  height: 240rpx;
  transition: transform 0.3s;
  
  &:active {
    transform: scale(0.98);
  }
}

.product-image-container {
  position: relative;
  width: 240rpx;
  height: 240rpx;
  overflow: hidden;
}

.tag {
  position: absolute;
  top: 16rpx;
  left: 16rpx;
  padding: 6rpx 16rpx;
  border-radius: 30rpx;
  font-size: 20rpx;
  color: #ffffff;
  font-weight: 500;
  box-shadow: 0 4rpx 8rpx rgba(0, 0, 0, 0.1);
}

.tag-hot {
  background: linear-gradient(to right, #ff4d4d, #f9526a);
}

.tag-new {
  background: linear-gradient(to right, #3b82f6, #1d4ed8);
}

.tag-discount {
  background: linear-gradient(to right, #f59e0b, #d97706);
}

.tag-package {
  background: linear-gradient(to right, #10b981, #059669);
}

.product-info {
  flex: 1;
  padding: 24rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  display: block;
}

.product-desc {
  font-size: 24rpx;
  color: #666666;
  margin-top: 12rpx;
  display: block;
  line-height: 1.5;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16rpx;
}

.price-container {
  display: flex;
  align-items: baseline;
}

.price {
  font-size: 32rpx;
  font-weight: bold;
  color: #3b82f6;
}

.unit {
  font-size: 22rpx;
  color: #999999;
  margin-left: 8rpx;
}
</style>