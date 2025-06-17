<template>
  <view class="container">

    <!-- 标签页 -->
    <view class="tabs-container">
      <u-tabs
        :list="tabList.map(tab => ({ ...tab, name: tab.name + '(' + tab.count + ')' }))" <!-- 动态显示数量 -->
        :current="currentTab"
        @change="tabChange"
        :activeStyle="{
          color: '#3b82f6',
          fontWeight: 'bold'
        }"
        :inactiveStyle="{
          color: '#909399',
          fontWeight: 'normal'
        }"
        itemStyle="padding-left: 15px; padding-right: 15px; height: 90rpx;"
        lineColor="#3b82f6"
        :animation="true"
      ></u-tabs>
    </view>

    <!-- 优惠券列表 -->
    <view class="coupon-list">
      <!-- 加载状态提示 -->
      <u-loading-icon v-if="loadingStatus" mode="circle" size="28" text="正在加载"></u-loading-icon>

      <!-- 可使用优惠券 -->
      <template v-if="currentTab === 0">
        <template v-if="!loadingStatus && tabList[0].list.length > 0">
          <view
            class="coupon-item"
            v-for="(item) in tabList[0].list"
            :key="item.id" <!-- 使用唯一ID作为key -->
            @click="useCoupon(item)"
          >
            <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
              <text class="coupon-amount">¥{{ item.amount }}</text>
              <text class="coupon-condition">{{ item.condition }}</text>
            </view>
            <view class="coupon-right">
              <view class="coupon-info">
                <view>
                  <text class="coupon-name">{{ item.name }}</text>
                  <text class="coupon-desc" v-if="item.description">{{ item.description }}</text>
                </view>
                <u-button
                  :type="item.buttonType || 'primary'" <!-- 提供默认buttonType -->
                  size="mini"
                  shape="circle"
                  @click.stop="useCoupon(item)"
                >立即使用</u-button>
              </view>
              <view class="coupon-footer">
                <text class="coupon-validity">有效期至: {{ item.validity }}</text>
                <text class="coupon-source" v-if="item.source">{{ item.source }}</text>
              </view>
            </view>
          </view>
        </template>
        <u-empty v-if="showEmptyState && currentTab === 0" mode="coupon" :text="emptyTips[0]" iconSize="240" textColor="#909399"></u-empty>
      </template>

      <!-- 已使用优惠券 -->
      <template v-if="currentTab === 1">
        <template v-if="!loadingStatus && tabList[1].list.length > 0">
          <view
            class="coupon-item used"
            v-for="(item) in tabList[1].list"
            :key="item.id"
          >
            <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
              <text class="coupon-amount">¥{{ item.amount }}</text>
              <text class="coupon-condition">{{ item.condition }}</text>
            </view>
            <view class="coupon-right">
              <view class="coupon-info">
                <view>
                  <text class="coupon-name">{{ item.name }}</text>
                  <text class="coupon-desc" v-if="item.description">{{ item.description }}</text>
                </view>
                <view class="coupon-used-tag">已使用</view>
              </view>
              <view class="coupon-footer">
                <text class="coupon-validity">使用时间: {{ item.usedTime }}</text>
                <text class="coupon-source" v-if="item.source">{{ item.source }}</text>
              </view>
            </view>
          </view>
        </template>
        <u-empty v-if="showEmptyState && currentTab === 1" mode="coupon" :text="emptyTips[1]" iconSize="240" textColor="#909399"></u-empty>
      </template>

      <!-- 已过期优惠券 -->
      <template v-if="currentTab === 2">
         <template v-if="!loadingStatus && tabList[2].list.length > 0">
            <view
              class="coupon-item expired"
              v-for="(item) in tabList[2].list"
              :key="item.id"
            >
              <view class="coupon-left" :style="{backgroundColor: item.bgColor}">
                <text class="coupon-amount">¥{{ item.amount }}</text>
                <text class="coupon-condition">{{ item.condition }}</text>
              </view>
              <view class="coupon-right">
                <view class="coupon-info">
                  <view>
                    <text class="coupon-name">{{ item.name }}</text>
                    <text class="coupon-desc" v-if="item.description">{{ item.description }}</text>
                  </view>
                  <view class="coupon-expired-tag">已过期</view>
                </view>
                <view class="coupon-footer">
                  <text class="coupon-validity">过期时间: {{ item.expiredTime }}</text>
                  <text class="coupon-source" v-if="item.source">{{ item.source }}</text>
                </view>
              </view>
            </view>
        </template>
        <u-empty v-if="showEmptyState && currentTab === 2" mode="coupon" :text="emptyTips[2]" iconSize="240" textColor="#909399"></u-empty>
      </template>
    </view>
  </view>
