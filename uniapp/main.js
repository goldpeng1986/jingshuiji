import App from './App'
import '@/utils/interceptor.js';//引入拦截

import Vue from 'vue'
import uView from '@/uni_modules/uview-ui'
Vue.use(uView)

import store from './store'

// 注册请求方法
import * as api from '@/api/api.js'
Vue.prototype.$api = api


import BASE_URL from '@/api/env.js' //引入接口共用地址
Vue.prototype.$API_BASE_URL= BASE_URL

import config from '@/config/config.js';
Vue.prototype.$systemConfig = config

let vuexStore = require("@/store/$u.mixin.js");
Vue.mixin(vuexStore);

//是否启用调试工具
// if(config.vconsole_status){
// 	const vconsole = require('vconsole')
// 	Vue.prototype.$vconsole = new vconsole() // 使用vconsole
// }


Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
	store,
  ...App
})
app.$mount()

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif