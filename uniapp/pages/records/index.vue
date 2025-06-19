<template>
  <view class="container">


    <!-- 统计卡片 -->
    <view class="stats-card">
      <view class="stats-header">
        <text class="stats-title">本月用水统计</text>
        <text class="stats-date">{{ currentMonth }}</text>
      </view>
      <view class="stats-content">
        <view class="stats-item">
          <text class="stats-label">用水次数</text>
          <text class="stats-value">{{ statistics.count }}次</text>
        </view>
        <view class="stats-item">
          <text class="stats-label">用水量</text>
          <text class="stats-value">{{ statistics.volume }}L</text>
        </view>
        <view class="stats-item">
          <text class="stats-label">消费金额</text>
          <text class="stats-value">¥{{ statistics.amount }}</text>
        </view>
      </view>
    </view>

    <!-- 筛选选项 -->
    <view class="filter-options">
      <view 
        class="filter-option" 
        v-for="(item, index) in filterOptions" 
        :key="index"
        :class="{active: currentFilter === item.value}"
        @click="setFilter(item.value)"
      >
        <text>{{ item.label }}</text>
      </view>
    </view>

    <!-- 记录列表 -->
    <view class="records-list">
      <!-- 列表初始加载状态 -->
      <view v-if="isLoading && rawRecords.length === 0 && !errorLoading" class="state-container">
        <u-loading-icon text="正在加载记录..." size="20"></u-loading-icon>
      </view>

      <!-- 列表错误状态 -->
      <view v-if="errorLoading && rawRecords.length === 0" class="state-container">
        <u-empty mode="network" text="记录加载失败">
          <u-button slot="bottom" type="primary" size="mini" @click="fetchRecords(1)" text="点我重试" :customStyle="{marginTop: '10px'}"></u-button>
        </u-empty>
      </view>

      <!-- 内容：日期分组和记录 -->
      <block v-if="!isLoading && !errorLoading && groupedRecords.length > 0">
        <view class="date-group" v-for="(group, groupIndex) in groupedRecords" :key="groupIndex">
          <view class="date-header">
            <text class="date-text">{{ group.date }}</text>
          </view>

          <view
            class="record-card"
            v-for="(item, index) in group.records"
            :key="item.id || index" <!-- 如果item.id存在，则使用item.id作为key，否则使用index -->
            @click="viewRecordDetail(item)"
          >
            <view class="record-header">
              <view class="record-icon-container">
                <view class="record-icon" :style="{backgroundColor: getRecordTypeColor(item.type)}">
                  <!-- 示例：显示类型首字母或通用图标 -->
                  <u-icon :name="getRecordTypeIcon(item.type)" :color="getRecordTypeIconColor(item.type)" size="24"></u-icon>
                </view>
              </view>
              <view class="record-info">
                <text class="record-title">{{ item.title || item.description || '未知记录' }}</text>
                <text class="record-time">{{ item.created_at || item.date || item.time || 'N/A' }}</text>
              </view>
              <view class="record-amount" v-if="item.amount">
                 <!-- 根据金额正负或类型条件性添加前缀 -->
                <text class="amount-text" :class="{'positive-amount': item.amount > 0, 'negative-amount': item.amount < 0}">
                  {{ item.amount > 0 ? '+' : '' }}¥{{ item.amount }}
                </text>
                <text class="volume-text" v-if="item.details">{{ item.details }}</text> <!-- 或其他次要信息 -->
              </view>
            </view>
            <view class="record-footer" v-if="item.category || item.status_text">
              <view class="location-info" v-if="item.category">
                 <u-icon name="tags" size="14" color="#909399"></u-icon>
                <text class="location-text">{{ item.category }}</text>
              </view>
               <text class="detail-link" :style="{color: getStatusColor(item.status_code)}">{{item.status_text || ''}}</text>
            </view>
          </view>
        </view>
      </block>
      
      <!-- 空状态 (加载后无记录) -->
      <view v-if="!isLoading && !errorLoading && groupedRecords.length === 0 && rawRecords.length === 0" class="empty-state state-container">
        <u-empty
          mode="history"
          text="暂无相关记录"
          textColor="#909399"
          iconSize="120"
        ></u-empty>
      </view>

      <!-- 加载更多组件 -->
      <u-loadmore v-if="rawRecords.length > 0 || isLoading" :status="loadMoreStatus" @loadmore="fetchRecords(currentPage + 1)" iconType="circle" class="load-more-component"/>
    </view>
  </view>