</template>

<script>
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '我的优惠券', // 页面标题
      currentTab: 0,    // 当前激活的tab索引
      tabList: [        // Tab标签列表，count会动态更新
        { name: '可使用', count: 0, status: 'available', loaded: false, list: [] }, // status 用于API请求，loaded标记是否已加载过数据
        { name: '已使用', count: 0, status: 'used', loaded: false, list: [] },
        { name: '已过期', count: 0, status: 'expired', loaded: false, list: [] }
      ],
      emptyTips: [ // 各tab下空状态的提示文字
        '暂无可用优惠券',
        '暂无已使用优惠券',
        '暂无已过期优惠券'
      ],
      loadingStatus: false, // 全局加载状态，true表示正在加载数据
      // 分页相关，如果需要可以启用
      // page: 1,
      // limit: 10,
      // loadMoreStatus: 'loadmore', // 'loadmore', 'loading', 'nomore'

      // 优惠券数据将直接存储在tabList的list属性中
      // availableCoupons: [], // 可使用优惠券列表 -> 改为 this.tabList[0].list
      // usedCoupons: [],    // 已使用优惠券列表 -> 改为 this.tabList[1].list
      // expiredCoupons: []  // 已过期优惠券列表 -> 改为 this.tabList[2].list
    }
  },
  computed: {
    // 计算当前tab显示的优惠券列表
    currentCouponList() {
      if (this.tabList[this.currentTab]) {
        return this.tabList[this.currentTab].list;
      }
      return [];
    },
    // 计算当前tab是否为空且非加载中
    showEmptyState() {
      return !this.loadingStatus && this.currentCouponList.length === 0;
    }
  },
  methods: {
    // 当tab切换时调用
    tabChange(index) {
      this.currentTab = typeof index === 'object' ? index.index : index; //兼容uView不同版本事件参数
      this.loadCurrentTabData(); // 加载当前激活tab的数据
    },
    // 加载当前激活Tab的数据
    loadCurrentTabData() {
      const currentTabData = this.tabList[this.currentTab];
      // 如果当前tab数据未加载过，则发起请求
      if (currentTabData && !currentTabData.loaded) {
        this.fetchCoupons(currentTabData.status, this.currentTab);
      }
    },
    // 根据状态获取优惠券列表
    async fetchCoupons(status, tabIndex) {
      this.loadingStatus = true; // 开始加载，设置全局加载状态
      try {
        const res = await this.$api.myCouponList({ status: status }); // 调用API
        if (res.code === 1) {
          let coupons = res.data.data || []; // API返回的数据列表，兼容非分页或分页数据在data.data中

          // 数据适配和格式化
          coupons = coupons.map(item => {
            let couponDetails = item.coupon || {}; // 优惠券主体信息在 coupon 对象下
            return {
              id: item.id, // 用户优惠券ID
              coupon_id: couponDetails.id, // 优惠券ID
              name: couponDetails.name || '未知券名',
              description: couponDetails.description || '', // 假设有描述字段
              amount: parseFloat(couponDetails.result_data && couponDetails.result_data.price || 0).toFixed(2), // 优惠金额
              condition: couponDetails.result_data && couponDetails.result_data.enough ? `满${parseFloat(couponDetails.result_data.enough).toFixed(2)}可用` : '无门槛', // 使用条件
              validity: item.expire_time ? this.$u.timeFormat(item.expire_time, 'yyyy-mm-dd') : '长期有效', // 有效期至
              usedTime: item.usetime ? this.$u.timeFormat(item.usetime, 'yyyy-mm-dd hh:MM') : '-', // 使用时间
              expiredTime: item.expire_time ? this.$u.timeFormat(item.expire_time, 'yyyy-mm-dd') : '-', // 过期时间 (同validity)
              source: couponDetails.store && couponDetails.store.name ? couponDetails.store.name : '平台券', // 来源 (假设有店铺信息)
              // 以下为前端展示用字段，可根据API实际返回或优惠券类型决定
              bgColor: this.getCouponBgColor(status, couponDetails.mode), // 根据状态和类型决定背景色
              buttonType: status === 'available' ? this.getCouponButtonType(couponDetails.mode) : '' // 可用时显示按钮类型
            };
          });

          this.tabList[tabIndex].list = coupons; // 更新对应tab的列表数据
          this.tabList[tabIndex].count = res.data.total || coupons.length; // 更新数量
          this.tabList[tabIndex].loaded = true; // 标记为已加载
        } else {
          toast(res.msg || '获取优惠券失败');
          this.tabList[tabIndex].list = []; // 清空列表确保显示空状态
          this.tabList[tabIndex].count = 0;
        }
      } catch (error) {
        console.error(`fetchCoupons (${status}) error:`, error);
        toast('网络请求优惠券出错');
        this.tabList[tabIndex].list = [];
        this.tabList[tabIndex].count = 0;
      } finally {
        this.loadingStatus = false; // 加载完成
      }
    },
    // 示例：根据优惠券状态和类型获取背景色
    getCouponBgColor(status, couponMode) {
      if (status === 'used' || status === 'expired') {
        return '#9ca3af'; // 已使用或已过期灰色
      }
      // 可用状态下根据优惠券类型（mode）区分颜色
      switch (couponMode) {
        case 'FIXED': return '#f43f5e'; // 例如固定金额红色
        case 'DISCOUNT': return '#3b82f6'; // 例如折扣券蓝色
        default: return '#10b981'; // 其他绿色
      }
    },
    // 示例：根据优惠券类型获取按钮类型
    getCouponButtonType(couponMode) {
      switch (couponMode) {
        case 'FIXED': return 'error';
        case 'DISCOUNT': return 'primary';
        default: return 'success';
      }
    },
    // 使用优惠券的逻辑
    useCoupon(coupon) {
      uni.showToast({
        title: `使用优惠券: ${coupon.name}`,
        icon: 'none'
      });
      // uni.navigateTo({ url: '/pages/goods/list?coupon_id=' + coupon.coupon_id });
    }
  },
  onShow() {
    // 页面显示时，加载当前激活tab的数据，或者可以强制刷新所有tab数据
    this.loadCurrentTabData();
    // 如果希望每次显示都刷新所有数据，可以遍历tabList调用fetchCoupons，但要注意API调用频率
    // this.tabList.forEach((tab, index) => {
    //   this.fetchCoupons(tab.status, index);
    // });
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

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

.tabs-container {
  background-color: #ffffff;
  margin-bottom: 20rpx;
}

.coupon-list {
  padding: 30rpx;
  padding-bottom: 50rpx;
}

.coupon-item {
  display: flex;
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  margin-bottom: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    left: -8rpx;
    top: 50%;
    width: 16rpx;
    height: 16rpx;
    background-color: #f5f7fa;
    border-radius: 50%;
    transform: translateY(-50%);
    z-index: 10;
  }
  
  &::after {
    content: '';
    position: absolute;
    right: -8rpx;
    top: 50%;
    width: 16rpx;
    height: 16rpx;
    background-color: #f5f7fa;
    border-radius: 50%;
    transform: translateY(-50%);
    z-index: 10;
  }
}

.coupon-left {
  width: 25%;
  padding: 30rpx 20rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    right: 0;
    top: 10%;
    bottom: 10%;
    border-right: 2rpx dashed rgba(0, 0, 0, 0.1);
  }
}

.coupon-amount {
  font-size: 48rpx;
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
  padding: 30rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.coupon-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  .u-button{ width: 100px;padding: 10px;}
}

.coupon-name {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  display: block;
}

.coupon-desc {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.coupon-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 20rpx;
  font-size: 22rpx;
  color: #909399;
}

.coupon-used-tag, .coupon-expired-tag {
  padding: 6rpx 16rpx;
  border-radius: 30rpx;
  font-size: 22rpx;
  color: #ffffff;
}

.coupon-used-tag {
  background-color: #9ca3af;
}

.coupon-expired-tag {
  background-color: #9ca3af;
}

.used, .expired {
  filter: grayscale(1);
  opacity: 0.7;
}

.empty-state {
  padding: 100rpx 0;
}
</style>