<template>
	<view>
		
		
		<view class="container">
			
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
					
					
				</view>
				
				
				
				
					
				<view  class="info">
									
					<view style="width: 30%;"><u-button v-if="!edit"  size="large" shape="circle" @click="copy"  text="复制全文"></u-button></view>
					<view  style="width: 60%;margin-left: 20rpx;display: flex;justify-content: center;">
						<view  style="width: 100%;display: flex;justify-content: center;">
							<u-button v-if="!edit" size="large" shape="circle" @click="editClick" color="#1acc89"  text="编辑内容"></u-button>
							<view style="margin: 0 10rpx;"><u-button v-if="edit" size="large" shape="circle" @click="editList" color="#1acc89"  text="保存内容"></u-button></view>
							<view style="margin: 0 10rpx;"><u-button  v-if="edit" size="large" shape="circle" @click="editClick"   text="取消编辑"></u-button></view>
							
						</view>
						
					</view>
									
									
				</view>
				
				
			
				
				
				</view>
				
				
			</view>
			<!-- <view v-if="ad" style="position:relative; bottom: 0;margin-top: 200rpx">
				<ad v-if="ad" ad-type="video" :unit-id="adVideo"></ad>
			</view> -->
		</view>
		
		
	</view>
</template>

<script>
	export default {
		components: {
			
		},
		data() {
			return {
				documentId:0,
				edit:0,
				list : '',
				assistant:'',
				ad:uni.getStorageSync('ad'),
				cdnUrl:this.$config.cdnUrl,
				adVideo: this.$config.adVideo,
				
				markdown:true,
				editInfo:'',
				editorIns : null
				
			
			}
		},
		onLoad(options){
			
			
			this.documentId = options.id
			this.getDocumentInfo();
			
		},
		methods: {
			initEditor(editor) {
			  this.editorIns = editor; 
			  
			},
			inputOver(e) {
			      this.editInfo = e.html
			},
			    
			overMax(e) {
			  // 若设置了最大字数限制，可在此处触发超出限制的回调
			},
			editClick(data){
				if(this.edit==1){
					this.edit=0;
				}else{
					this.editInfo = this.list;
					this.edit=1;
					this.editorIns.setContents({
					    html: this.editInfo,
					});
				}
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
			
			async getDocumentInfo(){
				var data = {};
				data.id = this.documentId;
				var res = await this.$http.requestApi('GET', '/document/getDocumentInfo',data);
				this.list = res.data.list;
				this.assistant = res.data.title;
				uni.setNavigationBarTitle({
					title:'我的文档 - '+this.assistant
				})
					
			},
		
			
			
			editList(){
				var postData = {};
				postData.id = this.documentId;
				postData.data = this.editInfo;
				postData.title = this.assistant;
				postData.showLoading = 0;
				this.$http.requestApi('POST', '/document/update', postData).then(res=>{
					
					if(res.code != 0){
						uni.showToast({
							title: res.message ? res.message : '编辑文档失败，请稍后重试',
							icon: 'none',
							duration:3000
						});
						return;
					}
					
					this.edit = 0;
					this.list = this.editInfo;
					uni.showToast({
						title: '保存成功',
						icon: 'none',
						duration:1000
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
