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
      <u-loading-icon v-if="isLoadingStats && statCardsForSwiper.length === 0" mode="circle" size="28" text="统计加载中..."></u-loading-icon>
      <u-swiper
        v-if="!isLoadingStats && statCardsForSwiper.length > 0"
        :list="statCardsForSwiper"
        keyName="title" <!-- 使用 statCardsForSwiper 中的属性，或者自定义key -->
        :height="220"
        :radius="16"
        :interval="3500"
        indicator
        indicatorMode="dot"
        circular
      >
        <!-- 自定义Swiper Item的内部结构 -->
        <template v-slot:default="{item}">
          <view class="stat-card-item" :class="`stat-card-${item.themeColor || 'blue'}`">
            <view class="stat-card-header">
              <text class="stat-card-title">{{ item.title }}</text>
              <text class="stat-card-subtitle">{{ item.subtitle }}</text>
            </view>
            <view class="stat-card-content">
              <view class="stat-item">
                <text class="stat-value">{{ item.count }}次</text>
                <text class="stat-label">用水次数</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">{{ item.volume }}L</text>
                <text class="stat-label">用水量</text>
              </view>
              <view class="stat-item">
                <text class="stat-value">¥{{ item.amount }}</text>
                <text class="stat-label">消费金额</text>
              </view>
            </view>
          </view>
        </template>
      </u-swiper>
      <u-empty mode="data" text="暂无统计数据" v-if="!isLoadingStats && statCardsForSwiper.length === 0"></u-empty>
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
        <!-- 加载中提示 (针对列表区域) -->
        <u-loading-icon v-if="isLoadingRecords && groupedRecords.length === 0 && page === 1" mode="circle" size="28" text="记录加载中..."></u-loading-icon>

        <!-- 日期分组 -->
        <template v-if="!isLoadingRecords || groupedRecords.length > 0"> <!-- 避免刷新时列表闪烁 -->
          <view class="date-group" v-for="(group) in groupedRecords" :key="group.date">
            <view class="date-header">
              <view class="date-badge">
                <text>{{ group.date }}</text>
              </view>
            </view>

            <!-- 记录项 -->
            <view
              class="record-card"
              v-for="(item) in group.records"
              :key="item.id" <!-- 使用记录的唯一ID -->
              @click="viewRecordDetail(item)"
            >
              <view class="record-left">
                <!-- API返回的icon和bgColor可以直接用，或前端根据设备类型映射 -->
                <view class="record-icon" :style="{backgroundColor: item.iconBg || getRandomColor(item.id)}">
                  <u-icon :name="item.iconName || 'arrow-down'" color="#ffffff" size="24"></u-icon> <!-- 假设有iconName -->
                </view>
              </view>
              <view class="record-content">
                <view class="record-header">
                  <text class="record-title">{{ item.deviceName }}</text>
                  <text class="record-amount" :style="{color: parseFloat(item.amount) >= 0 ? '#10b981' : '#fa3534'}">
                    {{ parseFloat(item.amount) >= 0 ? '+' : '-' }}¥{{ Math.abs(parseFloat(item.amount)).toFixed(2) }}
                  </text>
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
        </template>
        
        <!-- 无记录提示 -->
        <u-empty
          v-if="!isLoadingRecords && groupedRecords.length === 0 && loadMoreStatus !== 'loading'"
          mode="list"
          text="暂无使用记录"
          textColor="#909399"
          iconSize="240"
          marginTop="50"
        ></u-empty>
        
        <!-- 加载更多 -->
        <u-loadmore 
          v-if="groupedRecords.length > 0 || loadMoreStatus === 'loading'" <!-- 只有在有记录或正在加载时才显示loadmore -->
          :status="loadMoreStatus" 
          @loadmore="loadMore"
          marginTop="20" 
          marginBottom="20" 
          bgColor="transparent"
          nomoreText="没有更多记录了"
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
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      title: '使用记录', // 页面标题
      searchKeyword: '', // 搜索关键词 (如果模板中有)

      // 统计卡片数据 (将由API填充)
      usageStatsData: { // 用于替换 statCards.content 的HTML字符串
        currentMonth: { title: '本月用水统计', subtitle: '', count: 0, volume: 0, amount: 0 },
        lastMonth: { title: '上月用水统计', subtitle: '', count: 0, volume: 0, amount: 0 },
        // 可以根据API返回添加更多，例如年度统计
      },
      statCardsForSwiper: [], // 给u-swiper用的列表，将根据usageStatsData动态构建
      isLoadingStats: false, // 统计数据加载状态

      // 图表相关 (暂时保留静态或注释，待后端API)
      currentPeriod: 'week', // 图表周期选择 (周、月、季、年)
      periodOptions: [
        { label: '周', value: 'week' }, // 对应API的 date_type 参数可能需要映射
        { label: '月', value: 'month' }, // 'current_month' / 'last_month' 或 YYYY-MM
        // { label: '季', value: 'quarter' }, // API可能不支持，需要确认
        // { label: '年', value: 'year' }    // API可能不支持，需要确认
      ],
      mockChartData: [50, 80, 120, 90, 60, 100, 70], // 模拟图表数据
      // chartData: [], // 实际图表数据，将由API填充

      // 筛选器模型
      deviceFilter: 0, // 设备筛选，0为全部
      dateFilter: 0,   // 日期筛选，0为全部时间 (需要映射到API的filter_type或date_from/to)
      sortFilter: 0,   // 排序筛选 (需要映射到API的orderby和orderway)

      // 筛选器选项 (设备选项可以从API动态获取，或保持静态)
      deviceOptions: [{ label: '全部设备', value: 0 }], // 将从API获取或保持简单
      dateOptions: [ // 这个可以保持静态，逻辑在fetchRecords中处理
        { label: '全部时间', value: 0, filter_type: 'all' },
        { label: '今天', value: 1, filter_type: 'today' }, // 假设API支持 'today'
        { label: '昨天', value: 2, filter_type: 'yesterday' }, // 假设API支持 'yesterday'
        { label: '本周', value: 3, filter_type: 'this_week' }, // 假设API支持 'this_week'
        { label: '本月', value: 4, filter_type: 'current_month' }
      ],
      sortOptions: [ // 排序选项，需要映射到API的orderby和orderway
        { label: '时间降序', value: 0, orderby: 'createtime', orderway: 'desc' },
        { label: '时间升序', value: 1, orderby: 'createtime', orderway: 'asc' },
        { label: '金额降序', value: 2, orderby: 'payamount', orderway: 'desc' },
        { label: '金额升序', value: 3, orderby: 'payamount', orderway: 'asc' }
      ],

      // 记录列表
      recordsList: [], // 扁平的记录列表，将从API获取
      groupedRecords: [], // 按日期分组后的记录列表 (如果保持此结构)
      isLoadingRecords: false, // 记录列表加载状态
      isRefreshing: false, // 下拉刷新状态
      page: 1,
      limit: 10,
      loadMoreStatus: 'loadmore', // 'loadmore', 'loading', 'nomore'

      // 详情弹窗
      showDetail: false,
      selectedRecord: null, // 当前选中的记录详情
    };
  },
  onLoad() {
    this.initializePage(); // 初始化页面数据
    // TODO: 获取设备列表填充 deviceOptions (如果需要动态设备筛选)
  },
  methods: {
    // 初始化页面及首次加载数据
    initializePage() {
      this.fetchStatistics();
      // this.fetchChartData(); // 图表数据获取 (待API)
      this.fetchRecords(false); // 首次加载记录列表
    },
    // 下拉刷新
    onRefresh() {
      if (this.isRefreshing) return;
      this.isRefreshing = true;
      // 重置筛选条件到默认值（如果需要）
      // this.deviceFilter = 0;
      // this.dateFilter = 0;
      // this.sortFilter = 0;
      Promise.all([
        this.fetchStatistics(),
        // this.fetchChartData(), // 图表数据获取
        this.fetchRecords(false) // 重置并加载第一页记录
      ]).finally(() => {
        this.isRefreshing = false;
        uni.stopPullDownRefresh(); // 停止uniapp的下拉刷新动画
      });
    },
    // 加载更多记录
    loadMore() {
      if (this.loadMoreStatus === 'loadmore') {
        this.fetchRecords(true);
      }
    },
    // 获取统计数据
    async fetchStatistics() {
      this.isLoadingStats = true;
      try {
        // 获取本月统计 (后端API的date_type参数是current_month, last_month, 或 YYYY-MM)
        const currentMonthRes = await this.$api.getUsageStatistics({ date_type: 'current_month' });
        if (currentMonthRes.code === 1 && currentMonthRes.data) {
          this.usageStatsData.currentMonth = {
            title: '本月用水统计',
            subtitle: this.$u.timeFormat(new Date(), 'yyyy年mm月'), // 当前年月
            count: currentMonthRes.data.count || 0,
            volume: parseFloat(currentMonthRes.data.volume || 0).toFixed(1), // 保留一位小数
            amount: parseFloat(currentMonthRes.data.amount || 0).toFixed(2)
          };
        }
        // 获取上月统计
        const lastMonthRes = await this.$api.getUsageStatistics({ date_type: 'last_month' });
        if (lastMonthRes.code === 1 && lastMonthRes.data) {
           const lastMonthDate = new Date();
           lastMonthDate.setMonth(lastMonthDate.getMonth() - 1);
          this.usageStatsData.lastMonth = {
            title: '上月用水统计',
            subtitle: this.$u.timeFormat(lastMonthDate, 'yyyy年mm月'),
            count: lastMonthRes.data.count || 0,
            volume: parseFloat(lastMonthRes.data.volume || 0).toFixed(1),
            amount: parseFloat(lastMonthRes.data.amount || 0).toFixed(2)
          };
        }
        this.updateStatCardsForSwiper(); // 更新轮播卡片数据
      } catch (error) {
        console.error('fetchStatistics error:', error);
        toast('获取统计数据失败');
      } finally {
        this.isLoadingStats = false;
      }
    },
    // 更新轮播用的统计卡片数据 (将HTML字符串方式改为数据驱动)
    updateStatCardsForSwiper() {
        const cards = [];
        const monthColors = ['blue', 'green', 'purple']; // 示例颜色

        Object.entries(this.usageStatsData).forEach(([key, stat], index) => {
            if(stat.title){ //确保有数据才添加
                 cards.push({
                    // 不再使用content HTML字符串，而是传递数据给swiper-item内部的组件
                    title: stat.title,
                    subtitle: stat.subtitle,
                    count: stat.count,
                    volume: stat.volume,
                    amount: stat.amount,
                    themeColor: monthColors[index % monthColors.length] // 循环使用颜色
                });
            }
        });
        this.statCardsForSwiper = cards;
    },

    // 获取记录列表 (支持分页和筛选)
    async fetchRecords(isLoadMore = false) {
      if (isLoadMore && (this.loadMoreStatus === 'loading' || this.loadMoreStatus === 'nomore')) {
        return; // 防止重复加载
      }
      if (!isLoadMore) {
        this.page = 1;
        // this.recordsList = []; // 使用 groupedRecords 时，清空 groupedRecords
        this.groupedRecords = [];
        this.loadMoreStatus = 'loadmore';
        this.isLoadingRecords = true; // 首次加载或刷新时显示列表区域的loading
      } else {
        this.loadMoreStatus = 'loading';
      }

      // 构建API请求参数
      let apiParams = {
        page: this.page,
        limit: this.limit,
      };

      // 处理日期筛选 (dateFilter 的 value 对应 dateOptions 的 value)
      const selectedDateFilter = this.dateOptions.find(opt => opt.value === this.dateFilter);
      if (selectedDateFilter && selectedDateFilter.filter_type !== 'all') {
        apiParams.filter_type = selectedDateFilter.filter_type; // 'today', 'current_month', etc.
        // 如果后端支持 date_from, date_to, 则需要根据 filter_type 计算
      }

      // 处理设备筛选 (deviceFilter 的 value 对应 deviceOptions 的 value)
      if (this.deviceFilter !== 0 && this.deviceOptions[this.deviceFilter]) {
         // 假设 deviceOptions 的 value 直接是 goods_id 或其他设备标识
        apiParams.goods_id = this.deviceOptions.find(opt => opt.value === this.deviceFilter).value;
      }

      // 处理排序 (sortFilter 的 value 对应 sortOptions 的 value)
      const selectedSortFilter = this.sortOptions.find(opt => opt.value === this.sortFilter);
      if (selectedSortFilter) {
        apiParams.orderby = selectedSortFilter.orderby;
        apiParams.orderway = selectedSortFilter.orderway;
      }

      try {
        const res = await this.$api.getUsageRecordsList(apiParams);
        if (res.code === 1 && res.data && res.data.data) {
          const newRecords = res.data.data.map(record => {
            // API数据适配 (确保与模板中使用的字段一致)
            return {
              id: record.record_id, // 订单ID，作为记录ID
              order_sn: record.order_sn,
              deviceName: record.device_name || '未知设备',
              time: record.time, // 后端已格式化 'Y-m-d H:i:s'
              amount: parseFloat(record.amount || 0).toFixed(2),
              volume: parseFloat(record.volume || 0).toFixed(1), // 用水量，保留一位小数
              location: record.location || '未知地点',
              // 以下为弹窗详情可能需要的额外字段，确保API返回或在此处处理
              pay_type_text: record.pay_type_text || (record.paytype ? this.getPayTypeText(record.paytype) : '未知'), // 支付方式
              // ... 其他弹窗中需要的字段
            };
          });

          if (isLoadMore) {
            // this.recordsList = this.recordsList.concat(newRecords); // 如果使用扁平列表
            this.groupAndSetRecords(this.recordsList.concat(newRecords)); // 追加后再分组
          } else {
            // this.recordsList = newRecords; // 如果使用扁平列表
             this.groupAndSetRecords(newRecords); // 直接分组新数据
          }
          this.recordsList = this.recordsList.concat(newRecords); // 始终维护一个扁平列表用于下次追加

          this.page++;
          this.loadMoreStatus = newRecords.length < this.limit ? 'nomore' : 'loadmore';
        } else {
          if (!isLoadMore) this.groupAndSetRecords([]); // 清空分组
          this.loadMoreStatus = isLoadMore ? 'loadmore' : 'nomore';
          toast(res.msg || '获取使用记录失败');
        }
      } catch (error) {
        console.error('fetchRecords error:', error);
        if (!isLoadMore) this.groupAndSetRecords([]);
        this.loadMoreStatus = 'loadmore';
        toast('网络请求使用记录出错');
      } finally {
        this.isLoadingRecords = false;
        if (!isLoadMore && this.loadMoreStatus !== 'nomore' && this.recordsList.length === 0){
             // 如果首次加载后列表为空，但不是nomore状态（例如API错误），确保显示空提示而不是无限loading
        }
      }
    },

    // 将扁平记录列表按日期分组 (与模板中的 groupedRecords 对应)
    groupAndSetRecords(records) {
        const grouped = {};
        records.forEach(record => {
            // 从 record.time (YYYY-MM-DD HH:MM:SS) 中提取日期部分 (YYYY-MM-DD)
            // 然后可以进一步格式化为 "今天", "昨天", 或 "MM月DD日"
            const dateKey = this.formatDateForGrouping(record.time);
            if (!grouped[dateKey]) {
                grouped[dateKey] = { date: dateKey, records: [] };
            }
            grouped[dateKey].records.push(record);
        });
        this.groupedRecords = Object.values(grouped);
    },

    // 格式化日期用于分组的辅助函数
    formatDateForGrouping(dateTimeStr) {
        if (!dateTimeStr) return '未知日期';
        const today = this.$u.timeFormat(new Date(), 'yyyy-mm-dd');
        const yesterday = this.$u.timeFormat(new Date(Date.now() - 86400000), 'yyyy-mm-dd');
        const recordDate = dateTimeStr.substring(0, 10);

        if (recordDate === today) return '今天';
        if (recordDate === yesterday) return '昨天';
        // 对于更早的日期，可以显示 MM月DD日 或 YYYY年MM月DD日
        return this.$u.timeFormat(recordDate, 'mm月dd日');
    },

    // (其他方法如 setPeriod, viewRecordDetail, closeDetail, getRandomColor, generateOrderNumber 保持或按需调整)
    // 支付方式文本转换 (如果API不直接提供 pay_type_text)
    getPayTypeText(payType) {
        const payTypeMap = {
            'wechat': '微信支付',
            'alipay': '支付宝',
            'money': '余额支付',
            'system': '系统支付'
            // ... 其他支付方式
        };
        return payTypeMap[payType] || payType || '未知';
    },
    // ...
    // 示例: 筛选条件变化时重新加载数据
    handleFilterChange() {
        this.fetchRecords(false); // 重置并加载第一页
    },
    setPeriod(periodValue) {
      this.currentPeriod = periodValue;
      // TODO: 根据 currentPeriod 更新图表数据 (fetchChartData)
      // 如果记录列表也受此周期影响，则也需要调用 this.fetchRecords(false)
      // 但当前设计中，图表周期和列表的日期筛选是分开的
      toast(`图表周期切换为: ${this.periodOptions.find(p=>p.value === periodValue).label} (图表功能待实现)`);
    },
    viewRecordDetail(record) {
      this.selectedRecord = record; // API返回的记录直接赋值
      // 确保 selectedRecord 包含弹窗所需的所有字段，如 pay_type_text, order_sn
      // 如果API返回的item不直接包含，需要在这里或fetchRecords中做转换
      this.showDetail = true;
    },
    closeDetail() {
      this.showDetail = false;
      this.selectedRecord = null;
    },
    // 模拟辅助函数，实际应从API获取或在fetchRecords中处理
    getRandomColor(id) {
      const colors = ['#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'];
      return colors[id % colors.length];
    },
    generateOrderNumber(id) { // 这个应该从API获取 (order_sn)
      const date = new Date();
      const dateStr = date.getFullYear().toString() +
                     (date.getMonth() + 1).toString().padStart(2, '0') +
                     date.getDate().toString().padStart(2, '0');
      return dateStr + (id ? id.toString().padStart(8, '0') : Math.floor(Math.random()*100000000));
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