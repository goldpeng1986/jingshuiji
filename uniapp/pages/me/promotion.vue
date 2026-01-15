<template>
  <view class="container">
    <!-- 推广信息卡片 -->
    <view class="promotion-card">
      <view class="promotion-header">
        <view class="promotion-title">
          <text class="title-text">我的推广码</text>
          <text class="subtitle-text">邀请好友注册，共享收益</text>
        </view>
        <view class="promotion-stats">
          <view class="stat-item income">
            <text class="stat-value">{{ promotionInfo.totalIncome }}</text>
            <text class="stat-label">累计收益(元)</text>
          </view>
          <view class="stat-item users">
            <text class="stat-value">{{ promotionInfo.totalUsers }}</text>
            <text class="stat-label">推广用户(人)</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 推广码展示区 -->
    <view class="qrcode-section">
      <view class="qrcode-card">
        <view class="qrcode-container">
          <image class="qrcode-image" :src="promotionInfo.qrcode" mode="aspectFit" v-if="promotionInfo.qrcode"></image>
          <u-loading-icon mode="circle" size="40" v-else></u-loading-icon> <!-- 加载中或无二维码提示 -->
        </view>
        <view class="qrcode-info">
          <text class="promotion-code">推广码：{{ promotionInfo.code }}</text>
          <u-button 
            type="primary" 
            text="复制推广码" 
            shape="circle"
            :customStyle="{width: '240rpx', height: '80rpx', background: 'linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%)', boxShadow: '0 4rpx 12rpx rgba(255, 71, 87, 0.2)', border: 'none'}"
            @click="copyPromotionCode"
            :disabled="!promotionInfo.code || promotionInfo.code === '加载中...'"
          ></u-button>
        </view>
      </view>
    </view>

    <!-- 推广规则 -->
    <view class="rules-section" v-if="(promotionRules && promotionRules.length > 0) || (typeof promotionRules === 'string' && promotionRules !== '')">
      <view class="section-title">
        <u-icon name="info-circle" size="32" color="#ff6b6b"></u-icon>
        <text>推广规则</text>
      </view>
      <view class="rules-content">
        <!-- 如果 promotionRules 是字符串数组 -->
        <template v-if="Array.isArray(promotionRules)">
          <view class="rule-item" v-for="(rule, index) in promotionRules" :key="index">
            <text class="rule-number">{{ index + 1 }}</text>
            <text class="rule-text">{{ rule }}</text>
          </view>
        </template>
         <!-- 如果是单条富文本规则 -->
        <u-parse :content="promotionRules" v-else-if="typeof promotionRules === 'string' && promotionRules.includes('<')"></u-parse>
        <!-- 如果是单条纯文本规则 (非富文本，非数组) -->
         <view class="rule-item" v-else-if="typeof promotionRules === 'string'">
            <text class="rule-text">{{ promotionRules }}</text>
        </view>
      </view>
    </view>

    <!-- 收益记录 -->
    <view class="income-section">
      <view class="section-title">
        <u-icon name="rmb-circle" size="32" color="#ff6b6b"></u-icon>
        <text>收益记录</text>
      </view>
      <view class="income-list">
        <template v-if="incomeRecords.length > 0">
          <view class="income-item" v-for="(item) in incomeRecords" :key="item.id"> <!-- 使用后台返回的id作为key -->
            <view class="income-info">
              <text class="income-type">{{ item.describe || '未知类型' }}</text> <!-- API返回的是describe -->
              <text class="income-time">{{ $u.timeFormat(item.create_time, 'yyyy-mm-dd hh:MM') }}</text> <!-- API返回的是create_time -->
            </view>
            <text class="income-amount" :class="parseFloat(item.money) >= 0 ? 'positive' : 'negative'"> <!-- 金额正负判断 -->
              {{ parseFloat(item.money) >= 0 ? '+' : '' }}{{ parseFloat(item.money).toFixed(2) }}元
            </text>
          </view>
          <u-loadmore :status="loadStatus" @loadmore="loadMoreIncomeRecords" nomoreText="没有更多记录了" />
        </template>
        <template v-else-if="loadStatus !== 'loading'"> <!-- 非加载中状态下显示空状态 -->
          <u-empty mode="list" text="暂无收益记录"></u-empty>
        </template>
         <template v-else> <!-- 加载中状态下可以显示一个骨架屏或简单的loading -->
            <u-loading-icon text="正在努力加载..." mode="circle" size="28"></u-loading-icon>
        </template>
      </view>
    </view>
  </view>
