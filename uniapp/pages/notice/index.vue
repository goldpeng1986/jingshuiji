<template>
  <view class="container">
    <!-- 顶部标题区域 -->
    <view class="header-section">
      <view class="header-content">
        <view class="title-container">
          <u-icon name="volume-fill" size="32" color="#ffffff"></u-icon>
          <text class="page-title">通知公告</text>
        </view>
        <view class="header-decoration"></view>
      </view>
    </view>
    
    <!-- 加载状态 -->
    <view v-if="isLoading" class="loading-state">
      <u-loading-icon text="正在加载..." size="24"></u-loading-icon>
    </view>

    <!-- 错误状态 -->
    <view v-if="!isLoading && errorLoading" class="error-state">
      <u-empty mode="network" text="加载失败，请稍后重试"></u-empty>
      <u-button @click="fetchNoticeList" type="primary" size="small" text="重试" :customStyle="{marginTop: '20rpx'}"></u-button>
    </view>

    <!-- 公告列表: 仅在非加载、无错误且列表有数据时显示 -->
    <view class="notice-list" v-if="!isLoading && !errorLoading && noticeList.length > 0">
      <view 
        class="notice-item" 
        v-for="(item, index) in noticeList" 
        :key="item.id || index" <!-- 如果 item.id 存在则使用，否则回退到使用 index -->
        @click="viewNoticeDetail(item)"
        :style="{animationDelay: index * 0.1 + 's'}"
      >
        <view class="notice-card">
          <!-- 如果API响应结构可能变化，为属性使用回退值 -->
          <view class="notice-tag" :style="{backgroundColor: item.tagBgColor || '#fef0f0'}">{{ item.type || item.category || '通知' }}</view>
          <view class="notice-header">
            <view class="notice-title">
              <u-icon :name="item.icon || 'info-circle'" size="24" :color="item.iconColor || '#f56c6c'"></u-icon>
              <text class="title-content">{{ item.title }}</text>
            </view>
            <view class="notice-time">
              <u-icon name="calendar" size="14" color="#909399"></u-icon>
              <text class="time-text">{{ item.time || item.publish_time || item.create_time || 'N/A' }}</text>
            </view>
          </view>
          <view class="notice-content">
            <text class="content-preview">{{ item.content || item.description || item.summary || '暂无内容' }}</text>
          </view>
          <view class="notice-footer">
            <view class="publisher-info">
              <u-icon name="account" size="14" color="#909399"></u-icon>
              <text class="publisher-text">{{ item.publisher || item.author || '管理员' }}</text>
            </view>
            <view class="notice-action">
              <text class="action-text">查看详情</text>
              <u-icon name="arrow-right" size="14" color="#3c9cff"></u-icon>
            </view>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 空状态: 仅在非加载、无错误且列表为空时显示 -->
    <view class="empty-state" v-if="!isLoading && !errorLoading && noticeList.length === 0">
      <u-empty 
        mode="data" 
        icon="http://cdn.uviewui.com/uview/empty/data.png" 
        text="暂无公告信息"
        :customStyle="{padding: '120rpx 0'}"
      ></u-empty>
    </view>
  </view>
</template>

