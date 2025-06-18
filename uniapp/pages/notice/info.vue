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

    <!-- Loading State -->
    <view v-if="isLoading" class="loading-state state-container">
      <u-loading-icon text="正在加载公告详情..." size="24"></u-loading-icon>
    </view>

    <!-- Error State -->
    <view v-if="!isLoading && errorLoading" class="error-state state-container">
      <u-empty mode="network" text="加载失败，请检查网络后重试"></u-empty>
      <u-button @click="fetchNoticeDetails" type="primary" size="medium" text="点我重试" :customStyle="{marginTop: '20rpx', width: '300rpx'}"></u-button>
    </view>
    
    <!-- Content Display: Show only if not loading, no error, and noticeInfo is available -->
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
          <!-- If content can be HTML, use v-html and ensure it's sanitized. For plain text: -->
          <text class="content-text">{{ noticeInfo.content }}</text>
        </view>
        
        <!-- Additional Info Section - Conditionally render if data exists -->
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
    
    <!-- Empty State for No Data Found (after successful load but no data) -->
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
      title: '详情信息',
      noticeInfo: null,
      isLoading: false,
      errorLoading: false,
      noticeId: null
    }
  },
  onLoad(options) {
    // Check for 'id' first, as it's the primary expected parameter.
    if (options && options.id) {
      this.noticeId = options.id;
      this.fetchNoticeDetails();
    }
    // Fallback to 'item' if 'id' is not present. This might be a JSON string.
    else if (options && options.item) {
      try {
        const item = JSON.parse(decodeURIComponent(options.item));
        if (item && item.id) {
          this.noticeId = item.id;
          // Option: If the full item is passed, you might want to display it immediately
          // while fetching fresh data, or just use it directly if fresh data isn't critical.
          // For this example, we'll prioritize fetching fresh data.
          // this.noticeInfo = item; // Could be used for an initial quick display
          this.fetchNoticeDetails();
        } else {
          console.error('Parsed item does not contain an ID:', item);
          this.handleMissingId();
        }
      } catch (e) {
        console.error('Error parsing item from options:', e);
        this.handleMissingId();
      }
    }
    // If neither 'id' nor 'item' (with a valid ID) is found.
    else {
      console.error('Notice ID not found in options:', options);
      this.handleMissingId();
    }
  },
  methods: {
    handleMissingId() {
      this.isLoading = false;
      this.errorLoading = true; // Show an error state in the template
      uni.showToast({
        title: '无效的公告ID',
        icon: 'error',
        duration: 2000
      });
      // Consider navigating back or showing a more permanent error message in the UI
      // setTimeout(() => uni.navigateBack(), 2000);
    },
    fetchNoticeDetails() {
      if (!this.noticeId) {
        // This case should ideally be caught by onLoad logic, but as a safeguard:
        this.isLoading = false;
        this.errorLoading = true;
        console.error('fetchNoticeDetails called without a noticeId.');
        uni.showToast({ title: '公告ID缺失', icon: 'error' });
        return;
      }
      this.isLoading = true;
      this.errorLoading = false;
      this.$api.getNoticeInfo({ id: this.noticeId })
        .then(res => {
          if (res && res.data) {
            // Assuming the actual notice object is within res.data
            // Common patterns: res.data itself, or res.data.detail, res.data.info
            this.noticeInfo = res.data.info || res.data.detail || res.data;
          } else {
            this.noticeInfo = null; // Explicitly set to null if no valid data
            console.warn('Notice data not found in response or unexpected response structure:', res);
          }
          this.isLoading = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.errorLoading = true;
          this.noticeInfo = null; // Clear any potentially stale data
          console.error('Error fetching notice details:', error);
          uni.showToast({
            title: '加载失败，请重试',
            icon: 'none' // Using 'none' as error details are in console and UI will show error state
          });
        });
    },
    goBack() {
      uni.navigateBack();
    },
    shareInfo() {
      if (this.noticeInfo && this.noticeInfo.title && this.noticeInfo.id) {
        uni.share({
          provider: 'weixin', // Example: share to WeChat
          scene: 'WXSceneSession', // Example: share to chat session
          type: 0, // 0 for webpage
          href: `https://yourdomain.com/notice/${this.noticeInfo.id}`, // Replace with your actual notice URL
          title: this.noticeInfo.title,
          summary: this.noticeInfo.content ? (this.noticeInfo.content.substring(0, 50) + '...') : '查看公告详情',
          imageUrl: this.noticeInfo.imageUrl || '', // Optional: Add an image URL if available
          success: (res) => {
            uni.showToast({ title: '分享成功', icon: 'success' });
          },
          fail: (err) => {
            uni.showToast({ title: '分享取消或失败', icon: 'none' });
            console.error("Share failed:" + JSON.stringify(err));
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

.state-container { // Common styles for loading, error, and specific empty states
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