</template>

<script>
import { toast,setClipboardData } from '@/utils/utils.js'; // 引入toast和setClipboardData

export default {
  data() {
    return {
      promotionInfo: { // 推广信息
        code: '加载中...',         // 推广码
        qrcode: '',             // 推广二维码URL
        totalIncome: '0.00',    // 累计总收益
        totalUsers: '0'         // 推广用户总数
      },
      promotionRules: [], // 推广规则 (期望是字符串数组或单个富文本字符串)
      incomeRecords: [],    // 收益记录列表
      page: 1,              // 当前收益记录页码
      limit: 15,             // 每页收益记录数量
      loadStatus: 'loadmore' // 加载状态: 'loadmore', 'loading', 'nomore'
    }
  },
  onLoad() {
    this.fetchPromotionData(); // 获取推广信息和二维码
    this.fetchIncomeRecords(); // 首次加载收益记录
  },
  onReachBottom() { // 页面滚动到底部时触发
    this.loadMoreIncomeRecords();
  },
  methods: {
    // 获取推广核心数据 (码、统计、规则)
    async fetchPromotionData() {
      uni.showLoading({ title: '加载数据中...' });
      try {
        const res = await this.$api.getDealerUserSpead(); // 调用API获取推广信息
        if (res.code === 1 && res.data) {
          const data = res.data;
          this.promotionInfo.code = data.promotion_code || 'N/A';
          this.promotionInfo.totalIncome = parseFloat(data.total_income || 0).toFixed(2);
          this.promotionInfo.totalUsers = data.total_users || '0';

          // 处理推广规则
          if (typeof data.dealer_rule === 'string') {
             if (data.dealer_rule.includes('<p>') || data.dealer_rule.includes('<div>')) {
                this.promotionRules = data.dealer_rule;
             } else {
                this.promotionRules = data.dealer_rule.split('\\n'); // 后端如果是 \n 分割
             }
          } else if (Array.isArray(data.dealer_rule)) {
             this.promotionRules = data.dealer_rule;
          } else {
             this.promotionRules = ['暂无详细规则'];
          }

          this.fetchQrCode(); // 获取二维码
        } else {
          toast(res.msg || '获取推广信息失败');
          uni.hideLoading(); // 如果推广信息失败，也应隐藏loading
        }
      } catch (error) {
        console.error('fetchPromotionData error:', error);
        toast('网络请求推广信息出错');
        uni.hideLoading();
      }
    },
    // 获取推广二维码
    async fetchQrCode() {
      try {
        const qrRes = await this.$api.getDealerQrcode(); // 调用API获取二维码
        if (qrRes.code === 1 && qrRes.data && qrRes.data.qrcode) {
          this.promotionInfo.qrcode = qrRes.data.qrcode;
        } else {
          toast(qrRes.msg || '获取推广二维码失败');
          this.promotionInfo.qrcode = '/static/images/load_error.png'; // 加载失败占位图
        }
      } catch (error) {
        console.error('fetchQrCode error:', error);
        toast('网络请求二维码出错');
        this.promotionInfo.qrcode = '/static/images/load_error.png';
      } finally {
        uni.hideLoading(); // 二维码获取完成后隐藏loading
      }
    },
    // 获取收益记录
    async fetchIncomeRecords(isLoadMore = false) {
      if (isLoadMore && (this.loadStatus === 'loading' || this.loadStatus === 'nomore')) {
        return;
      }
      if (!isLoadMore) {
        this.page = 1;
        this.incomeRecords = [];
        this.loadStatus = 'loadmore';
      }
      this.loadStatus = 'loading';

      try {
        // 假设API返回的数据结构是 { code: 1, data: { data: [], total: N } } (FastAdmin标准分页)
        const res = await this.$api.userSpreadCapitalList({ page: this.page, limit: this.limit });
        if (res.code === 1 && res.data && res.data.data) {
          const records = res.data.data;
          if (records.length > 0) {
            this.incomeRecords = this.incomeRecords.concat(records);
            this.page++;
            this.loadStatus = records.length < this.limit ? 'nomore' : 'loadmore';
          } else {
            this.loadStatus = 'nomore';
          }
        } else {
          toast(res.msg || '获取收益记录失败');
          this.loadStatus = isLoadMore ? 'loadmore' : 'nomore'; //如果是加载更多失败，允许重试，否则说明无数据
        }
      } catch (error) {
        console.error('fetchIncomeRecords error:', error);
        toast('网络请求收益记录出错');
        this.loadStatus = 'loadmore';
      }
    },
    // 加载更多收益记录
    loadMoreIncomeRecords() {
      this.fetchIncomeRecords(true);
    },
    // 复制推广码
    copyPromotionCode() {
      if (this.promotionInfo.code && this.promotionInfo.code !== '加载中...') {
        setClipboardData(this.promotionInfo.code, () => {
          toast('推广码已复制');
        });
      } else {
        toast('推广码正在加载中');
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f8f9fa;
  padding-bottom: 120rpx;
}

.promotion-card {
  background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
  padding: 40rpx 30rpx;
  margin-bottom: 20rpx;
  color: #ffffff;
}

.promotion-header {
  .promotion-title {
    margin-bottom: 30rpx;
    
    .title-text {
      font-size: 36rpx;
      font-weight: bold;
      color: #ffffff;
      display: block;
    }
    
    .subtitle-text {
      font-size: 26rpx;
      color: rgba(255, 255, 255, 0.9);
      margin-top: 10rpx;
      display: block;
    }
  }
}

.promotion-stats {
  display: flex;
  justify-content: space-around;
  margin-top: 20rpx;
  
  .stat-item {
    text-align: center;
    padding: 20rpx 40rpx;
    border-radius: 16rpx;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10rpx);
    
    &.income {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    }
    
    &.users {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.2) 100%);
    }
    
    .stat-value {
      font-size: 40rpx;
      font-weight: bold;
      color: #ffffff;
      display: block;
    }
    
    .stat-label {
      font-size: 24rpx;
      color: rgba(255, 255, 255, 0.9);
      margin-top: 8rpx;
      display: block;
    }
  }
}

