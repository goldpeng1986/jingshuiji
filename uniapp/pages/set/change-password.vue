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
export default {
  data() {
    return {
      title: '修改密码',
      form: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
      },
      rules: {
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
          min: 8,
          max: 20,
          message: '密码长度为8-20个字符',
          trigger: ['blur', 'change']
        }, {
          validator: (rule, value, callback) => {
            return this.$u.test.password(value);
          },
          message: '密码必须包含字母和数字',
          trigger: ['blur', 'change']
        }],
        confirmPassword: [{
          required: true,
          message: '请再次输入新密码',
          trigger: ['blur', 'change']
        }, {
          validator: (rule, value, callback) => {
            return value === this.form.newPassword;
          },
          message: '两次输入的密码不一致',
          trigger: ['blur', 'change']
        }]
      }
    }
  },
  onReady() {
    this.$refs.uForm.setRules(this.rules);
  },
  methods: {
    submitForm() {
      this.$refs.uForm.validate(valid => {
        if (valid) {
          // 表单验证通过，执行修改密码操作
          uni.showLoading({
            title: '提交中...'
          });
          
          // 模拟API请求
          setTimeout(() => {
            uni.hideLoading();
            uni.showToast({
              title: '密码修改成功',
              icon: 'success'
            });
            
            // 返回上一页
            setTimeout(() => {
              uni.navigateBack();
            }, 1500);
          }, 2000);
        } else {
          // 表单验证失败
          uni.showToast({
            title: '请完善表单信息',
            icon: 'none'
          });
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