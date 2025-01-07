<template>
	<view class="th-file-box">
		<view class="progress-line-box" :style="{'opacity':(0<file.progess)?'1':'0',
		'width':file.progess+'%'
		,'border-top-right-radius':file.progess==100?'8rpx':'0'
		,'border-bottom-right-radius':file.progess==100?'8rpx':'0'}"></view>
		<slot>
			<image class="file-image" :src="setImg(file.name)"></image>
		</slot>
		<view class="file-content-box">
			<view class="title-text text-line-c" :style="{'-webkit-line-clamp':1}">{{file.name}}</view>
			<view class="desc-text">
				<view v-if="toMB(file.size)">{{toMB(file.size)}} </view>
				<view class="mar-left25">{{uploadStatus()}}</view>

			</view>
			<view class="text-priview">
				<view class="text-priview-remove-text-color" @tap="remove">移除</view>
				<view v-if="!file.status" class="left36 text-priview-try-text-color" @tap="reUpload">点击重试</view>
			</view>

		</view>


	</view>
</template>

<script>
	export default {
		name: "th-file",
		data() {
			return {
				statusMap: new Map([
					[0, '等待上传'],
					[100, '上传成功'],
					[-1, '上传中']
				])

			};
		},
		props: {
			file: {
				type: Object,
				default: {}
			},
			index:{
				type:Number,
				default:-1
			}
		},
		methods: {
			toMB(size) {
				if (!size)
					return ''
				if (size < 1024)
					return size + 'B'
				else if ((size / 1024).toFixed(2) < 1024) {
					return (size / 1024).toFixed(2) + 'K'
				} else
					return (size / 1024 / 1024).toFixed(2) + 'M'

			},
			remove(){
				this.$emit('remove',this.index)
			},
			reUpload(){
				this.$emit('reupload',this.index)
			},

			uploadStatus() {
				let statusStr = ''
				const status = this.file.status
				const progress = this.file.progess
				if (status) {
					if(progress==0||progress==100)
					statusStr=this.statusMap.get(progress)
					else
					statusStr=this.statusMap.get(-1)

				} else {
					statusStr = '上传失败'
				}
				return statusStr
			},
			
			getType(file) {
				let imgType = ['bmp', 'jpg', 'jpeg', 'png', 'tif', 'gif', 'pcx', 'tga', 'exif', 'fpx', 'svg', 'psd', 'cdr',
					'pcd', 'dxf', 'ufo', 'eps', 'ai', 'raw', 'WMF', 'webp'
				]
				let fileTypev = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'pdf']
				let videoType = ['avi', 'mov', 'rmvb', 'flv', 'mp4']
				let spertas = file.split('.')
				if (spertas.length > 0) {
					if (imgType.indexOf(spertas[spertas.length - 1]) != -1)
						return 'img'
					if (fileTypev.indexOf(spertas[spertas.length - 1]) != -1)
						return 'txt'
					if (videoType.indexOf(spertas[spertas.length - 1]) != -1)
						return 'vdo'
				}
				
				return 'unknow'
			},
			setImg(file) {
				let src = ''
				switch (this.getType(file)) {
					case 'img':
						src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAoBAMAAAET63VGAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAtUExURUxpcQC4VPX7/wC6Uwe7WI3duPL3/+/3/wC4VO/2/////8Xt3h/BaWLRmXLWpRQVcsIAAAAIdFJOUwDwDiWs/X7IfhDxuAAAARdJREFUKM+FUjtOw0AQHYmGPikoXaUOXUrouAJKkyPkClFAmsUsipDyGRlSW5uWwk7EASwfBYkzMLvrcdYmlp+046c3761nVwvXCIC8YvedAeoV4MJKLwioLEG0BjymzoK+EsCNqw+Iy0C31WS2HjD0K1ufE8enc67KZ1EcR2NSSZx362DfwmKaeaYpn3sWP562MPHtBcCtZ3d19qm5nzE8buff3FBtLe2dbydMk7BCmCLpqj1FVXdXvHtWnD4qltPa3bHFsj6HYAB1U8CHBWyhkvhyGYdQ6nJl2OO6HMz6XWe8/pPe6LMtlUSrhqR/iH6jhkSMpClp2pe0jdpDlLQJpNgG16OofoE8Uz5N8i8e4uo+zA3HAH986GBIByVt5gAAAABJRU5ErkJggg=="
						break
					case 'txt':
						src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAoBAMAAAET63VGAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAhUExURTKW+kxpcTaZ/DCY//H3//H3/+/2/////83m/ozE/VCm+n1OVwAAAAAGdFJOU/8ArCXGc+f13qUAAADwSURBVCjPjVJLDoJADG0wGWdcOTHu9QZ6A008gEeARBtwxW5cEheIFzB6AeMx7XwFFLFJy+P1vU6HAGMYA1By89wCiBhsDCmZw7p5zo0EbEVfZwCDGq+rKnQ9Ql3PdBWpwdVKE9ZrQlKelcotenOd6OIRx7UFAkuH+LXawCI4HJoHb9ScpxSt23maWarN5b37xR4J9OjhEUPfZVnoxixxsurgUIlZmDcI93gzEtrRTdHHpTj+oyr6Zn03Fv2qX5Sgy7aoJ2LSoMQN8d5UIUXapGgSx93HiU/c1964NmZTGf5AgFF5S8sTzZrM6r5oKeULXQ00cgxk1z8AAAAASUVORK5CYII='
						break
					case 'vdo':
						src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAoBAMAAAET63VGAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAtUExURUxpcfhNEPhSFfhMDvD3//H3//dOEO/2//////rXy/Db2fOmjPZjLvSScfHDtm7LORYAAAAGdFJOUwDwrCXNftZAgEIAAAEVSURBVCjPhVIxTsNAEFwJkQcQiTppqKGhTsED8oUQaQ+SnOQiZBWZUFrnDxg3NDR0tEjhAZb8kUh5BXt3WfvsYHkkr0ezM+u9k2GAAMjPs3tPAXUEOLPSEwIqSxCtAfPEWdBXArh29QZxHui2mszWFEO/snW5dfw44ap8FsWRG5NIop7WwT6ELWjqmab3iWfq5/gA9749A7jzbFRlH5vzjOF1O7/mlmprSe9+G2GahBXCFElXxVV3U0SeFd+vJ/ZFsbtji3l1DsEQqqaADwvYwkniy2WkodTlyrDH9X8w63fVWJ1JS9q1pZIoakh6T/TbdBFj25Q0xSWtz5Yo6SWQFjYYX9Z/IO9E+7fDJ8+6GIe5q1uAP70zDXveYi4aAAAAAElFTkSuQmCC'
						break
					default:
						src ='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAoBAMAAAET63VGAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAqUExURUxpcfi+aPWnIvaqKPD3//H3//WmI+/2//////br1/LQlvHgwvLHfPS0SXHgbQ8AAAAGdFJOUwAZ8KzNfpy7tfkAAAESSURBVCjPhVJBSsQwFP1uXBdB9yK4FheuZ+EB5gozwosZIoyg86HVbcmshXbAA8h4ARnwHoO38Sdpalqm9kFfX99/P/kJpWMQQR7t31OCWRLmzroHQTkBuAA2pY8gMBNder4BZonv2FaO10jzyvGi8Ho3EVahFzGxsbaMHX+rDajvqDRPgzJcT4LSH7tbOgvlOdFJUFdt7113PWtl3MHd/FB9rxydbxWV4aj2USmOVZVz1lRX6jGo/ftro2rO/R07zNpzRJxTW4yQwxJ6aCy5XME6tYZSFUZShxur8dR/1oJf+tYP87JjmS3zZ9axWFB0LcO55qesv6PmZ0q/BPlp1v6BwEO9Lb7eZIij67TvQlb/BW9yDDPkSVsuAAAAAElFTkSuQmCC'
						break
				}
				return src
			},
		}
	}
