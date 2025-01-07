<template>
	<view>
		<view class="title" style="margin-top: 20rpx;">
			
		</view>
		<view class="list">
			<view @click="info(item)"  class="item" v-for="(item,index) in documents" :key="index">
				<view :class="'cover-box label-'+ index%10">
					
					<text>{{item.title[0]}}</text>
					
				</view>
				<view class="item-info-box" >
					<view class="search_title">{{truncate(item.title,8)}}</view>
					<view class="search_desc">
						<text style="color:#B1B1B1">{{item.date}}  共{{item.nums}}字</text>
					</view>
					
				</view>
				<view  class="hot" @click.stop="deleteConfirm(item.id,index)">
					<u-icon name="trash" size="16" color="#B1B1B1"></u-icon>
				</view>
			</view>
			
			<u-modal confirmColor="#1acc89" @cancel="modalCancel()" @confirm="deleteSession(deleteSessionId,deleteSessionIndex)" :show="showModal" confirmText="确认" title="温馨提示"  :showCancelButton="true" :buttonReverse="true">
					<view class="slot-content">
						<text style="line-height: 2">确定要删除此文档吗？\n 文档内容将被删除，且无法撤销</text>
						
						
					</view>
			</u-modal>
			
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				documents:[],
				showModal:false,
				deleteSessionId:0,
				deleteSessionIndex:0
			}
		},
		onLoad() {
		
			
		},
		onShow(){
			this.$http.requestApi('GET', '/document/list',{}).then(res=>{
				if(res.code == 0){
					this.documents = res.data;
				}
				
			});
		},
		methods: {
			
			truncate(text, maxLength) {
			  if (text.length > maxLength) {
			    return text.substr(0, maxLength) + '...';
			  }
			  return text;
			},
			deleteConfirm(id,index){
				this.deleteSessionId = id;
				this.deleteSessionIndex = index;
				this.showModal = true;
			},
			modalCancel(){
				this.showModal = false;
			},
			async deleteSession(deleteSessionId,deleteSessionIndex){
				this.showModal = false;
				
				
				if(this.documents.length <= 0){
					return;
				}
				this.documents.splice(deleteSessionIndex, 1);
			
				
				var res = await this.$http.requestApi('POST', '/document/delete',{showLoading:0,id:deleteSessionId});
				if(res.code != 0){
					uni.showToast({
						title: res.message ? res.message : '未知错误',
						duration: 2000,
						icon: 'none'
					});
				}
				
				uni.showToast({
					title: '文档删除成功',
					duration: 2000,
					icon: 'none'
				});
			},
			
			info(item){
				uni.navigateTo({
					url: '/pages/document/info?id='+item.id
				});
				return;
			}
			
		}
	}
</script>

<style>
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
		width: 350rpx;
	
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
		width: 100rpx;
		height: 100rpx;
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
		
		position: absolute;
		right: 50rpx;
		
	}
	
	.title {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin: 100rpx 0 20rpx 50rpx;
	}
	
	.title>text {
		font-size: 30rpx;
		font-weight: bold;
	}
</style>
