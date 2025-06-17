<template>
  <view class="device-detail-container">
    <u-navbar title="设备详情" :autoBack="true" placeholder safeAreaInsetTop></u-navbar>

    <u-loading-page :loading="isLoading" loading-text="正在加载设备详情..."></u-loading-page>

    <view v-if="!isLoading && deviceDetail" class="content-wrapper">
      <!-- 设备图片 -->
      <view class="device-image-section" v-if="deviceDetail.product_image">
        <u-image :src="deviceDetail.product_image" width="100%" height="400rpx" mode="aspectFit"></u-image>
      </view>
      <view class="device-image-section placeholder" v-else>
        <u-icon name="photo" size="50" color="#c0c4cc"></u-icon>
        <text class="placeholder-text">暂无图片</text>
      </view>

      <!-- 基本信息 -->
      <u-cell-group title="基本信息" class="info-group">
        <u-cell title="设备名称" :value="deviceDetail.product_name || '未知'"></u-cell>
        <u-cell title="设备状态">
          <view slot="value" class="status-value">
            <text :style="{ color: deviceDetail.is_expired_flag ? '#fa3534' : '#19be6b' }">{{ deviceDetail.status_text || '未知' }}</text>
          </view>
        </u-cell>
        <u-cell title="订单号" :value="deviceDetail.order_sn || '未知'"></u-cell>
      </u-cell-group>

      <!-- 服务期限 -->
      <u-cell-group title="服务期限" class="info-group">
        <u-cell title="安装日期" :value="deviceDetail.installation_date || '未知'"></u-cell>
        <u-cell title="到期日期" :value="deviceDetail.expire_date_raw || '长期有效'"></u-cell>
        <u-cell title="服务剩余" :value="deviceDetail.remaining_text || 'N/A'"></u-cell>
      </u-cell-group>

      <!-- 位置信息 -->
      <u-cell-group title="位置信息" class="info-group">
        <u-cell title="安装地址">
          <text slot="value" class="address-text">{{ deviceDetail.parsed_address && deviceDetail.parsed_address.full || deviceDetail.full_address_string || '未知' }}</text>
        </u-cell>
         <u-cell title="联系人" :value="deviceDetail.receiver || deviceDetail.parsed_address && deviceDetail.parsed_address.name || '未知'"></u-cell>
         <u-cell title="联系电话" :value="deviceDetail.mobile || deviceDetail.parsed_address && deviceDetail.parsed_address.phone || '未知'"></u-cell>
      </u-cell-group>

      <!-- 产品规格 -->
      <u-cell-group title="产品规格" class="info-group" v-if="deviceDetail.specification_in_order">
        <u-cell title="规格参数" :label="deviceDetail.specification_in_order"></u-cell>
      </u-cell-group>

      <!-- 订单相关 -->
      <u-cell-group title="订单快照" class="info-group">
        <u-cell title="购买商品" :value="deviceDetail.goods_title_in_order || deviceDetail.product_name || '未知'"></u-cell>
        <u-cell title="购买数量" :value="String(deviceDetail.quantity || 1)"></u-cell> <!-- 确保是字符串 -->
        <u-cell title="购买单价" :value="`¥${parseFloat(deviceDetail.price_per_item_in_order || 0).toFixed(2)}`"></u-cell>
      </u-cell-group>

      <!-- 支付信息 -->
       <u-cell-group title="支付信息" class="info-group">
        <u-cell title="支付金额" :value="`¥${parseFloat(deviceDetail.amount || 0).toFixed(2)}`"></u-cell>
        <u-cell title="支付方式" :value="deviceDetail.pay_type_text || '未知'"></u-cell>
      </u-cell-group>


      <!-- 产品描述 -->
      <view class="description-section info-group" v-if="deviceDetail.product_description">
        <view class="u-cell-group__title">产品描述</view>
        <view class="description-content">
          <u-parse :content="deviceDetail.product_description"></u-parse>
        </view>
      </view>

      <!-- 备注信息 -->
      <u-cell-group title="订单备注" class="info-group" v-if="deviceDetail.memo">
        <u-cell>
            <view slot="title" class="u-cell-text">备注内容</view>
            <view slot="value" class="u-cell-text" style="text-align: left; flex: 2;">
                 <text class="address-text">{{ deviceDetail.memo }}</text>
            </view>
        </u-cell>
      </u-cell-group>


      <!-- 操作按钮区域 -->
      <view class="action-buttons">
        <!-- <u-button type="primary" text="续费服务" customStyle="margin-bottom: 20rpx;"></u-button> -->
        <!-- <u-button type="warning" text="设备报修"></u-button> -->
      </view>
    </view>

    <u-empty v-if="!isLoading && errorMsg" mode="data" :text="errorMsg" marginTop="100"></u-empty>
    <u-empty v-if="!isLoading && !deviceDetail && !errorMsg" mode="data" text="未找到设备详情" marginTop="100"></u-empty>

  </view>
