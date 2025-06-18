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
      <!-- Initial Loading State for the list -->
      <view v-if="isLoading && rawRecords.length === 0 && !errorLoading" class="state-container">
        <u-loading-icon text="正在加载记录..." size="20"></u-loading-icon>
      </view>

      <!-- Error State for the list -->
      <view v-if="errorLoading && rawRecords.length === 0" class="state-container">
        <u-empty mode="network" text="记录加载失败">
          <u-button slot="bottom" type="primary" size="mini" @click="fetchRecords(1)" text="点我重试" :customStyle="{marginTop: '10px'}"></u-button>
        </u-empty>
      </view>

      <!-- Content: Date Groups and Records -->
      <block v-if="!isLoading && !errorLoading && groupedRecords.length > 0">
        <view class="date-group" v-for="(group, groupIndex) in groupedRecords" :key="groupIndex">
          <view class="date-header">
            <text class="date-text">{{ group.date }}</text>
          </view>

          <view
            class="record-card"
            v-for="(item, index) in group.records"
            :key="item.id || index" <!-- Use a unique ID from item if available -->
            @click="viewRecordDetail(item)"
          >
            <view class="record-header">
              <view class="record-icon-container">
                <view class="record-icon" :style="{backgroundColor: getRecordTypeColor(item.type)}">
                  <!-- Example: Display type initial or a generic icon -->
                  <u-icon :name="getRecordTypeIcon(item.type)" :color="getRecordTypeIconColor(item.type)" size="24"></u-icon>
                </view>
              </view>
              <view class="record-info">
                <text class="record-title">{{ item.title || item.description || '未知记录' }}</text>
                <text class="record-time">{{ item.created_at || item.date || item.time || 'N/A' }}</text>
              </view>
              <view class="record-amount" v-if="item.amount">
                 <!-- Conditional prefix for amount based on sign or type -->
                <text class="amount-text" :class="{'positive-amount': item.amount > 0, 'negative-amount': item.amount < 0}">
                  {{ item.amount > 0 ? '+' : '' }}¥{{ item.amount }}
                </text>
                <text class="volume-text" v-if="item.details">{{ item.details }}</text> <!-- Or some other secondary info -->
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
      
      <!-- Empty State (No records after load) -->
      <view v-if="!isLoading && !errorLoading && groupedRecords.length === 0 && rawRecords.length === 0" class="empty-state state-container">
        <u-empty
          mode="history"
          text="暂无相关记录"
          textColor="#909399"
          iconSize="120"
        ></u-empty>
      </view>

      <!-- Load More Component -->
      <u-loadmore v-if="rawRecords.length > 0 || isLoading" :status="loadMoreStatus" @loadmore="fetchRecords(currentPage + 1)" iconType="circle" class="load-more-component"/>
    </view>
  </view>
</template>

<script>
import { getGenericRecords } from '../../api/api';
import { formatDate, groupRecordsByDate } from '../../utils/utils'; // Assuming utils for date formatting and grouping

