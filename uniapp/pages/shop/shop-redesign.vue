<template>
  <view class="container">
    <!-- 顶部搜索栏 -->
    <view class="search-header">
      <view class="search-bar">
        <u-search
          placeholder="搜索商品"
          v-model="searchKeyword"
          :showAction="false"
          :animation="true"
          :clearabled="true"
          shape="round"
        ></u-search>
      </view>
      <view class="filter-btn">
        <u-icon name="list" size="22" color="#333"></u-icon>
      </view>
    </view>

    <!-- 轮播广告 -->
    <view class="swiper-section">
      <u-swiper
        :list="bannerList"
        keyName="image"
        :height="300"
        :radius="12"
        :interval="3000"
        :effect3d="true"
        :autoplay="true"
        indicatorMode="dot"
      ></u-swiper>
    </view>

    <!-- 分类导航 -->
    <view class="category-nav">
      <view 
        class="category-item" 
        v-for="(item, index) in categories" 
        :key="index"
        @click="selectCategory(item)"
      >
        <view class="category-icon" :style="{backgroundColor: item.bgColor}">
          <u-icon :name="item.icon" size="24" :color="item.iconColor"></u-icon>
        </view>
        <text class="category-name">{{ item.name }}</text>
      </view>
    </view>

    <!-- 热销商品 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">热销商品</text>
        <view class="more-link" @click="viewMore('hot')">
          <text>查看更多</text>
          <u-icon name="arrow-right" size="14" color="#909399"></u-icon>
        </view>
      </view>
      
      <scroll-view scroll-x class="scroll-products">
        <view class="scroll-product-list">
          <view 
            class="product-card-vertical" 
            v-for="(item, index) in hotProducts" 
            :key="index"
            @click="viewProduct(item)"
          >
            <u-image 
              :src="item.image" 
              width="100%" 
              height="240rpx"
              mode="aspectFill"
              :radius="12"
            ></u-image>
            <view class="product-tag" :class="'tag-' + item.tagType">{{ item.tag }}</view>
            <view class="product-info-vertical">
              <text class="product-title-vertical">{{ item.title }}</text>
              <view class="price-row">
                <text class="price">¥{{ item.price }}</text>
                <u-button 
                  type="primary" 
                  size="mini" 
                  shape="circle"
                  @click.stop="buyNow(item)"
                  :customStyle="{height: '50rpx', padding: '0 20rpx'}"
                >购买</u-button>
              </view>
            </view>
          </view>
        </view>
      </scroll-view>
    </view>

    <!-- 新品上市 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">新品上市</text>
        <view class="more-link" @click="viewMore('new')">
          <text>查看更多</text>
          <u-icon name="arrow-right" size="14" color="#909399"></u-icon>
        </view>
      </view>
      
      <!-- 商品列表 -->
      <view class="product-grid">
        <view 
          class="product-card-grid" 
          v-for="(item, index) in newProducts" 
          :key="index"
          @click="viewProduct(item)"
        >
          <view class="product-image-wrapper">
            <u-image 
              :src="item.image" 
              width="100%" 
              height="240rpx"
              mode="aspectFill"
              :radius="12"
            ></u-image>
            <view class="product-tag" :class="'tag-' + item.tagType">{{ item.tag }}</view>
          </view>
          <view class="product-info-grid">
            <text class="product-title-grid">{{ item.title }}</text>
            <text class="product-desc-grid">{{ item.description }}</text>
            <view class="price-row">
              <text class="price">¥{{ item.price }}</text>
              <text class="unit">{{ item.unit }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>

    <!-- 底部导航栏 -->
    <u-tabbar
      :value="1"
      :fixed="true"
      :safeAreaInsetBottom="true"
      :border="true"
      @change="tabbarChange"
    >
      <u-tabbar-item
        text="首页"
        icon="home"
        @click="goToPage('/pages/index/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="商城"
        icon="shopping-cart"
        @click="goToPage('/pages/shop/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="订单"
        icon="file-text"
        @click="goToPage('/pages/order/index')"
      ></u-tabbar-item>
      <u-tabbar-item
        text="我的"
        icon="account"
        @click="goToPage('/pages/me/index')"
      ></u-tabbar-item>
    </u-tabbar>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '商城',
      searchKeyword: '',
      bannerList: [
        {
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          title: '高端净水机限时特惠'
        },
        {
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          title: '新品滤芯上市'
        },
        {
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          title: '618大促销'
        }
      ],
      categories: [
        {
          name: '净水机',
          icon: 'filter',
          bgColor: '#e7f1ff',
          iconColor: '#3c9cff'
        },
        {
          name: '滤芯',
          icon: 'reload',
          bgColor: '#e5f7ef',
          iconColor: '#19be6b'
        },
        {
          name: '配件',
          icon: 'setting',
          bgColor: '#fdf6ec',
          iconColor: '#ff9900'
        },
        {
          name: '服务',
          icon: 'server-fill',
          bgColor: '#fef0f0',
          iconColor: '#fa3534'
        }
      ],
      hotProducts: [
        {
          id: 1,
          title: '高端家用净水机',
          price: '1999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '热销',
          tagType: 'hot'
        },
        {
          id: 2,
          title: '高效复合滤芯',
          price: '299',
          unit: '/个',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '新品',
          tagType: 'new'
        },
        {
          id: 3,
          title: '商用大流量净水机',
          price: '3999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '特惠',
          tagType: 'discount'
        }
      ],
      newProducts: [
        {
          id: 1,
          title: '高端家用净水机',
          description: '五级过滤系统，智能控制，超静音设计',
          price: '1999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '热销',
          tagType: 'hot'
        },
        {
          id: 2,
          title: '高效复合滤芯',
          description: '有效过滤杂质，持久耐用，智能提醒更换',
          price: '299',
          unit: '/个',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '新品',
          tagType: 'new'
        },
        {
          id: 3,
          title: '商用大流量净水机',
          description: '适用于商业场所，大流量供水系统',
          price: '3999',
          unit: '起',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '特惠',
          tagType: 'discount'
        },
        {
          id: 4,
          title: '净水机配件套装',
          description: '管道接头+扳手+密封圈，安装维护必备',
          price: '159',
          unit: '/套',
          image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&h=600',
          tag: '套装',
          tagType: 'package'
        }
      ]
    }
  },
  methods: {
    selectCategory(category) {
      uni.showToast({
        title: `选择分类: ${category.name}`,
        icon: 'none'
      })
    },
    viewMore(type) {
      uni.showToast({
        title: `查看更多${type === 'hot' ? '热销' : '新品'}商品`,
        icon: 'none'
      })
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
  min-height: 100vh;
  background-color: #f8f9fc;
  padding-bottom: 120rpx;
}

.search-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background-color: #ffffff;
  padding: 20rpx 30rpx;
  display: flex;
  align-items: center;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.search-bar {
  flex: 1;
}

.filter-btn {
  width: 80rpx;
  height: 80rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.swiper-section {
  margin: 20rpx 30rpx;
  border-radius: 12rpx;
  overflow: hidden;
  box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
}

.category-nav {
  display: flex;
  justify-content: space-between;
  padding: 20rpx 30rpx 30rpx;
  background-color: #ffffff;
  margin-bottom: 20rpx;
  border-radius: 0 0 24rpx 24rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.03);
}

.category-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 140rpx;
}

