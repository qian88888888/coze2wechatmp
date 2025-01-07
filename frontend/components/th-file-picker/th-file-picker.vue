<template>
	<view>
		<!-- 回显区域 -->
		<block v-for="(item,index) in fileList">


			<view class="file-box">
				<view class="top10">
					<fileView :file="item" :index="index" @remove="remove" @reupload="opBtn"></fileView>
				</view>
			</view>
		</block>

		<!-- 选择操作区 -->
		<!-- 文件，图片，视频，音频 -->
		<view v-if="count>fileList.length" class="flex-boxtp">
			<block v-for="item in types" :key="item">
				<view class="op-box" @click="click(item)">
					<view class="op-box-image">
						<view class="op-box-image-line1"></view>
						<view class="op-box-image-line2"></view>
					</view>
					<text v-if="showTitle" class="op-box-text">{{getTitle(item)}}</text>
				</view>

			</block>
		</view>
		<!-- <view @click="preUpload">上传</view> -->
		<!-- <uniRecord ref="uniRecord"></uniRecord> -->


	</view>
</template>

<script>
	import fileView from './file-view.vue'
	import $config from 'common/config.js';
	import $request from 'common/request.js';
	// import uniRecord from './uni-record.vue'
	export default {
		name: "th-file-picker",
		components: {
			// uniRecord,
			fileView
		},
		data() {
			return {

				fileList: [], //文件列表
				uploadQueue: [],
				cancallBack: true, //是否回调结果的状态值

			};
		},
		props: {
			count: {
				type: [String, Number],
				default: 3
			},
			extension: {
				type: Array,
				default: () => ['.doc', '.xlsx', '.docx','.pdf'] //针对选择文件处理，如果选择图片视频等其他，使用对应的属性设置
			},
			files: {
				type: Array,
				default: () => []
			},
			types: {
				type: Array,
				default: () => ['file', 'image', 'video', 'audio']
			},
			showTitle: {
				type: Boolean,
				default: false,
			},
			// 开发者服务器 url
			url: {
				type: String,
				default: 'http://192.168.1.99:7786/file/upload'
			},
			pid: {
				type: [String, Number],
				default: 0
			},

		},
		watch: {
			files: {
				handler(newValue, oldValue) {},
				immediate: true
			}
		},
		methods: {

			//视频选择
			chooseVideo() {
				if (this.count - this.fileList.length == 0)
					return
				uni.chooseVideo({
					sourceType: ['album'],
					success: res => {
						let data = {
							progess: 0,
							name: res.name?res.name:res.tempFilePath,
							uri: res.tempFilePath,
							status: true, //false-上传失败
							size: res.size
						}
						this.fileList.push(data);
						this.preUpload();
					}
					
				})
				
			},

			//图片选择
			chooseImage() {
				if (this.count - this.fileList.length == 0)
					return
				// #ifndef MP-WEIXIN
				uni.chooseImage({
					count: this.count - this.fileList.length,
					sourceType: ['album'],
					success: res => {
						res.tempFilePaths.forEach((item, index) => {
							let data = {
								progess: 0,
								name: res.tempFiles[index].name,
								uri: item,
								status: true, //false-上传失败
								size: res.tempFiles[index].size
							}
							this.fileList.push(data)
							
						});
						this.preUpload();

					}
				})
				// #endif
				// #ifdef MP-WEIXIN
				uni.chooseMedia({
					count: this.count - this.fileList.length,
					sourceType: ['album'],
					success: res => {
						console.log('MP-WEIXIN:' + JSON.stringify(res))
						res.tempFiles.forEach((item, index) => {
							let data = {
								progess: 0,
								name: item.tempFilePath,
								uri: item.tempFilePath,
								status: true, //false-上传失败
								size: res.size
							}
							this.fileList.push(data)
						});
						this.preUpload();

					}
				})

				// #endif

			},

			// 文件选择
			chooseFile() {
				// #ifdef H5
				//h5端无法控制选择的数量，只能手动控制
				if (this.count - this.fileList.length == 0)
					return
					
					
				uni.chooseFile({
					count: this.count - this.fileList.length,
					extension: this.extension,
					success: res => {

						res.tempFilePaths.forEach((item, index) => {
							let data = {
								progess: 0,
								name: res.tempFiles[index].name,
								uri: item,
								status: true, //false-上传失败
								size: res.tempFiles[index].size
							}
							this.fileList.push(data)
						});
						this.preUpload();
					}
				})
				// #endif
				// #ifdef MP-WEIXIN
				uni.chooseMessageFile({
					count: this.count - this.fileList.length,
					extension: this.extension,
					success: res => {
						res.tempFiles.forEach((item, index) => {
							let data = {
								progess: 0,
								name: item.name,
								uri: item.path,
								status: true, //false-上传失败
								size: item.size
							}
							this.fileList.push(data)
						});
						this.preUpload();
					}
				})

				// #endif
			},

			// 上传
			preUpload() {
				if (this.fileList.length == 0)
					return
				this.uploadQueue.length = 0
				this.fileList.forEach(item => {
					if (item.progess == 0) {
						 this.upload(item)
					}
				})
			},
			async upload(item, name = item.name) {
				let data = {};
				let headerData = {
	'Content-Type': 'application/json;charset=utf-8',
	'Accept': 'application/json',
	'Channel' : $config.channel,
};
				
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
				data.assistant_id = this.pid
				
				data.sign = $request.requestEncrypt(data);
				
				headerData.Authorization = 'Bearer ' + token;
				
				
				

				const uploadTask = await uni.uploadFile({
					url: $config.baseUrl+this.url,
					filePath: item.uri,
					formData:data,
					method:'POST',
					name: 'file', //name,
					header: headerData,
					success: res => {
						// TODO 根据服务器具体返回的状态判断是否真正上传成功
						if (res.statusCode == 200) {
														
							let result = JSON.parse(res.data)
							
							// TODO 根据服务器具体返回的状态判断是否真正上传成功
							if (result.code == 0) {
								item.path = result.data.url
								item.status = true
							} else {
								item.status = false
								item.fail = result.msg
							}


						} else {
							item.status = false
							item.fail = res.data
						}
						this.callbackResult()

					},
					fail: err => {
						item.status = false
						item.fail = err.errMsg
						this.callbackResult()
					}

				})
				
				// 添加进请求队列
				this.uploadQueue.push({
					item: item,
					task: uploadTask
				})
				this.uploadQueue.forEach(tasks => {
					(tasks.task).onProgressUpdate((res) => {
						tasks.item.progess = res.progress
					})
				})

			},


			opBtn(index) {
				if (this.fileList[index].status) {
					//移除
					this.remove(index)
				} else {
					//上传失败重新上传
					this.fileList[index].status = true
					this.fileList[index].progess = 0
					this.upload(this.fileList[index])
				}
			},
			remove(index) {
				this.fileList.splice(index, 1)
				this.callbackResult()
			},


			// 结果回调
			callbackResult() {
				this.cancallBack = true
				for (let index in this.fileList) {
					// 回调结果参照：全部上传结束（包含失败情况）
					// 保证一次上传只触发一次回调
					//存在上传成功但path值还未赋值成功的情况
					if ((this.fileList[index].status && (this.fileList[index].progess < 100 || this.fileList[index].path ==
							undefined))) {
						this.cancallBack = false
						break
					}
				}
				if (this.cancallBack) {
					let list = []
					this.fileList.forEach(item => {
						if (item.status && item.progess == 100)
							list.push(item.path)
					})
					this.$emit('result', list)

				}

			},

			click(item) {
				switch (item) {
					case "file":
						this.chooseFile()
						break
					case "image":
						this.chooseImage()
						break
					case "video":
						this.chooseVideo()
						break
					case "audio":
						this.$refs.uniRecord.showPicker()
						// #ifndef H5


						// #endif
						// #ifdef H5
						throw Error('h5不支持录音')

						// #endif
						break
				}
			},

			getTitle(item) {
				let title = ''
				switch (item) {
					case "file":
						title = '上传附件'
						break
					case "image":
						title = '上传图片'
						break
					case "video":
						title = '上传视频'
						break
					case "audio":
						title = '上传音频'
						break
				}
				return title

			},


		}
	}