<script>
import { getNoticeList } from '../../api/api';
export default {
  data() {
    return {
      title: '通知公告',
      showDetail: false,
      currentNotice: null,
      noticeList: [],
      isLoading: false,
      errorLoading: false,
    }
  },
  mounted() {
    this.fetchNoticeList();
  },
  methods: {
    fetchNoticeList() {
      this.isLoading = true;
      this.errorLoading = false;
      this.$api.getNoticeList()
        .then(res => {
          let notices = [];
          if (res && res.data) {
            // API响应可能直接是数组，或者在 res.data.list 或 res.data.items 中
            if (Array.isArray(res.data)) {
              notices = res.data;
            } else if (res.data.list && Array.isArray(res.data.list)) {
              notices = res.data.list;
            } else if (res.data.items && Array.isArray(res.data.items)) {
                notices = res.data.items;
            }
             else {
              // 如果API响应的data不是预期格式（例如不是数组），发出警告
              console.warn('API响应 getNoticeList 的数据格式不符合预期或data不是一个数组:', res.data);
            }
          } else {
             // 如果API响应为空或格式不符合预期，发出警告
            console.warn('API响应 getNoticeList 为空或格式不符合预期:', res);
          }
          this.noticeList = notices;
          this.isLoading = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.errorLoading = true;
          console.error('获取通知列表时发生错误:', error);
        });
    },
    viewNoticeDetail(notice) {
      // 示例：uni.$u.route({ url: '/pages/notice/info', params: { id: notice.id } });
      // 如果详情页期望接收完整的 notice 对象，可以像这样传递：
      // uni.navigateTo({ url: '/pages/notice/info?item=' + encodeURIComponent(JSON.stringify(notice)) });
      // 目前，在详情页需求明确前，保留现有的通用导航逻辑。
      uni.$u.route("/pages/notice/info");
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

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
}

.error-state u-button {
  margin-top: 20rpx;
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
    flex-direction: column;
    justify-content: center;
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

.notice-list {
  padding: 30rpx;

  
  .notice-item {
    margin-bottom: 24rpx;
    transform: translateY(20rpx);
    opacity: 0;
    animation: fadeInUp 0.5s forwards;
  }
  
  @keyframes fadeInUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .notice-card {
    background-color: #ffffff;
    border-radius: 24rpx;
    padding: 30rpx;
    box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    
    &:active {
      transform: scale(0.98);
      box-shadow: 0 4rpx 10rpx rgba(0, 0, 0, 0.03);
    }
  }
  
  .notice-tag {
    position: absolute;
    top: 0;
    right: 0;
    padding: 6rpx 20rpx;
    font-size: 20rpx;
    color: #303133;
    border-bottom-left-radius: 16rpx;
    font-weight: 500;
  }
  
  .notice-header {
    display: flex;
    flex-direction: column;
    gap: 16rpx;
    margin-bottom: 20rpx;
    padding-top: 10rpx;
    
    .notice-title {
      display: flex;
      align-items: center;
      gap: 16rpx;
      
      .title-content {
        font-size: 32rpx;
        font-weight: 600;
        color: #303133;
      }
    }
    
    .notice-time {
      display: flex;
      align-items: center;
      gap: 8rpx;
      
      .time-text {
        font-size: 24rpx;
        color: #909399;
      }
    }
  }
  
  .notice-content {
    margin: 20rpx 0;
    
    .content-preview {
      font-size: 28rpx;
      color: #606266;
      line-height: 1.6;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }
  
  .notice-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20rpx;
    
    .publisher-info {
      display: flex;
      align-items: center;
      gap: 8rpx;
      
      .publisher-text {
        font-size: 24rpx;
        color: #909399;
      }
    }
    
    .notice-action {
      display: flex;
      align-items: center;
      gap: 8rpx;
      background-color: #f5f7fa;
      padding: 8rpx 16rpx;
      border-radius: 30rpx;
      
      .action-text {
        font-size: 24rpx;
        color: #3c9cff;
      }
    }
  }
}

.detail-popup {
  padding: 0;
  display: flex;
  flex-direction: column;
  max-height: 80vh;
  
  .detail-header {
    position: relative;
    padding: 40rpx 30rpx 20rpx;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    
    .detail-tag {
      display: inline-block;
      padding: 4rpx 16rpx;
      font-size: 20rpx;
      border-radius: 20rpx;
      margin-bottom: 16rpx;
      color: #303133;
      font-weight: 500;
    }
    
    .detail-title {
      font-size: 36rpx;
      font-weight: 600;
      color: #303133;
      line-height: 1.4;
    }
    
    .close-icon {
      position: absolute;
      top: 30rpx;
      right: 30rpx;
    }
  }
  
  .detail-info {
    display: flex;
    gap: 30rpx;
    padding: 0 30rpx;
    margin-bottom: 20rpx;
    
    .info-item {
      display: flex;
      align-items: center;
      gap: 8rpx;
      
      .info-text {
        font-size: 26rpx;
        color: #909399;
      }
    }
  }
  
  .detail-content-scroll {
    max-height: 50vh;
    padding: 0 30rpx;
  }
  
  .detail-content {
    padding: 20rpx 0;
    
    .content-text {
      font-size: 28rpx;
      color: #606266;
      line-height: 1.8;
      white-space: pre-line;
    }
  }
  
  .detail-footer {
    padding: 30rpx;
    display: flex;
    justify-content: center;
    border-top: 2rpx solid #f3f4f6;
    margin-top: 20rpx;
  }
}

.empty-state {
  padding: 100rpx 0;
}
</style>