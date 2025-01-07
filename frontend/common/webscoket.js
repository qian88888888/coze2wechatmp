import Vue from 'vue';
import md5 from "@/util/md5/md5.js";
import $config from './config';

function connectApi() {
	return uni.connectSocket({
		url: $config.wssUrl,
		success: () => {
			
		}
	});
}


function sendSocketMessage(data,socket) {
	let token = uni.getStorageSync('token');
	
	if (token == '' || token == undefined) {
		
		uni.removeStorageSync('token');
		uni.removeStorageSync('userInfo');

		uni.navigateTo({
			url: '/pages/my/login',
		});
		return false;
	}
	
	data.token = token;
	data.channel = $config.channel;
	
	var str = '';
	var keys = Object.keys(data);
	
	keys.forEach((key) => {
		str += key +'='+ data[key]+"&"
	});
	

	socket.send({
		data: encodeURIComponent(str),
		success:function(res){
			if(res.errMsg == "sendSocketMessage:ok"){
				
			}
			
		}
	});
}

export default {
	connectApi,sendSocketMessage
}