</template>

<script>
import { toast } from '@/utils/utils.js'; // 引入toast

export default {
  data() {
    return {
      orderId: null,        // 从路由参数获取的订单ID
      deviceDetail: null,   // 存储从API获取的设备详情对象
      isLoading: true,      // 页面加载状态，默认为true
      errorMsg: ''          // 错误信息，用于在页面上显示
    };
  },
  onLoad(options) {
    // options对象包含导航传递过来的参数
    if (options.order_id) {
      this.orderId = options.order_id; // 将获取到的order_id存入data
      this.fetchDeviceDetails();       // 调用方法获取设备详情
    } else {
      this.isLoading = false; // 没有order_id，停止加载
      this.errorMsg = '设备ID缺失，无法加载详情'; // 设置错误信息
      toast(this.errorMsg); // 提示用户
      // 考虑是否需要延时返回上一页
      // setTimeout(() => uni.navigateBack(), 1500);
    }
  },
  methods: {
    // 从API获取设备详细信息
    async fetchDeviceDetails() {
      this.isLoading = true;  // 开始加载
      this.errorMsg = '';    // 清除之前的错误信息
      try {
        // 调用API，注意API路径和参数名应与api.js中定义的一致
        // 此处假设api.js中的getDeviceDetail接受 { id: this.orderId }
        // 但后端实际需要的是 order_id，所以api.js中应做相应调整或此处直接传 order_id
        const res = await this.$api.getDeviceDetail({ order_id: this.orderId });

        if (res.code === 1 && res.data) {
          this.deviceDetail = res.data; // 将获取到的数据赋值给deviceDetail
          // 可以在此对返回数据进行进一步处理或格式化，如果后端未完全处理好
          // 例如：日期格式化，特定字段的文本转换等
          // this.deviceDetail.installation_date = this.$u.timeFormat(this.deviceDetail.installation_date_timestamp, 'yyyy-mm-dd');
        } else {
          this.deviceDetail = null; // 清空旧数据
          this.errorMsg = res.msg || '无法加载设备详情'; // 设置错误信息
          toast(this.errorMsg);
        }
      } catch (error) {
        console.error('fetchDeviceDetails error:', error);
        this.deviceDetail = null; // 清空旧数据
        this.errorMsg = '网络请求失败，请稍后再试'; // 设置网络错误信息
        toast(this.errorMsg);
      } finally {
        this.isLoading = false; // 加载完成
      }
    }
    // 可在此添加其他方法，如续费、报修等操作的事件处理函数
  }
};
</script>

<style lang="scss" scoped>
.device-detail-container {
  background-color: #f5f7fa;
  min-height: 100vh;
}

.content-wrapper {
  padding-bottom: 40rpx; // 为底部按钮区预留空间
}

.device-image-section {
  background-color: #ffffff;
  margin-bottom: 20rpx;
  &.placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400rpx;
    background-color: #f0f0f0;
    .placeholder-text {
      margin-top: 10rpx;
      color: #909399;
      font-size: 28rpx;
    }
  }
}

.info-group {
  margin-bottom: 20rpx;
  // uView的cell-group自带样式，这里可按需覆盖或添加额外样式
  .u-cell__title { // 调整标题宽度，防止值太长时被压缩
    flex: 0 0 200rpx; // 固定标题宽度，可根据实际情况调整
  }
   .u-cell__value {
    flex: 1;
    text-align: right;
    color: #606266; // 统一值的颜色
  }
}
.status-value{
    text-align: right; // 确保状态文本靠右
}
.address-text {
  font-size: 28rpx; // 地址字体大小
  line-height: 1.5;   // 地址行高
  text-align: right;
  word-break: break-all; // 长地址换行
}

.description-section {
  background-color: #ffffff;
  padding: 20rpx 30rpx; // 给描述内容一些内边距
  .u-cell-group__title{ // 模拟u-cell-group的标题样式
    font-size: 30rpx;
    color: #303133;
    margin-bottom: 10px;
    padding: 15px 0 5px 15px; // 近似uView的标题padding
  }
  .description-content {
    font-size: 28rpx;
    color: #606266;
    line-height: 1.6;
  }
}

.action-buttons {
  padding: 30rpx;
  background-color: #ffffff; // 给按钮区域一个背景色，或保持透明
  margin-top: 20rpx;
}
</style>
