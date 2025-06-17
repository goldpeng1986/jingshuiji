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
      <u-loading-icon v-if="isLoadingBanners && bannerList.length === 0" mode="circle" size="30"></u-loading-icon>
      <u-swiper
        v-if="bannerList.length > 0"
        :list="bannerList"
        keyName="image"
        previousMargin="30rpx"
        nextMargin="30rpx"
        circular
        :autoplay="true"
        radius="12"
        height="300rpx"
        @click="clickBanner" <!-- 假设有点击banner的事件 -->
      ></u-swiper>
      <u-empty mode="picture" text="暂无广告" v-if="!isLoadingBanners && bannerList.length === 0"></u-empty>
    </view>

    <!-- 分类导航 -->
    <view class="category-nav">
      <u-loading-icon v-if="isLoadingCategories && categories.length === 0" mode="circle" size="28"></u-loading-icon>
      <template v-if="!isLoadingCategories && categories.length > 0">
        <view
          class="category-item"
          v-for="(item) in categories"
          :key="item.id" <!-- 使用API返回的id作为key -->
          @click="selectCategory(item)"
        >
          <view class="category-icon" :style="{backgroundColor: item.bgColor}">
             <!-- 优先使用API返回的图片作为图标 -->
            <u-image v-if="item.image" :src="item.image" width="50rpx" height="50rpx" mode="aspectFit"></u-image>
            <u-icon v-else :name="item.icon" size="24" :color="item.iconColor"></u-icon>
          </view>
          <text class="category-name">{{ item.name }}</text>
        </view>
      </template>
      <u-empty mode="data" text="分类加载失败" v-if="!isLoadingCategories && categories.length === 0"></u-empty>
    </view>

    <!-- 热销商品 -->
    <view class="section" v-if="hotProducts.length > 0 || isLoadingHotProducts">
      <view class="section-header">
        <text class="section-title">热销商品</text>
        <view class="more-link" @click="viewMore('hot')">
          <text>查看更多</text>
          <u-icon name="arrow-right" size="14" color="#909399"></u-icon>
        </view>
      </view>
      <u-loading-icon v-if="isLoadingHotProducts && hotProducts.length === 0" mode="circle" size="28"></u-loading-icon>
      <scroll-view scroll-x class="scroll-products" v-if="hotProducts.length > 0">
        <view class="scroll-product-list">
          <view 
            class="product-card-vertical" 
            v-for="(item) in hotProducts"
            :key="item.id" <!-- 使用API返回的id作为key -->
            @click="viewProduct(item)"
          >
            <u-image 
              :src="item.image" 
              width="100%" 
              height="240rpx"
              mode="aspectFill"
              radius="12"
              lazyLoad="true"
            ></u-image>
            <view class="product-tag" :class="'tag-' + item.tagType" v-if="item.tag">{{ item.tag }}</view>
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
      <!-- <u-empty mode="list" text="暂无热销商品" v-if="!isLoadingHotProducts && hotProducts.length === 0"></u-empty> -->
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
      
      <u-loading-icon v-if="isLoadingNewProducts && newProducts.length === 0 && page === 1" mode="circle" size="28"></u-loading-icon>
      <!-- 商品列表 -->
      <view class="product-grid" v-if="newProducts.length > 0">
        <view 
          class="product-card-grid" 
          v-for="(item) in newProducts"
          :key="item.id" <!-- 使用API返回的id作为key -->
          @click="viewProduct(item)"
        >
          <view class="product-image-wrapper">
            <u-image 
              :src="item.image" 
              width="100%" 
              height="240rpx"
              mode="aspectFill"
              radius="12"
              lazyLoad="true"
            ></u-image>
            <view class="product-tag" :class="'tag-' + item.tagType" v-if="item.tag">{{ item.tag }}</view>
          </view>
          <view class="product-info-grid">
            <text class="product-title-grid">{{ item.title }}</text>
            <text class="product-desc-grid" v-if="item.description">{{ item.description }}</text>
            <view class="price-row">
              <text class="price">¥{{ item.price }}</text>
              <text class="unit" v-if="item.unit">{{ item.unit }}</text>
            </view>
          </view>
        </view>
      </view>
      <u-loadmore :status="loadStatus" @loadmore="fetchNewProducts(true)" v-if="newProducts.length > 0 || loadStatus === 'loading'" />
      <u-empty mode="list" text="暂无新品" v-if="!isLoadingNewProducts && newProducts.length === 0 && loadStatus !== 'loading'"></u-empty>
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
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '商城', // 页面标题
      searchKeyword: '', // 搜索关键词
      bannerList: [],    // Banner列表
      categories: [],    // 分类导航列表
      hotProducts: [],   // 热销商品列表
      newProducts: [],   // 新品上市/主商品列表

      isLoadingBanners: false,    // Banner加载状态
      isLoadingCategories: false, // 分类加载状态
      isLoadingHotProducts: false,// 热销商品加载状态
      isLoadingNewProducts: false,// 新品加载状态

      // 新品分页参数 (如果需要)
      page: 1,
      limit: 10, // 每页加载10条新品
      loadStatus: 'loadmore' // 'loadmore', 'loading', 'nomore'
    };
  },
  onLoad() {
    // 页面加载时获取初始数据
    this.loadInitialData();
  },
  onShow() {
    // 可根据需求决定是否在onShow中也刷新部分或全部数据
  },
  onReachBottom() {
    // 页面滚动到底部时加载更多新品
    this.fetchNewProducts(true);
  },
  methods: {
    // 加载所有初始数据
    loadInitialData() {
      this.fetchBanners();
      this.fetchCategories();
      this.fetchHotProducts();
      this.fetchNewProducts(false); // 首次加载新品
    },
    // 获取Banner数据
    async fetchBanners() {
      this.isLoadingBanners = true;
      try {
        const res = await this.$api.getBlockListByType({ type: 'uniapp_shop_banner' }); // 假设Banner类型为 uniapp_shop_banner
        if (res.code === 1 && res.data) {
          this.bannerList = res.data.map(item => ({
            ...item, // 保留API返回的其他字段，如url, title
            image: item.image // API已处理cdnurl
          }));
        } else {
          toast(res.msg || '获取Banner失败');
          this.bannerList = []; // API失败则清空
        }
      } catch (error) {
        console.error('fetchBanners error:', error);
        toast('网络请求Banner出错');
        this.bannerList = [];
      } finally {
        this.isLoadingBanners = false;
      }
    },
    // 获取分类数据
    async fetchCategories() {
      this.isLoadingCategories = true;
      // 预设分类样式，如果API不返回颜色和图标信息
      const staticCategoryStyles = {
        '净水机': { icon: 'filter', bgColor: '#e7f1ff', iconColor: '#3c9cff' },
        '滤芯': { icon: 'reload', bgColor: '#e5f7ef', iconColor: '#19be6b' },
        '配件': { icon: 'setting', bgColor: '#fdf6ec', iconColor: '#ff9900' },
        '服务': { icon: 'server-fill', bgColor: '#fef0f0', iconColor: '#fa3534' }
      };
      try {
        const res = await this.$api.getCategory(); // 或者 this.$api.allCategory()
        if (res.code === 1 && res.data) {
           // 假设 res.data 是一个数组，每个元素包含 id, name, image (可选)等
          this.categories = res.data.map(item => {
            const style = staticCategoryStyles[item.name] || { icon: 'list-dot', bgColor: '#f0f0f0', iconColor: '#909399' }; // 默认样式
            return {
              id: item.id,
              name: item.name,
              image: item.image ? item.image : null, // 如果API返回图片则使用，否则可能只用图标
              icon: item.icon || style.icon, // 优先使用API返回的图标
              bgColor: item.bgColor || style.bgColor, // 优先使用API返回的背景色
              iconColor: item.iconColor || style.iconColor // 优先使用API返回的图标颜色
            };
          });
        } else {
          toast(res.msg || '获取分类失败');
          this.categories = Object.entries(staticCategoryStyles).map(([name, style]) => ({ id: name, name, ...style })); // API失败则使用静态数据回退
        }
      } catch (error) {
        console.error('fetchCategories error:', error);
        toast('网络请求分类出错');
        this.categories = Object.entries(staticCategoryStyles).map(([name, style]) => ({ id: name, name, ...style }));
      } finally {
        this.isLoadingCategories = false;
      }
    },
    // 获取热销商品数据
    async fetchHotProducts() {
      this.isLoadingHotProducts = true;
      try {
        const res = await this.$api.getGoodsList({ is_hot: 1, limit: 4 }); // 假设 is_hot=1 表示热销
        if (res.code === 1 && res.data && res.data.data) {
          this.hotProducts = res.data.data.map(item => ({
            id: item.id,
            title: item.title,
            price: parseFloat(item.price).toFixed(2),
            image: item.image, // API已处理cdnurl
            tag: item.flag_text || '热销', // 后端可能直接返回 flag_text
            tagType: 'hot' // 前端定义样式用
          }));
        } else {
          toast(res.msg || '获取热销商品失败');
          this.hotProducts = [];
        }
      } catch (error) {
        console.error('fetchHotProducts error:', error);
        toast('网络请求热销商品出错');
        this.hotProducts = [];
      } finally {
        this.isLoadingHotProducts = false;
      }
    },
    // 获取新品/主列表商品数据 (支持分页)
    async fetchNewProducts(isLoadMore = false) {
      if (isLoadMore && (this.loadStatus === 'loading' || this.loadStatus === 'nomore')) {
        return; // 防止重复加载或已无更多时加载
      }
      if (!isLoadMore) {
        this.page = 1;
        this.newProducts = [];
        this.loadStatus = 'loadmore';
      }
      this.isLoadingNewProducts = !isLoadMore; // 首次加载时显示列表的loading状态
      this.loadStatus = 'loading';

      try {
        const res = await this.$api.getGoodsList({ is_new: 1, page: this.page, limit: this.limit }); // 假设 is_new=1 表示新品
        if (res.code === 1 && res.data && res.data.data) {
          const productList = res.data.data.map(item => ({
            id: item.id,
            title: item.title,
            description: item.description || item.keywords || '', // 简短描述
            price: parseFloat(item.price).toFixed(2),
            unit: item.unit || '件', // 商品单位，如果API提供
            image: item.image, // API已处理cdnurl
            tag: item.flag_text || (item.is_new ? '新品' : ''), // 标签文本
            tagType: item.is_new ? 'new' : (item.is_hot ? 'hot' : (item.is_discount ? 'discount' : '')) // 标签类型
          }));

          if (isLoadMore) {
            this.newProducts = this.newProducts.concat(productList);
          } else {
            this.newProducts = productList;
          }

          this.page++;
          this.loadStatus = productList.length < this.limit ? 'nomore' : 'loadmore';
        } else {
          toast(res.msg || '获取新品失败');
          this.loadStatus = isLoadMore ? 'loadmore' : 'nomore';
        }
      } catch (error) {
        console.error('fetchNewProducts error:', error);
        toast('网络请求新品出错');
        this.loadStatus = 'loadmore';
      } finally {
        this.isLoadingNewProducts = false;
      }
    },
    // 跳转到分类商品列表页
    selectCategory(category) {
      uni.navigateTo({
        url: `/pages/shop/list?category_id=${category.id}&title=${category.name}` // 假设列表页用 category_id
      });
    },
    // 查看更多商品 (热销/新品)
    viewMore(type) {
      let params = { type: type };
      if (type === 'hot') params.is_hot = 1;
      if (type === 'new') params.is_new = 1;
      uni.navigateTo({
        url: `/pages/shop/list?${this.$u.queryParams(params)}`
      });
    },
    // 查看商品详情
    viewProduct(item) {
      uni.navigateTo({
        url: `/pages/shop/family?id=${item.id}` // 跳转到商品详情页，传递商品ID
      });
    },
    // 立即购买 (示例，具体逻辑根据业务调整)
    buyNow(item) {
      // 可能跳转到订单确认页或加入购物车等
      uni.navigateTo({
         url: `/pages/shop/addinfo?productId=${item.id}&price=${item.price}&title=${item.title}` // 简化版参数
      });
    },
    tabbarChange(index) {
      // 底部导航栏切换处理，如果当前页不是tab页，则不需要此方法或应调整
      // 此处goToPage已经处理了跳转
    },
    goToPage(url) {
      uni.switchTab({ // 使用switchTab跳转到Tab页
        url: url
      });
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