<template>
  <view class="container">
    <!-- 用户信息卡片 -->
    <view class="user-info-card">
      <view class="user-info-content">
        <view class="avatar-container">
          <u-avatar
            :src="userInfo.avatar || '/static/images/avatar_default.png'" <!-- 确保有默认头像 -->
            size="80"
            mode="square"
            :customStyle="{borderRadius: '24rpx', boxShadow: '0 4rpx 8rpx rgba(0, 0, 0, 0.1)'}"
          ></u-avatar>
        </view>
        <view class="user-details">
          <text class="username">{{ userInfo.nickname }}</text>
          <view class="user-id">
            <u-icon name="account" size="24" color="#333333"></u-icon>
            <!-- ID 显示，注意保护隐私，如果不需要可移除或用其他标识 -->
            <text class="id-text">ID: {{ userInfo.id }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 账户信息卡片 -->
    <view class="account-card">
      <view class="account-info">
        <view class="account-money">
          <text class="account-label">分销佣金</text>
          <!-- 使用 userInfo.money 作为分销佣金 -->
          <text class="account-balance">¥{{ userInfo.money || '0.00' }}</text>
        </view>
        <u-button 
          type="primary" 
          shape="circle"
          :customStyle="{width: '200rpx', height: '90rpx', background: 'linear-gradient(135deg, #4a7aff 0%, #1d4ed8 100%)', boxShadow: '0 4rpx 12rpx rgba(0, 0, 0, 0.15)', border: 'none'}"
          @click="goToRecharge"
        >
          <view class="btn-content">
            <text>充值</text>
          </view>
		  
        </u-button>
      </view>
      <view class="expense-info">
        <!-- 使用 accountInfo.todayExpense 和 accountInfo.monthExpense -->
        <text>今日支出: ¥{{ accountInfo.todayExpense || '0.00' }}</text>
        <text>本月支出: ¥{{ accountInfo.monthExpense || '0.00' }}</text>
      </view>
      <!-- 如果需要显示账户余额，可以添加 -->
       <view class="account-info" style="margin-top: 20rpx;">
         <view class="account-money">
           <text class="account-label">账户余额</text>
           <text class="account-balance" style="color: #10b981;">¥{{ userInfo.balance || '0.00' }}</text>
         </view>
       </view>
    </view>

    <!-- 功能列表 -->
    <view class="function-list">
      <u-cell-group>
        <u-cell 
          v-for="(item, index) in functionList" 
          :key="index"
          :title="item.title"
          :label="item.subtitle"
          :isLink="true"
          @click="handleFunctionClick(item)"
        >
          <view slot="icon" class="cell-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="36"></u-icon>
          </view>
        </u-cell>
      </u-cell-group>
    </view>

    <!-- 其他功能 -->
    <view class="other-functions">
      <u-cell-group>
        <u-cell 
          v-for="(item, index) in otherFunctionList" 
          :key="index"
          :title="item.title"
          :isLink="true"
          @click="handleOtherFunctionClick(item)"
        >
          <view slot="icon" class="cell-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="36"></u-icon>
          </view>
        </u-cell>
      </u-cell-group>
    </view>
  </view>
</template>

<script>
	import {toast, clearStorageSync, setStorageSync,getStorageSync, useRouter} from '@/utils/utils.js'
export default {
  data() {
    return {
      title: '个人中心',
      userInfo: { // 用户基本信息
        nickname: '加载中...', // 昵称
        id: '---',       // 用户ID
        avatar: '',      // 头像URL，默认为空或加载中占位图
        money: '0.00',   // 分销佣金 (来自API的 money 字段)
        balance: '0.00'  // 账户余额 (来自API的 balance 字段)
      },
      accountInfo: { // 账户相关的统计信息
        todayExpense: '0.00', // 今日支出
        monthExpense: '0.00'  // 本月支出
      },
      functionList: [ // 功能列表项
        {
          title: '我的设备',
          subtitle: '查看和管理您的设备', // 修改副标题为通用描述
          icon: 'level', // uView 图标名称
          iconColor: '#3c9cff', // 图标颜色
          bgColor: '#e7f1ff',   // 图标背景色
          url: '/pages/devices/index' // 跳转页面路径
        },
        {
          title: '使用记录',
          subtitle: '查看历史使用数据',
          icon: 'clock',
          iconColor: '#19be6b',
          bgColor: '#e5f7ef',
          url: '/pages/records/index' // 指向新的使用记录页面
        },
        {
          title: '订单记录',
          subtitle: '查看历史订单', // 修改副标题
          icon: 'order',
          iconColor: '#ff9900',
          bgColor: '#fdf6ec',
          url: '/pages/order/index'
        },
        {
          title: '优惠券',
          subtitle: '查看和使用您的优惠券', // 修改副标题
          icon: 'coupon',
          iconColor: '#fa3534',
          bgColor: '#fef0f0',
          url: '/pages/coupons/index'
        },
		{
		  title: '我的推广码',
		  subtitle: '邀请好友赚取佣金', // 修改副标题
		  icon: 'share', // 修改图标为更合适的 'share'
		  iconColor: '#41b883', // 修改图标颜色
		  bgColor: '#e9f7f2',   // 修改图标背景色
		  url: '/pages/me/promotion'
		}
      ],
      otherFunctionList: [ // 其他功能列表
        {
          title: '帮助中心',
          icon: 'question-circle',
          iconColor: '#9a6ee8',
          bgColor: '#f3effa',
          url: '/pages/help/index'
        },
        {
          title: '设置',
          icon: 'setting',
          iconColor: '#909399',
          bgColor: '#f3f4f6',
          url: '/pages/set/index'
        }
      ]
    }
  },
  onShow() { // 使用 onShow 生命周期函数，确保每次页面显示都尝试获取最新数据
    this.fetchUserAndAccountData(); // 获取用户和账户数据
  },
  methods: {
    // 获取用户及账户数据的方法
    async fetchUserAndAccountData() {
      uni.showLoading({ title: '加载中...' }); // 显示加载提示
      try {
        const res = await this.$api.getUserIndex(); // 调用API获取用户中心数据
        if (res.code === 1 && res.data && res.data.userInfo) { // 检查API调用是否成功且数据有效
          const apiUserInfo = res.data.userInfo;

          // 更新用户信息
          this.userInfo.nickname = apiUserInfo.nickname || '未设置昵称';
          this.userInfo.id = apiUserInfo.id || '未知ID';
          this.userInfo.avatar = apiUserInfo.avatar || '/static/images/avatar_default.png'; // 确保有默认头像

          // 更新账户信息
          // 注意：后端API返回的 userInfo.money 是总余额，userInfo.dealer_money (如果后端提供) 才是分销佣金
          // 此处根据当前后端实现，money为分销佣金，balance为账户余额
          this.userInfo.money = parseFloat(apiUserInfo.money || 0).toFixed(2); // 分销佣金
          this.userInfo.balance = parseFloat(apiUserInfo.balance || 0).toFixed(2); // 账户余额

          // 更新支出统计
          this.accountInfo.todayExpense = parseFloat(apiUserInfo.today_expense || 0).toFixed(2);
          this.accountInfo.monthExpense = parseFloat(apiUserInfo.month_expense || 0).toFixed(2);

          // 更新本地存储的用户信息 (可选，但建议，以便其他页面能快速访问)
          setStorageSync('userInfo', apiUserInfo);
        } else {
          // API调用失败或返回数据结构不符预期
          toast(res.msg || '获取用户信息失败');
          // 可以考虑加载本地缓存的旧数据作为回退
          const cachedUserInfo = getStorageSync('userInfo');
          if (cachedUserInfo) {
            this.userInfo.nickname = cachedUserInfo.nickname || '昵称';
            this.userInfo.id = cachedUserInfo.id || 'ID';
            this.userInfo.avatar = cachedUserInfo.avatar || '/static/images/avatar_default.png';
          }
        }
      } catch (error) {
        console.error('fetchUserAndAccountData error:', error);
        toast('网络请求出错，请稍后再试');
        // 同样，可以考虑加载本地缓存
        const cachedUserInfo = getStorageSync('userInfo');
          if (cachedUserInfo) {
            this.userInfo.nickname = cachedUserInfo.nickname || '昵称';
            this.userInfo.id = cachedUserInfo.id || 'ID';
            this.userInfo.avatar = cachedUserInfo.avatar || '/static/images/avatar_default.png';
          }
      } finally {
        uni.hideLoading(); // 隐藏加载提示
      }
    },
    goToRecharge() { // 跳转到充值页面
      uni.navigateTo({
        url: '/pages/recharge/recharge-redesign'
      })
    },
    handleFunctionClick(item) { // 处理功能列表项点击事件
      if (item.url) {
        uni.navigateTo({
          url: item.url
        });
      }
    },
    handleOtherFunctionClick(item) { // 处理其他功能列表项点击事件
      if (item.url) {
        uni.navigateTo({
          url: item.url
        });
      }
    },
    // tabbarChange(index) {
    //   // 处理底部导航切换 (如果当前页面是Tab页且需要特殊处理)
    // },
    // goToPage(url) {
    //   uni.switchTab({
    //     url: url
    //   })
    // }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding-bottom: 120rpx;
}

.user-info-card {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 60rpx 30rpx 60rpx;
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

.user-info-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.avatar-container {
  position: relative;
}

.user-details {
  margin-left: 30rpx;
}

.username {
  font-size: 34rpx;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 10rpx;
}

.user-id {
  display: flex;
  align-items: center;
  background-color: #f8f8f8;
  border-radius: 50rpx;
  padding: 6rpx 20rpx;
  margin-top: 10px;
}

.id-text {
  font-size: 22rpx;
  color: #333333;
  margin-left: 10rpx;
}

.account-card {
  margin: -30rpx 30rpx 0;
  background-color: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20rpx);
  border-radius: 24rpx;
  padding: 24rpx 30rpx;
  box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 10;
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
}

.account-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16rpx;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 10rpx;
  .text{
	  margin-left: 12rpx; font-weight: 500; color: #ffffff;
  }
}

.account-label {
  font-size: 26rpx;
  color: #666666;
  display: block;
}
.account-money{
	width: 70%;
}
.account-balance {
  font-size: 48rpx;
  font-weight: bold;
  color: #3c9cff; /* 默认为佣金颜色 */
  display: block;
  margin-top: 6rpx;
}

.expense-info {
  display: flex;
  justify-content: space-between;
  font-size: 22rpx;
  color: #999999;
  margin-top: 20rpx; /* 与账户余额显示部分隔开 */
}

.function-list, .other-functions {
  margin: 24rpx 30rpx;
  border-radius: 24rpx;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
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
</style>