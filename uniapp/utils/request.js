

import BASE_URL from '@/api/env.js' //引入接口共用地址
import RequestManager from '@/utils/requestManager.js'
import {toast, clearStorageSync, getStorageSync, useRouter} from '@/utils/utils.js'
import systemConfig from '@/config/config.js';
const manager = new RequestManager()

const baseRequest = async (url, method, data = {}, loading = true) =>{
	let requestId = manager.generateId(method, url, data)
	if(!requestId) {
		console.log('重复请求')
	}
	if(!requestId)return false;
	
	const header = {}
	header.Authorization = getStorageSync(systemConfig.token) || ''
	return new Promise((reslove, reject) => {
		loading && uni.showLoading({title: 'loading'})
		uni.request({
			url: BASE_URL + url,
			method: method || 'GET',
			header: header,
			timeout: 10000,
			data: data || {},
			complete: ()=>{
				uni.hideLoading()
				manager.deleteById(requestId)
			},
			success: (successData) => {
				//console.log(successData)
				const res = successData.data
				if(successData.statusCode == 200){
					// 业务逻辑，自行修改，401是服务器上返回该token过期，过期后跳转到登陆页面
					if(res.code > 400){
						clearStorageSync('token')
						useRouter(systemConfig.login_page, 'reLaunch')
					}else{
						reslove(res)
					}
				}else{
					console.log('网络连接失败，请稍后重试' ,url)
					toast('网络连接失败，请稍后重试')
					reject(res)
				}
			},
			fail: (msg) => {
				console.log("请求："+BASE_URL + url +'，发生错误：', err)
				toast('网络连接失败，请稍后重试')
				reject(msg)
			}
		})
	})
}

const request = {};

['options', 'get', 'post', 'put', 'head', 'delete', 'trace', 'connect'].forEach((method) => {
	request[method] = (api, data, loading) => baseRequest(api, method, data, loading)
})

export default request