.qrcode-section {
  padding: 0 30rpx;
  margin-bottom: 20rpx;
}

.qrcode-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 24rpx;
  padding: 40rpx;
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.08);
  
  .qrcode-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30rpx;
    
    .qrcode-image {
      width: 400rpx;
      height: 400rpx;
      border-radius: 16rpx;
      box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
    }
  }
  
  .qrcode-info {
    text-align: center;
    
    .promotion-code {
      font-size: 32rpx;
      color: #333333;
      font-weight: bold;
      display: block;
      margin-bottom: 20rpx;
    }
  }
}

.rules-section, .income-section {
  margin: 20rpx 30rpx;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.08);
}

.section-title {
  display: flex;
  align-items: center;
  margin-bottom: 30rpx;
  
  text {
    font-size: 32rpx;
    font-weight: bold;
    color: #333333;
    margin-left: 16rpx;
  }
}

.rules-content {
  .rule-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20rpx;
    padding: 16rpx;
    border-radius: 12rpx;
    background: rgba(255, 107, 107, 0.05);
    
    .rule-number {
      width: 40rpx;
      height: 40rpx;
      background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24rpx;
      color: #ffffff;
      margin-right: 20rpx;
      flex-shrink: 0;
    }
    
    .rule-text {
      font-size: 26rpx;
      color: #666666;
      line-height: 1.6;
    }
  }
}

.income-list {
  .income-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx;
    margin-bottom: 10rpx;
    border-radius: 12rpx;
    background: rgba(255, 107, 107, 0.05);
    
    &:last-child {
      margin-bottom: 0;
    }
    
    .income-info {
      .income-type {
        font-size: 28rpx;
        color: #333333;
        display: block;
      }
      
      .income-time {
        font-size: 24rpx;
        color: #999999;
        margin-top: 8rpx;
        display: block;
      }
    }
    
    .income-amount {
      font-size: 32rpx;
      color: #ff4757;
      font-weight: bold;
    }
  }
}
</style> 