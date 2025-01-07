<template>
	<view>
		<!-- #ifdef APP-PLUS ||MP-WEIXIN -->

		<view class="record-btn" @longpress="startRecord" @touchstart="touchStart" @touchmove.stop.prevent="touchMove"
			@touchend="endRecord" hover-class="record-btn-hover" hover-start-time="200" hover-stay-time="150" :style="[
        btnStyle,
        {
          '--btn-hover-fontcolor': btnHoverFontcolor,
          '--btn-hover-bgcolor': btnHoverBgcolor,
        },
      ]">
			{{ btnTextContent }}
		</view>
		<!-- #endif -->
		<!-- #ifdef H5 -->
		<view class="record-btn" @click="startRecord" hover-class="record-btn-hover" hover-start-time="200"
			hover-stay-time="150" :style="[
		  btnStyle,
		  {
		    '--btn-hover-fontcolor': btnHoverFontcolor,
		    '--btn-hover-bgcolor': btnHoverBgcolor,
		  },
		]">
			{{ btnTextContent }}
		</view>
		<!-- #endif -->




		<view class="record-popup" :style="{
        '--popup-height': popupHeight,
        '--popup-width': upx2px(popupMaxWidth),
        '--popup-bottom': upx2px(popupFixBottom),
        '--popup-bg-color': popupBgColor,
      }">
			<view class="inner-content" v-if="recordPopupShow">
				<!-- <view>{{recordTime}}</view> -->
				<view class="title" v-html="popupTitle"></view>
				<view class="voice-line-wrap" v-if="recording" :style="{
            '--line-height': upx2px(lineHeight),
            '--line-start-color': lineStartColor,
            '--line-end-color': lineEndColor,
          }">
					<view class="voice-line one"></view>
					<view class="voice-line two"></view>
					<view class="voice-line three"></view>
					<view class="voice-line four"></view>
					<view class="voice-line five"></view>
					<view class="voice-line six"></view>
					<view class="voice-line seven"></view>
					<view class="voice-line six"></view>
					<view class="voice-line five"></view>
					<view class="voice-line four"></view>
					<view class="voice-line three"></view>
					<view class="voice-line two"></view>
					<view class="voice-line one"></view>
				</view>
				<view class="cancel-icon" v-if="!recording">+</view>
				<view class="tips">{{
          recording ? popupDefaultTips : popupCancelTips
        }}</view>
			</view>
		</view>
	</view>
