import App from './App'

import request from './common/request.js';
Vue.prototype.$http = request;

import scoket from './common/webscoket.js';
Vue.prototype.$scoket = scoket;


import uView from "uview-ui"
Vue.use(uView);


import share from './util/wxShare/wxShare.js'
Vue.mixin(share)

import config from './common/config.js';
Vue.prototype.$config = config;

import wxpay from './util/wxpay/wxPayUtil.js';
Vue.prototype.$wxpay = wxpay;

Vue.prototype.$channelInfo = {};

Vue.prototype.$onLaunched = new Promise(resolve => {
    Vue.prototype.$isResolve = resolve
})

import ZAudio from 'uniapp-zaudio';

let zaudio = new ZAudio({
			  continuePlay: false, //续播
			  autoPlay: true, //自动播放 部分浏览器不支持
			});
			
Vue.prototype.$zaudio = zaudio; //挂载vue原型链上



// #ifndef VUE3
import Vue from 'vue'
import './uni.promisify.adaptor'
Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
  ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif