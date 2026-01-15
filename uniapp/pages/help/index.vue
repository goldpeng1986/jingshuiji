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
      <u-loading-icon v-if="isLoadingQuickEntries" mode="circle" size="28"></u-loading-icon>
      <view class="quick-entry-grid" v-if="!isLoadingQuickEntries && quickEntries.length > 0">
        <view 
          class="entry-card" 
          v-for="(item) in quickEntries"
          :key="item.id" <!-- 使用 item.id 或 item.diyname 作为key -->
          @click="handleEntryClick(item)"
        >
          <view class="entry-icon" :style="{backgroundColor: item.bgColor}">
            <u-icon :name="item.icon" :color="item.iconColor" size="28"></u-icon>
          </view>
          <text class="entry-name">{{ item.name }}</text>
        </view>
      </view>
      <u-empty mode="data" text="快速入口加载失败" v-if="!isLoadingQuickEntries && quickEntries.length === 0"></u-empty>
    </view>

    <!-- 常见问题 -->
    <view class="section">
      <view class="section-header">
        <text class="section-title">常见问题</text>
        <text class="section-more" @click="viewAllFaqs">查看全部</text>
      </view>
      <u-loading-icon v-if="isLoadingFaqs" mode="circle" size="28"></u-loading-icon>
      <view class="faq-container" v-if="!isLoadingFaqs && faqList.length > 0">
        <view 
          class="faq-card" 
          v-for="(item, index) in faqList" 
          :key="item.id" <!-- 使用 item.id 作为key -->
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
          <view class="faq-answer" :style="{height: activeIndex === index && item.answer !== '点击查看详情' ? 'auto' : '0'}">
             <!-- 只有当答案不是“点击查看详情”时才显示内容，否则点击会跳转 -->
            <text class="answer-text" v-if="item.answer !== '点击查看详情'">{{ item.answer }}</text>
          </view>
        </view>
      </view>
      <u-empty mode="message" text="暂无常见问题" v-if="!isLoadingFaqs && faqList.length === 0"></u-empty>
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
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '帮助中心', // 页面标题
      searchKeyword: '', // 搜索关键词
      activeIndex: -1,   // 当前展开的FAQ项索引
      quickEntries: [],  // 快速入口列表，将从API获取
      faqList: [],       // 常见问题列表，将从API获取
      isLoadingQuickEntries: false, // 快速入口加载状态
      isLoadingFaqs: false,         // FAQ加载状态
      // 保留原有的静态图标和颜色配置，API返回的数据主要用于标题和跳转逻辑
      staticQuickEntryStyles: {
        '使用指南': { icon: 'file-text-fill', bgColor: '#e7f1ff', iconColor: '#3b82f6' },
        '故障排除': { icon: 'tool-fill', bgColor: '#e5f7ef', iconColor: '#10b981' }, // 修改图标为tool-fill
        '服务条款': { icon: 'file-text', bgColor: '#fdf6ec', iconColor: '#f59e0b' },
        '联系客服': { icon: 'server-man', bgColor: '#fef0f0', iconColor: '#f43f5e', action: 'contact' }
      }
    };
  },
  onShow() {
    // 页面显示时加载数据
    this.fetchQuickEntries();
    this.fetchFaqs();
  },
  methods: {
    // 获取快速入口数据
    async fetchQuickEntries() {
      this.isLoadingQuickEntries = true;
      try {
        const res = await this.$api.pageList({ type: 'help_quick_entry' }); // API调用
        if (res.code === 1 && res.data && res.data.data) {
          this.quickEntries = res.data.data.map(item => {
            const style = this.staticQuickEntryStyles[item.title] ||
                          this.staticQuickEntryStyles[item.name] || // 兼容name字段
                          { icon: 'question-circle-fill', bgColor: '#f0f0f0', iconColor: '#909399' }; // 默认样式
            return {
              id: item.id, // API返回的ID
              name: item.title, // API返回的标题作为name
              diyname: item.diyname, // API返回的diyname
              icon: style.icon,
              bgColor: style.bgColor,
              iconColor: style.iconColor,
              action: style.action // 如果是联系客服等特殊操作
            };
          });
        } else {
          toast(res.msg || '获取快速入口失败');
          this.quickEntries = Object.entries(this.staticQuickEntryStyles).map(([name, style]) => ({ // API失败则使用静态数据作为回退
             id: name, name: name, diyname: name.toLowerCase().replace(/\s+/g, '-'), ...style
          }));
        }
      } catch (error) {
        console.error('fetchQuickEntries error:', error);
        toast('网络请求快速入口出错');
         this.quickEntries = Object.entries(this.staticQuickEntryStyles).map(([name, style]) => ({
             id: name, name: name, diyname: name.toLowerCase().replace(/\s+/g, '-'), ...style
         }));
      } finally {
        this.isLoadingQuickEntries = false;
      }
    },
    // 获取常见问题数据
    async fetchFaqs() {
      this.isLoadingFaqs = true;
      try {
        const res = await this.$api.pageList({ type: 'faq_item', limit: 5 }); // 假设每页获取5条，或者根据后端调整limit
        if (res.code === 1 && res.data && res.data.data) {
          this.faqList = res.data.data.map(item => ({
            id: item.id, // API返回的ID
            question: item.title, // API返回的标题作为question
            answer: item.description || '点击查看详情', // API返回的description作为answer预览，或者提示点击查看
            diyname: item.diyname // 用于跳转详情
          }));
        } else {
          toast(res.msg || '获取常见问题失败');
          this.faqList = []; // 清空
        }
      } catch (error) {
        console.error('fetchFaqs error:', error);
        toast('网络请求常见问题出错');
        this.faqList = [];
      } finally {
        this.isLoadingFaqs = false;
      }
    },
    // goBack() { // uni-app通常有默认返回机制
    //   uni.navigateBack();
    // },
    toggleFaq(index) {
      this.activeIndex = this.activeIndex === index ? -1 : index;
      // 如果答案是“点击查看详情”，则跳转
      const faqItem = this.faqList[index];
      if (faqItem && faqItem.answer === '点击查看详情' && (faqItem.id || faqItem.diyname)) {
         uni.navigateTo({
            url: `/pages/help/info?${faqItem.diyname ? 'diyname=' + faqItem.diyname : 'id=' + faqItem.id}`
         });
      }
    },
    handleEntryClick(item) {
      if (item.action === 'contact') {
        this.contactCustomerService();
      } else if (item.diyname || item.id) { // 优先使用diyname
        uni.navigateTo({
          url: `/pages/help/info?${item.diyname ? 'diyname=' + item.diyname : 'id=' + item.id}`
        });
      } else if (item.url) { // 兼容旧的静态url跳转
         uni.navigateTo({ url: item.url });
      }
    },
    contactCustomerService() {
      uni.makePhoneCall({
        phoneNumber: '400-123-4567' // 客服电话号码，应来自配置或API
      });
    },
    viewAllFaqs() {
      // TODO: 实现跳转到所有FAQ列表页的逻辑，如果FAQ很多需要分页展示
      // 目前API是一次性获取部分FAQ，此按钮可能需要调整或指向一个新页面
      toast('查看全部FAQ功能待开发');
      // uni.navigateTo({
      //   url: '/pages/help/all-faqs' // 假设有这样一个页面
      // });
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