<template>
  <view class="container">
    <!-- 搜索框 -->
    <view class="search-container">
      <u-search
        placeholder="搜索常见问题"
        v-model="searchKeyword"
        :showAction="false"
        :clearabled="true"
        shape="round"
        :animation="true"
        bgColor="#f5f7fa"
      ></u-search>
    </view>

    <!-- 快速入口 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">快速入口</text>
      </view>
      <view class="quick-entry-grid">
        <view 
          class="entry-card" 
          v-for="(item, index) in quickEntries" 
          :key="index"
          @click="handleEntryClick(item)"
          :class="{'entry-card-last': (index + 1) % 4 === 0}"
        >
          <view class="entry-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="28"></u-icon>
          </view>
          <text class="entry-name">{{ item.name }}</text>
        </view>
      </view>
    </view>

    <!-- 常见问题 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">常见问题</text>
        <text class="section-more" @click="viewAllFaqs">查看全部</text>
      </view>
      <view class="faq-container">
        <view 
          class="faq-card" 
          v-for="(item, index) in faqList" 
          :key="index"
          :class="{expanded: activeIndex === index}"
          @click="toggleFaq(index)"
        >
          <view class="faq-header">
            <view class="question-wrapper">
              <view class="question-icon" :style="{backgroundColor: '#e7f1ff'}">
                <u-icon name="question-circle" color="#3b82f6" size="18"></u-icon>
              </view>
              <text class="question-text">{{ item.question }}</text>
            </view>
            <u-icon 
              name="arrow-down" 
              color="#909399" 
              size="16"
              :class="{rotate: activeIndex === index}"
            ></u-icon>
          </view>
          <view class="faq-answer" :style="{height: activeIndex === index ? 'auto' : '0'}">
            <text class="answer-text">{{ item.answer }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 联系客服 -->
    <view class="contact-container">
      <view class="contact-card">
        <view class="contact-header">
          <u-icon name="server-fill" size="24" color="#3b82f6"></u-icon>
          <text class="contact-title">在线客服</text>
        </view>
        <view class="contact-content">
          <text class="contact-desc">遇到问题？我们的客服团队随时为您提供帮助</text>
          <view class="contact-info-row">
            <text class="contact-label">客服热线：</text>
            <text class="contact-value">400-123-4567</text>
          </view>
          <view class="contact-info-row">
            <text class="contact-label">工作时间：</text>
            <text class="contact-value">周一至周日 9:00-18:00</text>
          </view>
          <u-button 
            type="primary" 
            shape="circle"
            icon="phone-fill"
            @click="contactCustomerService"
            class="contact-btn"
          >联系客服</u-button>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '帮助中心',
      searchKeyword: '',
      activeIndex: -1,
      quickEntries: [
        {
          name: '使用指南',
          icon: 'file-text-fill',
          bgColor: '#e7f1ff',
          iconColor: '#3b82f6',
          url: '/pages/help/guide'
        },
        {
          name: '故障排除',
          icon: 'account-fill',
          bgColor: '#e5f7ef',
          iconColor: '#10b981',
          url: '/pages/help/troubleshoot'
        },
        {
          name: '服务条款',
          icon: 'file-text',
          bgColor: '#fdf6ec',
          iconColor: '#f59e0b',
          url: '/pages/help/terms'
        },
        {
          name: '联系客服',
          icon: 'server-man',
          bgColor: '#fef0f0',
          iconColor: '#f43f5e',
          action: 'contact'
        }
      ],
      faqList: [
        {
          question: '如何使用净水机？',
          answer: '使用森泽共享净水机非常简单，只需按照以下步骤操作：\n1. 打开APP，选择您要使用的净水机\n2. 点击"开始用水"按钮\n3. 将水杯放在出水口下方\n4. 选择所需水量或直接按下出水按钮\n5. 等待出水完成即可\n\n如果您在使用过程中遇到任何问题，可以联系我们的客服人员获取帮助。'
        },
        {
          question: '如何充值账户？',
          answer: '您可以通过以下方式为账户充值：\n1. 在"我的"页面点击"充值"按钮\n2. 选择充值金额或输入自定义金额\n3. 选择支付方式（微信支付或支付宝）\n4. 完成支付流程\n\n充值成功后，金额将立即添加到您的账户余额中。我们目前支持微信支付和支付宝两种支付方式。'
        },
        {
          question: '净水机的水质如何保证？',
          answer: '森泽共享净水机采用多级过滤系统，确保水质安全可靠：\n1. PP棉滤芯：过滤水中的铁锈、泥沙等大颗粒杂质\n2. 活性炭滤芯：吸附水中的余氯、异味等\n3. RO反渗透膜：过滤水中的重金属、细菌等微小颗粒\n4. 后置活性炭：进一步改善水的口感\n\n我们的净水机会实时监测水质TDS值，确保出水水质达到国家饮用水标准。同时，我们的维护团队会定期对设备进行检查和滤芯更换，保证水质安全。'
        },
        {
          question: '如何绑定新设备？',
          answer: '绑定新设备的步骤如下：\n1. 在"我的"页面点击"我的设备"\n2. 在设备页面底部点击"添加新设备"\n3. 点击"扫码绑定"，使用相机扫描设备上的二维码\n4. 扫描成功后，按照提示完成设备绑定\n\n如果您无法扫描二维码，也可以手动输入设备ID进行绑定。设备ID通常印在设备背面或底部的标签上。'
        }
      ]
    }
  },
  methods: {
    goBack() {
      uni.navigateBack();
    },
    toggleFaq(index) {
      this.activeIndex = this.activeIndex === index ? -1 : index;
    },
    handleEntryClick(item) {
      if (item.action === 'contact') {
        this.contactCustomerService();
      } else if (item.url) {
        uni.navigateTo({
          url: item.url
        });
      }
    },
    contactCustomerService() {
      uni.makePhoneCall({
        phoneNumber: '400-123-4567'
      });
    },
    viewAllFaqs() {
      uni.navigateTo({
        url: '/pages/help/all-faqs'
      });
    }
  }
}
</script>

