<template>
  <view class="container">
    <!-- 页面标题 -->
    <view class="page-header">
      <view class="header-title">
        <u-icon name="lock" size="28" color="#fff"></u-icon>
        <text class="title-text">修改密码</text>
      </view>
    </view>
    
    <!-- 修改密码表单 -->
    <view class="form-section">
      <u-form :model="this" ref="uForm" :errorType="['message']"> <!-- :model can point to `this` if fields are direct properties -->
        <!-- 原密码 -->
        <u-form-item label="原密码" prop="oldPassword" borderBottom labelWidth="80">
          <u-input 
            v-model="oldPassword"
            type="password" 
            placeholder="请输入原密码" 
            :border="false"
            @input="clearError('oldPassword')"
          ></u-input>
        </u-form-item>
        <view class="error-text" v-if="errorMessages.oldPassword">{{ errorMessages.oldPassword }}</view>
        
        <!-- 新密码 -->
        <u-form-item label="新密码" prop="newPassword" borderBottom labelWidth="80">
          <u-input 
            v-model="newPassword"
            type="password" 
            placeholder="请输入新密码" 
            :border="false"
            @input="clearError('newPassword')"
          ></u-input>
        </u-form-item>
        <view class="error-text" v-if="errorMessages.newPassword">{{ errorMessages.newPassword }}</view>
        
        <!-- 确认新密码 -->
        <u-form-item label="确认新密码" prop="confirmPassword" borderBottom labelWidth="80">
          <u-input 
            v-model="confirmPassword"
            type="password" 
            placeholder="请再次输入新密码" 
            :border="false"
            @input="clearError('confirmPassword')"
          ></u-input>
        </u-form-item>
        <view class="error-text" v-if="errorMessages.confirmPassword">{{ errorMessages.confirmPassword }}</view>
      </u-form>
      
      <!-- 提交按钮 -->
      <view class="submit-section">
        <u-button 
          type="primary" 
          text="确认修改" 
          shape="circle"
          :customStyle="{width: '90%', marginTop: '40rpx'}"
          @click="handleChangePassword"
          :loading="isLoading"
          :disabled="isLoading"
        ></u-button>
      </view>
      
      <!-- 密码规则提示 -->
      <view class="tips-section">
        <view class="tips-title">
          <u-icon name="info-circle" size="16" color="#909399"></u-icon>
          <text class="tips-text">密码规则</text>
        </view>
        <view class="tips-content">
          <text>1. 密码长度为8-20个字符</text>
          <text>2. 必须包含字母和数字</text>
          <text>3. 不能与原密码相同</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import { changeUserPassword } from '../../api/api';

export default {
  data() {
    return {
      title: '修改密码',
      oldPassword: '',
      newPassword: '',
      confirmPassword: '',
      isLoading: false,
      errorMessages: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
      }
      // uView form rules removed, using custom validation
    }
  },
  // onReady() {
    // this.$refs.uForm.setRules(this.rules); // Not needed if not using uView rules prop
  // },
  methods: {
    clearError(fieldName) {
      this.errorMessages[fieldName] = '';
    },
    validateForm() {
      let isValid = true;
      // Clear previous errors
      this.errorMessages = { oldPassword: '', newPassword: '', confirmPassword: '' };

      if (!this.oldPassword) {
        this.errorMessages.oldPassword = '请输入原密码';
        isValid = false;
      }

      if (!this.newPassword) {
        this.errorMessages.newPassword = '请输入新密码';
        isValid = false;
      } else if (this.newPassword.length < 6) { // Example: min length 6
        this.errorMessages.newPassword = '新密码长度至少为6位';
        isValid = false;
      }
      // Add more complex regex validation if needed, e.g., for letters and numbers
      // const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
      // if (this.newPassword && !passwordRegex.test(this.newPassword)) {
      //   this.errorMessages.newPassword = '密码必须包含字母和数字，且长度至少6位';
      //   isValid = false;
      // }


      if (!this.confirmPassword) {
        this.errorMessages.confirmPassword = '请再次输入新密码';
        isValid = false;
      } else if (this.newPassword && this.confirmPassword !== this.newPassword) {
        this.errorMessages.confirmPassword = '两次输入的密码不一致';
        isValid = false;
      }

      return isValid;
    },
    async handleChangePassword() { // Renamed from submitForm
      if (!this.validateForm()) {
        uni.showToast({ title: '请检查输入内容', icon: 'none' });
        return;
      }

      this.isLoading = true;
      try {
        const params = {
          old_password: this.oldPassword,
          new_password: this.newPassword,
          confirm_password: this.confirmPassword // API might just need new_password
        };
        // Adjust params based on actual API spec (e.g. some APIs only need old_password and new_password)

        const res = await changeUserPassword(params); // Direct call
        // Or: const res = await this.$api.changeUserPassword(params);

        this.isLoading = false;

        // Assuming API returns a success message or specific structure on success
        if (res && (res.code === 0 || res.success || res.code === 200)) { // Adjust condition based on API
            uni.showToast({
              title: res.message || res.msg || '密码修改成功，请重新登录',
              icon: 'success',
              duration: 2000
            });
            this.oldPassword = '';
            this.newPassword = '';
            this.confirmPassword = '';
            // Optionally navigate away
            setTimeout(() => {
              // uni.reLaunch({ url: '/pages/login/index' }); // Example: to login
              uni.navigateBack();
            }, 2000);
        } else {
             // Handle cases where API indicates success=false but not via HTTP error
             uni.showToast({ title: res.message || res.msg || '密码修改失败', icon: 'none' });
        }

      } catch (err) {
        this.isLoading = false;
        console.error('Change password error:', err);
        if (err.data && err.data.errors) {
          // Handle field-specific errors from backend
          const backendErrors = err.data.errors;
          for (const key in backendErrors) {
            if (this.errorMessages.hasOwnProperty(key)) {
              this.errorMessages[key] = backendErrors[key][0]; // Take the first error message for the field
            } else if (key === 'password') { // Common backend field name for new password
                 this.errorMessages.newPassword = backendErrors[key][0];
            }
          }
           uni.showToast({ title: err.data.message || '请检查表单字段', icon: 'none' });
        } else if (err.data && (err.data.message || err.data.msg) ) {
          uni.showToast({ title: err.data.message || err.data.msg, icon: 'none', duration: 3000 });
        } else if (err.message) {
            uni.showToast({ title: err.message, icon: 'none', duration: 3000 });
        }
        else {
          uni.showToast({ title: '密码修改失败，请稍后再试', icon: 'none', duration: 3000 });
        }
      }
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

.error-text {
  color: $u-error; // Use uView's error color variable
  font-size: 24rpx;
  margin-top: 4rpx;
  margin-left: 80px; // Align with u-form-item content, adjust if labelWidth changes
  padding-left: 16rpx; // Similar to u-input padding if any
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

.form-section {
  margin: 30rpx 30rpx 0;
  border-radius: 24rpx;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 10;
  padding: 30rpx;
}

.submit-section {
  display: flex;
  justify-content: center;
  margin-top: 40rpx;
}

.tips-section {
  margin-top: 40rpx;
  padding: 20rpx;
  background-color: #f8f9fa;
  border-radius: 12rpx;
}

.tips-title {
  display: flex;
  align-items: center;
  margin-bottom: 10rpx;
}

.tips-text {
  font-size: 26rpx;
  color: #606266;
  margin-left: 8rpx;
  font-weight: 500;
}

.tips-content {
  display: flex;
  flex-direction: column;
  font-size: 24rpx;
  color: #909399;
  line-height: 1.8;
}
</style>