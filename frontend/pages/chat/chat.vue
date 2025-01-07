<template>
	<view>
		
		
		<view class="container">
			<image v-if="img" @click="previewImage" :src="img" style="width: 100%;" mode="aspectFit"></image>
			
			<view class="content">
				
				
				<view class="list">
					
					
					
				<sp-editor  v-show="edit"
				        :toolbar-config="{
				          keys: ['bold', 'italic', 'underline', 'strike', 'alignLeft','alignCenter','lineHeight','letterSpacing','fontFamily','fontSize','color','backgroundColor','listOrdered','listBullet','header','undo','redo','clear'],
				          iconSize: '18px',
				        }"
						maxlength="5000"
				        @init="initEditor"
				        @input="inputOver"
				        @overMax="overMax"
				      ></sp-editor>
				
				
				</view>


				<view class="processed-context">
					<text class="text" v-if="!edit && !markdown">{{list}}</text>
					<zero-markdown-view v-if="!edit && markdown" :markdown="list ? list : ''"></zero-markdown-view>
					<zaudio v-if="show_audio && !edit" theme="theme1"></zaudio>
					<view v-if="!creating && !edit" style="display: flex;justify-content: center; margin-top:100rpx;">
						<view @click="scoreClick(1)" style="display: flex;align-items: center; flex-direction: column;padding:0 50rpx;">
							<view  style="margin-bottom: 10rpx;display: flex;justify-content: center; align-items: center; box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);border-radius: 100%;background-color:#fff;width: 100rpx;height: 100rpx"><u-icon color="#1acc89" size="25" :name="score==1 ? 'thumb-up-fill' : 'thumb-up'"></u-icon></view>	
							<text style="color: #999;">很有帮助</text>
							
						</view>
						
						<view @click="scoreClick(-1)" style="display: flex;align-items: center; flex-direction: column;padding:0 50rpx;">
							<view style="margin-bottom: 10rpx;display: flex;justify-content: center; align-items: center; box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);border-radius: 100%;background-color:#fff;width: 100rpx;height: 100rpx"><u-icon color="#1acc89" size="25" :name="score==-1 ? 'thumb-down-fill' : 'thumb-down'"></u-icon></view>	
							<text style="color: #999;">需要改进</text>
							
						</view>
						
					</view>
					
				</view>
				
				
				
				
					
				<view  class="info">
									
					<view style="width: 30%;"><u-button v-if="!edit" loadingText="创作中" :loading="creating" size="large" shape="circle" @click="copy"  text="复制全文"></u-button></view>
					<view  style="width: 60%;margin-left: 20rpx;display: flex;justify-content: center;">
						<view v-if="!creating" style="width: 100%;display: flex;justify-content: center;">
							<u-button v-if="!edit" size="large" shape="circle" @click="editClick" color="#1acc89"  text="编辑内容"></u-button>
							<view style="margin: 0 10rpx;"><u-button v-if="edit" size="large" shape="circle" @click="saveEdit" color="#1acc89"  text="保存内容"></u-button></view>
							<view style="margin: 0 10rpx;"><u-button  v-if="edit" size="large" shape="circle" @click="cannelEdit"   text="取消编辑"></u-button></view>
							
						</view>
						
						<u-button iconColor="#fff" color="#1acc89" v-if="creating" icon="pause-circle" @click="finish" size="large" shape="circle"  text="终止创作"></u-button>
					</view>
									
									
				</view>
				
				
			
				
				
				</view>
				
				
			</view>
			
			
			<u-modal confirmColor="#1acc89" @cancel="modalCancel()" @confirm="modalConfirm()" :show="showModal" confirmText="确认保存" title="温馨提示" content='ddd' :showCancelButton="true" :buttonReverse="true">
					<view class="slot-content">
						<text style="line-height: 2">{{modalContent}}</text>
						<view style="margin-top: 40rpx;">
						<u-checkbox-group v-model="checked">
							<u-checkbox activeColor="#1acc89"  label="我知道了,以后不再提示"></u-checkbox>
						</u-checkbox-group>
						</view>
					</view>
			</u-modal>
			
			
			
		</view>
		
		
	</view>
