<template>
  <view class="container">
    <!-- 页面标题 -->
    <view class="page-header">
      <view class="header-title">
        <u-icon name="setting" size="28" color="#3c9cff"></u-icon>
        <text class="title-text">设置</text>
      </view>
    </view>
    
    <!-- 设置列表 -->
    <view class="settings-list">
      <u-cell-group>
        <!-- 修改密码 -->
        <u-cell 
          title="修改密码" 
          isLink 
          @click="goToChangePwd"
        >
          <view slot="icon" class="cell-icon" style="background-color: #e7f1ff;">
            <u-icon name="lock" color="#3c9cff" size="36"></u-icon>
          </view>
        </u-cell>
        
        <!-- 清除缓存 -->
        <u-cell 
          title="清除缓存" 
          :value="isLoadingSettings ? '获取中...' : (errorLoadingSettings ? '获取失败' : (settingsInfo && settingsInfo.cacheSize !== undefined ? settingsInfo.cacheSize : cacheSize))"
          @click="clearCache"
        >
          <view slot="icon" class="cell-icon" style="background-color: #fdf6ec;">
            <u-icon name="trash" color="#ff9900" size="36"></u-icon>
          </view>
        </u-cell>
        
        <!-- 关于我们 -->
        <u-cell 
          title="关于我们" 
          isLink
          @click="goToAbout"
        >
          <view slot="icon" class="cell-icon" style="background-color: #e5f7ef;">
            <u-icon name="info-circle" color="#19be6b" size="36"></u-icon>
          </view>
        </u-cell>
        
        <!-- 版本信息 -->
        <u-cell 
          title="版本信息" 
          :value="isLoadingSettings ? '获取中...' : (errorLoadingSettings ? '获取失败' : (settingsInfo && settingsInfo.appVersion !== undefined ? settingsInfo.appVersion : appVersion))"
        >
          <view slot="icon" class="cell-icon" style="background-color: #f3effa;">
            <u-icon name="reload" color="#9a6ee8" size="36"></u-icon>
          </view>
        </u-cell>
      </u-cell-group>
    </view>
    
    <!-- 退出登录按钮 -->
    <view class="logout-section">
      <u-button 
        type="error" 
        text="退出登录" 
        shape="circle"
        :customStyle="{width: '90%', marginTop: '40rpx'}"
        @click="logout"
      ></u-button>
    </view>
    
    <!-- 确认弹窗 -->
    <u-modal
      :show="showModal"
      :title="modalTitle"
      :content="modalContent"
      showCancelButton
      @confirm="confirmModal"
      @cancel="cancelModal"
    ></u-modal>
  </view>
</template>

<script>
import { getUserSettings } from '../../api/api';

export default {
  data() {
    return {
      title: '设置',
      cacheSize: '8.5MB', // Default/fallback value
      appVersion: 'v1.0.0', // Default/fallback value
      settingsInfo: null,
      isLoadingSettings: false,
      errorLoadingSettings: false,
      showModal: false,
      modalTitle: '',
      modalContent: '',
      modalType: '' // 'logout' 或 'clearCache'
    }
  },
  onLoad() {
    this.fetchSettings();
  },
  methods: {
    async fetchSettings() {
      this.isLoadingSettings = true;
      this.errorLoadingSettings = false;
      try {
        // Assuming $api is globally available or getUserSettings is directly callable
        const res = await getUserSettings(); // Direct call
        // Or: const res = await this.$api.getUserSettings();

        if (res && res.data) {
          this.settingsInfo = res.data.settings || res.data; // Adjust if nested
          // Update local fallbacks if API provides these specific fields
          if (this.settingsInfo && this.settingsInfo.appVersion !== undefined) {
            this.appVersion = this.settingsInfo.appVersion;
          }
          if (this.settingsInfo && this.settingsInfo.cacheSize !== undefined) {
            this.cacheSize = this.settingsInfo.cacheSize;
          }
          // Handle other settings like notificationStatus if needed
          // e.g., this.notificationEnabled = this.settingsInfo.notificationStatus;
        } else {
          console.warn('User settings not found or in unexpected format:', res);
          // Do not nullify settingsInfo here if parts of the page depend on its structure being an object
        }
      } catch (err) {
        console.error('Error fetching user settings:', err);
        this.errorLoadingSettings = true;
        // Optionally show a toast, but inline indicators in template are preferred for non-critical data
        // uni.showToast({ title: '设置加载失败', icon: 'none' });
      } finally {
        this.isLoadingSettings = false;
      }
    },
    goToChangePwd() {
      uni.navigateTo({
        url: '/pages/set/change-password'
      });
    },
    goToAbout() {
      uni.navigateTo({
        url: '/pages/set/about'
      });
    },
    clearCache() {
      this.modalType = 'clearCache';
      this.modalTitle = '清除缓存';
      this.modalContent = '确定要清除所有缓存吗？';
      this.showModal = true;
    },
    logout() {
      this.modalType = 'logout';
      this.modalTitle = '退出登录';
      this.modalContent = '确定要退出登录吗？';
      this.showModal = true;
    },
    confirmModal() {
      if (this.modalType === 'clearCache') {
        // 执行清除缓存操作
        uni.showLoading({
          title: '清除中...'
        });
        
        setTimeout(() => {
          uni.hideLoading();
          this.cacheSize = '0KB';
          uni.showToast({
            title: '缓存已清除',
            icon: 'success'
          });
        }, 1500);
      } else if (this.modalType === 'logout') {
        // 执行退出登录操作
        uni.showToast({
          title: '已退出登录',
          icon: 'success'
        });
        
        // 实际应用中应该清除登录状态并跳转到登录页
        // uni.clearStorageSync();
        // uni.reLaunch({
        //   url: '/pages/login/index'
        // });
      }
      this.showModal = false;
    },
    cancelModal() {
      this.showModal = false;
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

.title-text {
  font-size: 36rpx;
  font-weight: bold;
  color: #ffffff;
  margin-left: 16rpx;
}

.settings-list {
  margin: 30rpx 30rpx 0;
  border-radius: 24rpx;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 10;
}

.cell-icon {
  width: 72rpx;
  height: 72rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16rpx;
  margin-right: 20rpx;
}

.logout-section {
  display: flex;
  justify-content: center;
  padding: 20rpx 30rpx;
}
</style>