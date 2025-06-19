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
      <u-form :model="this" ref="uForm" :errorType="['message']"> <!-- 如果表单字段是 data 中的直接属性，:model 可以指向 this -->
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
<<<<<<< feature/backend-apis-phase1
          @click="submitForm"
          :loading="isSubmitting"
          :disabled="isSubmitting"
=======
          @click="handleChangePassword"
          :loading="isLoading"
          :disabled="isLoading"
>>>>>>> main
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
<<<<<<< feature/backend-apis-phase1
import { toast, clearStorageSync } from '@/utils/utils.js'; // 引入toast和clearStorageSync
=======
import { changeUserPassword } from '../../api/api';
>>>>>>> main

export default {
  data() {
    return {
<<<<<<< feature/backend-apis-phase1
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
=======
      title: '修改密码',
      oldPassword: '',
      newPassword: '',
      confirmPassword: '',
      isLoading: false,
      errorMessages: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
>>>>>>> main
      }
      // uView 的表单规则已移除，改用自定义校验逻辑
    }
  },
<<<<<<< feature/backend-apis-phase1
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
=======
  // onReady() {
    // 如果使用 uView 的 rules 属性进行校验，则在此设置规则
    // this.$refs.uForm.setRules(this.rules);
  // },
  methods: {
    clearError(fieldName) {
      // 清除指定字段的错误信息
      this.errorMessages[fieldName] = '';
    },
    validateForm() {
      let isValid = true;
      // 清除之前的错误信息
      this.errorMessages = { oldPassword: '', newPassword: '', confirmPassword: '' };

      if (!this.oldPassword) {
        this.errorMessages.oldPassword = '请输入原密码';
        isValid = false;
      }

      if (!this.newPassword) {
        this.errorMessages.newPassword = '请输入新密码';
        isValid = false;
      } else if (this.newPassword.length < 6) { // 示例：新密码最小长度为6位
        this.errorMessages.newPassword = '新密码长度至少为6位';
        isValid = false;
      }
      // 如果需要更复杂的正则校验（例如，要求同时包含字母和数字），可以在此添加
      // const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/; // 示例正则：至少6位，含字母和数字
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

      return isValid; // 返回校验结果
    },
    async handleChangePassword() { // 方法名从 submitForm 修改而来
      if (!this.validateForm()) { // 首先进行表单校验
        uni.showToast({ title: '请检查输入内容', icon: 'none' });
        return;
      }

      this.isLoading = true; // 开始加载状态
      try {
        const params = {
          old_password: this.oldPassword,
          new_password: this.newPassword,
          confirm_password: this.confirmPassword // API可能仅需要 new_password，根据实际情况调整
        };
        // 根据实际API规范调整参数 (例如，某些API仅需 old_password 和 new_password)

        const res = await changeUserPassword(params); // 直接调用导入的API函数
        // 或者: const res = await this.$api.changeUserPassword(params); // 如果是通过 this.$api 方式调用

        this.isLoading = false; // 结束加载状态

        // 假设API成功时返回特定结构或状态码
        if (res && (res.code === 0 || res.success || res.code === 200)) { // 根据API实际响应调整成功判断条件
            uni.showToast({
              title: res.message || res.msg || '密码修改成功，请重新登录',
              icon: 'success',
              duration: 2000
            });
            // 清空输入框
            this.oldPassword = '';
            this.newPassword = '';
            this.confirmPassword = '';
            // 可选：延时后导航离开当前页面
            setTimeout(() => {
              // uni.reLaunch({ url: '/pages/login/index' }); // 示例：跳转到登录页
              uni.navigateBack(); // 返回上一页
            }, 2000);
        } else {
             // 处理API返回 success=false 但非HTTP错误的情况
             uni.showToast({ title: res.message || res.msg || '密码修改失败', icon: 'none' });
        }

      } catch (err) {
        this.isLoading = false; // 发生错误时也结束加载状态
        console.error('修改密码时发生错误:', err);
        if (err.data && err.data.errors) {
          // 处理后端返回的字段特定错误
          const backendErrors = err.data.errors;
          for (const key in backendErrors) {
            if (this.errorMessages.hasOwnProperty(key)) {
              this.errorMessages[key] = backendErrors[key][0]; // 取该字段的第一个错误信息
            } else if (key === 'password') { // 后端通用新密码字段名可能为 'password'
                 this.errorMessages.newPassword = backendErrors[key][0];
            }
          }
           uni.showToast({ title: err.data.message || '请检查表单字段', icon: 'none' });
        } else if (err.data && (err.data.message || err.data.msg) ) {
          // 通用错误信息处理（非字段特定）
          uni.showToast({ title: err.data.message || err.data.msg, icon: 'none', duration: 3000 });
        } else if (err.message) {
            // 其他类型的错误（如网络错误但通过catch捕获）
            uni.showToast({ title: err.message, icon: 'none', duration: 3000 });
>>>>>>> main
        }
        else {
          // 未知错误或上述条件均不满足时的通用提示
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