</script>

<style lang="scss" scoped>
	.flex-boxtp {
		display: flex;
		margin-top: 10rpx;
	}

	.op-box {
		display: flex;
		flex-direction: column;
		align-items: center;
		margin: 10rpx 15rpx;

		&-image {
			width: 120rpx;
			height: 120rpx;
			background: #F6F7FB;
			border-radius: 8rpx;
			position: relative;

			&-line1 {
				position: absolute;
				left: 0;
				right: 0;
				top: 0;
				bottom: 0;
				margin: auto;
				background: #999999;
				width: 4rpx;
				height: 35%;
				border-radius: 5rpx;
			}

			&-line2 {
				position: absolute;
				left: 0;
				right: 0;
				top: 0;
				bottom: 0;
				margin: auto;
				background: #999999;
				height: 4rpx;
				width: 35%;
				border-radius: 5rpx;
			}
		}

		&-text {
			margin-top: 10rpx;
			font-weight: 500;
			font-size: 28rpx;
			font-family: PingFang-SC-Medium, PingFang-SC;
			font-weight: 500;
			color: #666666;
			line-height: 40rpx;


		}

	}

	.file-box {
		padding: 5rpx 15rpx;
		box-sizing: border-box;


		&-title {
			word-break: break-all;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
			font-size: 30rpx;
			display: flex;
			align-items: center;
			min-height: 80rpx;


			&-del {
				font-size: 26rpx;
				margin-left: 10rpx;
				color: palevioletred;
				flex: 1;
				white-space: nowrap;
				text-align: right;
				flex-direction: row-reverse;
				display: flex;
				align-items: center;
			}
		}

	}

	.fail-text {
		font-size: 26rpx;
		color: red;
		word-break: break-all;


	}
</style>