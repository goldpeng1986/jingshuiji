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
      <!-- 日期分组 -->
      <view class="date-group" v-for="(group, groupIndex) in groupedRecords" :key="groupIndex">
        <view class="date-header">
          <text class="date-text">{{ group.date }}</text>
        </view>
        
        <!-- 记录项 -->
        <view 
          class="record-card" 
          v-for="(item, index) in group.records" 
          :key="index"
          @click="viewRecordDetail(item)"
        >
          <view class="record-header">
            <view class="record-icon-container">
              <view class="record-icon">
                <u-icon name="arrow-down" color="#3b82f6" size="24"></u-icon>
              </view>
            </view>
            <view class="record-info">
              <text class="record-title">{{ item.deviceName }}</text>
              <text class="record-time">{{ item.time }}</text>
            </view>
            <view class="record-amount">
              <text class="amount-text">-¥{{ item.amount }}</text>
              <text class="volume-text">{{ item.volume }}L</text>
            </view>
          </view>
          <view class="record-footer">
            <view class="location-info">
              <u-icon name="map" size="14" color="#909399"></u-icon>
              <text class="location-text">{{ item.location }}</text>
            </view>
            <text class="detail-link">查看详情</text>
          </view>
        </view>
      </view>
      
      <!-- 无记录提示 -->
      <view v-if="groupedRecords.length === 0" class="empty-state">
        <u-empty
          mode="list"
          icon="http://cdn.uviewui.com/uview/empty/list.png"
          text="暂无使用记录"
          textColor="#909399"
          iconSize="240"
        ></u-empty>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '使用记录',
      currentMonth: '2024年1月',
      currentFilter: 'all',
      statistics: {
        count: 12,
        volume: 36,
        amount: '10.00'
      },
      filterOptions: [
        { label: '全部记录', value: 'all' },
        { label: '本月记录', value: 'current' },
        { label: '上月记录', value: 'last' },
        { label: '筛选', value: 'filter', icon: 'filter' }
      ],
      recordsList: [
        {
          date: '今天',
          records: [
            {
              id: 1,
              deviceName: '森泽净水器 A-001',
              time: '今天 08:30',
              amount: '1.00',
              volume: '3',
              location: '北京市朝阳区建国路88号'
            },
            {
              id: 2,
              deviceName: '森泽净水器 A-001',
              time: '今天 07:15',
              amount: '0.50',
              volume: '1.5',
              location: '北京市朝阳区建国路88号'
            }
          ]
        },
        {
          date: '昨天',
          records: [
            {
              id: 3,
              deviceName: '森泽净水器 A-001',
              time: '昨天 18:45',
              amount: '1.50',
              volume: '4.5',
              location: '北京市朝阳区建国路88号'
            },
            {
              id: 4,
              deviceName: '森泽净水器 B-002',
              time: '昨天 12:30',
              amount: '0.70',
              volume: '2',
              location: '北京市海淀区中关村大街1号'
            }
          ]
        }
      ]
    }
  },
  computed: {
    groupedRecords() {
      // 根据筛选条件返回不同的记录列表
      // 这里简化处理，实际应用中应该根据日期进行筛选
      return this.recordsList
    }
  },
  methods: {
    goBack() {
      uni.navigateBack()
    },
    setFilter(filter) {
      this.currentFilter = filter
      if (filter === 'filter') {
        this.showFilterDialog()
      }
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