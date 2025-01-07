<template>
	<view class="app-content">
		<view class="app-cate-item" v-for="(item,key) in assistants" :key="key">
			<view class="app-item-title">{{item.name}}</view>
			<u-grid :border="false" col="4">
				<block v-for="(listItem,listIndex) in item.data" :key="listIndex">
					<u-grid-item @click="assistantInfo(listItem)" >
						<u-icon :customStyle="{paddingTop:20+'rpx'}" :name="listItem.icon" size="90rpx"></u-icon>
						<text class="grid-text" v-if="listItem.id == assistant_id" style="font-weight:bolder;color: #1acc89;">{{listItem.name}}</text>
						<text class="grid-text" v-if="listItem.id != assistant_id">{{listItem.name}}</text>
					</u-grid-item>
				</block>
			</u-grid>
		</view>
	</view>
</template>

<script>

	export default {
		components: {
			
		},
		data() {
			return {
				assistants: [],
				
				assistant_id :0,
				levelId :8,
			}
		},
		onLoad(options) {
			
			if(options.assistant_id != undefined){
				this.assistant_id = options.assistant_id
			}
			if(options.level != undefined){
				this.levelId = options.level
			}
			
			this.getAssistants();
		},
		methods: {
			
			async getAssistants() {
				var res = await this.$http.requestApi('GET', '/cate/cateAssistant',{'levelId':this.levelId});
				this.assistants = res.data;
			},
			
			
			assistantInfo(data){
			
				if(data.appid != ''){
					uni.navigateToMiniProgram({
						appId: data.appid,
						path: data.apppath,
						fail() {}
					})
				}else{
					if(data.show_type == 3){
						data.show_type = this.type;
					}
					
					if(data.show_type == 1){
						uni.reLaunch({
							url: '/pages/create/index?assistant_id='+data.id,
						});
					}
					
					if(data.show_type == 2){
						uni.reLaunch({
							url: '/pages/chat/dialog?assistant_id='+data.id,
						});
					}
					
				}
			},
			
		}
	}
</script>

<style lang="scss">
	

	.app-content {
		width: 92%;
		margin: 20rpx auto;

		.app-cate-item {
			background-color: $uni-bg-color;
			border-radius: 14rpx;
			padding: 10rpx;
			margin-bottom: 20rpx;

			.app-item-title {
				padding: 20rpx;
				font-weight: bolder;
			}
		}
	}

	.grid-text {
		font-size: 12px;
		color: #909399;
		padding: 10rpx 0 20rpx 0rpx;
	}
</style>