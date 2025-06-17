<template>
  <view class="container">
   
    <!-- 登录标题 -->
    <view class="login-title">
      <view class="title-icon">
        <u-icon name="home" color="#3c9cff" size="40"></u-icon>
      </view>
      <view class="title-content">
        <text class="main-title">欢迎登录</text>
        <text class="sub-title">森泽共享净水机</text>
      </view>
    </view>
    
    <!-- 登录表单 -->
    <view class="login-form">
      <!-- 手机号输入 -->
      <view class="form-item">
        <view class="input-container">
          <u-icon name="phone" size="30" color="#3c9cff"></u-icon>
          <u-input
            v-model="form.phone"
            type="number"
            placeholder="请输入手机号"
            border="none"
            :clearable="true"
            maxlength="11"
            fontSize="28rpx"
            height="70rpx"
          ></u-input>
        </view>
      </view>
      
      <!-- 验证码输入 -->
      <view class="form-item">
        <view class="input-container">
          <u-icon name="lock" size="30" color="#3c9cff"></u-icon>
          <u-input
            v-model="form.code"
            type="number"
            placeholder="请输入验证码"
            border="none"
            :clearable="true"
            maxlength="6"
            fontSize="28rpx"
            height="70rpx"
          ></u-input>
          <view class="code-btn" @click="getVerifyCode">
            <text :class="{disabled: codeBtnDisabled}">{{ codeBtnText }}</text>
          </view>
        </view>
      </view>
      
      <!-- 协议勾选 -->
      <view class="agreement-section">
        <u-checkbox-group v-model="checkboxValue" @change="checkboxChange">
          <u-checkbox
            :name="1"
            shape="circle"
            activeColor="#3c9cff"
          ></u-checkbox>
        </u-checkbox-group>
        <view class="agreement-text">
          <text>我已阅读并同意</text>
          <text class="link" @click="openAgreement('service')">《服务协议》</text>
          <text>和</text>
          <text class="link" @click="openAgreement('privacy')">《隐私政策》</text>
        </view>
      </view>
      
      <!-- 登录按钮 -->
      <u-button 
        type="primary" 
        shape="circle"
        :customStyle="{background: 'linear-gradient(135deg, #4a7aff 0%, #1d4ed8 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none', marginTop: '40rpx'}"
        @click="handleLogin"
        :disabled="!form.agreement || !form.phone || !form.code"
      >
        <view class="btn-content">
          <text>登录</text>
        </view>
      </u-button>
      
      <!-- 微信登录 -->
      <view class="wechat-login-section">
        <view class="divider">
          <view class="line"></view>
          <text class="divider-text">或</text>
          <view class="line"></view>
        </view>
        <view class="wechat-btn" @click="handleWechatLogin">
          <u-icon name="weixin-fill" size="40" color="#07c160"></u-icon>
          <text>微信快捷登录</text>
        </view>
        <button open-type="getPhoneNumber" @getphonenumber="onGetPhoneNumber">唤起授权</button>
      </view>
    </view>
  </view>
</template>

<script>
import { Jump } from '../../utils/utils';
import { login } from '@/api/api.js';

