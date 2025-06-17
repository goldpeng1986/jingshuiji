<template>
  <view class="container">
    <!-- 产品图片轮播 -->
    <view class="swiper-section">
      <u-loading-icon v-if="isLoading && productImages.length === 0" mode="circle" size="30"></u-loading-icon>
      <u-swiper
        v-if="productImages.length > 0"
        :list="productImages"
        keyName="image" <!-- 确保productImages数组中每个对象都有image属性 -->
        :height="uni.upx2px(750)" <!-- 设置高度为屏幕宽度，使其成为正方形 -->
        :interval="3000"
        :autoplay="true"
        indicator
        indicatorMode="dot"
        circular
      ></u-swiper>
      <u-empty mode="picture" text="暂无图片" v-if="!isLoading && productImages.length === 0"></u-empty>
    </view>
    
    <!-- 产品标题和价格 -->
    <view class="product-header">
      <view class="product-title-section">
        <text class="product-title">{{ product.title }}</text>
        <u-tag v-if="product.flag" :text="product.flag" :type="product.flag === '热销' ? 'error' : 'primary'" size="mini" shape="circle"></u-tag>
      </view>
      <view class="price-section">
        <text class="price">¥{{ currentPrice }}</text> <!-- 显示计算后的当前价格 -->
        <text class="original-price" v-if="parseFloat(product.marketprice) > 0">¥{{ product.marketprice }}</text>
        <!-- 动态计算折扣，仅当原价大于现价时显示 -->
        <text class="discount" v-if="parseFloat(product.marketprice) > parseFloat(currentPrice)">
          {{ ((parseFloat(currentPrice) / parseFloat(product.marketprice)) * 10).toFixed(1) }}折
        </text>
      </view>
      <view class="sales-info">
        <text>月销 {{ product.monthlySales || 0 }}+</text>
        <text class="divider">|</text>
        <text>好评率 {{ product.goodRating || 0 }}%</text>
      </view>
    </view>
    
    <!-- 产品规格选择 -->
    <view class="specs-section" v-if="product.specs && product.specs.length > 0">
      <view class="section-title">
        <u-icon name="tags" size="18" color="#3c9cff"></u-icon>
        <text class="title-text">规格选择</text> <!-- 假设一级规格是主要规格 -->
      </view>
      <view class="specs-options">
        <view 
          v-for="(item, index) in product.specs" 
          :key="item.id || index" <!-- 使用规格ID作为key -->
          :class="['spec-item', selectedSpec === index ? 'spec-selected' : '']"
          @click="selectSpec(index)"
        >
          {{ item.name }}
        </view>
      </view>
    </view>
    <view class="specs-section" v-if="product.specs1 && product.specs1.length > 0"> <!-- 假设specs1是二级规格 -->
	  <view class="section-title">
	    <u-icon name="tags" size="18" color="#3c9cff"></u-icon>
	    <text class="title-text">房屋类型</text> <!-- 或其他二级规格名称 -->
	  </view>
	  <view class="specs-options">
	    <view 
	      v-for="(item, index) in product.specs1" 
	      :key="item.id || index" <!-- 使用规格ID作为key -->
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
    <view class="detail-section" v-if="product.description">
      <view class="section-header">
        <view class="header-left">
          <u-icon name="file-text" size="24" color="#3c9cff"></u-icon>
          <text class="header-title">产品详情</text>
        </view>
      </view>
      <u-line color="#f3f4f6" margin="15rpx 0"></u-line>
      
      <!-- 产品描述，使用u-parse解析HTML内容 -->
      <view class="description">
        <u-parse :content="product.description"></u-parse>
      </view>
    </view>
	
	<!-- 底部购买栏 -->
	<view class="footer" v-if="!isLoading"> <!-- 数据加载完成后显示 -->
	  <view class="footer-left">
	      <text class="current-total-price-label">合计：</text>
	      <text class="current-total-price">¥{{ (currentPrice * quantity).toFixed(2) }}</text>
	  </view>
	  <view class="footer-right">
	    <u-button type="primary" text="下一步" size="medium" @click="buyNow" :disabled="isLoading"></u-button>
	  </view>
	</view>
  </view>
  
  
</template>

