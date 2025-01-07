<template>
	<view v-if="assistantInfo.id">
		<view v-if="!imageEdit">
		<view  class="container">
			<view class="pre-form">
				
					<view class="option-item" @click="assistantList(assistantInfo.id)">
						<view class="option-content">
							<view class="sd-model-item sd-model-item-after">
								<view class="sd-item">
									<view style="border-radius: 6px;margin:auto 0">
										<u--image :src="assistantInfo.icon" radius="6" width="80rpx"
											height="80rpx" :lazyLoad="false"
											>
										</u--image>
									</view>
									<view class="sd-item-context">
										<view class="sd-item-title sd-item-title-weight">
											{{assistantInfo.name}}
											
										</view>
										<view class="sd-item-tips">
											{{assistantInfo.desc}}
											
										</view>
									</view>
									
								</view>
							</view>
						</view>
					</view>
					
					
					<view class="option-item" >
						
						<view v-for="(val, index) in assistantInfo.keywords" :key="index">
							
						
						
						
						<view style="display: flex;">
						<view class="title layout">
							<text style="z-index: 1;" class="title-item">{{val.title}}</text>
							
							
						</view>
						<view v-if="val.title=='字数要求'" style="margin: auto 5rpx;">
							<u-icon @click="showModalBox(1,1)" name="question-circle"  size="15"></u-icon>
						</view>
						</view>
						
						<view v-if="val.type=='image'" class="option-content">
							<cl-upload v-model="uploadImgList" cloudType="other" fileType="image" :add="uploadImgList.length ? false : true" :useBeforeUpload="true" :useBeforeDelete="true" @beforeDelete="beforeDelete" @beforeUpload="uploadImg" :listStyle="{
								    columns: '1',
								    columnGap: '20rpx',
								    rowGap:'20rpx',
								    padding:'10rpx',
								    height:'250rpx',
								    radius:'20rpx',
									
								}"
								:imageFormData="{
									count :'1',
									size:'10'
								}"
								
								max="1"
								>
								<template v-slot:addImg>
								        <view style="width: 100%;height: 250rpx;background: rgba(255, 255, 255, .3);
							backdrop-filter: blur(10rpx);
							border-radius: 15rpx;
							box-shadow: 0rpx 0rpx 10rpx #fff;
							background-color: #ffffff;" class="newAddImg">
												
												<view style="margin-top:50rpx;text-align: center;color: #515151;font-size: 60rpx;">＋</view>
												<view style="text-align: center;font-size: 26rpx;color: #515151;">点击上传图片</view>
												<view style="text-align: center;font-size: 22rpx;color: #515151;">支持图片格式：JPG,PNG 不超过：8MB</view>
												
								        </view>
								</template>
								   
								</cl-upload>
						</view>
						
						
						<view v-if="val.type=='list'" class="option-content">
						
							<view class="list">
								<view  @click="listSubmit(item.title)"  class="item" v-for="(item,childIndex) in val.val" :key="childIndex">
									<view :class="'cover-box label-'+ childIndex%10">
										
											<u--image v-if="item.image" :src="item.image" radius="6" width="70rpx"
												height="70rpx" :lazyLoad="false"
												>
											</u--image>
										
										<text v-else>{{item.title[0]}}</text>
										
									</view>
									<view class="item-info-box" >
										<view class="search_title">{{item.title}}</view>
										
									</view>
									<view class="hot">
										<u-icon name="arrow-right" size="16" color="#B1B1B1"></u-icon>
									</view>
								</view>
								
							</view>
							
						</view>
						
						
						
						<view v-if="val.type=='input'" class="option-content">
							<view  class="sd-model-item-context">
								
								<u--textarea v-model="val.val"
									:placeholder="val.default"  autoHeight  count maxlength="20" confirmType="done"
								>
								</u--textarea>
								
							</view>
						</view>
						<view v-if="val.type=='textarea'" class="option-content">
							<view  class="sd-model-item-context">
								<u--textarea v-model="val.val"
									:placeholder="val.default"  height="100"  count maxlength="400" confirmType="done"
								>
								</u--textarea>
							</view>
						
						</view>
						<view v-if="val.type=='file'" class="option-content">
							<th-file-picker url="/chat/uploadFile" :pid="assistantInfo.id" ref="filePick"  :types="types" :showTitle="false" count="5" @result="uploadResult"></th-file-picker>
							
						</view>
						
						
						<view  v-if="val.type=='slider'" class="option-content">
								
								<u-slider activeColor="#1acc89" v-model="val.val" step="50" min="50" max="1000" showValue></u-slider>
						</view>
						
						</view>
						<view v-if="showStream">
							<view style="display: flex;">
							<view class="title layout">
								<text style="z-index: 1;" class="title-item">流式输出</text>
								
								
							</view>
							<view style="margin: auto 5rpx;">
								<u-icon @click="showModalBox(1,2)" name="question-circle"  size="15"></u-icon>
							</view>
							</view>
							
							<u-switch activeColor="#1acc89" activeValue="2" inactiveValue="1" :value="stream" @change="streamChange"></u-switch>
						</view>
					</view>
					
				
			</view>
		</view>
		<u-modal confirmColor="#000" @close="showModalBox(0)" @confirm="showModalBox(0)" :content="modalContent" :show="showModal" :closeOnClickOverlay="true" confirmText="知道了"></u-modal>
		
		
		<view style="display: flex;">
			<view style="width: 75%;margin:0 20rpx 0 20rpx;padding-bottom: 50rpx">
				<u-button color="#1acc89" @click="submit"   type="primary" size="large" text="开始创作" ></u-button>
			</view>
		
			<view @click="likeClick()" class="like">
				<view class="like_item">
					<u-icon color="#1acc89" size="25" :name="assistantInfo.like ? 'star-fill' : 'star'"></u-icon>
				</view>
				
			</view>
			
			
		</view>
		</view>
		
		<view v-if="imageEdit">
			<l-clipper max-height="1200" min-height="100"  :is-lock-ratio="false" :is-limit-move="true" min-ratio="0.5" max-ratio="1" max-width="800" width="800" v-if="tempPhoto.length > 0" :image-url="tempPhoto" @success="photoConfirm" @cancel="photoCancel"  >
			
				</l-clipper>
		</view>
		
		
		
		
		
	<view v-if="!imageEdit">
		<ad  ad-type="video" :unit-id="adVideo"></ad>
	</view>
	</view>
	
	
	
	
	
