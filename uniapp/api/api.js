import request from '@/utils/request'

// Notice List - Fetches list of notices
export const getNoticeList = data => request.get('/addons/shop/api.notice/list', data);
// Notice Info - Fetches details for a specific notice
export const getNoticeInfo = data => request.get('/addons/shop/api.notice/detail', data);
// Recharge Records - Fetches recharge records
export const getRechargeRecords = data => request.get('/addons/shop/api.recharge/records', data);
// Generic Records - Fetches generic records list
export const getGenericRecords = data => request.get('/addons/shop/api.records/list', data);
// About Info - Fetches about us information
export const getAboutInfo = data => request.get('/addons/shop/api.common/aboutus', data);
// User Settings - Fetches user settings
export const getUserSettings = data => request.get('/addons/shop/api.user/settings', data);
// Change Password - Submits change password request
export const changeUserPassword = data => request.post('/addons/shop/api.user/changepassword', data);
// Add Shop Information - Adds shop information
export const addShopInformation = data => request.post('/addons/shop/api.shop/addinfo', data);
// Shop Family List - Fetches shop family information
export const getShopFamilyList = data => request.get('/addons/shop/api.shop/familylist', data);
// Shop Page Data - Fetches main data for the shop page
export const getShopPageData = data => request.get('/addons/shop/api.shop/pagedata', data);
//使用说明
//  export const getList = data => request.get('/api/list', data, false)
//              页面调用名  请求参数       请求类型  接口地址         loading是否显示
/*页面中调用方法：（若无请求参数则留空,例：this.$api.getList()）
this.$api.getList(params).then(res => {
	
})
*/
//首页 - 获取首页数据信息
export const getIndex = data => request.get('/addons/shop/api.index/index', data)
//手机验证码登录 - 使用手机号和验证码进行登录
export const mobilelogin = data => request.post('/addons/shop/api.login/mobilelogin', data)
//微信登录 - 微信授权登录
export const wechatLogin = data => request.post('/addons/shop/api.login/wxlogin', data)
//微信手机号授权登录 - 获取微信绑定的手机号进行登录
export const getWechatPhoneAuth = data => request.post('/addons/shop/api.login/getwechatmobile', data)
//发送验证码 - 发送短信验证码
export const sendSms = data => request.post('/addons/shop/api.sms/send', data)
//商品详情 - 获取商品详细信息
export const getHomeProduct = data => request.get('/addons/shop/api.goods/detail', data)
//提交订单 - 创建新订单
export const addOrder = data => request.post('/addons/shop/api.order/add', data)


//通用接口
export const config = data => request.get('/addons/shop/api.common/init', data) // 获取系统初始化配置信息
export const area = data => request.get('/addons/shop/api.common/area', data) // 获取地区数据（省市区）
export const goUpload = data => request.post('/addons/shop/api.upload', data) // 文件上传接口

//用户相关接口
export const getUserIndex = data => request.get('/addons/shop/api.user/index', data) // 获取用户首页信息
export const getUserProfile = data => request.post('/addons/shop/api.user/profile', data) // 更新用户资料
export const goUserLogout = data => request.get('/addons/shop/api.user/logout', data) // 用户退出登录
export const goUserAvatar = data => request.post('/addons/shop/api.user/avatar', data) // 更新用户头像
export const getSigned = data => request.post('/addons/shop/api.user/getSigned', data) // 获取用户签到状态

//登录相关接口
export const getEmsSend = data => request.post('/addons/shop/api.ems/send', data) // 发送邮箱验证码
export const getSmsSend = data => request.post('/addons/shop/api.sms/send', data) // 发送短信验证码
export const goLogin = data => request.post('/addons/shop/api.login/login', data) // 用户登录
export const goRegister = data => request.post('/addons/shop/api.login/register', data) // 用户注册
export const goResetpwd = data => request.post('/addons/shop/api.login/resetpwd', data) // 重置密码
export const gowxLogin = data => request.post('/addons/shop/api.login/wxLogin', data) // 微信登录
export const goAppLogin = data => request.post('/addons/shop/api.login/appLogin', data) // APP登录
export const getWechatMobile = data => request.post('/addons/shop/api.login/getWechatMobile', data) // 获取微信手机号

