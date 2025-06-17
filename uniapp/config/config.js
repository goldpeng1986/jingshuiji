
let config = {
	//不拦截页面路径
    whiteList: [
		'/pages/me/login',
    	'/pages/index/index',
		'/pages/shop/family',
		'/pages/shop/index',
		'/pages/shop/addinfo',
		// '/pages/notice/index',
		// '/pages/me/index',
		// '/pages/subPack/index/detail',
		//一行一个
    ],
    token : 'token', //本地存储token的变量名
    login_page : '/pages/me/login',  //拦截后跳转的登陆页路径
	vconsole_status: 1,  //是否启用调试工具，1为启用，0不启用
}
export default config