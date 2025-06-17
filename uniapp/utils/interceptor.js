
import systemConfig from '@/config/config.js';
// 页面白名单，不受拦截
const whiteList = systemConfig.whiteList 
function hasPermission (url) {
	let islogin = uni.getStorageSync(systemConfig.token );//isLogin是登录成功后在本地存储登录标识，存储一个能够判断用户登录的唯一标识就行
    // 在白名单中或有登录判断条件可以直接跳转
    if(whiteList.indexOf(url) !== -1 || islogin) {
		//console.log('通过')
        return true 
    } 
	// console.log('失败')
    return false
}
uni.addInterceptor('navigateTo', {
    // 页面跳转前进行拦截, invoke根据返回值进行判断是否继续执行跳转
    invoke (e) {
		const url = e.url.split('?')[0]
        if(!hasPermission(url)){
            uni.reLaunch({
                url:systemConfig.login_page 
            })
            return false
        }
        return true
    },
    success (e) {
		
    }
})
 
uni.addInterceptor('switchTab', {
    // tabbar页面跳转前进行拦截
    invoke (e) {
		
		const url = e.url.split('?')[0]
		if(!hasPermission(url)){
            uni.reLaunch({
                url: systemConfig.login_page 
            })
			//console.log('不在白名单内')
            return false
        }
		//console.log('在白名单内')
        return true
    },
    success (e) {
		
    }
})