export default {
  data() {
    return {
      title: '使用记录', // This can be made dynamic if generic records have types
      currentMonth: this.getCurrentMonthString(), // Dynamic current month
      currentFilter: 'all', // Default filter
      statistics: { // Statistics would also likely come from an API or be calculated
        count: 0,
        volume: 0,
        amount: '0.00'
      },
      filterOptions: [
        { label: '全部记录', value: 'all' },
        { label: '本月记录', value: 'currentMonth' }, // Value changed for clarity
        { label: '上月记录', value: 'lastMonth' },   // Value changed for clarity
        // { label: '筛选', value: 'filter', icon: 'filter' } // Filter dialog not in scope for now
      ],
      rawRecords: [], // Stores flat list from API
      isLoading: true,
      errorLoading: false,
      currentPage: 1,
      totalPages: 1,
      // recordsList: [] // Replaced by rawRecords and groupedRecords
    }
  },
  computed: {
    groupedRecords() {
      // Group rawRecords by date. Expects records to have a 'created_at' or similar date field.
      // The groupRecordsByDate function needs to be implemented in utils/utils.js
      // It should take this.rawRecords and return an array of { date: 'Formatted Date', records: [...] }
      if (!this.rawRecords || this.rawRecords.length === 0) {
        return [];
      }
      // Ensure your records have a date property like 'created_at' or 'record_date'
      // The groupRecordsByDate utility would handle this.
      // Example: return groupRecordsByDate(this.rawRecords, 'created_at');
      // For now, a simplified grouping if groupRecordsByDate is not available:
      // This is a placeholder, actual grouping logic would be more complex.
      const_groups = {};
      this.rawRecords.forEach(record => {
        const dateStr = record.date ? formatDate(record.date, 'YYYY-MM-DD') : '未知日期'; // formatDate from utils
        if (!const_groups[dateStr]) {
          const_groups[dateStr] = { date: dateStr, records: [] };
        }
        const_groups[dateStr].records.push(record);
      });
      return Object.values(const_groups).sort((a, b) => new Date(b.date) - new Date(a.date));
    },
    loadMoreStatus() {
      if (this.isLoading && this.currentPage > 0 && this.rawRecords.length > 0 && this.currentPage < this.totalPages) return 'loading';
      if (!this.isLoading && this.currentPage >= this.totalPages && this.rawRecords.length > 0) return 'nomore';
      if (this.isLoading && this.rawRecords.length === 0) return 'loading'; // Initial load
      return 'loadmore'; // Should not be visible if logic is correct and empty state handles no data.
    }
  },
  onLoad() {
    this.fetchRecords();
    // this.fetchStatistics(); // Would fetch statistics data
  },
  onReachBottom() {
    if (this.currentPage < this.totalPages && !this.isLoading) {
      this.fetchRecords(this.currentPage + 1);
    }
  },
  methods: {
    getRecordTypeColor(type) {
      // Example: return a color based on record type
      if (type === 'income') return '#e7f8ef'; // Light green
      if (type === 'expense') return '#fdecea'; // Light red
      return '#e7f1ff'; // Default light blue
    },
    getRecordTypeIcon(type) {
      if (type === 'income') return 'arrow-up-fill';
      if (type === 'expense') return 'arrow-down-fill';
      if (type === 'water_usage') return 'tint-fill';
      return 'file-text-fill'; // Default
    },
    getRecordTypeIconColor(type) {
      if (type === 'income') return '#19be6b';
      if (type === 'expense') return '#fa3534';
      if (type === 'water_usage') return '#2979ff';
      return '#909399';
    },
    getStatusColor(statusCode) {
        // Example:
        if (statusCode === 'completed' || statusCode === 1) return '#19be6b'; // Green for success
        if (statusCode === 'pending' || statusCode === 0) return '#ff9900'; // Orange for pending
        if (statusCode === 'failed' || statusCode === 2) return '#fa3534'; // Red for failed
        return '#909399'; // Default grey
    },
    getCurrentMonthString() {
        const date = new Date();
        return `${date.getFullYear()}年${date.getMonth() + 1}月`;
    },
    async fetchRecords(page = 1, filter = null) {
      this.isLoading = true;
      // this.errorLoading = false; // Reset error on new fetch attempt

      let apiParams = { page: page, limit: 15 };
      if (filter && filter !== 'all') {
        // Assuming filter values like 'currentMonth', 'lastMonth' are sent to API
        apiParams.period = filter;
      }

      try {
        const res = await getGenericRecords(apiParams);

        if (res && res.data && res.data.list) {
          if (page === 1) {
            this.rawRecords = res.data.list;
          } else {
            this.rawRecords = this.rawRecords.concat(res.data.list);
          }
          this.currentPage = parseInt(res.data.current_page || this.currentPage);
          this.totalPages = parseInt(res.data.last_page || this.totalPages);

          if (res.data.list.length === 0) { // If API returns empty list
             this.totalPages = this.currentPage; // Assume no more data
          }
        } else {
          console.warn('Generic records data not found or in unexpected format:', res);
          if (page === 1) this.rawRecords = [];
          this.totalPages = page;
        }
      } catch (err) {
        console.error('Error fetching generic records:', err);
        this.errorLoading = true; // Set error on catch
        if (page === 1) this.rawRecords = [];
      } finally {
        this.isLoading = false;
      }
    },
    // async fetchStatistics() { /* Fetch stats data */ },
    goBack() {
      uni.navigateBack();
    },
    setFilter(filterValue) {
      this.currentFilter = filterValue;
      this.currentPage = 1;
      this.rawRecords = [];
      this.totalPages = 1;
      this.errorLoading = false; // Reset error when filter changes
      this.fetchRecords(1, this.currentFilter);
    },
    showFilterDialog() {
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