export default {
  data() {
    return {
      form: {
        phone: '',
        code: '',
        agreement: false,
        invite_id: ''
      },
      checkboxValue: [],
      codeBtnText: '获取验证码',
      codeBtnDisabled: false,
      countdown: 60
    }
  },
  methods: {
    // 获取验证码
    getVerifyCode() {
      if (this.codeBtnDisabled) return;
      
      // 验证手机号
      if (!this.form.phone || this.form.phone.length !== 11) {
        uni.showToast({
          title: '请输入正确的手机号',
          icon: 'none'
        });
        return;
      }
      
      // 发送验证码请求
      // 这里应该调用发送验证码的API
      uni.showLoading({
        title: '发送中...'
      });
      this.$api.sendSms({
		  mobile:this.form.phone
	  }).then(res => {
		 uni.showToast(res.msg)
		});
    },
    
    // 开始倒计时
    startCountdown() {
      this.codeBtnDisabled = true;
      this.countdown = 60;
      this.codeBtnText = `${this.countdown}秒后重新获取`;
      
      const timer = setInterval(() => {
        this.countdown--;
        this.codeBtnText = `${this.countdown}秒后重新获取`;
        
        if (this.countdown <= 0) {
          clearInterval(timer);
          this.codeBtnDisabled = false;
          this.codeBtnText = '获取验证码';
        }
      }, 1000);
    },
    
    // 协议勾选变化
    checkboxChange(e) {
      this.form.agreement = e.length > 0;
    },
    
    // 打开协议
    openAgreement(type) {
      let url = '';
      if (type === 'service') {
        url = '/pages/agreement/service';
      } else if (type === 'privacy') {
        url = '/pages/agreement/privacy';
      }
      
      uni.navigateTo({
        url: url
      });
    },
    
    // 处理登录
    handleLogin() {
      if (!this.form.phone || this.form.phone.length !== 11) {
        uni.showToast({
          title: '请输入正确的手机号',
          icon: 'none'
        });
        return;
      }
      
      if (!this.form.code || this.form.code.length !== 6) {
        uni.showToast({
          title: '请输入正确的验证码',
          icon: 'none'
        });
        return;
      }
      
      if (!this.form.agreement) {
        uni.showToast({
          title: '请阅读并同意协议',
          icon: 'none'
        });
        return;
      }
      
      // 调用登录API
      uni.showLoading({
        title: '登录中...'
      });
		
		uni.hideLoading();
		//实际项目中应该调用登录API
	
		this.$api.mobilelogin({
		phone: this.form.phone,
		code: this.form.code,
		invite_id:this.form.invite_id
		}).then(res => {
		if(res.code==1){ //登录成功
			// 处理登录成功
			  uni.setStorageSync('token', res.data.token);
			  uni.setStorageSync('userInfo', res.data.user);
			  /* uni.switchTab({
				url: '/pages/index/index'
			  }); */
			  Jump("/pages/index/index")
		}
		else{ //登录失败
			uni.showToast({
			  title: err.message || '登录失败',
			  icon: 'none'
			});
		}        
		}, 1500);
    },
    
    // 微信登录
    handleWechatLogin() {
      // 调用微信登录API 先获取微信的code 然后获取微信用户授权信息放入rawData 提交到后台
      uni.showLoading({
        title: '登录中...'
      });
      let that=this;
      // 获取微信登录凭证（code）
      uni.login({
        provider: 'weixin',
        success: (loginRes) => {
          if (loginRes.code) {
            // 获取用户信息
            uni.getUserInfo({
              provider: 'weixin',
              withCredentials: true, // 是否带上登录态信息
              lang: 'zh_CN',
              success: (infoRes) => {
                // 组装登录参数
                const params = {
                  code: loginRes.code,
                  rawData: JSON.parse(infoRes.rawData),
                  signature: infoRes.signature, //用于校验用户信息
                  encryptedData: infoRes.encryptedData, //敏感数据在内的完整用户信息的加密数据
                  iv: infoRes.iv, //加密算法的初始向量
                  invite_id:that.form.invite_id
                };
                
                // 调用后端登录接口
                this.$api.wechatLogin(params).then(res => {
                  uni.hideLoading();
                  if (res.code === 1) {
                    // 保存token
                    uni.setStorageSync('token', res.data.user.token);
                    // 保存用户信息
                    uni.setStorageSync('userInfo', res.data.user);
                    
                    uni.showToast({
                      title: '登录成功',
                      icon: 'success',
                      duration: 1500,
                      success: () => {
                        setTimeout(() => {
                          uni.switchTab({
                            url: '/pages/index/index'
                          });
                        }, 1500);
                      }
                    });
                  } else {
                    uni.showToast({
                      title: res.msg || '登录失败',
                      icon: 'none'
                    });
                  }
                });
              },
              fail: (err) => {
                uni.hideLoading();
                // 用户拒绝授权
                if (err.errMsg.indexOf('auth deny') !== -1) {
                  uni.showModal({
                    title: '提示',
                    content: '需要您授权才能登录，是否重新授权？',
                    success: (res) => {
                      if (res.confirm) {
                        // 重新打开授权设置页
                        uni.openSetting({
                          success: (settingRes) => {
                            if (settingRes.authSetting['scope.userInfo']) {
                              // 用户已同意授权，重新登录
                              this.handleWechatLogin();
                            }
                          }
                        });
                      }
                    }
                  });
                } else {
                  uni.showToast({
                    title: '获取用户信息失败',
                    icon: 'none'
                  });
                }
              }
            });
          } else {
            uni.hideLoading();
            uni.showToast({
              title: '微信登录失败',
              icon: 'none'
            });
          }
        },
        fail: () => {
          uni.hideLoading();
          uni.showToast({
            title: '微信登录失败',
            icon: 'none'
          });
        }
      });
    },
    
    onGetPhoneNumber(e) {
      // #ifdef MP-WEIXIN
      uni.showLoading({
        title: '登录中...'
      });
      
      let that = this;
      // 先获取微信登录凭证（code）
      uni.login({
        provider: 'weixin',
        success: (loginRes) => {
          if (loginRes.code) {
            // 调用后端接口，传递code，后端会返回手机号授权所需的参数
            this.$api.getWechatPhoneAuth({
              code: loginRes.code,
              invite_id: that.form.invite_id,
			  signature: loginRes.signature, //用于校验用户信息
			  encryptedData: e.detail.encryptedData, //敏感数据在内的完整用户信息的加密数据
			  iv: e.detail.iv, //加密算法的初始向量
            }).then(res => {
              uni.hideLoading();
              if (res.code === 1) {
                // 保存token
                uni.setStorageSync('token', res.data.user.token);
                // 保存用户信息
                uni.setStorageSync('userInfo', res.data.user);
                
                uni.showToast({
                  title: '登录成功',
                  icon: 'success',
                  duration: 1500,
                  success: () => {
                    setTimeout(() => {
                      uni.switchTab({
                        url: '/pages/index/index'
                      });
                    }, 1500);
                  }
                });
              } else {
                uni.showToast({
                  title: res.msg || '登录失败',
                  icon: 'none'
                });
              }
            }).catch(err => {
              uni.hideLoading();
              uni.showToast({
                title: '登录失败，请稍后重试',
                icon: 'none'
              });
            });
          } else {
            uni.hideLoading();
            uni.showToast({
              title: '获取微信授权失败',
              icon: 'none'
            });
          }
        },
        fail: () => {
          uni.hideLoading();
          uni.showToast({
            title: '微信登录失败',
            icon: 'none'
          });
        }
      });
      // #endif
      
      // #ifndef MP-WEIXIN
      uni.showToast({
        title: '请在微信小程序中使用',
        icon: 'none'
      });
      // #endif
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f5f5;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.login-header {
  width: 100%;
  height: 320rpx;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  position: relative;
  overflow: hidden;
  
  .wave-animation {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80rpx;
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDQwIDMyMCI+PHBhdGggZmlsbD0iI2Y1ZjVmNSIgZmlsbC1vcGFjaXR5PSIxIiBkPSJNMCwxNjBMNDAsMTQ0QzgwLDEyOCwxNjAsOTYsMjQwLDEwNkMzMjAsMTE3LDQwMCwxNzEsNDgwLDE5MkM1NjAsMjEzLDY0MCwyMDMsNzIwLDE5MkM4MDAsMTgxLDg4MCwxNzEsOTYwLDE3NkMxMDQwLDE4MSwxMTIwLDIwMywxMjAwLDIwM0MxMjgwLDIwMywxMzYwLDE4MSwxNDAwLDE3MUwxNDQwLDE2MEwxNDQwLDMyMEwxNDAwLDMyMEMxMzYwLDMyMCwxMjgwLDMyMCwxMjAwLDMyMEMxMTIwLDMyMCwxMDQwLDMyMCw5NjAsMzIwQzg4MCwzMjAsODAwLDMyMCw3MjAsMzIwQzY0MCwzMjAsNTYwLDMyMCw0ODAsMzIwQzQwMCwzMjAsMzIwLDMyMCwyNDAsMzIwQzE2MCwzMjAsODAsMzIwLDQwLDMyMEwwLDMyMFoiPjwvcGF0aD48L3N2Zz4=') repeat-x;
    background-size: 1440px 80rpx;
    animation: wave 10s linear infinite;
  }
  
  @keyframes wave {
    0% {
      background-position-x: 0;
    }
    100% {
      background-position-x: 1440px;
    }
  }
}

.login-title {
  margin-top: 60rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  
  .title-icon {
    background: linear-gradient(135deg, #e6f7ff 0%, #d0e8ff 100%);
    width: 100rpx;
    height: 100rpx;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8rpx 16rpx rgba(60, 156, 255, 0.2);
    margin-bottom: 24rpx;
  }
  
  .title-content {
    text-align: center;
  }
  
  .main-title {
    font-size: 52rpx;
    font-weight: bold;
    background: linear-gradient(135deg, #3c9cff 0%, #1d4ed8 100%);
    -webkit-background-clip: text;
    color: transparent;
    display: block;
    margin-bottom: 16rpx;
    text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
    letter-spacing: 2rpx;
  }
  
  .sub-title {
    font-size: 30rpx;
    color: #666666;
    display: block;
    letter-spacing: 1rpx;
  }
}

.login-form {
  width: 90%;
  margin-top: 60rpx;
}

.form-item {
  margin-bottom: 24rpx;
}

.input-container {
  display: flex;
  align-items: center;
  background-color: #ffffff;
  border-radius: 14rpx;
  padding: 16rpx 24rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.code-btn {
  min-width: 160rpx;
  text-align: center;
  padding: 8rpx 16rpx;
  border-left: 1px solid #eeeeee;
  margin-left: 16rpx;
  
  text {
    font-size: 24rpx;
    color: #3c9cff;
    
    &.disabled {
      color: #999999;
    }
  }
}

.agreement-section {
  display: flex;
  align-items: center;
  margin-top: 20rpx;
  padding: 0 10rpx;
}

.agreement-text {
  font-size: 24rpx;
  color: #666666;
  margin-left: 10rpx;
  
  .link {
    color: #3c9cff;
  }
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 10rpx;
  height: 90rpx;
  
  text {
    font-size: 32rpx;
    font-weight: 500;
    color: #ffffff;
  }
}

.wechat-login-section {
  margin-top: 80rpx;
  width: 100%;
}

.divider {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 40rpx;
  
  .line {
    flex: 1;
    height: 1px;
    background-color: #eeeeee;
  }
  
  .divider-text {
    padding: 0 30rpx;
    font-size: 24rpx;
    color: #999999;
  }
}

.wechat-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 30rpx;
  
  text {
    font-size: 26rpx;
    color: #666666;
    margin-top: 10rpx;
    margin-top: 10rpx;
  }
}

.wechat-phone-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  
  text {
    font-size: 26rpx;
    color: #666666;
    margin-top: 10rpx;
    margin-top: 16rpx;
  }
}
</style>