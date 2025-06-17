export const tools = {
	filters: {

	},
	computed: {
		
	},
	methods: {
		//富文本的回调
		navigate(e) {
			if (e.href && e.href.indexOf('http') == -1) { //不完整的链接					
				// #ifdef MP
				this.$util.uniCopy({
					content: this.vuex_config.upload.cdnurl + e.href,
					success: () => {
						this.$u.toast('链接已复制,请在浏览器中打开')
					}
				})
				// #endif
				// #ifndef MP				
				window.open(this.vuex_config.upload.cdnurl + e.href);
				// #endif
			}
		},
		//卡片跳转
		diylinkpress(e) {			
			e.ignore();
			this.goPage(e.href);			
			return false;
		},
		//复制url
		copyUrl(url = '') {
			this.$util.uniCopy({
				content: url || window.location.href,
				success: () => {
					this.$u.toast('复制成功，请去粘贴发送给好友吧');
				},
				error: () => {
					console.log('复制失败！')
				}
			})
		},
		//cdnurl
		cdnurl(url) {
			if (!/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/.test(url)) {
				return this.vuex_config.upload.cdnurl + url;
			};
			return url;
		},
		//页面跳转
		goPage(path, auth) {
			if (path == 'out') {
				this.$u.vuex('vuex_token', '');
				this.$u.vuex('vuex_user', {});
				return;
			}
			if (auth && !this.vuex_token) {
				this.$u.route('/pages/login/wxlogin');
				return;
			}
			uni.$u.route({
				url: path,
				complete(e) {
					console.log(e, path)
				}
			})
		},
		logistics(res) {
			// #ifdef MP-WEIXIN
			if (this.vuex_config.logisticstype == 'kd100') {
				wx.navigateToMiniProgram({
					appId: 'wx6885acbedba59c14',
					path: `pages/result/result?nu=${res.expressno}&com=&querysource=third_xcx`, //备注：nu为快递单号，com为快递公司编码，com没有可不传
					extraData: {
						foo: 'bar'
					},
					success(res) {
						// 打开成功
					}
				});
			} else {
				this.goPage(`/pages/order/logistics?nu=${res.expressno}&coname=${res.expressname}&order_sn=${res.order_sn}`);
			}
			// #endif
			// #ifndef MP-WEIXIN
			this.goPage(`/pages/order/logistics?nu=${res.expressno}&coname=${res.expressname}&order_sn=${res.order_sn}`);
			// #endif
		}
	}
}
//修改头像的事件
export const avatar = {
	methods: {
		chooseAvatar() {
			uni.$on('uAvatarCropper', this.upload);
			this.$u.route({
				// 关于此路径，请见下方"注意事项"
				url: '/uview-ui/components/u-avatar-cropper/u-avatar-cropper',
				// 内部已设置以下默认参数值，可不传这些参数
				params: {
					// 输出图片宽度，高等于宽，单位px
					destWidth: 300,
					// 裁剪框宽度，高等于宽，单位px
					rectWidth: 300,
					// 输出的图片类型，如果'png'类型发现裁剪的图片太大，改成"jpg"即可
					fileType: 'jpg'
				}
			});
		},
		upload: async function(path) {
			uni.$off('uAvatarCropper', this.upload);
			// 可以在此上传到服务端
			try {
				let res = await this.$api.goUpload({
					filePath: path
				});
				if (!res.code) {
					this.$u.toast(res.msg);
				};
				this.form.avatar = res.data.url;
				this.url = res.data.fullurl;
				if (typeof this.editAvatar == 'function') {
					this.editAvatar();
				}
			} catch (e) {
				console.error(e);
				this.$u.toast('图片上传失败！');
			}
		}
	}
}

//登录
//登录成功跳转
export const loginfunc = {
	methods: {
		//登录成功
		success(index = 1) {
			//不在H5
			// #ifndef H5
			uni.$u.route({
				type: 'back',
				delta: index
			})
			// #endif
			// 在H5 刷新导致路由丢失
			// #ifdef H5
			var pages = getCurrentPages();
			//有上次页面，关闭所有页面，到此页面,是从授权的，授权页面被刷新过,路由栈丢失
			if (pages.length <= 1 || pages[0].route == 'pages/login/auth') {
				//默认到首页
				uni.reLaunch({
					url: (this.vuex_lasturl || '/pages/index/index'),
					complete(res) {
						console.log(res)
					}
				})
				return;
			}
			uni.$u.route({
				type: 'back',
				delta: index
			})
			// #endif
		},
		// #ifdef H5
		async goAuth() {
			if (this.$util.isWeiXinBrowser()) {
				let url = '';
				if (window.location.hash != '') {
					url = window.location.origin + window.location.pathname + '?hashpath=/pages/login/auth'
				} else {
					url = window.location.origin + window.location.pathname.replace(/pages.*/, 'pages/login/auth');
				};
				let res = await this.$api.getAuthUrl({
					platform: 'wechat',
					url: url
				});
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				var pages = getCurrentPages();
				let len = pages.length;
				if (len > 1) {
					let url = pages[len - 1].route;
					if (url.includes('login')) {
						//找到上一个不是登录页面
						for (let i = len - 1; i >= 0; i--) {
							if (!pages[i].route.includes('login')) {
								this.$u.vuex('vuex_lasturl', '/' + pages[i].route + this.$u.queryParams(pages[i]
									.options));
								break;
							}
						}
					} else {
						this.$u.vuex('vuex_lasturl', '/' + url + this.$u.queryParams(pages[pages.length - 1]
							.options))
					}
				}
				window.location.href = res.data;
			}
		},
		// #endif
		// #ifdef APP-PLUS
		goAppLogin(index = 1) {
			let that = this;
			var all, Service;
			// 1.发送请求获取code
			plus.oauth.getServices(
				function(Services) {
					all = Services;
					Object.keys(all).some(key => {
						if (all[key].id == 'weixin') {
							Service = all[key];
						}
					});
					Service.authorize(
						async function(e) {
								console.log(e);
								let res = await that.$api.goAppLogin({
									code: e.code,
									scope: e.scope
								});
								if (!res.code) {
									that.$u.toast(res.msg);
									return;
								}
								if (res.data.user) {
									that.$u.vuex('vuex_token', res.data.user.token);
									that.$u.vuex('vuex_user', res.data.user || {});
									that.success(index);
									return;
								}
								that.$u.vuex('vuex_third', res.data.third);
								that.$u.route('/pages/login/register?bind=bind');
							},
							function(e) {
								that.$u.toast('授权失败！');
							}
					);
				},
				function(err) {
					console.log(err);
					that.$u.toast('授权失败！');
				}
			);
		}
		// #endif
	}
}
