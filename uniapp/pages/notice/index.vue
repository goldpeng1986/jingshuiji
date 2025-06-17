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
    
    <!-- 公告列表 -->
    <view class="notice-list">
      <view 
        class="notice-item" 
        v-for="(item, index) in noticeList" 
        :key="index" 
        @click="viewNoticeDetail(item)"
        :style="{animationDelay: index * 0.1 + 's'}"
      >
        <view class="notice-card">
          <view class="notice-tag" :style="{backgroundColor: item.tagBgColor}">{{ item.type }}</view>
          <view class="notice-header">
            <view class="notice-title">
              <u-icon :name="item.icon" size="24" :color="item.iconColor"></u-icon>
              <text class="title-content">{{ item.title }}</text>
            </view>
            <view class="notice-time">
              <u-icon name="calendar" size="14" color="#909399"></u-icon>
              <text class="time-text">{{ item.time }}</text>
            </view>
          </view>
          <view class="notice-content">
            <text class="content-preview">{{ item.content }}</text>
          </view>
          <view class="notice-footer">
            <view class="publisher-info">
              <u-icon name="account" size="14" color="#909399"></u-icon>
              <text class="publisher-text">{{ item.publisher }}</text>
            </view>
            <view class="notice-action">
              <text class="action-text">查看详情</text>
              <u-icon name="arrow-right" size="14" color="#3c9cff"></u-icon>
            </view>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 空状态 -->
    <view class="empty-state" v-if="noticeList.length === 0">
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
export default {
  data() {
    return {
      title: '通知公告',
      showDetail: false,
      currentNotice: null,
      noticeList: [
        {
          id: 1,
          title: '系统维护通知',
          content: '尊敬的用户，我们将于2023年10月15日凌晨2:00-4:00进行系统维护，期间服务可能暂时不可用，给您带来的不便敬请谅解。',
          fullContent: '尊敬的用户：\n\n为了给您提供更好的服务体验，我们将于2023年10月15日凌晨2:00-4:00进行系统维护升级。在此期间，森泽共享净水机APP及相关服务将暂时不可用。\n\n维护内容：\n1. 系统性能优化\n2. 安全性提升\n3. 新功能上线准备\n\n我们建议您提前做好相关安排，避免在维护时段使用我们的服务。系统维护结束后，您可能需要重新登录账号。\n\n感谢您的理解与支持！\n\n森泽共享净水机团队\n2023年10月13日',
          time: '2023-10-13 10:00',
          publisher: '系统管理员',
          icon: 'info-circle',
          iconColor: '#f56c6c',
          type: '重要',
          tagType: 'error',
          tagBgColor: '#fef0f0'
        },
        {
          id: 2,
          title: '新功能上线公告',
          content: '森泽共享净水机APP新版本V2.5.0已上线，新增设备远程控制、水质检测等功能，欢迎更新体验。',
          fullContent: '亲爱的用户：\n\n我们很高兴地通知您，森泽共享净水机APP新版本V2.5.0现已正式上线！此次更新带来了多项实用新功能和体验优化：\n\n【主要更新】\n1. 设备远程控制：现在您可以通过APP远程控制家中的净水设备，随时随地调整设备状态\n2. 水质检测报告：新增水质检测功能，实时监控水质状况，守护家人健康\n3. 消费账单优化：账单明细更加清晰，消费记录一目了然\n4. 性能优化：提升APP运行速度，操作更流畅\n\n建议您立即更新到最新版本，体验全新功能。如在使用过程中遇到任何问题，请通过客服中心与我们联系。\n\n感谢您一直以来对森泽共享净水机的支持！\n\n森泽共享净水机团队\n2023年9月25日',
          time: '2023-09-25 14:30',
          publisher: '产品团队',
          icon: 'checkbox-mark',
          iconColor: '#5ac725',
          type: '新功能',
          tagType: 'success',
          tagBgColor: '#e5f7ef'
        },
        {
          id: 3,
          title: '价格调整通知',
          content: '因原材料成本上涨，自2023年11月1日起，部分净水服务将进行价格调整，详情请查看公告。',
          fullContent: '尊敬的用户：\n\n因原材料成本上涨及运营成本增加，我们经慎重考虑后决定自2023年11月1日起对部分净水服务价格进行调整：\n\n【调整详情】\n1. 家用净水机：\n   - 基础套餐：由原价0.1元/60分钟调整为0.12元/60分钟\n   - 高级套餐：保持不变\n\n2. 商用净水机：\n   - 标准套餐：由原价0.1元/60分钟调整为0.15元/60分钟\n   - 企业套餐：根据具体合同执行\n\n我们始终致力于为您提供高品质的净水服务，此次调整将用于提升产品质量和服务水平。已购买套餐的用户将不受此次调整影响，套餐到期后续费将按新价格执行。\n\n如有任何疑问，请联系客服热线：400-888-9999\n\n感谢您的理解与支持！\n\n森泽共享净水机团队\n2023年10月1日',
          time: '2023-10-01 09:15',
          publisher: '运营团队',
          icon: 'rmb-circle',
          iconColor: '#f9ae3d',
          type: '价格调整',
          tagType: 'warning',
          tagBgColor: '#fdf6ec'
        }
      ]
    }
  },
  methods: {
    viewNoticeDetail(notice) {
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