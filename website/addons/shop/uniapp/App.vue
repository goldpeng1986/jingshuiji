<script>
import md5Libs from 'uview-ui/libs/function/md5';
export default {
	onLaunch: function() {
		console.log('uview 版本', this.$u.config.v);	
		this.$api.config().then(res => {
			if (res.code) {
				//主题做缓存
				let theme_key = md5Libs.md5(JSON.stringify(res.data.theme));
				if(!this.vuex_theme.key || this.vuex_theme.key != theme_key){
					this.$u.vuex('vuex_theme', {key:theme_key,value:res.data.theme});
				}
				this.$u.vuex('vuex_config', res.data);
			}
		});
		// #ifdef H5
		if (window.location.hash != '') {
			let search = window.location.search.substring(1);
			try {
				if (search.indexOf('hashpath') != -1) {
					let sea = JSON.parse(
						'{"' +
							decodeURIComponent(search)
								.replace(/"/g, '\\"')
								.replace(/&/g, '","')
								.replace(/=/g, '":"') +
							'"}'
					);
					if (sea.hashpath && sea.code && sea.state) {
						window.location.href = window.location.origin + window.location.pathname + '#' + sea.hashpath + '?code=' + sea.code + '&state=' + sea.state;
					}
				}
			} catch (e) {
				//TODO handle the exception
			}
		}
		// #endif
	},
	onShow: function() {
	
	},
	onHide: function() {
		console.log('App Hide');
	}
};
</script>

<style lang="scss">
@import 'uview-ui/index.scss';

/*每个页面公共css */
.bg-white {
	background-color: #ffffff;
}
.price {
	color: $u-type-error;
}
.text-weight {
	font-weight: bold;
}
.text-normal {
	font-weight: normal;
}

.fa-emmpty {
	width: 100%;
	flex-direction: column;
	image {
		width: 400rpx;
		height: 400rpx;
	}
	&.top-15 {
		padding-top: 15vh;
	}
}
.footer-bar {
	position: fixed;
	left: 0;
	bottom: 0;
	width: 100%;
	height: 120rpx;
	background-color: #ffffff;
	padding: 0 30rpx;
	z-index: 9999;
}

.share-btn {
	padding: 0;
	margin: 0;
	border: 0;
	background-color: transparent;
	line-height: inherit;
	border-radius: 0;
	font-size: inherit;
}
.share-btn::after {
	border: none;
}
.u-mode-center-box {
	background: rgba(255, 255, 255, 0) !important;
}
</style>

