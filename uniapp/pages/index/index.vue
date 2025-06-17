<template>
	<view class="container">
		<!-- 授权登录弹窗 -->
		<u-popup :show="showAuthPopup" mode="center" :closeable="true" @close="closeAuthPopup">
			<view class="auth-popup">
				<view class="auth-title">会员登录提示</view>
				<view class="auth-content">
					<text>您还未登录，是否立即登录？</text>
				</view>
				<view class="auth-buttons">
					<u-button type="default" text="取消" @click="closeAuthPopup"></u-button>
					<u-button type="primary" text="立即登录" @click="goToLogin"></u-button>
				</view>
			</view>
		</u-popup>
	
		<!-- Banner轮播图 -->
		<view class="swiper-section">
		  <u-swiper
		    :list="bannerList"
		    keyName="image"
		    :height="200"
		    :interval="3000"
		    :autoplay="true"
		    indicatorMode="dot"
		  ></u-swiper>
		</view>
	
		<!-- 功能菜单 -->
		<view class="menu-section">
			<u-grid :col="4" :border="false">
				<u-grid-item v-for="(item, index) in menuList" :key="index" @click="home_route(item)">
					<view class="menu-item">
						<view :class="['icon-box', item.bgClass]">
							<u-icon :name="item.icon" size="32" :color="item.color"></u-icon>
						</view>
						<text class="menu-text">{{ item.title }}</text>
					</view>
				</u-grid-item>
			</u-grid>
		</view>
		
		<!-- 主要内容区 -->
		<view class="content-section">
		
			<!-- 家用净水机 -->
			<view class="product-card">
				<view class="card-header">
					<view class="header-left">
						<u-icon name="home" size="24" color="#3c9cff"></u-icon>
						<text class="header-title">家用净水机</text>
					</view>
					<u-tag text="热销中" type="primary" size="mini" shape="circle"></u-tag>
				</view>
				<u-line color="#f3f4f6" margin="15rpx 0"></u-line>
				<view class="card-content">
					<view class="info-item" v-for="(item, index) in homeDeviceInfo" :key="index">
						<u-icon :name="item.icon" size="16" color="#3c9cff"></u-icon>
						<text class="ml-2">{{ item.text }}</text>
					</view>
				</view>
				<view class="card-footer">
					<u-button type="primary" text="下单" shape="circle" size="medium" @click="goToFamilyDetail()"></u-button>
				</view>
			</view>
		
			
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			showAuthPopup: false,
			title: '',
			current: 0,
			
			bannerList: [],
			menuList: [],
			homeDeviceInfo: []
		}
	},
	onLoad() {
		// 检查登录状态
		this.checkLoginStatus();
		// 获取首页数据
		this.getHomeData();
	},
	methods: {
		// 获取首页数据
		async getHomeData() {
			this.$api.getIndex().then(res => {
				// console.log(res)
				if (res.code == 1) {
					const { bannerList, title, menuList, homeDeviceInfo } = res.data;
					this.bannerList = bannerList;
					this.title = title;
					this.menuList = menuList;
					this.homeDeviceInfo = JSON.parse(homeDeviceInfo);
				}else{
					this.$refs.paging.complete(false);
				}
			})
		},
		// 检查登录状态
		checkLoginStatus() {
			const token = uni.getStorageSync('token');
			if (!token) {
				this.showAuthPopup = true;
			}
		},
		// 关闭授权弹窗
		closeAuthPopup() {
			this.showAuthPopup = false;
		},
		// 跳转到登录页
		goToLogin() {
			this.showAuthPopup = false;
			uni.navigateTo({
				url: '/pages/me/login'
			});
		},
		showMore() {
			// 显示更多菜单
			uni.$u.toast('更多功能开发中...');
		},
		showSearch() {
			// 显示搜索页面
			uni.$u.toast('搜索功能开发中...');
		},
		home_route(item) {
			console.log(item.url);
			let res=uni.$u.route(item.url);
			if(res==false){
				uni.$u.toast('请登录后查看！');
			}
		},
		goToFamilyDetail() {
			// 跳转到家用净水机详情页
			uni.navigateTo({
				url: '/pages/shop/family'
			});
		},
		tabbarChange(index) {
			this.current = index
		}
	}
}
</script>

<style lang="scss" scoped>
.container {
	min-height: 100vh;
	background-color: #f8f8f8;
}

.banner-section {
	padding: 0rpx;
}

.menu-section {
	margin: 20rpx;
	padding: 30rpx 20rpx;
	background-color: #ffffff;
	border-radius: 16rpx;
	box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
	
	.menu-item {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 10rpx;
		
		.icon-box {
			width: 90rpx;
			height: 90rpx;
			border-radius: 20rpx;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 16rpx;
			transition: transform 0.2s;
			
			&:active {
				transform: scale(0.95);
			}
			
			&.bg-red { background-color: #fef0f0; }
			&.bg-blue { background-color: #ecf5ff; }
			&.bg-orange { background-color: #fdf6ec; }
			&.bg-green { background-color: #f0f9eb; }
		}
		
		.menu-text {
			font-size: 26rpx;
			color: #303133;
			font-weight: 500;
		}
	}
}

.content-section {
	padding: 20rpx;
	
	.section-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20rpx;
	}
	
	.section-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #303133;
		position: relative;
		padding-left: 20rpx;
		
		&::before {
			content: '';
			position: absolute;
			left: 0;
			top: 50%;
			transform: translateY(-50%);
			width: 6rpx;
			height: 30rpx;
			background-color: #3c9cff;
			border-radius: 3rpx;
		}
	}
	
	.product-card {
		background-color: #ffffff;
		border-radius: 16rpx;
		padding: 30rpx;
		margin-bottom: 30rpx;
		box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
		
		.card-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 10rpx;

			.header-left {
				display: flex;
				align-items: center;
				gap: 16rpx;
			}

			.header-title {
				font-size: 30rpx;
				color: #303133;
				font-weight: 600;
			}
		}
		
		.card-content {
			margin: 20rpx 0;
			
			.info-item {
				display: flex;
				align-items: center;
				margin-bottom: 16rpx;
				font-size: 26rpx;
				color: #606266;
			}
		}
		
		.card-footer {
			display: flex;
			justify-content: center;
			margin-top: 20rpx;
		}
	}
}

.recommend-section {
	margin: 20rpx;
	padding: 30rpx 20rpx;
	background-color: #ffffff;
	border-radius: 16rpx;
	box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
	
	.recommend-grid {
		display: flex;
		flex-wrap: wrap;
		margin-top: 20rpx;
	}
	
	.recommend-item {
		width: 25%;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 10rpx;
	}
}

/* 辅助类 */
.ml-2 {
	margin-left: 10rpx;
}

.mr-3 {
	margin-right: 15rpx;
}

.auth-popup {
	width: 90%;
	padding: 40rpx;
	background-color: #ffffff;
	.auth-title {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin-bottom: 30rpx;
	}
	
	.auth-content {
		text-align: center;
		margin-bottom: 40rpx;
		font-size: 28rpx;
		color: #606266;
	}
	
	.auth-buttons {
		display: flex;
		justify-content: space-between;
		gap: 20rpx;
		
		.u-button {
			flex: 1;
		}
	}
}
</style>