</template>
<script>
	var that
	// #ifdef APP-PLUS || MP-WEIXIN
	const recorderManager = uni.getRecorderManager()
	// #endif

	// #ifdef H5
	import speech from '../../js_sdk/h5-speech/speech.js'

	const recorderManager = new speech(8000)
	// #endif

	// #ifdef APP-PLUS
	// 引入权限判断
	import permision from '../../js_sdk/wa-permission/permission.js'
	// #endif
	export default {
		name: 'nbVoiceRecord',
		/**
		 * 录音交互动效组件
		 * @property {Object} recordOptions 录音配置
		 * @property {Object} btnStyle 按钮样式
		 * @property {Object} btnHoverFontcolor 按钮长按时字体颜色
		 * @property {String} btnHoverBgcolor 按钮长按时背景颜色
		 * @property {String} btnDefaultText 按钮初始文字
		 * @property {String} btnRecordingText 录制时按钮文字
		 * @property {Boolean} vibrate 弹窗时是否震动
		 * @property {String} popupTitle 弹窗顶部文字
		 * @property {String} popupDefaultTips 录制时弹窗底部提示
		 * @property {String} popupCancelTips 滑动取消时弹窗底部提示
		 * @property {String} popupMaxWidth 弹窗展开后宽度
		 * @property {String} popupMaxHeight 弹窗展开后高度
		 * @property {String} popupFixBottom 弹窗展开后距底部高度
		 * @property {String} popupBgColor 弹窗背景颜色
		 * @property {String} lineHeight 声波高度
		 * @property {String} lineStartColor 声波波谷时颜色色值
		 * @property {String} lineEndColor 声波波峰时颜色色值
		 * @event {Function} startRecord 开始录音回调
		 * @event {Function} endRecord 结束录音回调
		 * @event {Function} cancelRecord 滑动取消录音回调
		 * @event {Function} stopRecord 主动停止录音
		 */
		props: {
			recordOptions: {
				type: Object,
				default () {
					return {
						duration: 600000,
					} // 请自行查看各端的的支持情况，这里全部使用默认配置
				},
			},
			btnStyle: {
				type: Object,
				default () {
					return {
						// width: '300rpx',
						height: '70rpx',
						borderRadius: '35rpx',
						backgroundColor: '#fff',
						border: '3rpx solid whitesmoke',
						permisionState: false,
						
					}
				},
			},
			btnHoverFontcolor: {
				type: String,
				default: '#000', // 颜色名称或16进制色值
			},
			btnHoverBgcolor: {
				type: String,
				default: 'whitesmoke', // 颜色名称或16进制色值
			},
			btnDefaultText: {
				type: String,
				default: '长按开始录音',
			},
			btnRecordingText: {
				type: String,
				default: '录音中',
			},
			vibrate: {
				type: Boolean,
				default: true,
			},
			// #ifdef APP-PLUS || MP-WEIXIN
			popupTitle: {
				type: String,
				default: '正在录制音频',
			},
			popupDefaultTips: {
				type: String,
				default: '左右滑动后松手完成录音',
			},
			// #endif
			// #ifdef H5
			popupTitle: {
				type: String,
				default: '点击录制音频',
			},
			popupDefaultTips: {
				type: String,
				default: '点击完成录音',
			},
			// #endif
			popupCancelTips: {
				type: String,
				default: '松手取消录音',
			},
			popupMaxWidth: {
				type: Number,
				default: 600, // 单位为rpx
			},
			popupMaxHeight: {
				type: Number,
				default: 300, // 单位为rpx
			},
			popupFixBottom: {
				type: Number,
				default: 200, // 单位为rpx
			},
			popupBgColor: {
				type: String,
				default: 'whitesmoke',
			},
			lineHeight: {
				type: Number,
				default: 50, // 单位为rpx
			},
			lineStartColor: {
				type: String,
				default: 'royalblue', // 颜色名称或16进制色值
			},
			lineEndColor: {
				type: String,
				default: 'indianred', // 颜色名称或16进制色值
			},
		},
		data() {
			return {
				stopStatus: true, // 是否已被父页面通知主动结束录音
				btnTextContent: this.btnDefaultText,
				startTouchData: {},
				popupHeight: '0px', // 这是初始的高度
				recording: true, // 录音中
				recordPopupShow: false,
				recordTimeout: null, // 录音定时器
				h5start: false,
				recordTime:0,
				recordTimeInterval:null
			}
		},
		created() {
			
			that = this

			// 请求权限
			this.checkPermission()
			// #ifdef APP-PLUS || MP-WEIXIN
			recorderManager.onStop((res) => {
				// 判断是否用户主动结束录音
				if (that.recordTimeout !== null) {
					// 延时未结束，则是主动结束录音
					clearTimeout(that.recordTimeout)
					that.recordTimeout = null // 恢复状态
				}

				// 继续判断是否为取消录音
				if (that.recording) {
					
					res.time = that.recordTime;
					that.$emit('endRecord', res)
				} else {
					// 用户向上滑动，此时松手后响应的是取消录音的回调
					that.recording = true // 恢复状态
					that.$emit('cancelRecord')
				}
				that.recordTime = 0;
				clearInterval(that.recordTimeInterval)
				that.recordTimeInterval = null;
				
			})
			recorderManager.onStart((err) => {
				that.recordTimeInterval=setInterval(()=>{
					that.recordTime+=1;
					
				},1000)
			})
			recorderManager.onError((err) => {
				
			})
			
			recorderManager.onFrameRecorded((res) => {
				that.$emit('frameRecorded', res)
			})
			
			// #endif
		},
		computed: {},
		methods: {
			upx2px(upx) {
				return uni.upx2px(upx) + 'px'
			},
			async checkPermission() {
				var that = this
				// #ifdef APP-PLUS
				// 先判断os
				let os = uni.getSystemInfoSync().osName
				if (os == 'ios') {
					this.permisionState = await permision.judgeIosPermission('record')
				} else {
					this.permisionState = await permision.requestAndroidPermission(
						'android.permission.RECORD_AUDIO'
					)
				}
				if (this.permisionState !== true && this.permisionState !== 1) {
					uni.showToast({
						title: '请先授权使用录音',
						icon: 'none',
					})
					return
				}
				// #endif
				// #ifdef H5
				if (!window.navigator?.mediaDevices?.getUserMedia) {
					this.permisionState = false
					uni.showToast({
						title: '请先授权使用录音',
						icon: 'none',
					})
					return
				} else {
					this.permisionState = true
				}
				// #endif

				// #ifdef MP-WEIXIN
				uni.authorize({
					scope: 'scope.record',
					success(e) {
						that.permisionState = true
						// that.startRecord();
					},
					fail() {
						uni.showToast({
							title: '请授权使用录音',
							icon: 'none',
						})
					},
				})
				// #endif
			},

			startRecord() {
				if (!this.permisionState) {
					this.checkPermission()
					return
				}
				// #ifdef H5

				if (this.h5start) {
					this.h5start = false
					this.endRecord()
					return
				}
				this.h5start = true
				// #endif
				this.stopStatus = false
				setTimeout(() => {
					this.popupHeight = this.upx2px(this.popupMaxHeight)
					setTimeout(() => {
						this.recordPopupShow = true
						this.btnTextContent = this.btnRecordingText
						if (this.vibrate) {
							// #ifdef APP-PLUS
							// 震动
							plus.device.vibrate(350)
							// #endif
							// #ifdef MP-WEIXIN
							uni.vibrateShort()
							// #endif
						}
						// 开始录音
						recorderManager.start(this.recordOptions)
						// 设置定时器
						this.recordTimeout = setTimeout(
							() => {
								// 如果定时器先结束，则说明此时录音时间超限
								this.stopRecord() // 结束录音动画（实际上录音的end回调已经先执行）
								this.recordTimeout = null // 重置
							},
							this.recordOptions.duration ? this.recordOptions.duration : 30000
						)

						this.$emit('startRecord')
					}, 100)
				}, 200)
			},
			endRecord() {
				var that = this
				if (this.stopStatus) {
					return
				}
				this.popupHeight = '0px'
				this.recordPopupShow = false
				this.btnTextContent = this.btnDefaultText
				// #ifdef APP-PLUS || MP-WEIXIN
				setTimeout(function() {
				    recorderManager.stop()
				}, 500);
				
				// #endif
				// #ifdef H5
				const res = recorderManager.stop()
				if (that.recordTimeout !== null) {
					// 延时未结束，则是主动结束录音
					clearTimeout(that.recordTimeout)
					that.recordTimeout = null // 恢复状态
				}

				// 继续判断是否为取消录音
				if (that.recording) {
					res.time = that.recordTime;
					that.$emit('endRecord', res)
					
				} else {
					// 用户向上滑动，此时松手后响应的是取消录音的回调
					that.recording = true // 恢复状态
					that.$emit('cancelRecord')
				}
				that.recordTime = 0;
				clearInterval(that.recordTimeInterval);
				that.recordTimeInterval = null;
				// #endif
				
				
			},
			stopRecord() {
				// 用法如你录音限制了时间，那么将在结束时强制停止组件的显示
				this.endRecord()
				this.stopStatus = true
			},
			touchStart(e) {
				this.startTouchData.clientX = e.changedTouches[0].clientX //手指按下时的X坐标
				this.startTouchData.clientY = e.changedTouches[0].clientY //手指按下时的Y坐标
			},
			touchMove(e) {
				let touchData = e.touches[0] //滑动过程中，手指滑动的坐标信息 返回的是Objcet对象
				let moveX = touchData.clientX - this.startTouchData.clientX
				let moveY = touchData.clientY - this.startTouchData.clientY
				if (moveY < -50) {
					if (this.vibrate && this.recording) {
						// #ifdef APP-PLUS
						plus.device.vibrate(35)
						// #endif
						// #ifdef MP-WEIXIN
						uni.vibrateShort()
						// #endif
					}
					this.recording = false
				} else {
					this.recording = true
				}
			},
		},
	}
