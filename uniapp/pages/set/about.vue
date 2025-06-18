<template>
  <view class="container">
    <!-- Page Loading State -->
    <u-loading-page :loading="isLoading" loading-text="正在加载..."></u-loading-page>

    <!-- Page Header (remains static or can be part of API data) -->
    <view class="page-header" v-if="!isLoading && !errorLoading">
      <view class="header-title">
        <u-icon name="info-circle" size="28" color="#fff"></u-icon>
        <text class="header-title-text">{{ (aboutInfo && aboutInfo.appName) ? '关于 ' + aboutInfo.appName : '关于我们' }}</text>
      </view>
    </view>
    
    <!-- Error State -->
    <view v-if="!isLoading && errorLoading" class="state-container">
      <u-empty mode="network" text="信息加载失败">
        <u-button slot="bottom" type="primary" size="medium" @click="retryFetch" text="点我重试" :customStyle="{marginTop: '20px'}"></u-button>
      </u-empty>
    </view>

    <!-- Not Found State -->
    <view v-if="!isLoading && !errorLoading && !aboutInfo" class="state-container">
      <u-empty mode="data" text="未能获取到相关信息"></u-empty>
    </view>

    <!-- Main Content: Display only if not loading, no error, and aboutInfo is available -->
    <view class="about-section" v-if="!isLoading && !errorLoading && aboutInfo">
      <view class="logo-container">
        <image :src="aboutInfo.logoUrl || '/static/logo.png'" mode="aspectFit" class="app-logo"></image>
        <text class="app-name">{{ aboutInfo.appName || '应用名称' }}</text>
        <text class="app-version">版本 {{ aboutInfo.version || '1.0.0' }}</text>
      </view>
      
      <view class="app-intro" v-if="aboutInfo.description">
        <!-- Use u-parse if description can contain HTML -->
        <u-parse :content="aboutInfo.description" v-if="aboutInfo.description.includes('<') && aboutInfo.description.includes('>')"></u-parse>
        <text class="intro-text" v-else>{{ aboutInfo.description }}</text>
      </view>
      
      <view class="feature-list" v-if="aboutInfo.features && aboutInfo.features.length > 0">
        <view class="section-title">
          <u-icon name="checkmark-circle" size="18" color="#19be6b"></u-icon>
          <text class="title-text">功能特点</text>
        </view>
        <view class="feature-item" v-for="(feature, index) in aboutInfo.features" :key="index">
          <u-icon name="checkbox-mark" size="16" color="#3c9cff"></u-icon>
          <!-- Assuming features is an array of strings. If objects, use feature.text or similar -->
          <text class="feature-text">{{ typeof feature === 'string' ? feature : feature.text }}</text>
        </view>
      </view>
      
      <view class="contact-info">
        <view class="section-title">
          <u-icon name="phone" size="18" color="#3c9cff"></u-icon>
          <text class="title-text">联系与支持</text>
        </view>
        <u-cell-group :border="false">
          <u-cell title="公司名称" :value="aboutInfo.companyName || '未提供'" :isLink="false"></u-cell>
          <u-cell title="官方网站" :value="aboutInfo.websiteUrl || '未提供'" @click="openLink(aboutInfo.websiteUrl)" :isLink="!!aboutInfo.websiteUrl"></u-cell>
          <u-cell title="客服电话" :value="aboutInfo.contactPhone || '未提供'" @click="callService(aboutInfo.contactPhone)" :isLink="!!aboutInfo.contactPhone"></u-cell>
          <u-cell title="联系邮箱" :value="aboutInfo.contactEmail || '未提供'" :isLink="false"></u-cell>
          <u-cell title="公司地址" :value="aboutInfo.companyAddress || '未提供'" :isLink="false" labelRows="3" titleStyle="flex: 0 0 160rpx;"></u-cell>
        </u-cell-group>
      </view>
      
      <view class="copyright" v-if="aboutInfo.copyright">
        <text class="copyright-text">{{ aboutInfo.copyright }}</text>
      </view>
       <view class="copyright" v-else>
        <text class="copyright-text">© {{ new Date().getFullYear() }} {{ aboutInfo.appName || '应用名称' }} 版权所有</text>
      </view>
    </view>
  </view>
</template>

<script>
import { getAboutInfo } from '../../api/api';

export default {
  data() {
    return {
      title: '关于我们', // Can be overridden by API if needed
      isLoading: true,
      errorLoading: false,
      aboutInfo: null,
      // features: [] // This will now come from aboutInfo if available
    }
  },
  onLoad() {
    this.fetchAboutDetails();
  },
  methods: {
    async fetchAboutDetails() {
      this.isLoading = true;
      this.errorLoading = false;
      try {
        // Assuming $api is globally available or getAboutInfo is directly callable
        const res = await getAboutInfo(); // Direct call
        // Or: const res = await this.$api.getAboutInfo();

        if (res && res.data) {
          // Assuming API returns data directly, or nested like res.data.info
          this.aboutInfo = res.data.info || res.data;
          if (this.aboutInfo && this.aboutInfo.appName) { // Example: override page title if appName exists
             uni.setNavigationBarTitle({ title: `关于 ${this.aboutInfo.appName}` });
          }
        } else {
          console.warn('About info not found or in unexpected format:', res);
          this.aboutInfo = null; // Ensure it's null if data is not as expected
        }
      } catch (err) {
        console.error('Error fetching about info:', err);
        this.errorLoading = true;
        this.aboutInfo = null;
        uni.showToast({ title: '信息加载失败', icon: 'none' });
      } finally {
        this.isLoading = false;
      }
    },
    callService(phoneNumber) {
      if (phoneNumber) {
        uni.makePhoneCall({
          phoneNumber: phoneNumber
        });
      } else {
        uni.showToast({ title: '电话号码未提供', icon: 'none' });
      }
    },
    openLink(url) {
      if (url) {
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
          url = 'http://' + url;
        }
        // For web, directly open. For app, consider uni.navigateTo to a webview page or use plus.runtime.openURL
        // #ifdef H5
        window.open(url, '_blank');
        // #endif
        // #ifdef APP-PLUS
        plus.runtime.openURL(url);
        // #endif
        // Fallback for other platforms or if above don't work as expected
        // #ifndef H5 || APP-PLUS
        uni.setClipboardData({
            data: url,
            success: () => {
                uni.showToast({ title: '网址已复制，请在浏览器中打开', icon: 'none' });
            }
        });
        // #endif
      } else {
        uni.showToast({ title: '网址未提供', icon: 'none' });
      }
    },
    retryFetch() {
        this.fetchAboutDetails();
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

.state-container {
  padding: 100rpx 30rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 50vh; // Ensure it takes some space
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