//第三方登录接口
export const getAuthUrl = data => request.get('/addons/third/api/getAuthUrl', data) // 获取第三方授权登录URL
export const goAuthCallback = data => request.post('/addons/third/api/callback', data) // 第三方登录回调处理
export const goThirdAccount = data => request.post('/addons/third/api/account', data) // 第三方账号绑定
//签到相关接口
export const signinConfig = data => request.get('/addons/signin/api.index/index', data) // 获取签到配置信息
export const monthSign = data => request.get('/addons/signin/api.index/monthSign', data) // 获取月签到记录
export const dosign = data => request.post('/addons/signin/api.index/dosign', data) // 执行签到操作
export const fillup = data => request.get('/addons/signin/api.index/fillup', data) // 补签操作
export const signRank = data => request.get('/addons/signin/api.index/rank', data) // 获取签到排行榜
export const signLog = data => request.get('/addons/signin/api.index/signLog', data) // 获取签到日志

//商品相关接口
export const getGoodsIndex = data => request.get('/addons/shop/api.goods/index', data) // 获取商品首页数据
export const getGoodsInfo = data => request.get('/addons/shop/api.goods/detail', data) // 获取商品详情
export const getGoodsList = data => request.post('/addons/shop/api.goods/lists', data) // 获取商品列表
export const getWxCode = data => request.post('/addons/shop/api.goods/getWxCode', data) // 获取商品微信小程序码

//分类相关接口
export const getCategory = data => request.get('/addons/shop/api.category/index', data) // 获取商品分类列表
export const allCategory = data => request.get('/addons/shop/api.category/alls', data) // 获取所有分类数据

//购物车相关接口
export const addCart = data => request.post('/addons/shop/api.cart/add', data) // 添加商品到购物车
export const getCartIndex = data => request.get('/addons/shop/api.cart/index', data) // 获取购物车列表
export const setCartNums = data => request.post('/addons/shop/api.cart/set_nums', data) // 设置购物车商品数量
export const delCart = data => request.post('/addons/shop/api.cart/del', data) // 删除购物车商品
export const cart_nums = data => request.get('/addons/shop/api.cart/cart_nums', data) // 获取购物车商品数量

//订单相关接口
export const orderList = data => request.get('/addons/shop/api.order/index', data) // 获取订单列表
export const orderDetail = data => request.get('/addons/shop/api.order/detail', data) // 获取订单详情
export const orderRenewDetail = data => request.get('/addons/shop/api.order/renewDetail', data) // 获取续费订单详情
export const orderCancel = data => request.post('/addons/shop/api.order/cancel', data) // 取消订单
export const orderCarts = data => request.post('/addons/shop/api.order/carts', data) // 购物车结算
export const orderPay = data => request.post('/addons/shop/api.order/pay', data) // 订单支付
export const orderRenewPay = data => request.post('/addons/shop/api.order/renewPay', data) // 续费订单支付
export const takedelivery = data => request.post('/addons/shop/api.order/takedelivery', data) // 确认收货
export const logistics = data => request.get('/addons/shop/api.order/logistics', data) // 获取物流信息

//订单商品相关接口
export const orderGoodsDetail = data => request.get('/addons/shop/api.order_goods/detail', data) // 获取订单商品详情
export const ordeAfterSaleApply = data => request.post('/addons/shop/api.order_goods/apply', data) // 申请售后服务
export const ordeAfterSale = data => request.get('/addons/shop/api.order_goods/aftersale', data) // 获取售后信息
export const saveExpressInfo = data => request.post('/addons/shop/api.order_goods/saveExpressInfo', data) // 保存快递信息

//地址相关接口
export const addressList = data => request.get('/addons/shop/api.address/index', data) // 获取收货地址列表
export const addressAdd = data => request.post('/addons/shop/api.address/addedit', data) // 添加/编辑收货地址
export const addressInfo = data => request.get('/addons/shop/api.address/detail', data) // 获取地址详情
export const defAddress = data => request.get('/addons/shop/api.address/def_address', data) // 获取默认地址
export const delAddress = data => request.post('/addons/shop/api.address/del', data) // 删除收货地址