</script>
<style lang="scss">
	.record-btn {
		color: #787878;
		font-size: 28rpx;
		font-weight: bold;
		display: flex;
		align-items: center;
		justify-content: center;
		border: 3rpx surround whitesmoke;
		
	}

	.record-btn-hover {
		color: var(--btn-hover-fontcolor) !important;
		background-color: var(--btn-hover-bgcolor) !important;
	}

	.record-popup {
		position: absolute;
		bottom: 100rpx;
		
		//left: calc(50vw - calc(var(--popup-width) / 2));
		left : 0;
		z-index: 1;
		width: 100%;
		height: var(--popup-height);
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 10rpx;
		//box-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
		background: var(--popup-bg-color);
		color: #000;
		transition: 0.2s height;
		 

		.inner-content {
			height: var(--popup-height);
			font-size: 24rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: space-between;

			.title {
				height: 350rpx;
				font-size: 36rpx;
				line-height: 50rpx;
				font-weight: bold;
				padding: 20rpx 50rpx;
				color: #000;
			}

			.tips {
				color: #999;
				padding: 20rpx 0;
			}
		}
	}

	.cancel-icon {
		width: 100rpx;
		height: 100rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #fff;
		font-size: 44rpx;
		line-height: 44rpx;
		background-color: orangered;
		border-radius: 50%;
		transform: rotate(45deg);
	}

	.voice-line-wrap {
		display: flex;
		align-items: center;
		
		.voice-line {
			width: 5rpx;
			height: var(--line-height);
			border-radius: 3rpx;
			margin: 0 5rpx;
		}

		.one {
			animation: wave 0.4s 1s linear infinite alternate;
		}

		.two {
			animation: wave 0.4s 0.9s linear infinite alternate;
		}

		.three {
			animation: wave 0.4s 0.8s linear infinite alternate;
		}

		.four {
			animation: wave 0.4s 0.7s linear infinite alternate;
		}

		.five {
			animation: wave 0.4s 0.6s linear infinite alternate;
		}

		.six {
			animation: wave 0.4s 0.5s linear infinite alternate;
		}

		.seven {
			animation: wave 0.4s linear infinite alternate;
		}
	}

	@keyframes wave {
		0% {
			transform: scale(1, 1);
			background-color: var(--line-start-color);
		}

		100% {
			transform: scale(1, 0.2);
			background-color: var(--line-end-color);
		}
	}
</style>