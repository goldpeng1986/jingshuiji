<template>
  <view class="container">
    <!-- 页面标题 -->
    <view class="page-header">
      <view class="header-title">
        <u-icon name="info-circle" size="28" color="#fff"></u-icon>
        <text class="header-title-text">关于我们</text>
      </view>
    </view>
    
    <!-- 应用信息 -->
    <view class="about-section">
      <!-- 应用Logo -->
      <view class="logo-container">
        <image src="/static/logo.png" mode="aspectFit" class="app-logo"></image>
        <text class="app-name">森泽净水</text>
        <text class="app-version">v1.0.0</text>
      </view>
      
      <!-- 应用介绍 -->
      <view class="app-intro">
        <text class="intro-text">森泽净水是一款专注于提供高品质净水设备和服务的应用，致力于为用户提供健康、安全的饮用水解决方案。我们的产品采用先进的净水技术，能有效去除水中的有害物质，为您和家人的健康保驾护航。</text>
      </view>
      
      <!-- 功能特点 -->
      <view class="feature-list">
        <view class="section-title">
          <u-icon name="checkmark-circle" size="18" color="#19be6b"></u-icon>
          <text class="title-text">功能特点</text>
        </view>
        <view class="feature-item" v-for="(item, index) in features" :key="index">
          <u-icon name="checkbox-mark" size="16" color="#3c9cff"></u-icon>
          <text class="feature-text">{{ item }}</text>
        </view>
      </view>
      
      <!-- 联系我们 -->
      <view class="contact-info">
        <view class="section-title">
          <u-icon name="phone" size="18" color="#3c9cff"></u-icon>
          <text class="title-text">联系我们</text>
        </view>
        <view class="contact-item">
          <text class="contact-label">客服电话：</text>
          <text class="contact-value">400-888-8888</text>
        </view>
        <view class="contact-item">
          <text class="contact-label">客服邮箱：</text>
          <text class="contact-value">service@senze.com</text>
        </view>
        <view class="contact-item">
          <text class="contact-label">公司地址：</text>
          <text class="contact-value">深圳市南山区科技园南区高新南一道9号</text>
        </view>
      </view>
      
      <!-- 版权信息 -->
      <view class="copyright">
        <text class="copyright-text">© 2023 森泽净水 版权所有</text>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '关于我们',
      features: [
        '智能设备管理，远程控制净水设备',
        '水质监测，实时掌握水质状况',
        '滤芯寿命提醒，到期自动通知更换',
        '在线商城，便捷购买滤芯和设备',
        '使用记录，查看历史用水情况',
        '专业客服，提供7*24小时技术支持'
      ]
    }
  },
  methods: {
    // 可以添加拨打电话等功能
    callService() {
      uni.makePhoneCall({
        phoneNumber: '4008888888'
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding-bottom: 40rpx;
}

.page-header {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 60rpx 30rpx 40rpx;
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

.header-title {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.header-title-text {
  font-size: 36rpx;
  font-weight: bold;
  color: #ffffff;
  margin-left: 16rpx;
}

.about-section {
  margin: 30rpx 30rpx 0;
  border-radius: 24rpx;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 10;
  padding: 30rpx;
}

.logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40rpx 0;
}

.app-logo {
  width: 160rpx;
  height: 160rpx;
  margin-bottom: 20rpx;
}

.app-name {
  font-size: 36rpx;
  font-weight: bold;
  color: #333333;
  margin-bottom: 10rpx;
}

.app-version {
  font-size: 24rpx;
  color: #909399;
}

.app-intro {
  padding: 20rpx 0;
  border-bottom: 1px solid #ebeef5;
}

.intro-text {
  font-size: 26rpx;
  color: #606266;
  line-height: 1.8;
  text-align: justify;
}

.feature-list {
  padding: 20rpx 0;
  border-bottom: 1px solid #ebeef5;
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

.feature-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 16rpx;
}

.feature-text {
  font-size: 26rpx;
  color: #606266;
  margin-left: 10rpx;
  line-height: 1.5;
}

.contact-info {
  padding: 20rpx 0;
  border-bottom: 1px solid #ebeef5;
}

.contact-item {
  display: flex;
  margin-bottom: 16rpx;
}

.contact-label {
  font-size: 26rpx;
  color: #909399;
  width: 160rpx;
}

.contact-value {
  font-size: 26rpx;
  color: #606266;
  flex: 1;
}

.copyright {
  padding: 30rpx 0 10rpx;
  display: flex;
  justify-content: center;
}

.copyright-text {
  font-size: 24rpx;
  color: #c0c4cc;
}
</style>