</template>


<script>
	export default {
		components: {
			
		},
		data() {
			return {
				stream:'2',
				showStream : true,
				assistantInfo:{
					id : 0,
					name : '',
					desc : '',
					icon : '',
					like : 0,
					keywords : [],
					type:1
				},
				msg:[],
				showModal:false,
				modalContent:'',
				showCamera:false,
				tempPhoto:'',
				uploadImgList:[],
				imageEdit:false,
				types:['file'],
				fileList:[],
				adVideo: this.$config.adVideo


				

			}
		},
		
		onLoad(options) {
			
			
			this.getAssistantInfo(options.assistant_id || 0);
		},
		methods: {
			beforeDelete(item, index, next) {
				uni.showModal({
					title: '提示信息',
					content: '确定要删除这个文件嘛？',
					success: res => {
						if (res.confirm) {
							this.assistantInfo.keywords.forEach((item) => {
								if(item.type=='image'){
									item.val = '';
								}
							});
							next();
						}
					}
				});
			},
			
			uploadFile(){
			    this.$refs.filePick.preUpload()
			}
			,
			uploadResult(e){
				this.fileList = e;
			}
			,
			photoCancel(){
				this.imageEdit = false;
			},
			
			async photoConfirm(data){
				this.imageEdit = false;
				
				var res = await this.$http.upload('/chat/uploadImage',data.url,{showLoading:1,loadingData:'上传中'});
				res = JSON.parse(res);
				if(res.code === 0){
					this.uploadImgList.push(res.data.url);
					
					
					this.assistantInfo.keywords.forEach((item) => {
						if(item.type=='image'){
							item.val = res.data.url;
						}
					});
				}else{
					uni.showToast({
						title: res.message ? res.message : '上传失败',
						duration: 3000,
						icon: 'none'
					});
				}
				
			},
			uploadImg(tempFile, next){
				this.tempPhoto = tempFile.path;
				this.imageEdit = true;
				
			},
			
			async getAssistantInfo(assistant_id){
				var data = {};
				data.assistant_id = assistant_id;
				var res = await this.$http.requestApi('GET', '/assistants/info',data);
				this.assistantInfo = res.data;
				
			
				
				this.assistantInfo.keywords.forEach((item) => {
					if(item.type=='file'){
						this.showStream = false;
					}
				});
				
				

				if(this.showStream){
					var stream = uni.getStorageSync('stream');
					if(stream){
						this.stream = stream;
					}
				}
				
				this.streamChange(this.stream);
	
			},
			assistantList(assistant_id){
				uni.navigateTo({
					url: '/pages/assistant/index?assistant_id='+assistant_id+'&level='+uni.getStorageSync('level')
				});
			},
			
			
			streamChange(e){
				if(e=='2'){
					uni.setStorageSync('stream',2)
					this.stream = '2';
				}else{
					uni.setStorageSync('stream',1)
					this.stream = '1';
				}
			},
			
			showModalBox(e,t=0){
				if(e){
					this.showModal = true;
				}else{
					this.showModal = false;
				}
				
				if(t==1){
					this.modalContent = '你可以指定字数要求供AI参考，实际字数请参考以下说明：\n1.AI会参考所指定的字数要求值\n2.最终实际字数由AI大模型决定，无法确定精准的字数';
				}
				
				if(t==2){
					this.modalContent = '你可以指定生成内容输出方式：\n1.流式输出：点击生成后，生成结果按一段一段的即时响应，请求时间相对较短\n2.非流式输出：点击生成后，生成完成一次性返回所有数据，请求时间相对较久';
				}
				
			},
			
			likeClick(){
				this.assistantInfo.like = this.assistantInfo.like ? 0 : 1;
				
				var data = {};
				data.assistant_id = this.assistantInfo.id;
				data.type = this.assistantInfo.like;
				data.showLoading = 0;
				this.$http.requestApi('GET', '/assistants/like',data);
				
				
			},
			
			listSubmit(text){
				this.submit(text)
			},
			
			
			submit(text='') {
				
	
	
			
				let post = {
					input:JSON.stringify(this.assistantInfo.keywords),
					assistant_id:this.assistantInfo.id,
					loadingData:"创作中",
					
				};
				
				if(this.fileList.length > 0){
					post.fileList=JSON.stringify(this.fileList)
				}
				
				
				if(text){
					post.text = text;
				}
				
				if(this.showStream){
					post.stream = this.stream
				}
				
				this.$http.requestApi('POST', '/chat/create/', post).then(res => {
					
					const resCode = res.code;
					const resData = res.data;
					if (resCode == 2009) {
						uni.showModal({
							title: '温馨提示',
							content: '您的可用生成次数不足，可通过以下方式免费获取次数哦',
							confirmText: '每日任务',
							success: function(res) {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pages/my/buy'
									});
								} else if (res.cancel) {
									
								}
							}
						});
						return false;
					}
					if (resCode == 0) {
						
						var stream = resData.stream;
						var chatId = resData.chat_id;
						
						if(this.assistantInfo.type == '1'){
							uni.navigateTo({
								url: '/pages/chat/chat?chatId='+chatId+'&stream='+stream+'&assistant='+this.assistantInfo.name
							});
							return;
						}
						
						if(this.assistantInfo.type == '2'){
							uni.navigateTo({
								url: '/pages/chat/chat_audio?chatId='+chatId+'&stream='+stream+'&assistant='+this.assistantInfo.name
							});
							return;
						}
						
						if(this.assistantInfo.type == '3'){
							uni.navigateTo({
								url: '/pages/chat/chat_image?chatId='+chatId+'&stream='+stream+'&assistant='+this.assistantInfo.name
							});
							return;
						}
						
						
						
					} else {
						uni.showToast({
							title: res.message ? res.message : '未知错误',
							duration: 3000,
							icon: 'none'
						});
					}
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	
	.container {
		width: 92%;
		margin: 30rpx auto;

		

		.pre-form {
			margin: 20rpx 0;
		}

		
	}

	.option-item {
		.title {
			padding: 30rpx 0;
			font-size: 30rpx;
			font-weight: 600;
		}

		.option-content {

			.sd-model-item {
				margin-bottom: 20rpx;
				background-color: #FFF;
				padding: 20rpx;
				color: #222;
				border-radius: 12rpx;
				position: relative;

				.sd-item {
					display: flex;

					.sd-item-context {
						margin: 0 50rpx;
						display: flex;
						flex-direction: column;
						justify-content: center;
						line-height: 48rpx;

						.sd-item-title {
							font-size: 30rpx;
						}

						.sd-item-title-weight {
							font-weight: 600;
						}

						.sd-item-tips {
							font-size: 24rpx;
							color: #999;
							line-height: initial;
							white-space:pre-wrap;
							margin-top: 10rpx;
						}

						.sd-item-font {
							font-size: 28rpx;
						}
					}
				}
			}

			.sd-model-item-after {
				.sd-item::after {
					content: '';
					position: absolute;
					right: 30rpx;
					top: calc(50% - 12rpx);
					width: 20rpx;
					height: 20rpx;
					border-top: 4rpx solid;
					border-right: 4rpx solid;
					border-color: #999;
					content: '';
					transform: rotate(45deg);
				}
			}
		}
	}

	.layout {
		
		display: flex;
		justify-content: space-between;
		align-items: center;
		.tips-item {
			font-weight: 400;
			font-size: 24rpx;
			color: #999;
		}
	}
		
	.like{
		position:relative;
		
		width: 120rpx;
		height: 100rpx;
		.like_item{
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
		}
	}
	
	
	.like::after {
	  content: "";
	  position:absolute;
	  bottom:0;
	  z-index:-1;
	  width: 200%;
	  height: 200%;
	  display:block;
	  border:1px solid #b6b6b6;
	  border-radius:8px;
	  transform:scale(0.5);
	  transform-origin:left bottom;
	  
	}
	
	.list .item {
		background: #f5f6f8;
		border-radius: 24rpx;
		margin: 15rpx 25rpx 0 25rpx;
		display: flex;
		padding: 15rpx 0;
		align-items: center;
		box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
	}
	
	.item-info-box {
		display: flex;
		flex-direction: column;
		justify-content: space-evenly;
		color: #333333;
		white-space:pre-wrap;
		width: 450rpx;
	
	}
	
	.item-info-box .search_title {
		-webkit-text-stroke-width: 0.1px;
		font-size: 30rpx;
		font-weight: bolder;
	}
	
	.item-info-box .search_desc {
		-webkit-text-stroke-width: 0.3px;
		display: flex;
		align-items: center;
		margin-top: 10rpx;
		font-size: 25rpx;
		color: #868888;
	}
	
	
	
	.cover-box {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 70rpx;
		height: 70rpx;
		border-radius: 14rpx;
		margin: 0 20rpx;
		font-size: 40rpx;
		font-weight: bold;
	}
	
	
	
	.label-10 {
		background: #93ff97;
	}
	
	.label-1 {
		background: #ffd059;
	}
	
	.label-7 {
		background: #EDC744;
	}
	
	.label-6 {
		background: #e2ffd7;
	}
	
	.label-4 {
		background: #fff49e;
	}
	.label-5 {
		background: #8efcfc;
	}
	.label-3 {
		background: #b1f8a5;
	}
	.label-2 {
		background: #ffdbfc;
	}
	.label-8 {
		background: #9caefe;
	}
	.label-9 {
		background: #ffafed;
	}
	.label-0{
		background: #b9c6ff;
	}
	
	.hot{
		position: relative;
		margin: auto 20rpx;
		
	}


	
</style>