</template>

<script>
	import zaudio from 'uniapp-zaudio/zaudio';
	export default {
		components: {
			zaudio: zaudio,
			
		},
		data() {
			return {
				creating:true,
				stream :1,
				edit:0,
				list : '',
				assistant:'',
				ad:uni.getStorageSync('ad'),
				socketMsgQueue : [],
				cdnUrl:this.$config.cdnUrl,
				adVideo: this.$config.adVideo,
				score:0,
				chatId:0,
				markdown:true,
				editInfo:'',
				modalContent:'',
				showModal:false,
				checked:[],
				chatSocket:null,
				editorIns:null,
				img:'',
				show_audio:false
			
			}
		},
		onLoad(options){
			uni.setNavigationBarTitle({
				title:options.assistant
			})
			this.stream = options.stream;
			this.assistant = options.assistant;
			this.chatId = options.chatId;
			if(options.stream == 1){
				this.chat(options.chatId);
			}
			
			if(options.stream == 2){
				this.chatStream(options.chatId);
			}
			
		
			
		},
		methods: {
			previewImage(){
				uni.previewImage({
					current: 0,
					urls: [this.img],
					success() {
					},
					fail(err) {
						
					}
				})
			},
			initEditor(editor) {
			  this.editorIns = editor; 
			  
			},
			inputOver(e) {
			      this.editInfo = e.html
			      console.log("==== inputOver :", e);
			},
			    
			overMax(e) {
			  // 若设置了最大字数限制，可在此处触发超出限制的回调
			  console.log("==== overMax :", e);
			},
			
			editClick(data){
				this.editInfo = this.list;
				this.edit=1;
				
				this.editorIns.setContents({
				    html: this.editInfo,
				});
				
			},
			copy() {
				uni.setClipboardData({
					data: this.list,
					success: function() {
						uni.showToast({
							title: '复制成功',
							icon: 'none'
						})
					}
				});
			},
			chat(chatId){
				var postData = {};
				postData.chatId = chatId;
				postData.loadingData = '生成中';
				this.$http.requestApi('POST', '/chat/getChat', postData).then(res => {
					const resData = res.data;
					const resCode = res.code;
					if (resCode == 0) {
						this.list = resData.msg;
						this.img = resData.upload_img;
						this.show_audio = false;
						var bool = this.list.indexOf("```");
						var bool2 = this.list.indexOf("*");
						if(bool>0 || bool2>0){
							this.markdown = true;
						}
						
						
						
							
						const regex = /(https?:\/\/[^\s]+?\.mp3)/g;
						let match;
						let mp3Links = '';
						while ((match = regex.exec(this.list)) !== null) {
							mp3Links = match[1];
						}
						
						if(mp3Links){
							this.list = this.list.replace(mp3Links, '');
							this.show_audio = true;
							var data = [
							  {
								src:mp3Links,
								title: '宝宝问，孩子成长的智能伙伴',
								singer: this.assistant,
								coverImgUrl:this.img?this.img:this.$config.cdnUrl+'/bbw.png',
							  },
							  
							];
							
							this.$zaudio.setAudio(data); //添加音频
							this.$zaudio.setRender(0)//渲染第一首音频
						}
						
						
						
						
					} else {
						uni.showToast({
							title: '生成失败，请稍后重试！',
							icon: 'none'
						});
					}
					
					this.creating = false;
					
				})
			},
			finish(){
				this.creating = false;
				if(this.chatSocket){
					this.chatSocket.close();
				}
			},
			
			
			chatStream(chatId){
				if(this.chatSocket){
					this.chatSocket.close();
				}
				
				this.chatSocket = this.$scoket.connectApi();
				
				
				let post = {
					chatId:chatId,
					type:'creation'
				};
				
				var thats = this;
				
			
				this.chatSocket.onOpen(function (res) {
					
					
					thats.$scoket.sendSocketMessage(post,thats.chatSocket);
				  
				});
			
				this.chatSocket.onClose(function(res) {
					thats.creating = false;
					var bool = thats.list.indexOf("```");
					if(bool>0){
						thats.markdown = true;
					}
					thats.show_audio = false;
												
					const regex = /(https?:\/\/[^\s]+?\.mp3)/g;
					let match;
					let mp3Links = '';
					while ((match = regex.exec(thats.list)) !== null) {
						mp3Links = match[1];
					}
					
					if(mp3Links){
						thats.list = thats.list.replace(mp3Links, '');
						thats.show_audio = true;
						var data = [
						  {
						    src:mp3Links,
						    title: '宝宝问，孩子成长的智能伙伴',
						    singer: thats.assistant,
						    coverImgUrl:thats.img?thats.img:thats.$config.cdnUrl+'/bbw.png',
						  },
						  
						];
						
						thats.$zaudio.setAudio(data); //添加音频
						thats.$zaudio.setRender(0)//渲染第一首音频
					}
					
					

					uni.pageScrollTo({
						duration: 0,
						scrollTop: 99999
					})
				});
				
				
				this.chatSocket.onMessage(function(res) {		
					
					var json = JSON.parse(res.data);
					if(json.hasOwnProperty('upload_img')){
						thats.img = json.upload_img
					}
					
					thats.list = json.data;
					
					uni.pageScrollTo({
						duration: 0,
						scrollTop: 99999
					})
					
					if(json.code != 0){
						uni.showToast({
							title: json.message ? json.message : '生成失败',
							icon: 'none',
							duration:10000
						});
						
					}
	
					
					
				});
			},
			evaluate(){
				var plugin = requirePlugin("wxacommentplugin");
					plugin.openComment({
						success: (res) => {
							console.log('plugin.openComment success', res)
						},
						fail: (res) => {
							console.log('plugin.openComment fail', res)
						}
					})
			},
			scoreClick(e){
				if(e == this.score){
					return;
				}
				this.score = e;
				
				var postData = {};
				postData.id = this.chatId;
				postData.score = this.score;
				postData.showLoading = 0;
				this.$http.requestApi('POST', '/chat/score', postData).then(res=>{
					
				});
			},
			saveEdit(){
				
				var flag = uni.getStorageSync('hide_edit_modal');
				if(flag){
					this.modalConfirm();
					return;
				}
				
				this.modalContent = "编辑内容将保存至我的-我的文档，后续可以在我的文档中查看编辑之后的内容";
				this.showModal = true;
				
				
			},
			cannelEdit(){
				this.edit = 0;
			},
			modalCancel(){
				this.showModal = false;
			},
			modalConfirm(){
				
				if(this.checked.length){
					uni.setStorageSync('hide_edit_modal',true)
				}
				
				var postData = {};
				postData.id = this.chatId;
				postData.data = this.editInfo;
				postData.title = this.assistant;
				postData.showLoading = 0;
				this.$http.requestApi('POST', '/document/insert', postData).then(res=>{
					
					if(res.code != 0){
						uni.showToast({
							title: res.message ? res.message : '创建文档失败，请稍后重试',
							icon: 'none',
							duration:3000
						});
						return;
					}
					
					this.edit = 0;
					uni.redirectTo({
					    url: '/pages/document/info?id='+res.data.id
					});
					
				});
			}
		}
	}
</script>

<style lang="scss">
	.container{
		width: 100%;
	}
	.title{
		margin: 20rpx;
	}
	.content{
		margin: 20rpx;
		.processed-context {
			padding: 30rpx 30rpx 300rpx 30rpx;
			border-radius: 12rpx;
			min-height: 200rpx;
			
		}
	}
	
	.info{
		position: fixed;
		background-color: #f1f1f1;
		bottom: 0;
		width: 100%;
		height: 150rpx;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	.tag{
		float: left;
		margin-left: 10rpx;
		padding: 20rpx;
		width: 40%;
		
	}
	.finish{
		margin: 0 auto;
		width: 35%;
	}
	
	.text{
		margin:0;
		font-size: 14px;
		line-height:2;
		letter-spacing:0.1em;
		word-spacing:0.1em;
	}
	
</style>