</template>

<script>
import { getGenericRecords } from '../../api/api'; // 引入获取通用记录的API函数
import { formatDate, groupRecordsByDate } from '../../utils/utils'; // 假设这些是日期格式化和分组的辅助函数

export default {
  data() {
    return {
      title: '使用记录', // 如果通用记录有类型，此标题可以动态化
      currentMonth: this.getCurrentMonthString(), // 动态获取当前月份字符串
      currentFilter: 'all', // 默认筛选条件
      statistics: { // 统计数据，实际应用中可能来自API或通过计算得出
        count: 0,
        volume: 0,
        amount: '0.00'
      },
      filterOptions: [ // 筛选选项
        { label: '全部记录', value: 'all' },
        { label: '本月记录', value: 'currentMonth' }, // 选项值已修改，更清晰
        { label: '上月记录', value: 'lastMonth' },   // 选项值已修改，更清晰
        // { label: '筛选', value: 'filter', icon: 'filter' } // “筛选”对话框功能当前版本暂不实现
      ],
      rawRecords: [], // 用于存储从API获取的原始记录列表（扁平列表）
      isLoading: true, // 加载状态标志，默认为true，页面加载时即开始加载数据
      errorLoading: false, // 错误状态标志
      currentPage: 1, // 当前页码
      totalPages: 1, // 总页数
      // recordsList: [] // 已被 rawRecords 和 groupedRecords 替代
    }
  },
  computed: {
    groupedRecords() {
      // 按日期对 rawRecords 进行分组。期望记录对象中包含 'created_at' 或类似的日期字段。
      // groupRecordsByDate 函数需要在 utils/utils.js 中实现。
      // 它应接收 this.rawRecords 作为参数，并返回一个数组，格式为 { date: '格式化后的日期', records: [...] }。
      if (!this.rawRecords || this.rawRecords.length === 0) {
        return [];
      }
      // 请确保您的记录对象中有一个日期属性，例如 'created_at' 或 'record_date'。
      // groupRecordsByDate 工具函数将处理此属性。
      // 示例: return groupRecordsByDate(this.rawRecords, 'created_at');
      // 如果 groupRecordsByDate 不可用，则使用以下简化版分组逻辑（占位符）：
      // 注意：这是一个占位符，实际的分组逻辑会更复杂。
      const_groups = {};
      this.rawRecords.forEach(record => {
        const dateStr = record.date ? formatDate(record.date, 'YYYY-MM-DD') : '未知日期'; // formatDate 来自 utils
        if (!const_groups[dateStr]) {
          const_groups[dateStr] = { date: dateStr, records: [] };
        }
        const_groups[dateStr].records.push(record);
      });
      // 按日期降序排序分组
      return Object.values(const_groups).sort((a, b) => new Date(b.date) - new Date(a.date));
    },
    loadMoreStatus() {
      // 计算 u-loadmore 组件的加载状态
      if (this.isLoading && this.currentPage > 0 && this.rawRecords.length > 0 && this.currentPage < this.totalPages) return 'loading'; // 正在加载更多（非首次）
      if (!this.isLoading && this.currentPage >= this.totalPages && this.rawRecords.length > 0) return 'nomore'; // 没有更多数据
      if (this.isLoading && this.rawRecords.length === 0) return 'loading'; // 首次加载中
      return 'loadmore'; // 默认状态，在逻辑正确且空状态处理无数据的情况下不应显示
    }
  },
  onLoad() {
    // 页面加载时获取第一页记录
    this.fetchRecords();
    // this.fetchStatistics(); // 此处可以调用获取统计数据的方法
  },
  onReachBottom() {
    // 页面滚动到底部时，如果还有更多页且不处于加载中，则获取下一页记录
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchRecords(this.currentPage + 1);
    }
  },
  methods: {
    getRecordTypeColor(type) {
      // 示例：根据记录类型返回颜色
      if (type === 'income') return '#e7f8ef'; // 浅绿色 (收入)
      if (type === 'expense') return '#fdecea'; // 浅红色 (支出)
      return '#e7f1ff'; // 默认浅蓝色
    },
    getRecordTypeIcon(type) {
      // 示例：根据记录类型返回图标名称
      if (type === 'income') return 'arrow-up-fill';
      if (type === 'expense') return 'arrow-down-fill';
      if (type === 'water_usage') return 'tint-fill'; // 用水记录示例
      return 'file-text-fill'; // 默认图标
    },
    getRecordTypeIconColor(type) {
      // 示例：根据记录类型返回图标颜色
      if (type === 'income') return '#19be6b'; // 绿色
      if (type === 'expense') return '#fa3534'; // 红色
      if (type === 'water_usage') return '#2979ff'; // 蓝色
      return '#909399'; // 默认灰色
    },
    getStatusColor(statusCode) {
        // 示例：根据状态码返回颜色
        if (statusCode === 'completed' || statusCode === 1) return '#19be6b'; // 成功状态为绿色
        if (statusCode === 'pending' || statusCode === 0) return '#ff9900'; // 处理中状态为橙色
        if (statusCode === 'failed' || statusCode === 2) return '#fa3534'; // 失败状态为红色
        return '#909399'; // 默认灰色
    },
    getCurrentMonthString() {
        // 获取当前年月字符串，例如 "2024年1月"
        const date = new Date();
        return `${date.getFullYear()}年${date.getMonth() + 1}月`;
    },
    async fetchRecords(page = 1, filter = null) {
      // 获取记录列表的异步方法
      this.isLoading = true;
      // this.errorLoading = false; // 每次尝试获取数据时重置错误状态（或者仅在成功时重置）

      let apiParams = { page: page, limit: 15 }; // API分页参数
      if (filter && filter !== 'all') {
        // 假设API接收类似 'currentMonth', 'lastMonth' 的筛选参数
        apiParams.period = filter;
      }

      try {
        const res = await getGenericRecords(apiParams); // 调用API

        if (res && res.data && res.data.list) { // 检查API响应是否符合预期
          if (page === 1) {
            this.rawRecords = res.data.list; // 第一页数据，直接覆盖
          } else {
            this.rawRecords = this.rawRecords.concat(res.data.list); // 后续页数据，追加
          }
          // 更新当前页码和总页数，如果API未返回则使用当前值
          this.currentPage = parseInt(res.data.current_page || this.currentPage);
          this.totalPages = parseInt(res.data.last_page || this.totalPages);

          // 如果API返回空列表，表示没有更多数据了
          if (res.data.list.length === 0) {
             this.totalPages = this.currentPage; // 假设当前页即为最后一页
          }
        } else {
          // API响应为空或数据格式不符合预期
          console.warn('通用记录数据未找到或格式不正确:', res);
          if (page === 1) this.rawRecords = []; // 如果是第一页且获取数据失败，则清空列表
          this.totalPages = page; // 如果响应错误，则阻止后续加载尝试
        }
      } catch (err) {
        console.error('获取通用记录时发生错误:', err);
        this.errorLoading = true; // 仅在捕获到错误时设置错误状态
        if (page === 1) this.rawRecords = []; // 如果是第一页且发生错误，则清空列表
      } finally {
        this.isLoading = false; // 无论成功或失败，结束加载状态
      }
    },
    // async fetchStatistics() { /* 获取统计数据的方法 */ },
    goBack() {
      uni.navigateBack(); // 返回上一页
    },
    setFilter(filterValue) {
      // 设置筛选条件并重新获取数据
      this.currentFilter = filterValue;
      this.currentPage = 1; // 重置到第一页
      this.rawRecords = []; // 清空现有记录
      this.totalPages = 1; // 重置总页数
      this.errorLoading = false; // 重置错误状态
      this.fetchRecords(1, this.currentFilter); // 获取第一页筛选后的数据
    },
    showFilterDialog() { // 如果重新启用“筛选”选项，则此方法可用
      uni.showToast({
        title: '显示筛选对话框',
        icon: 'none'
      })
    },
    viewRecordDetail(record) {
      uni.showToast({
        title: `查看记录详情: ID ${record.id}`,
        icon: 'none'
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.state-container {
  padding: 80rpx 0; // Increased padding for better visibility
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column; // Ensure button in error state is below empty icon
}

.load-more-component {
  margin: 30rpx 0;
}

// Styles for record item amounts
.amount-text {
  font-size: 30rpx;
  font-weight: 500;
  display: block;
}
.positive-amount {
  color: #19be6b; // Green for positive amounts (e.g. income)
}
.negative-amount {
  color: #fa3534; // Red for negative amounts (e.g. expense)
}


/* Existing styles below, ensure no conflicts */
.header {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  padding: 30rpx;
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

.header-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
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
  font-weight: 600;
  color: #ffffff;
  margin-left: 20rpx;
}

.stats-card {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20rpx;
}

.stats-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
}

.stats-date {
  font-size: 26rpx;
  color: #909399;
}

.stats-content {
  display: flex;
  justify-content: space-between;
}

.stats-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stats-label {
  font-size: 26rpx;
  color: #909399;
  margin-bottom: 10rpx;
}

.stats-value {
  font-size: 36rpx;
  font-weight: bold;
  color: #3b82f6;
}

.filter-options {
  display: flex;
  padding: 0 30rpx;
  margin-bottom: 20rpx;
  overflow-x: auto;
  white-space: nowrap;
}

.filter-option {
  padding: 16rpx 30rpx;
  background-color: #ffffff;
  border-radius: 30rpx;
  margin-right: 20rpx;
  font-size: 26rpx;
  color: #606266;
  border: 2rpx solid #e5e7eb;
  transition: all 0.2s;
  
  &.active {
    background-color: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
  }
  
  &:active {
    transform: scale(0.95);
  }
}

.records-list {
  padding: 0 30rpx 50rpx;
}

.date-group {
  margin-bottom: 30rpx;
}

.date-header {
  padding: 16rpx 10rpx;
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #f5f7fa;
}

.date-text {
  font-size: 28rpx;
  font-weight: 500;
  color: #909399;
}

.record-card {
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
  transition: transform 0.2s;
  
  &:active {
    transform: scale(0.98);
  }
}

.record-header {
  display: flex;
  align-items: flex-start;
  margin-bottom: 20rpx;
}

.record-icon-container {
  margin-right: 20rpx;
}

.record-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 16rpx;
  background-color: #e7f1ff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.record-info {
  flex: 1;
}

.record-title {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
  display: block;
}

.record-time {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.record-amount {
  text-align: right;
}

.amount-text {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
  display: block;
}

.volume-text {
  font-size: 24rpx;
  color: #909399;
  margin-top: 8rpx;
  display: block;
}

.record-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16rpx;
  border-top: 2rpx solid #f2f3f5;
}

.location-info {
  display: flex;
  align-items: center;
}

.location-text {
  font-size: 24rpx;
  color: #909399;
  margin-left: 8rpx;
}

.detail-link {
  font-size: 24rpx;
  color: #3b82f6;
}

.empty-state {
  padding: 100rpx 0;
}
</style>