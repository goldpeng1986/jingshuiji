<template>
  <view class="container">
    <!-- 顶部标题区域 -->
    <view class="header-section">
      <view class="header-content">
        <view class="title-container">
          <text class="page-title">详情信息</text>
        </view>
        <view class="header-decoration"></view>
      </view>
    </view>

    <!-- 加载状态 -->
    <view v-if="isLoading" class="loading-state state-container">
      <u-loading-icon text="正在加载公告详情..." size="24"></u-loading-icon>
    </view>

    <!-- 错误状态 -->
    <view v-if="!isLoading && errorLoading" class="error-state state-container">
      <u-empty mode="network" text="加载失败，请检查网络后重试"></u-empty>
      <u-button @click="fetchNoticeDetails" type="primary" size="medium" text="点我重试" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button>
    </view>
    
    <!-- 内容展示: 仅在非加载中、无错误且 noticeInfo 有数据时显示 -->
    <view v-if="!isLoading && !errorLoading && noticeInfo" class="info-container" :style="{animationDelay: '0.1s'}">
      <view class="info-card">
        <view class="info-tag" :style="{backgroundColor: noticeInfo.tagBgColor || '#e5f7ff'}">{{ noticeInfo.type || noticeInfo.category || '通知' }}</view>
        <view class="info-header">
          <view class="info-title">
            <u-icon :name="noticeInfo.icon || 'file-text'" size="24" :color="noticeInfo.iconColor || '#3b82f6'"></u-icon>
            <text class="title-content">{{ noticeInfo.title }}</text>
          </view>
          <view class="info-time">
            <u-icon name="calendar" size="14" color="#909399"></u-icon>
            <text class="time-text">{{ noticeInfo.time || noticeInfo.publish_time || noticeInfo.create_time || 'N/A' }}</text>
          </view>
        </view>
        
        <u-line color="#f3f4f6" margin="20rpx 0"></u-line>
        
        <view class="publisher-info" v-if="noticeInfo.publisher || noticeInfo.author">
          <u-icon name="account" size="14" color="#909399"></u-icon>
          <text class="publisher-text">{{ noticeInfo.publisher || noticeInfo.author }}</text>
        </view>
        
        <view class="info-content">
          <!-- 如果内容可能包含HTML，使用v-html并确保其已净化。纯文本则直接使用： -->
          <text class="content-text">{{ noticeInfo.content }}</text>
        </view>
        
        <!-- 附加信息区域 - 如果数据存在则条件渲染 -->
        <view class="additional-info" v-if="noticeInfo.additionalInfo && noticeInfo.additionalInfo.length > 0">
          <u-line color="#f3f4f6" margin="20rpx 0"></u-line>
          <view class="section-title">附加信息</view>
          <view 
            class="info-item" 
            v-for="(item, index) in noticeInfo.additionalInfo"
            :key="index"
          >
            <view class="info-item-header">
              <u-icon :name="item.icon || 'tags'" size="16" :color="item.iconColor || '#3b82f6'"></u-icon>
              <text class="info-item-title">{{ item.title }}</text>
            </view>
            <view class="info-item-content">
              <text>{{ item.content }}</text>
            </view>
          </view>
        </view>
        
        <!-- 操作按钮 -->
        <view class="action-buttons">
          <u-button 
            type="primary" 
            text="返回" 
            shape="circle" 
            size="medium" 
            @click="goBack"
            :customStyle="{background: 'linear-gradient(135deg, #4a7aff 0%, #1d4ed8 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none', marginRight: '20rpx'}"
          ></u-button>
          <u-button 
            type="info" 
            text="分享" 
            shape="circle" 
            size="medium" 
            icon="share-square"
            @click="shareInfo"
            :customStyle="{background: 'linear-gradient(135deg, #3c9cff 0%, #5cadff 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none'}"
          ></u-button>
        </view>
      </view>
    </view>
    
    <!-- 空状态 - 数据未找到 (API成功加载但无数据) -->
    <view class="empty-state state-container" v-if="!isLoading && !errorLoading && !noticeInfo">
      <u-empty 
        mode="data" 
        icon="http://cdn.uviewui.com/uview/empty/data.png" 
        text="公告不存在或已被删除"
        :customStyle="{padding: '120rpx 0'}"
      ></u-empty>
       <u-button @click="goBack" type="primary" plain text="返回列表" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button>
    </view>
  </view>
