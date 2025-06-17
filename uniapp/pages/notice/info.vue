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
    
    <!-- 详情内容 -->
    <view class="info-container" :style="{animationDelay: '0.1s'}">
      <view class="info-card">
        <view class="info-tag" :style="{backgroundColor: infoData.tagBgColor}">{{ infoData.type }}</view>
        <view class="info-header">
          <view class="info-title">
            <u-icon :name="infoData.icon" size="24" :color="infoData.iconColor"></u-icon>
            <text class="title-content">{{ infoData.title }}</text>
          </view>
          <view class="info-time">
            <u-icon name="calendar" size="14" color="#909399"></u-icon>
            <text class="time-text">{{ infoData.time }}</text>
          </view>
        </view>
        
        <u-line color="#f3f4f6" margin="20rpx 0"></u-line>
        
        <view class="publisher-info">
          <u-icon name="account" size="14" color="#909399"></u-icon>
          <text class="publisher-text">{{ infoData.publisher }}</text>
        </view>
        
        <view class="info-content">
          <text class="content-text">{{ infoData.content }}</text>
        </view>
        
        <!-- 附加信息区域 -->
        <view class="additional-info" v-if="infoData.additionalInfo && infoData.additionalInfo.length > 0">
          <u-line color="#f3f4f6" margin="20rpx 0"></u-line>
          <view class="section-title">附加信息</view>
          <view 
            class="info-item" 
            v-for="(item, index) in infoData.additionalInfo" 
            :key="index"
          >
            <view class="info-item-header">
              <u-icon :name="item.icon" size="16" :color="item.iconColor"></u-icon>
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
            @click="shareInfo"
            :customStyle="{background: 'linear-gradient(135deg, #3c9cff 0%, #5cadff 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none'}"
          ></u-button>
        </view>
      </view>
    </view>
    
    <!-- 空状态 -->
    <view class="empty-state" v-if="!infoData">
      <u-empty 
        mode="data" 
        icon="http://cdn.uviewui.com/uview/empty/data.png" 
        text="暂无详情信息"
        :customStyle="{padding: '120rpx 0'}"
      ></u-empty>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '详情信息',
      infoData: {
        id: 1,
        title: '净水机使用指南',
        content: '尊敬的用户：\n\n感谢您选择森泽共享净水机产品。为了帮助您更好地使用我们的产品，我们特别准备了这份详细的使用指南。\n\n【基本操作】\n1. 开机：轻触屏幕或按下电源键唤醒设备\n2. 选择模式：根据需要选择常温水、热水或冰水\n3. 出水：将水杯放在出水口下方，按下对应按钮即可出水\n4. 停止：水量达到预设值会自动停止，或再次按下按钮手动停止\n\n【注意事项】\n1. 首次使用前，请先冲洗3-5分钟\n2. 定期检查滤芯状态，及时更换\n3. 避免阳光直射和高温环境\n4. 如长时间不使用，请断开电源并排空水箱\n\n【常见问题】\n1. 出水量减少：可能是滤芯堵塞，请检查滤芯状态\n2. 水质异常：请联系客服进行检测和维护\n3. 设备不启动：检查电源连接是否正常\n\n如有任何疑问，请随时联系我们的客服热线：400-888-9999\n\n森泽共享净水机团队\n2023年10月10日',
        time: '2023-10-10 15:30',
        publisher: '产品团队',
        icon: 'file-text',
        iconColor: '#3b82f6',
        type: '使用指南',
        tagType: 'info',
        tagBgColor: '#e5f7ff',
        additionalInfo: [
          {
            title: '适用型号',
            content: 'SZ-100、SZ-200、SZ-300系列',
            icon: 'setting',
            iconColor: '#3b82f6'
          },
          {
            title: '保修信息',
            content: '自购买日起12个月内免费保修（人为损坏除外）',
            icon: 'calendar',
            iconColor: '#f56c6c'
          },
          {
            title: '联系方式',
            content: '客服热线：400-888-9999（工作日 9:00-18:00）',
            icon: 'phone',
            iconColor: '#5ac725'
          }
        ]
      }
    }
  },
  onLoad(options) {
    // 如果有传入ID参数，可以根据ID获取详情
    if (options.id) {
      // 这里可以添加根据ID获取详情的逻辑
      // this.getInfoById(options.id)
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
    shareInfo() {
      uni.showToast({
        title: '分享功能开发中',
        icon: 'none'
      })
    },
    getInfoById(id) {
      // 模拟从服务器获取数据
      // 实际项目中应该通过API请求获取
      console.log('获取ID为', id, '的详情信息')
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