.category-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12rpx;
}

.category-name {
  font-size: 24rpx;
  color: #333333;
}

.section {
  margin: 30rpx 0;
  padding: 30rpx;
  background-color: #ffffff;
  border-radius: 24rpx;
  margin-left: 30rpx;
  margin-right: 30rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.03);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  position: relative;
  padding-left: 20rpx;
  
  &::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6rpx;
    bottom: 6rpx;
    width: 6rpx;
    background: linear-gradient(to bottom, #3b82f6, #1d4ed8);
    border-radius: 6rpx;
  }
}

.more-link {
  display: flex;
  align-items: center;
  font-size: 24rpx;
  color: #909399;
}

.scroll-products {
  width: 100%;
  white-space: nowrap;
}

.scroll-product-list {
  display: inline-flex;
  padding: 10rpx 0;
}

.product-card-vertical {
  width: 240rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  margin-right: 20rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  position: relative;
  display: inline-block;
}

.product-tag {
  position: absolute;
  top: 16rpx;
  left: 16rpx;
  padding: 4rpx 12rpx;
  border-radius: 20rpx;
  font-size: 20rpx;
  color: #ffffff;
  font-weight: 500;
  box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.1);
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

.product-info-vertical {
  padding: 16rpx;
}

.product-title-vertical {
  font-size: 26rpx;
  font-weight: 500;
  color: #333333;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12rpx;
}

.price {
  font-size: 28rpx;
  font-weight: bold;
  color: #3b82f6;
}

.unit {
  font-size: 20rpx;
  color: #999999;
  margin-left: 4rpx;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20rpx;
}

.product-card-grid {
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.product-image-wrapper {
  position: relative;
}

.product-info-grid {
  padding: 16rpx;
}

.product-title-grid {
  font-size: 28rpx;
  font-weight: 500;
  color: #333333;
  display: block;
  margin-bottom: 8rpx;
}

.product-desc-grid {
  font-size: 22rpx;
  color: #666666;
  display: block;
  margin-bottom: 12rpx;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>