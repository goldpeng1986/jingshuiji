<template>
  <view class="help-info-container">
    <!-- 导航栏，标题动态设置 -->
    <u-navbar :title="pageTitle" :autoBack="true" placeholder safeAreaInsetTop></u-navbar>

    <!-- 加载状态提示 -->
    <u-loading-page :loading="isLoading" loading-text="正在加载内容..."></u-loading-page>

    <!-- 内容展示区域 -->
    <view v-if="!isLoading && infoData" class="content-wrapper">
      <!-- 文章标题 (如果需要在内容外部单独显示) -->
      <!-- <view class="article-title" v-if="infoData.title">{{ infoData.title }}</view> -->

      <!-- 文章内容，使用u-parse解析HTML -->
      <view class="article-content">
        <u-parse :content="infoData.content" :tagStyle="tagStyle"></u-parse>
      </view>

      <!-- 其他信息，如发布时间、查看次数等 (如果API提供) -->
      <view class="meta-info" v-if="infoData.createtime || infoData.views">
        <text v-if="infoData.createtime">发布时间：{{ formatDate(infoData.createtime) }}</text>
        <text v-if="infoData.views" class="views-text">阅读量：{{ infoData.views }}</text>
      </view>
    </view>

    <!-- 错误或空状态提示 -->
    <u-empty
      v-if="!isLoading && !infoData && errorMsg"
      mode="network"
      :text="errorMsg"
      marginTop="100"
    ></u-empty>
    <u-empty
      v-if="!isLoading && !infoData && !errorMsg"
      mode="search"
      text="未找到相关内容"
      marginTop="100"
    ></u-empty>
  </view>
</template>

<script>
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      pageId: null,         // 从路由参数获取的页面ID
      pageDiyname: null,    // 从路由参数获取的DIY名称
      infoData: null,       // 存储从API获取的页面详细信息
      isLoading: true,      // 页面加载状态，默认为true
      errorMsg: '',         // 错误信息
      tagStyle: {           // u-parse的标签样式 (可选)
        // p: 'line-height: 1.8em; margin-bottom: 1em;',
        // img: 'max-width: 100%; border-radius: 8rpx;'
      }
    };
  },
  computed: {
    // 动态计算导航栏标题
    pageTitle() {
      return this.infoData && this.infoData.title ? this.infoData.title : '帮助详情';
    }
  },
  onLoad(options) {
    // options对象包含导航传递过来的参数
    if (options.id) {
      this.pageId = options.id;
    } else if (options.diyname) {
      this.pageDiyname = options.diyname;
    }

    if (this.pageId || this.pageDiyname) {
      this.fetchHelpDetail(); // 调用方法获取详情
    } else {
      this.isLoading = false;
      this.errorMsg = '参数错误，无法加载帮助内容';
      toast(this.errorMsg);
      // 考虑是否延时返回
      // setTimeout(() => uni.navigateBack(), 1500);
    }
  },
  methods: {
    // 从API获取帮助文章的详细信息
    async fetchHelpDetail() {
      this.isLoading = true;
      this.errorMsg = '';
      let params = {};
      if (this.pageId) {
        params.id = this.pageId;
      } else if (this.pageDiyname) {
        params.diyname = this.pageDiyname;
      }

      try {
        const res = await this.$api.pageIndex(params); // 调用API
        if (res.code === 1 && res.data) {
          this.infoData = res.data; // 将获取到的数据赋值给infoData
          // 如果后端返回的content是Markdown，可能需要前端转换，但u-parse通常处理HTML
        } else {
          this.infoData = null;
          this.errorMsg = res.msg || '无法加载帮助内容';
          toast(this.errorMsg);
        }
      } catch (error) {
        console.error('fetchHelpDetail error:', error);
        this.infoData = null;
        this.errorMsg = '网络请求失败，请稍后再试';
        toast(this.errorMsg);
      } finally {
        this.isLoading = false;
      }
    },
    // 格式化时间戳 (如果API返回的是时间戳)
    formatDate(timestamp) {
      if (!timestamp) return '未知';
      return this.$u.timeFormat(timestamp, 'yyyy-mm-dd hh:MM');
    }
  }
};
</script>

<style lang="scss" scoped>
.help-info-container {
  background-color: #ffffff;
  min-height: 100vh;
}

.content-wrapper {
  padding: 30rpx;
}

.article-title {
  font-size: 40rpx;
  font-weight: bold;
  color: #303133;
  margin-bottom: 30rpx;
  text-align: center; // 标题居中
}

.article-content {
  font-size: 30rpx;
  line-height: 1.8;
  color: #333333;
  // u-parse会处理内部元素的样式，这里是容器样式
  /deep/ img { // 使用 /deep/ 或 ::v-deep (取决于CSS预处理器) 来穿透修改u-parse内部图片样式
    max-width: 100% !important;
    height: auto !important;
    border-radius: 8rpx; // 给图片加圆角
    margin: 1em 0; // 图片上下边距
  }
}

.meta-info {
  margin-top: 40rpx;
  font-size: 24rpx;
  color: #909399;
  display: flex;
  justify-content: space-between;
  padding-top: 20rpx;
  border-top: 1rpx solid #f0f0f0;

  .views-text {
    margin-left: 20rpx;
  }
}
</style>