</template>

<script>
import { getNoticeInfo } from '../../api/api';
export default {
  data() {
    return {
      title: '详情信息', // 页面标题
      noticeInfo: null, // 存储获取到的公告详情对象
      isLoading: false, // 加载状态标志
      errorLoading: false, // 错误状态标志
      noticeId: null // 从路由参数中获取的公告ID
    }
  },
  onLoad(options) {
    // 首先检查 'id'，因为它是预期的主要参数。
    if (options && options.id) {
      this.noticeId = options.id;
      this.fetchNoticeDetails();
    }
    // 如果 'id' 不存在，则回退到检查 'item'。这可能是一个JSON字符串。
    else if (options && options.item) {
      try {
        const item = JSON.parse(decodeURIComponent(options.item));
        if (item && item.id) {
          this.noticeId = item.id;
          // 可选项: 如果传递了完整的item对象，可以考虑立即显示它，
          // 同时获取最新数据，或者如果最新数据不那么关键，就直接使用它。
          // 本例中，我们将优先获取最新数据。
          // this.noticeInfo = item; // 可用于初始快速显示
          this.fetchNoticeDetails();
        } else {
          console.error('解析后的 item 对象不包含 ID:', item);
          this.handleMissingId(); // 处理ID缺失的情况
        }
      } catch (e) {
        console.error('从 options 解析 item 时发生错误:', e);
        this.handleMissingId(); // 处理ID缺失的情况
      }
    }
    // 如果既没有 'id' 也没有有效的 'item' (包含ID)。
    else {
      console.error('在 options 中未找到公告 ID:', options);
      this.handleMissingId(); // 处理ID缺失的情况
    }
  },
  methods: {
    handleMissingId() {
      this.isLoading = false;
      this.errorLoading = true; // 在模板中显示错误状态
      uni.showToast({
        title: '无效的公告ID',
        icon: 'error',
        duration: 2000
      });
      // 考虑导航回上一页或在UI中显示更持久的错误消息
      // setTimeout(() => uni.navigateBack(), 2000);
    },
    fetchNoticeDetails() {
      if (!this.noticeId) {
        // 此情况理想上应由 onLoad 逻辑捕获，但作为安全措施：
        this.isLoading = false;
        this.errorLoading = true;
        console.error('调用 fetchNoticeDetails 时 noticeId 为空');
        uni.showToast({ title: '公告ID缺失', icon: 'error' });
        return;
      }
      this.isLoading = true;
      this.errorLoading = false;
      this.$api.getNoticeInfo({ id: this.noticeId }) // API请求，传递ID
        .then(res => {
          if (res && res.data) {
            // 假设实际的公告对象在 res.data 中
            // 常见的模式有：res.data 本身，或者嵌套在 res.data.detail, res.data.info 中
            this.noticeInfo = res.data.info || res.data.detail || res.data;
          } else {
            this.noticeInfo = null; // 如果未找到有效数据，则显式设置为 null
            console.warn('未在响应中找到公告数据或响应结构不符合预期:', res);
          }
          this.isLoading = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.errorLoading = true;
          this.noticeInfo = null; // 清除任何可能存在的旧数据
          console.error('获取公告详情时发生错误:', error);
          uni.showToast({
            title: '加载失败，请重试',
            icon: 'none' // 使用 'none' 是因为错误详情已在控制台输出，且UI会显示错误状态
          });
        });
    },
    goBack() {
      uni.navigateBack(); // 返回上一页
    },
    shareInfo() {
      // 分享信息逻辑
      if (this.noticeInfo && this.noticeInfo.title && this.noticeInfo.id) {
        uni.share({
          provider: 'weixin', // 例如：分享到微信
          scene: 'WXSceneSession', // 例如：分享到聊天会话
          type: 0, // 0 表示网页类型
          href: `https://yourdomain.com/notice/${this.noticeInfo.id}`, // 替换为你的实际公告链接
          title: this.noticeInfo.title,
          summary: this.noticeInfo.content ? (this.noticeInfo.content.substring(0, 50) + '...') : '查看公告详情', // 内容摘要
          imageUrl: this.noticeInfo.imageUrl || '', // 可选：如果公告有图片，则添加图片URL
          success: (res) => {
            uni.showToast({ title: '分享成功', icon: 'success' });
          },
          fail: (err) => {
            uni.showToast({ title: '分享取消或失败', icon: 'none' });
            console.error("分享失败:" + JSON.stringify(err));
          }
        });
      } else {
        uni.showToast({
          title: '无法分享，公告信息不完整',
          icon: 'none'
        });
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding-bottom: 30rpx;
}

.state-container { // 加载、错误和特定空状态的通用样式
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 120rpx 30rpx;
  text-align: center;
}

.header-section {
  position: relative;
  height: 150rpx;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 30rpx;
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
  
  .header-content {
    position: relative;
    z-index: 1;
    height: 100%;
    display: flex;
    align-items: center;
  }
  
  .back-btn {
    margin-right: 20rpx;
  }
  
  .title-container {
    display: flex;
    align-items: center;
    gap: 16rpx;
  }
  
  .page-title {
    font-size: 40rpx;
    font-weight: bold;
    color: #ffffff;
  }
  
  .header-decoration {
    position: absolute;
    bottom: -60rpx;
    right: -60rpx;
    width: 200rpx;
    height: 200rpx;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
  }
}

.info-container {
  padding: 30rpx;
  transform: translateY(20rpx);
  opacity: 0;
  animation: fadeInUp 0.5s forwards;
  
  @keyframes fadeInUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
}

.info-card {
  background-color: #ffffff;
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.info-tag {
  position: absolute;
  top: 0;
  right: 0;
  padding: 6rpx 20rpx;
  font-size: 20rpx;
  color: #303133;
  border-bottom-left-radius: 16rpx;
  font-weight: 500;
}

.info-header {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
  margin-bottom: 20rpx;
  padding-top: 10rpx;
  
  .info-title {
    display: flex;
    align-items: center;
    gap: 16rpx;
    
    .title-content {
      font-size: 32rpx;
      font-weight: 600;
      color: #303133;
    }
  }
  
  .info-time {
    display: flex;
    align-items: center;
    gap: 8rpx;
    
    .time-text {
      font-size: 24rpx;
      color: #909399;
    }
  }
}

.publisher-info {
  display: flex;
  align-items: center;
  gap: 8rpx;
  margin-bottom: 20rpx;
  
  .publisher-text {
    font-size: 24rpx;
    color: #909399;
  }
}

.info-content {
  margin: 30rpx 0;
  
  .content-text {
    font-size: 28rpx;
    color: #606266;
    line-height: 1.8;
    white-space: pre-line;
  }
}

.additional-info {
  margin-top: 30rpx;
  
  .section-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #303133;
    margin-bottom: 20rpx;
  }
  
  .info-item {
    background-color: #f8f9fa;
    border-radius: 16rpx;
    padding: 20rpx;
    margin-bottom: 20rpx;
    
    .info-item-header {
      display: flex;
      align-items: center;
      gap: 10rpx;
      margin-bottom: 10rpx;
      
      .info-item-title {
        font-size: 26rpx;
        font-weight: 500;
        color: #303133;
      }
    }
    
    .info-item-content {
      font-size: 26rpx;
      color: #606266;
      padding-left: 34rpx;
    }
  }
}

.action-buttons {
  display: flex;
  justify-content: center;
  margin-top: 40rpx;
}

.empty-state {
  padding: 100rpx 0;
}
</style>