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
      title: '设置', // 页面标题
      cacheSize: '8.5MB', // 缓存大小的默认值/回退值
      appVersion: 'v1.0.0', // App版本号的默认值/回退值
      settingsInfo: null, // 用于存储从API获取的设置信息
      isLoadingSettings: false, // 是否正在加载设置信息的状态标志
      errorLoadingSettings: false, // 加载设置信息是否出错的状态标志
      showModal: false, // 控制确认弹窗的显示与隐藏
      modalTitle: '', // 弹窗标题
      modalContent: '', // 弹窗内容
      modalType: '' // 弹窗类型，用于区分是 'logout' (退出登录) 还是 'clearCache' (清除缓存)
    }
  },
  onLoad() {
    // 页面加载时获取设置信息
    this.fetchSettings();
  },
  methods: {
    async fetchSettings() {
      this.isLoadingSettings = true;
      this.errorLoadingSettings = false;
      try {
        // 假设 $api 已全局可用，或者可以直接调用 getUserSettings (如果不是通过 $api 方式)
        const res = await getUserSettings(); // 直接调用导入的API函数
        // 或者: const res = await this.$api.getUserSettings(); // 如果是通过 this.$api 方式调用

        if (res && res.data) {
          this.settingsInfo = res.data.settings || res.data; // 根据API响应结构调整，可能需要 res.data.settings
          // 如果API提供了特定字段，则更新本地的回退值
          if (this.settingsInfo && this.settingsInfo.appVersion !== undefined) {
            this.appVersion = this.settingsInfo.appVersion;
          }
          if (this.settingsInfo && this.settingsInfo.cacheSize !== undefined) {
            this.cacheSize = this.settingsInfo.cacheSize;
          }
          // 如果需要，处理其他设置项，例如通知状态
          // e.g., this.notificationEnabled = this.settingsInfo.notificationStatus;
        } else {
          console.warn('用户信息未找到或格式不符合预期:', res);
          // 此处不要将 settingsInfo 设为 null，因为页面的某些部分可能依赖其对象结构
        }
      } catch (err) {
        console.error('获取用户设置时发生错误:', err);
        this.errorLoadingSettings = true;
        // 可选：显示一个toast提示，但对于非关键数据，模板内的内联指示器可能更好
        // uni.showToast({ title: '设置加载失败', icon: 'none' });
      } finally {
        this.isLoadingSettings = false; // 结束加载状态
      }
    },
    goToChangePwd() {
      // 跳转到修改密码页面
      uni.navigateTo({
        url: '/pages/set/change-password'
      });
    },
    goToAbout() {
      // 跳转到关于我们页面
      uni.navigateTo({
        url: '/pages/set/about'
      });
    },
    clearCache() {
      // 准备清除缓存的确认弹窗
      this.modalType = 'clearCache';
      this.modalTitle = '清除缓存';
      this.modalContent = '确定要清除所有缓存吗？';
      this.showModal = true;
    },
    logout() {
      // 准备退出登录的确认弹窗
      this.modalType = 'logout';
      this.modalTitle = '退出登录';
      this.modalContent = '确定要退出登录吗？';
      this.showModal = true;
    },
    confirmModal() {
      // 处理确认弹窗的逻辑
      if (this.modalType === 'clearCache') {
        // 执行清除缓存操作
        uni.showLoading({
          title: '清除中...'
        });
        
        // 模拟清除缓存的过程
        setTimeout(() => {
          uni.hideLoading();
          this.cacheSize = '0KB'; // 更新显示的缓存大小
          // 如果 cacheSize 也由API管理，则此处可能需要重新调用 fetchSettings()
          uni.showToast({
            title: '缓存已清除',
            icon: 'success'
          });
        }, 1500);
      } else if (this.modalType === 'logout') {
        // 执行退出登录操作
      }
      this.showModal = false; // 关闭弹窗
    },
    cancelModal() {
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