<script>
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '商品详情', // 页面标题
      productId: null,     // 商品ID
      selectedSpec: 0,   // 当前选中的一级规格索引
      selectedSpec1: 0,  // 当前选中的二级规格索引 (例如房屋类型)
      quantity: 1,       // 当前选中的购买数量
      product: {         // 商品详情对象，将由API填充
        id: null,
        title: '加载中...',
        flag: '', // 例如 'hot', 'new'
        price: '0.00', // 商品基础价格
        marketprice: '0.00',
        monthlySales: 0,
        goodRating: 0,
        specs: [],       // 一级规格定义列表 e.g., [{id: 1, name: '颜色'}, {id:2, name:'尺寸'}]
        specs1: [],      // 二级规格定义列表 (如果有多层规格)
        sku: [],         // SKU列表， e.g., [{id:101, goods_id:1, sku_id:'1_blue,2_xl', price:'100', stock:10}, ...]
        description: '', // 商品详情描述 (HTML或富文本)
        content: ''      // 兼容后端可能用 content 字段存储详情
      },
      productImages: [], // 商品图片轮播列表，格式: [{image: 'url1'}, {image: 'url2'}]
      isLoading: true,   // 加载状态
      currentPrice: '0.00', // 当前选中规格组合的最终价格
      selectedSkuItem: null // 当前选中的完整SKU对象
    };
  },
  onLoad(options) {
    if (options.id) {
      this.productId = options.id;
      this.getShopData(this.productId); // 获取商品数据
    } else {
      toast('商品ID不存在');
      this.isLoading = false;
      // 可以考虑返回上一页
      // uni.navigateBack();
    }
  },
  methods: {
    // 从API获取商品数据
    async getShopData(id) {
      this.isLoading = true;
      try {
        const res = await this.$api.getHomeProduct({ id: id }); // 或 getGoodsInfo
        if (res.code == 1 && res.data) {
          this.product = { // 对product对象进行更安全的赋值
            id: res.data.id,
            title: res.data.title || '商品名称',
            flag: res.data.flag_text || '', // 后端若有flag_text则使用
            price: parseFloat(res.data.price || 0).toFixed(2), // 基础价格
            marketprice: parseFloat(res.data.marketprice || 0).toFixed(2),
            monthlySales: res.data.sales || 0, // 假设月销字段是 sales
            goodRating: res.data.comments_good_count || 0, // 假设好评数字段
            specs: res.data.spec_list || [], // 后端返回的规格定义列表，例如 [{id:1, name:'颜色', list:[{id:10,name:'红'},{id:11,name:'蓝'}]}]
            // specs1: res.data.specs1 || [], // 如果有多层独立规格，否则规格应在spec_list中嵌套或通过sku处理
            sku: res.data.sku_list || [],    // 后端返回的SKU列表，包含价格、库存、及构成该SKU的规格值ID组合
            description: res.data.description || res.data.content || '', // 兼容 description 和 content
            // 其他需要的字段...
          };

          // 初始化选中的规格索引 (默认为每个规格类型的第一个)
          // 这里需要根据后端返回的 spec_list 结构来正确初始化 selectedSpec 和 selectedSpec1
          // 假设 spec_list[0] 是一级规格， spec_list[1] 是二级规格 (如果存在)
          this.selectedSpec = 0; // 默认选中一级规格的第一个选项
          // if (this.product.specs.length > 1 && this.product.specs[1] && this.product.specs[1].list.length > 0) { // 这段逻辑先注释，因为specs1的获取方式不确定
          //    this.selectedSpec1 = 0; // 默认选中二级规格的第一个选项
          // }

          // 处理图片列表，确保是 {image: 'url'} 格式
          if (Array.isArray(res.data.images_list) && res.data.images_list.length > 0) {
             this.productImages = res.data.images_list.map(imgUrl => ({ image: imgUrl }));
          } else if (res.data.image) { // 如果只有单张主图
             this.productImages = [{ image: res.data.image }];
          } else {
             this.productImages = [];
          }

          this.updateCurrentPriceAndSku(); // 初始化或更新当前价格和选中的SKU
        } else {
          toast(res.msg || '获取商品信息失败');
        }
      } catch (error) {
        console.error("getShopData error:", error);
        toast('网络请求商品数据出错');
      } finally {
        this.isLoading = false;
      }
    },
    // 选择一级规格
    selectSpec(index) { // 此方法需要重构以适应新的规格数据结构
      // 假设 this.product.specs 是 [{id:1, name:'颜色', list:[{id:10,name:'红'},{id:11,name:'蓝'}]}, ...]
      // 并且 selectedSpec 是一个对象，如 {1: 0, 2: 0} (规格组ID: 选中项在list中的索引)
      // 或者，如果 specs 是扁平的，则旧逻辑可能部分适用
      // 为简化，我们假设 selectSpec(index) 对应旧的 this.product.specs (一级规格)
      // selectSpec1(index) 对应旧的 this.product.specs1 (二级规格)
      // **实际项目中，规格选择逻辑会更复杂，需要正确地更新已选规格值，然后调用 updateCurrentPriceAndSku**
      this.selectedSpec = index; // 这是选中一级规格的 *索引*
      this.updateCurrentPriceAndSku();
    },
    // 选择二级规格 (如果存在)
	selectSpec1(index) {
	  this.selectedSpec1 = index; // 这是选中二级规格的 *索引*
      this.updateCurrentPriceAndSku();
	},
    // 更新当前显示的价格和选中的SKU (基于选中的规格)
    updateCurrentPriceAndSku() {
        let price = parseFloat(this.product.price); // 默认使用商品基础价格
        this.selectedSkuItem = null; // 重置选中的SKU

        // 获取当前选中的规格值ID们
        // 这一步非常关键，需要根据 this.product.specs 的实际结构来获取
        // 假设 this.product.specs (来自后端的spec_list) 是这样的结构：
        // [
        //   {id: 1, name: '颜色', list: [{id:10, name:'红'}, {id:11, name:'蓝'}]},  // 一级规格 (selectedSpec 对应此list的索引)
        //   {id: 2, name: '尺寸', list: [{id:20, name:'S'}, {id:21, name:'M'}]}  // 二级规格 (selectedSpec1 对应此list的索引)
        // ]
        let selectedSpecValueIds = [];
        if (this.product.specs && this.product.specs[0] && this.product.specs[0].list && this.product.specs[0].list[this.selectedSpec]) {
            selectedSpecValueIds.push(this.product.specs[0].list[this.selectedSpec].id);
        }
        // 注意：如果specs1是独立的第二层规格，而不是specs数组的第二个元素，这里的逻辑需要调整
        // 假设specs1是独立的，或者 specs 数组包含多个规格组
        // if (this.product.specs1 && this.product.specs1[this.selectedSpec1]) { // 旧的specs1逻辑
        //    selectedSpecValueIds.push(this.product.specs1[this.selectedSpec1].id);
        // }
        // 对应新的 this.product.specs 结构 (假设第二组规格在 specs[1])
         if (this.product.specs && this.product.specs.length > 1 && this.product.specs[1] && this.product.specs[1].list && this.product.specs[1].list[this.selectedSpec1]) {
             selectedSpecValueIds.push(this.product.specs[1].list[this.selectedSpec1].id);
         }


        if (selectedSpecValueIds.length > 0 && this.product.sku && this.product.sku.length > 0) {
            selectedSpecValueIds.sort((a,b) => a - b); // 将规格值ID排序，以匹配后端生成的 goods_sku_id (通常是 "ID小,ID大")
            const currentSelectedSkuKey = selectedSpecValueIds.join(','); // 例如 "10,20"

            const matchedSku = this.product.sku.find(s => {
                // s.sku_id 应该是后端组合好的规格值ID字符串，例如 "10,20"
                return s.sku_id === currentSelectedSkuKey;
            });

            if (matchedSku) {
                price = parseFloat(matchedSku.price);
                this.selectedSkuItem = matchedSku; // 存储当前选中的SKU对象
            } else {
                // 没有匹配的SKU，可能该组合不可用，或价格就是基础价
                // console.warn('No matching SKU for selected spec values: ' + currentSelectedSkuKey);
                // 此时 selectedSkuItem 保持 null，购买时可能需要提示用户选择有效规格组合
            }
        }
        // 如果没有SKU逻辑（例如商品无规格或规格不影响价格），价格将保持为product.price
        this.currentPrice = price.toFixed(2);
    },
    // 立即购买
    buyNow() {
      // 获取选中的规格名称用于显示，如果 product.specs 是分组的
      let specName = '';
      if (this.product.specs && this.product.specs[0] && this.product.specs[0].list && this.product.specs[0].list[this.selectedSpec]){
          specName = this.product.specs[0].list[this.selectedSpec].name;
      }
      let spec1Name = ''; // 二级规格名
       if (this.product.specs && this.product.specs.length > 1 && this.product.specs[1] && this.product.specs[1].list && this.product.specs[1].list[this.selectedSpec1]){
          spec1Name = this.product.specs[1].list[this.selectedSpec1].name;
      }

      // 准备传递给addinfo页面的参数
      const params = {
        productId: this.product.id,
        productName: this.product.title,
        goods_sku_id: this.selectedSkuItem ? this.selectedSkuItem.id : null, // 传递SKU ID (fa_shop_goods_sku.id)
        selectedSpecName: specName,
        selectedSpec1Name: spec1Name, // 如果有二级规格，则传递
        quantity: this.quantity,
        price: this.currentPrice,
        image: this.productImages.length > 0 ? this.productImages[0].image : '' // 商品主图
      };

      // 跳转到订单信息填写页面
      uni.navigateTo({
        url: `/pages/shop/addinfo?${this.$u.queryParams(params)}`
      });
    },
    // goToHome() { // 如果需要返回首页的按钮
    //   uni.switchTab({
    //     url: '/pages/index/index'
    //   });
    // }
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