<template>
  <view class="container">
    <!-- 顶部状态栏 -->
    <u-navbar
      title="使用记录"
      :autoBack="true"
      bgColor="#ffffff"
      titleStyle="color: #333333; font-weight: 600"
      :fixed="true"
      :border="false"
      :immersive="true"
    ></u-navbar>
    
    <!-- 统计卡片 -->
    <view class="stats-section">
      <u-swiper
        :list="statCards"
        keyName="content"
        :height="220"
        :radius="16"
        :interval="3000"
        :effect3d="true"
        :autoplay="false"
        indicatorMode="dot"
      ></u-swiper>
    </view>
    
    <!-- 图表区域 -->
    <view class="chart-section">
      <view class="chart-header">
        <text class="chart-title">用水趋势</text>
        <view class="chart-period-selector">
          <view 
            class="period-option" 
            v-for="(item, index) in periodOptions" 
            :key="index"
            :class="{active: currentPeriod === item.value}"
            @click="setPeriod(item.value)"
          >
            <text>{{ item.label }}</text>
          </view>
        </view>
      </view>
      <view class="chart-placeholder">
        <!-- 实际应用中这里应该是一个图表组件 -->
        <view class="chart-mock">
          <view class="chart-bar" v-for="(item, index) in mockChartData" :key="index" :style="{height: item + 'rpx'}"></view>
        </view>
        <view class="chart-labels">
          <text v-for="(item, index) in 7" :key="index">{{ index + 1 }}日</text>
        </view>
      </view>
    </view>
    
    <!-- 筛选器 -->
    <view class="filter-section">
      <u-dropdown>
        <u-dropdown-item v-model="deviceFilter" title="设备" :options="deviceOptions"></u-dropdown-item>
        <u-dropdown-item v-model="dateFilter" title="日期" :options="dateOptions"></u-dropdown-item>
        <u-dropdown-item v-model="sortFilter" title="排序" :options="sortOptions"></u-dropdown-item>
      </u-dropdown>
    </view>
    
    <!-- 记录列表 -->
    <scroll-view
      scroll-y
      class="records-scroll"
      @scrolltolower="loadMore"
      refresher-enabled
      :refresher-triggered="isRefreshing"
      @refresherrefresh="onRefresh"
    >
      <view class="records-list">
        <!-- 日期分组 -->
        <view class="date-group" v-for="(group, groupIndex) in groupedRecords" :key="groupIndex">
          <view class="date-header">
            <view class="date-badge">
              <text>{{ group.date }}</text>
            </view>
          </view>
          
          <!-- 记录项 -->
          <view 
            class="record-card" 
            v-for="(item, index) in group.records" 
            :key="index"
            @click="viewRecordDetail(item)"
          >
            <view class="record-left">
              <view class="record-icon" :style="{backgroundColor: getRandomColor(item.id)}">
                <u-icon name="arrow-down" color="#ffffff" size="24"></u-icon>
              </view>
            </view>
            <view class="record-content">
              <view class="record-header">
                <text class="record-title">{{ item.deviceName }}</text>
                <text class="record-amount">-¥{{ item.amount }}</text>
              </view>
              <view class="record-details">
                <view class="record-detail-item">
                  <u-icon name="clock" size="14" color="#909399"></u-icon>
                  <text class="detail-text">{{ item.time }}</text>
                </view>
                <view class="record-detail-item">
                  <u-icon name="map" size="14" color="#909399"></u-icon>
                  <text class="detail-text">{{ item.location }}</text>
                </view>
              </view>
              <view class="record-footer">
                <view class="volume-badge">
                  <text>{{ item.volume }}L</text>
                </view>
                <u-button 
                  type="info" 
                  size="mini" 
                  shape="circle"
                  plain
                  @click.stop="viewRecordDetail(item)"
                >详情</u-button>
              </view>
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
        
        <!-- 加载更多 -->
        <u-loadmore 
          v-if="groupedRecords.length > 0"
          :status="loadMoreStatus" 
          marginTop="20" 
          marginBottom="20" 
          bgColor="transparent"
        />
      </view>
    </scroll-view>
    
    <!-- 记录详情弹窗 -->
    <u-popup
      :show="showDetail"
      @close="closeDetail"
      mode="center"
      borderRadius="16"
      width="80%"
      height="auto"
    >
      <view class="record-detail-popup" v-if="selectedRecord">
        <view class="popup-header">
          <text class="popup-title">用水详情</text>
          <u-icon name="close" size="24" color="#909399" @click="closeDetail"></u-icon>
        </view>
        <view class="popup-content">
          <view class="detail-item">
            <text class="detail-label">设备名称</text>
            <text class="detail-value">{{ selectedRecord.deviceName }}</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">使用时间</text>
            <text class="detail-value">{{ selectedRecord.time }}</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">使用地点</text>
            <text class="detail-value">{{ selectedRecord.location }}</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">用水量</text>
            <text class="detail-value">{{ selectedRecord.volume }}L</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">消费金额</text>
            <text class="detail-value">¥{{ selectedRecord.amount }}</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">支付方式</text>
            <text class="detail-value">账户余额</text>
          </view>
          <view class="detail-item">
            <text class="detail-label">交易单号</text>
            <text class="detail-value">{{ generateOrderNumber(selectedRecord.id) }}</text>
          </view>
        </view>
        <view class="popup-footer">
          <u-button 
            type="primary" 
            shape="circle"
            @click="closeDetail"
          >确定</u-button>
        </view>
      </view>
    </u-popup>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: '使用记录',
      currentMonth: '2024年1月',
      isRefreshing: false,
      loadMoreStatus: 'nomore',
      showDetail: false,
      selectedRecord: null,
      currentPeriod: 'week',
      deviceFilter: 0,
      dateFilter: 0,
      sortFilter: 0,
      mockChartData: [50, 80, 120, 90, 60, 100, 70],
      statCards: [
        {
          content: `<view class="stat-card stat-card-blue">
            <view class="stat-card-header">
              <text class="stat-card-title">本月用水统计</text>
              <text class="stat-card-subtitle">2024年1月</text>
            </view>
            <view class="stat-card-content">
              <view class="stat-item">
                <text class="stat-value">12次</text>
                <text class="stat-label">用水次数</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">36L</text>
                <text class="stat-label">用水量</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">¥10.00</text>
                <text class="stat-label">消费金额</text>
              </view>
            </view>
          </view>`
        },
        {
          content: `<view class="stat-card stat-card-green">
            <view class="stat-card-header">
              <text class="stat-card-title">上月用水统计</text>
              <text class="stat-card-subtitle">2023年12月</text>
            </view>
            <view class="stat-card-content">
              <view class="stat-item">
                <text class="stat-value">18次</text>
                <text class="stat-label">用水次数</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">52L</text>
                <text class="stat-label">用水量</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">¥15.60</text>
                <text class="stat-label">消费金额</text>
              </view>
            </view>
          </view>`
        },
        {
          content: `<view class="stat-card stat-card-purple">
            <view class="stat-card-header">
              <text class="stat-card-title">年度用水统计</text>
              <text class="stat-card-subtitle">2023年全年</text>
            </view>
            <view class="stat-card-content">
              <view class="stat-item">
                <text class="stat-value">156次</text>
                <text class="stat-label">用水次数</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">468L</text>
                <text class="stat-label">用水量</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">¥140.40</text>
                <text class="stat-label">消费金额</text>
              </view>
            </view>
          </view>`
        }
      ],
      periodOptions: [
        { label: '周', value: 'week' },
        { label: '月', value: 'month' },
        { label: '季', value: 'quarter' },
        { label: '年', value: 'year' }
      ],
      deviceOptions: [
        { label: '全部设备', value: 0 },
        { label: '森泽净水器 A-001', value: 1 },
        { label: '森泽净水器 B-002', value: 2 }
      ],
      dateOptions: [
        { label: '全部时间', value: 0 },
        { label: '今天', value: 1 },
        { label: '昨天', value: 2 },
        { label: '本周', value: 3 },
        { label: '本月', value: 4 }
      ],
      sortOptions: [
        { label: '时间降序', value: 0 },
        { label: '时间升序', value: 1 },
        { label: '金额降序', value: 2 },
        { label: '金额升序', value: 3 }
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
      // 这里简化处理，实际应用中应该根据筛选条件进行过滤
      return this.recordsList
    }
  },
  methods: {
    setPeriod(period) {
      this.currentPeriod = period
    },
    loadMore() {
      // 模拟加载更多数据
      this.loadMoreStatus = 'loading'
      setTimeout(() => {
        this.loadMoreStatus = 'nomore'
      }, 1500)
    },
    onRefresh() {
      this.isRefreshing = true
      // 模拟刷新数据
      setTimeout(() => {
        this.isRefreshing = false
        uni.showToast({
          title: '刷新成功',
          icon: 'none'
        })
      }, 1500)
    },
    viewRecordDetail(record) {
      this.selectedRecord = record
      this.showDetail = true
    },
    closeDetail() {
      this.showDetail = false
    },
    getRandomColor(id) {
      // 根据ID生成固定的颜色，这样同一个设备的颜色是一致的
      const colors = ['#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6']
      return colors[id % colors.length]
    },
    generateOrderNumber(id) {
      // 生成一个模拟的订单号
      const date = new Date()
      const dateStr = date.getFullYear().toString() +
                     (date.getMonth() + 1).toString().padStart(2, '0') +
                     date.getDate().toString().padStart(2, '0')
      return dateStr + id.toString().padStart(8, '0')
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  min-height: 100vh;
  background-color: #f8f9fc;
  padding-bottom: 30rpx;
}

.stats-section {
  margin: 100rpx 30rpx 30rpx;
}

/* 统计卡片样式 */
:deep(.stat-card) {
  border-radius: 16rpx;
  padding: 30rpx;
  height: 100%;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

:deep(.stat-card-blue) {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

:deep(.stat-card-green) {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

:deep(.stat-card-purple) {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

:deep(.stat-card-header) {
  margin-bottom: 20rpx;
}

:deep(.stat-card-title) {
  font-size: 32rpx;
  font-weight: bold;
  color: #ffffff;
  display: block;
}

:deep(.stat-card-subtitle) {
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 6rpx;
  display: block;
}

:deep(.stat-card-content) {
  display: flex;
  justify-content: space-between;
}

:deep(.stat-item) {
  display: flex;
  flex-direction: column;
  align-items: center;
}

:deep(.stat-value) {
  font-size: 36rpx;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 6rpx;
}

:deep(.stat-label) {
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.8);
}

.chart-section {
  margin: 30rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
}

.chart-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333333;
}

.chart-period-selector {
  display: flex;
  background-color: #f5f7fa;
  border-radius: 30rpx;
  overflow: hidden;
}

.period-option {
  padding: 10rpx 20rpx;
  font-size: 24rpx;
  color: #606266;
  transition: all 0.2s;
  
  &.active {
    background-color: #3b82f6;
    color: #ffffff;
  }
}

.chart-placeholder {
  height: 300rpx;
  position: relative;
}

.chart-mock {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  height: 240rpx;
}

.chart-bar {
  width: 40rpx;
  background: linear-gradient(to top, #3b82f6, #93c5fd);
  border-radius: 6rpx 6rpx 0 0;
}

.chart-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 10rpx;
  
  text {
    font-size: 22rpx;
    color: #909399;
  }
}

.filter-section {
  margin: 0 30rpx 20rpx;
}

.records-scroll {
  height: calc(100vh - 700rpx);
}

.records-list {
  padding: 0 30rpx;
}

.date-group {
  margin-bottom: 30rpx;
}

.date-header {
  margin-bottom: 20rpx;
}

.date-badge {
  display: inline-block;
  padding: 6rpx 20rpx;
  background-color: #f2f3f5;
  border-radius: 30rpx;
  font-size: 24rpx;
  color: #606266;
}

.record-card {
  display: flex;
  background-color: #ffffff;
  border-radius: 16rpx;
  overflow: hidden;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.record-left {
  width: 100rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20rpx 0;
}

.record-icon {
  width: 60rpx;
  height: 60rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.record-content {
  flex: 1;
  padding: 20rpx;
  border-left: 2rpx dashed #e5e7eb;
}

.record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16rpx;
}

.record-title {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
}

.record-amount {
  font-size: 30rpx;
  font-weight: 500;
  color: #333333;
}

.record-details {
  margin-bottom: 16rpx;
}

.record-detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 8rpx;
}

.detail-text {
  font-size: 24rpx;
  color: #909399;
  margin-left: 8rpx;
}

.record-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.volume-badge {
  background-color: #e7f1ff;
  border-radius: 30rpx;
  padding: 4rpx 16rpx;
  font-size: 22rpx;
  color: #3b82f6;
}

.empty-state {
  padding: 100rpx 0;
}

.record-detail-popup {
  width: 100%;
  padding: 30rpx;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30rpx;
}

.popup-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
}

.popup-content {
  margin-bottom: 30rpx;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 16rpx 0;
  border-bottom: 2rpx solid #f2f3f5;
}

.detail-label {
  font-size: 28rpx;
  color: #909399;
}

.detail-value {
  font-size: 28rpx;
  color: #333333;
  font-weight: 500;
}
</style>