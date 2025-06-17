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
      <u-form :model="form" ref="uForm" :errorType="['message']">
        <!-- 原密码 -->
        <u-form-item label="原密码" prop="oldPassword" borderBottom labelWidth="50">
          <u-input 
            v-model="form.oldPassword" 
            type="password" 
            placeholder="请输入原密码" 
            :border="false"
          ></u-input>
        </u-form-item>
        
        <!-- 新密码 -->
        <u-form-item label="新密码" prop="newPassword" borderBottom labelWidth="50">
          <u-input 
            v-model="form.newPassword" 
            type="password" 
            placeholder="请输入新密码" 
            :border="false"
          ></u-input>
        </u-form-item>
        
        <!-- 确认新密码 -->
        <u-form-item label="确认新密码" prop="confirmPassword" borderBottom labelWidth="80">
          <u-input 
            v-model="form.confirmPassword" 
            type="password" 
            placeholder="请再次输入新密码" 
            :border="false"
          ></u-input>
        </u-form-item>
      </u-form>
      
      <!-- 提交按钮 -->
      <view class="submit-section">
        <u-button 
          type="primary" 
          text="确认修改" 
          shape="circle"
          :customStyle="{width: '90%', marginTop: '40rpx'}"
          @click="submitForm"
          :loading="isSubmitting"
          :disabled="isSubmitting"
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
import { toast, clearStorageSync } from '@/utils/utils.js'; // 引入toast和clearStorageSync

export default {
  data() {
    return {
      title: '修改密码', // 页面标题
      form: { // 表单数据
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
      },
      isSubmitting: false, // 控制提交按钮的加载状态和禁用
      rules: { // 表单验证规则
        oldPassword: [{
          required: true,
          message: '请输入原密码',
          trigger: ['blur', 'change']
        }],
        newPassword: [{
          required: true,
          message: '请输入新密码',
          trigger: ['blur', 'change']
        }, {
          min: 6, // 根据后端 Auth->changepwd 的实际最小长度调整，通常是6或8
          max: 20,
          message: '密码长度为6-20个字符', // 提示信息也应对应调整
          trigger: ['blur', 'change']
        }, {
          validator: (rule, value, callback) => {
            // 示例：要求包含字母和数字 (可以根据实际安全策略调整)
            // FastAdmin Auth类默认可能不强制此复杂度，但前端可加此校验
            const hasLetter = /[a-zA-Z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            // return hasLetter && hasNumber; // uView validator 返回true或false
            if (value && value.length >=6 && !(hasLetter && hasNumber)) {
                 callback(new Error('密码必须包含字母和数字'));
            } else {
                 callback();
            }
          },
        //   message: '密码必须包含字母和数字', // 此message在callback(new Error())中定义
          trigger: ['blur', 'change']
        }],
        confirmPassword: [{
          required: true,
          message: '请再次输入新密码',
          trigger: ['blur', 'change']
        }, {
          validator: (rule, value, callback) => {
            if (value !== this.form.newPassword) {
              callback(new Error('两次输入的密码不一致'));
            } else {
              callback();
            }
          },
        //   message: '两次输入的密码不一致',
          trigger: ['blur', 'change']
        }]
      }
    }
  },
  onReady() {
    this.$refs.uForm.setRules(this.rules); // 设置表单验证规则
  },
  methods: {
    // 提交表单事件处理
    submitForm() {
      this.$refs.uForm.validate(async valid => { // 将回调改为 async 以便使用 await
        if (valid) {
          this.isSubmitting = true; // 开始提交，设置加载状态
          uni.showLoading({ title: '正在提交...' });

          const params = {
            oldpassword: this.form.oldPassword,
            newpassword: this.form.newPassword,
            // 后端 changePassword API 通常只需要 oldpassword 和 newpassword
            // renewpassword 主要用于前端确认，但如果后端也需要可以传递
            renewpassword: this.form.confirmPassword
          };

          try {
            const res = await this.$api.changePassword(params); // 调用API
            uni.hideLoading();
            this.isSubmitting = false;

            if (res.code === 1) { // 假设code为1表示成功
              toast('密码修改成功，请重新登录', 'success');

              // 清除本地存储的token和用户信息
              clearStorageSync('token'); // 或 uni.removeStorageSync('token')
              clearStorageSync('userInfo'); // 或 uni.removeStorageSync('userInfo')

              // 延时后跳转到登录页
              setTimeout(() => {
                uni.reLaunch({ url: '/pages/me/login' }); // 使用reLaunch确保其他页面栈被清除
              }, 1500);
            } else {
              toast(res.msg || '密码修改失败'); // 显示后端返回的错误信息
            }
          } catch (error) {
            uni.hideLoading();
            this.isSubmitting = false;
            console.error('submitForm error:', error);
            toast('请求失败，请稍后再试');
          }
        } else {
          // 表单验证失败的提示已由uView的Form组件处理
          // uni.showToast({ title: '请检查表单信息', icon: 'none' });
        }
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