</script>

<style scoped lang="scss">
	.th-file-box {
		position: relative;
		width: 100%;
		min-height: 120rpx;
		background: #F6F7FB;
		border-radius: 8rpx;
		box-sizing: border-box;
		padding: 14rpx 24rpx;
		display: flex;
		align-items: center;
		border-radius: 8rpx;

		.file-image {
			width: 54rpx;
			height: 62rpx;
			white-space: nowrap;
			z-index: 999;
		}

		.title-text {
			font-size: 30rpx;
			color: #333333;
			line-height: 42rpx;
			word-break: break-all;
		}

		.desc-text {
			display: flex;
			align-items: center;
			font-size: 24rpx;
			font-family: PingFang-SC-Medium, PingFang-SC;
			font-weight: 500;
			color: #999999;
			line-height: 34rpx;
		}
	}

	.file-content-box {
		flex: 1;
		margin-left: 18rpx;
		position: relative;

		.text-priview {
			display: flex;
			align-items: center;
			position: absolute;
			right: 0;
			bottom: 0;
			font-size: 24rpx;
			
			line-height: 34rpx;
			.left36 {
				margin-left: 36rpx;
			}
			&-remove-text-color{
				color: #E73535;
			}
			&-try-text-color{
				color: #0077FF;
			}
		}
	}

	.text-line-c {
		word-break: break-all;
		display: -webkit-box;
		-webkit-line-clamp: 1;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.progress-line-box {
		height: 100%;
		background-color:#efefef;// #41ae3c;
		position: absolute;
		left: 0;
		border-radius: 8rpx;
		border-top-left-radius: 8rpx;
		border-bottom-left-radius: 8rpx;

	}

	.mar-left25 {
		margin-left: 25rpx;
	}
</style>