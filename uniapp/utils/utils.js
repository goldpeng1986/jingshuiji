/**
 * 提示方法
 * @param {String} title 提示文字
 * @param {String}  icon icon图片
 * @param {Number}  duration 提示时间
 */
export function toast(title, icon = 'none', duration = 1500) {
	if(title) {
		uni.showToast({
		    title,
		    icon,
		    duration
		})
	}
}

/**
 * @param {String} url
 * @return {function}
 * @description navigateTo跳转
 */
export function Jump(url) {
	uni.navigateTo({
		url: url
	})
}



/**
 * 提示信息
 * **/
export function showModal(title, msg, showCancel) {
	uni.showModal({
		title: title,
		content: msg,
		showCancel: showCancel,
		success: function(res) {

		}
	})
}

/**
 * 显示loading
 * **/
export function showLoading(content) {
	uni.showLoading({
		title: content
	})
}
/**
 * 隐藏loading
 * **/
export function hideLoading() {
	uni.hideLoading()
}

/***
 * 获取时间戳
 */
export function getTimStamp() {
	var timestamp = Date.parse(new Date());
	timestamp = timestamp / 1000;
	return timestamp;
}
/***
 * 生成随机数
 */
export function getNonce() {
	var t = '';
	for (var i = 0; i < 12; i++) {
		t += Math.floor(Math.random() * 10);
	}
	return t;
}
/**
 * 获取32位随机数
 * ***/
export function getnoncestr() {
	let len = len || 32;
	var $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	var maxPos = $chars.length;
	var pwd = '';
	for (var i = 0; i < len; i++) {
		pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
	}
	return pwd;
}

/**
 * 解析url中的参数
 */
export function UrlParamHash(url) {
	var params = {},
		h;
	var hash = url.slice(url.indexOf("?") + 1).split("&");
	for (var i = 0; i < hash.length; i++) {
		h = hash[i].split("=");
		if (h.length > 1) {
			params[h[0]] = h[1];
		} else {
			params[h[0]] = "";
		}
	}
	return params;
}


/**
 * 一键复制 uni.setClipboardData
 * 用法：
 *    1. import {setClipboardData} from '该文件的地址'
 *    2. 利用async await
 *    3. await setClipboardData(需要复制的文本)
 */
const setClipboardData = (text) =>{
	return new Promise((resolve,reject)=>{
		uni.setClipboardData({
			data: text,
			success: (res) => {
				resolve(res)
			},
			fail: (err) => {
				reject(err)
			}
		})
	})
}

/**
 * 获取系统剪贴板内容 uni.getClipboardData
 * 用法：
 *    1. import {getClipboardData} from '该文件的地址'
 *    2. 利用async await来接收获取到的数据
 *    3. await getClipboardData()
 */
const getClipboardData = () =>{
	return new Promise((resolve,reject)=>{
		uni.getClipboardData({
			success: (res) => {
				resolve(res)
			},
			fail: (err) => {
				reject(err)
			}
		})
	})
}

/**
 * 拨打电话 uni.makePhoneCall()
 * 用法：
 *    1. import {getBatteryInfo} from '该文件的地址'
 *    2. 直接makePhoneCall(拨打的电话号码)
 */
const makePhoneCall = (phone) =>{
	uni.makePhoneCall({
		phoneNumber:phone
	})
}


/**
 * 设置缓存
 * @param {String} key 键名
 * @param {String} data 值
 */
export function setStorageSync(key, data) {
    uni.setStorageSync(key, data)
}

/**
 * 获取缓存
 * @param {String} key 键名
 */
export function getStorageSync(key) {
    return uni.getStorageSync(key)
}

/**
 * 删除缓存
 * @param {String} key 键名
 */
export function removeStorageSync(key) {
    return uni.removeStorageSync(key)
}

/**
 * 清空缓存
 * @param {String} key 键名
 */
export function clearStorageSync() {
    return uni.clearStorageSync()
}


/**
 * 页面跳转
 * @param {'navigateTo' | 'redirectTo' | 'reLaunch' | 'switchTab' | 'navigateBack' | number } url  转跳路径
 * @param {String} params 跳转时携带的参数
 * @param {String} type 转跳方式
 **/