//收藏相关接口
export const optionCollect = data => request.post('/addons/shop/api.collect/optionCollect', data) // 收藏/取消收藏操作
export const collectList = data => request.get('/addons/shop/api.collect/collectList', data) // 获取收藏列表

//评论相关接口
export const commentList = data => request.get('/addons/shop/api.comment/index', data) // 获取评论列表
export const commentAdd = data => request.post('/addons/shop/api.comment/add', data) // 添加评论
export const commentReply = data => request.post('/addons/shop/api.comment/reply', data) // 回复评论
export const commentMyList = data => request.get('/addons/shop/api.comment/myList', data) // 获取我的评论列表

//积分相关接口
export const scoreLogs = data => request.get('/addons/shop/api.score/logs', data) // 获取积分记录
export const exchangeList = data => request.get('/addons/shop/api.score/exchangeList', data) // 获取积分兑换商品列表
export const exchange = data => request.post('/addons/shop/api.score/exchange', data) // 积分兑换
export const myExchange = data => request.get('/addons/shop/api.score/myExchange', data) // 获取我的兑换记录

//优惠券相关接口
export const couponList = data => request.get('/addons/shop/api.coupon/couponList', data) // 获取优惠券列表
export const couponDetail = data => request.get('/addons/shop/api.coupon/couponDetail', data) // 获取优惠券详情
export const drawCoupon = data => request.post('/addons/shop/api.coupon/drawCoupon', data) // 领取优惠券
export const myCouponList = data => request.get('/addons/shop/api.coupon/myCouponList', data) // 获取我的优惠券列表

//页面相关接口
export const pageIndex = data => request.get('/addons/shop/api.page/index', data) // 获取页面配置
export const pageList = data => request.get('/addons/shop/api.page/lists', data) // 获取页面列表

//订阅相关接口
export const subscribe = data => request.post('/addons/shop/api.subscribe/index', data) // 消息订阅

//属性相关接口
export const attribute = data => request.get('/addons/shop/api.attribute/index', data) // 获取商品属性

//分销配置相关接口
export const getDealerQrcode = data => request.get('/addons/shop/api.dealer.dealer/getQrcode', data) // 获取分销商二维码
export const getDealerUserSpead = data => request.get('/addons/shop/api.dealer.dealer/spead_user', data) // 获取分销用户推广信息
export const userSpreadNewList = data => request.get('/addons/shop/api.dealer.user/getList', data) // 获取推广用户列表
export const userSpreadCapitalList = data => request.get('/addons/shop/api.dealer.capital/getList', data) // 获取分销资金记录
export const userSpreadOrderList = data => request.get('/addons/shop/api.dealer.order/getList', data) // 获取分销订单列表
export const getWithdraw = data => request.get('/addons/shop/api.dealer.withdraw/info', data) // 获取提现信息
export const withdraw = data => request.post('/addons/shop/api.dealer.withdraw/apply', data) // 申请提现
export const userSpreadWithdrawList = data => request.get('/addons/shop/api.dealer.withdraw/getList', data) // 获取提现记录列表

//预约服务相关接口
export const serviceOrder = data => request.post('/addons/shop/api.service.order/create', data) // 创建服务预约订单
export const serviceList = data => request.get('/addons/shop/api.service.order/list', data) // 获取服务订单列表
export const getServiceOrderInfo = data => request.get('/addons/shop/api.service.order/detail', data) // 获取服务订单详情
export const cancelOrder = data => request.post('/addons/shop/api.service.order/cancel', data) // 取消服务订单
export const payServiceOrder = data => request.post('/addons/shop/api.service.order/payParams', data) // 服务订单支付参数
export const orderServiceInfo = data => request.get('/addons/shop/api.service.order/ordercount', data) // 获取服务订单统计信息

//余额充值相关接口
export const confirmMoney = data => request.post('/addons/shop/api.user/userMoney', data) // 用户余额充值
export const userMoneyLog = data => request.get('/addons/shop/api.user/getList', data) // 获取用户余额记录