<style>
.container {
  min-height: 100vh;
  background-color: #f8f9fc;
  padding-bottom: 30rpx;
}

.header-section {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding-bottom: 20rpx;
  position: relative;
}

.status-bar {
  height: var(--status-bar-height);
}

.header-content {
  display: flex;
  align-items: center;
  padding: 10rpx 30rpx;
}

.back-btn {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title {
  font-size: 36rpx;
  font-weight: bold;
  color: #ffffff;
  margin-left: 20rpx;
}

.search-container {
  padding: 30rpx;
  margin-top: -20rpx;
  position: relative;
  z-index: 10;
}

.section {
  margin-bottom: 30rpx;
  background-color: #fff;
  padding: 20px 0px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 30rpx;
  margin-bottom: 20rpx;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
}

.section-more {
  font-size: 24rpx;
  color: #3b82f6;
}

.quick-entry-grid {
  display: flex;
  flex-wrap: wrap;
  padding: 0 20rpx;
}

.entry-card {
  width: 25%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20rpx 0px;
}

.entry-icon {
  width: 100rpx;
  height: 100rpx;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.entry-name {
  font-size: 24rpx;
  color: #333333;
  text-align: center;
}

.faq-container {
  padding: 0 30rpx;
}

.faq-card {
  background-color: #ffffff;
  border-radius: 16rpx;
  margin-bottom: 20rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.faq-card.expanded {
  box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.08);
}

.faq-header {
  padding: 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.question-wrapper {
  display: flex;
  align-items: center;
  flex: 1;
}

.question-icon {
  width: 40rpx;
  height: 40rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20rpx;
}

.question-text {
  font-size: 28rpx;
  color: #333333;
  flex: 1;
}

.rotate {
  transform: rotate(180deg);
  transition: transform 0.3s ease;
}

.faq-answer {
  padding: 0 30rpx;
  height: 0;
  overflow: hidden;
  transition: height 0.3s ease;
  background-color: #f8f9fc;
  border-top: 1px solid #f0f0f0;
}

.answer-text {
  font-size: 26rpx;
  color: #666666;
  line-height: 1.6;
  padding: 30rpx 0;
  white-space: pre-wrap;
}

.contact-container {
  padding: 0 30rpx;
  margin-top: 40rpx;
}

.contact-card {
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.contact-header {
  display: flex;
  align-items: center;
  margin-bottom: 20rpx;
}

.contact-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  margin-left: 16rpx;
}

.contact-content {
  padding: 10rpx 0;
}

.contact-desc {
  font-size: 26rpx;
  color: #666666;
  margin-bottom: 20rpx;
}

.contact-info-row {
  display: flex;
  margin-bottom: 10rpx;
}

.contact-label {
  font-size: 26rpx;
  color: #999999;
  width: 160rpx;
}

.contact-value {
  font-size: 26rpx;
  color: #333333;
  font-weight: 500;
}

.contact-btn {
  margin-top: 30rpx;
}
</style>