export function useRouter(url, params = {}, type = 'navigateTo') {
    try {
        if (Object.keys(params).length) url = `${url}?data=${encodeURIComponent(JSON.stringify(params))}`
        if (type === 'navigateBack') {
            uni[type]({ delta: url })
        } else {
            uni[type]({ url })
        }
    } catch (error) {
        console.error(error)
    }
}

/**
 * 预览图片
 * @param {Array} urls 图片链接
 */
export function previewImage(urls, itemList = ['发送给朋友', '保存图片', '收藏']) {
    uni.previewImage({
        urls,
        longPressActions: {
            itemList,
            fail: function (error) {
                console.error(error,'===previewImage')
            }
        }
    })
}

/**
 * 保存图片到本地
 * @param {String} filePath 图片临时路径
 **/
export function saveImage(filePath) {
    if (!filePath) return false
    uni.saveImageToPhotosAlbum({
        filePath,
        success: (res) => {
            toast('图片保存成功', 'success')
        },
        fail: (err) => {
            if (err.errMsg === 'saveImageToPhotosAlbum:fail:auth denied' || err.errMsg === 'saveImageToPhotosAlbum:fail auth deny') {
                uni.showModal({
                    title: '提示',
                    content: '需要您授权保存相册',
                    showCancel: false,
                    success: (modalSuccess) => {
                        uni.openSetting({
                            success(settingdata) {
                                if (settingdata.authSetting['scope.writePhotosAlbum']) {
                                    uni.showModal({
                                        title: '提示',
                                        content: '获取权限成功,再次点击图片即可保存',
                                        showCancel: false
                                    })
                                } else {
                                    uni.showModal({
                                        title: '提示',
                                        content: '获取权限失败，将无法保存到相册哦~',
                                        showCancel: false
                                    })
                                }
                            },
                            fail(failData) {
                                console.log('failData', failData)
                            }
                        })
                    }
                })
            }
        }
    })
}

/**
 * 深拷贝
 * @param {Object} data
 **/
export const clone = (data) => JSON.parse(JSON.stringify(data))
 /**
 * 获取平台名称
 * @return {string} 平台名称
 */
export function getPlatform() {
	let platform;
	switch (process.env.VUE_APP_PLATFORM) {
		case 'app':
		case 'app-plus':
			let n = uni.getSystemInfoSync().platform.toLowerCase();
			if (n === 'ios') {
				platform = 'ios';
			} else if (n === 'android') {
				platform = 'android';
			} else {
				platform = 'app';
			}
			break;
		case 'mp-weixin':
			platform = 'wx';
			break;
		case 'mp-alipay':
			platform = 'alipay';
			break;
		case 'mp-baidu':
			platform = 'baidu';
			break;
		case 'mp-qq':
			platform = 'qq';
			break;
		case 'mp-toutiao':
			platform = 'toutiao';
			break;
		case 'quickapp-webview':
			platform = 'kuai';
			break;
	}

	return platform;
}

/**
 * 数组去重
 * @param {Array} array 数值
 * @retrun {Array} 数值
 */
export function   arrayShuffle(array) {
	let i = array.length, t, j;
	while (i) {
		j = Math.floor(Math.random() * i--);
		t = array[i];
		array[i] = array[j];
		array[j] = t;
	}
	return array;
}

/**
 * 日期格式化
 * @param {Date} date 日期
 * @param {string} format 返回的日期格式
 * @retrun {string} 日期
 */
export function dateFormat(date, format = 'YYYY-MM-DD HH:mm:ss') {
	const config = {
		YYYY: date.getFullYear(),
		MM: date.getMonth()+1,
		DD: date.getDate(),
		HH: date.getHours(),
		mm: date.getMinutes(),
		ss: date.getSeconds(),
	}
	for(const key in config){
		let value = config[key];
		if (value < 10) {
			value = '0' + value;
		}
		format = format.replace(key, value)
	}
	return format
}

/**
 * base64转文件
 * @param {string} base64data base64
 * @param {Function} cb 回调
 */
export function base64ToSrc(base64data, cb) {
	const FILE_BASE_NAME = 'tmp_base64src';
	const [, format, bodyData] = /data:image\/(\w+);base64,(.*)/.exec(base64data) || [];
	if (!format) {
		return (new Error('格式错误'));
	}

	// #ifdef MP-WEIXIN
	let filePath = `${wx.env.USER_DATA_PATH}/${FILE_BASE_NAME}.${format}`;
	// #endif
	// #ifdef MP-QQ
	let filePath = `${qq.env.USER_DATA_PATH}/${FILE_BASE_NAME}.${format}`;
	// #endif

	const buffer = uni.base64ToArrayBuffer(bodyData);
	uni.getFileSystemManager().writeFile({
		filePath,
		data: buffer,
		encoding: 'binary',
		success() {
			cb && cb(filePath);
		}
	});
}

