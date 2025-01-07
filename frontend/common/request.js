import Vue from 'vue';
import md5 from "@/util/md5/md5.js";
import $config from './config';

var headerData = {
	'Content-Type': 'application/json;charset=utf-8',
	'Accept': 'application/json',
	'Channel' : $config.channel,
};

function upload(url,file,data={}){
	let token = uni.getStorageSync('token');
	if (typeof(data.needLogin) != 'undefined' && data.needLogin == 1) {
		if (token == '' || token == undefined) {
			
			uni.removeStorageSync('token');
			uni.removeStorageSync('userInfo');
	
			uni.navigateTo({
				url: '/pages/my/login',
			});
			return false;
		}
	}
	data.app_key = $config.appKey;
	data.timestamp = (new Date()).valueOf();
	
	
	data.sign = requestEncrypt(data);
	headerData.Authorization = 'Bearer ' + token;
	url = $config.baseUrl + url
	
	
	let showLoading = ((typeof data.showLoading == 'undefined' || data.showLoading) ? true : false);
	let loadingData = ((typeof data.loadingData == 'undefined' || !data.loadingData) ? '加载中' : data.loadingData);
	
	
	return new Promise((resolve, reject) => {
		if (showLoading) {
			uni.showLoading({
				title: loadingData,
				mask:true
			});
		}
		
		
		uni.uploadFile({
			url:url,
			filePath:file,
			name:'file',
			formData:data,
			header:headerData,
			method:'POST',
			timeout:'10000',
			success:(res)=>{
				if (res.data.code == 1003) {
					
					uni.removeStorageSync('token');
					uni.removeStorageSync('userInfo');
					
					uni.navigateTo({
						url: '/pages/my/login',
					});
					return false;
				}
				resolve(res.data);
			},
			complete: () => {
				if (showLoading) {
					uni.hideLoading();
				}
			},
			fail: (err) => {
				reject(err);
			},
		})
	})
	
}


function requestApi(type, url, data = {}) {
	let token = uni.getStorageSync('token');
	if (typeof(data.needLogin) != 'undefined' && data.needLogin == 1) {
		if (token == '' || token == undefined) {
			
			uni.removeStorageSync('token');
			uni.removeStorageSync('userInfo');
	
			uni.navigateTo({
				url: '/pages/my/login',
			});
			return false;
		}
	}
	data.app_key = $config.appKey;
	data.timestamp = (new Date()).valueOf();
	
	
	data.sign = requestEncrypt(data);
	headerData.Authorization = 'Bearer ' + token;
	url = $config.baseUrl + url
	

	let showLoading = ((typeof data.showLoading == 'undefined' || data.showLoading) ? true : false);
	let loadingData = ((typeof data.loadingData == 'undefined' || !data.loadingData) ? '加载中' : data.loadingData);

	return new Promise((resolve, reject) => {
		if (showLoading) {
			uni.showLoading({
				title: loadingData,
				mask:true
			});
		}
		uni.request({
			url: url,
			method: type,
			header: headerData,
			data: data,
			timeout:120000,
			success: (res) => {
				
				if(res.data.code != 0 && res.data.message){
					console.error('error:'+res.data.message+"\n"+'以下接口异常，请调试:'+url);
				}
				
				if (res.data.code == 1003) {
					
					uni.removeStorageSync('token');
					uni.removeStorageSync('userInfo');
					
					uni.navigateTo({
						url: '/pages/my/login',
					});
					return false;
				}
				resolve(res.data);
			},
			fail: (err) => {
				reject(err);
			},
			complete: () => {
				if (showLoading) {
					uni.hideLoading();
				}
			}
		});
	})
}



function requestEncrypt(data) {
	let secret = $config.appSecret;
	let _str = '';
	let sorted = {},
	keys = Object.keys(data);
	keys.sort();
	keys.forEach((key) => {
		_str += key + data[key];
	});
	_str = secret + _str + secret;
	
	
	
	return md5.hex_md5_32Upper(_str);
}


export default {
	requestApi,upload,requestEncrypt
}