/**
 * base64编码
 * @param {string} str str
 * @param {string}
 */
export function encodeBase64(str) {
	return new Buffer.from(str).toString('base64');
}

/**
 * base64解码
 * @param {string} str str
 * @param {string}
 */
export function decodeBase64(str) {
	const commonContent = str.replace(/\s/g, '+');
	return new Buffer.from(commonContent, 'base64').toString();
}

/**
 * 播放声音
 * @param {string} src 声音文件地址
 * @param {Boolean} loop 是否循环
 */
export function playSound(src, loop = false) {
	const innerAudioContext = uni.createInnerAudioContext();
	innerAudioContext.autoplay = true;
	innerAudioContext.loop = loop;
	innerAudioContext.src = src;
	innerAudioContext.onPlay(() => {});
	innerAudioContext.onError((res) => {});
}

/**
 * 生成订单ID
 * @param {string} prefix 订单前缀
 * @param {string} 订单ID
 */
export function createOrderSn(prefix = '') {
	return `${prefix}${this.randomString(10).toUpperCase()}${+new Date()}`;
}

/**
 * 图片转base64
 * @param {string} src 图片地址
 * @return {Promise} base64
 */
export function imageToBase64(src) {
	return new Promise((resolve, reject) => {
		uni.getImageInfo({
			src,
			success: image => {
				console.log(image);
				uni.getFileSystemManager().readFile({
					filePath: image.path,
					encoding: 'base64',
					success: e => {
						return resolve(`data:image/jpeg;base64,${e.data}`);
					},
					fail: e => {
						return reject(null);
					}
				})
			}
		});
	})
}


/**
 * 随机范围内的数字
 * @param {number} start 起始数字
 * @param {number} end 起始数字
 * @return {number || null} 随机数
 */
export function  randomByRange(start, end){
	if (typeof start === 'number' && typeof end === 'number') {
		return start + Math.floor(Math.random() * (end - start));
	} else {
		console.error('参数必须为数字');
		return null;
	}
}

/**
 * 获取系统信息
 * @author 深圳前海万联科技有限公司 <www.wanlshop.com>
 */
export function getSystemInfo(){
	const sys = uni.getSystemInfoSync();
	const data = {
		// 存储系统状态栏的高度  
		top: sys.statusBarHeight,  
		// 计算并存储一个特定高度的值，该值为状态栏高度加上转换后的90单位（upx为单位，需转换为px）  
		height: sys.statusBarHeight + uni.upx2px(90),  
		// 存储屏幕的高度  
		screenHeight: sys.screenHeight,  
		// 存储系统的平台类型，如iOS、Android等  
		platform: sys.platform,  
		// 存储设备的型号  
		model: sys.model,  
		// 存储可用于应用界面的窗口高度  
		windowHeight: sys.windowHeight,  
		// 存储窗口底部的距离（通常是屏幕底部到窗口底部的距离）  
		windowBottom: sys.windowBottom,  
		// 存储设备的唯一标识符  
		deviceId: sys.deviceId  
	};
	// #ifdef MP-ALIPAY
	data.height = sys.statusBarHeight + sys.titleBarHeight;
	// #endif
	return data;
}

/**
 * 修改标题栏
 * @param {Object} text 新标题
 * @param {Object} barColor 导航栏颜色 
 * {
 * "frontColor":"#ccc", 前景颜色值
 * "backgroundColor":"#ccc" 背景颜色值
 * "animation":{duration,timingFunc} 动画效果
 * "success":接口调用成功的回调函数
 * "fail":接口调用失败的回调函数
 * "complete":接口调用结束的回调函数（调用成功、失败都会执行）	
 * }
 */
export function setTitle(text = '', setbar = {}){
	if (text) {
		uni.setNavigationBarTitle({
			title: text
		});
	}
	if (JSON.stringify(setbar) != "{}") {
		uni.setNavigationBarColor(setbar);
	}
}
