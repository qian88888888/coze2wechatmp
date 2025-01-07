<template>
	<view>
		
		
		
		<view style="background-color: #f5f5f5;padding: 10rpx 0 10rpx 0;display: flex;align-items: center;justify-content: left;">
		<view style="margin-left: 10rpx;width: 20%;display: flex;align-items: center;justify-content: left;">
			<text @click="clickLevel()" style="font-weight: bold;margin-right: 5rpx;">{{levelText}}</text>
			<u-icon name="arrow-down-fill" size="8" ></u-icon>
		</view>
		
		<u-picker keyName="label" @cancel="cancelLevel()" @confirm="confirmLevel()" :show="showLevel" :columns="level"></u-picker>
		
		
		<view style="width: 100%;">
			<u-search :disabled="true" @click="search" bgColor="#fff" margin="0 5rpx"  height="35" placeholder="输入关键字搜索" :showAction="false" shape="round"></u-search>
		</view>
		
		</view>
		
		
		<view v-if="swiperList.length != 0">
			<u-swiper  @click="toSwiper()" indicator indicatorMode="line" :list="swiperList" keyName="img" showTitle radius="10" circular></u-swiper>
			<view style="height: 10rpx;background-color: #f5f5f5;"></view>
		</view>
		
		<u-sticky>
			<view style="margin-left: 20rpx;,height: 80rpx;background-color: #f5f5f5;">
				
				<u-tabs  v-if="cate.length != 0" :list="cate" :current="cateIndex"  @change="tabsChange" lineWidth="30" lineColor="#1acc89" :activeStyle="{color: '#303133',fontWeight: 'bolder',transform: 'scale(1.15)'}" :inactiveStyle="{color: '#606266',fontWeight: 'bold',transform: 'scale(1)'}" itemStyle="padding-left: 7px; padding-right: 7px; height: 34px;">
					<view slot="right" style="padding-left: 4px;" @tap="assistantList">
						<u-icon name="list" size="21" bold ></u-icon>
					</view>
				</u-tabs>
			</view>
		</u-sticky>

		<view class="container">

			<view class="cateList">
				 <u-transition :show="showTrans"  :mode="mode" duration="200"> 
				 
				 <view style="background-color: red;"></view>
				 
					 <u-empty icon="" iconColor="#e8e8e8" iconSize="130" marginTop="30" :show="empty" mode="data" :text="emptyText">
						 
					 </u-empty>
					 
					 <view v-if="cateInfo.length > 0" class="list">
					 
						<view class="leftList">
						<block v-for="(row, index) in leftList" :key="index">
							<view class="column" @click="info(row)">
								<view>
									<view class="top">
										<view class="right">
											<image style="width: 80rpx;height: 80rpx;" :src="row.icon"></image>
										</view>
										<view class="title">{{row.name}}</view>
										
									</view>
									<view class="left">
										<view class="desc">{{row.desc}}</view>
									</view>
								</view>
								
							</view>
							<view style="margin-top: 10px;"></view>
						</block>
						</view>
						
						<view class="rightList">
						<block v-for="(row, index) in rightList" :key="index">
							<view class="column" @click="info(row)">
								<view>
									<view class="top">
										<view class="right">
											<image style="width: 80rpx;height: 80rpx;" :src="row.icon"></image>
										</view>
										<view class="title">{{row.name}}</view>
										
									</view>
									<view class="left">
										<view class="desc">{{row.desc}}</view>
									</view>
								</view>
							</view>
							<view style="margin-top: 10px;"></view>
						</block>
						</view>
						
					 </view>
					 

					 <view v-if="hotCateAssistant.length > 0" v-for="(rowa, index) in hotCateAssistant">
						 <view class="my-item-top" >
							<view class="item-title">{{rowa.name}}</view>
							<view class="item-top-ret" @click="cateMore(rowa.id,index)">
								<text>查看更多</text>
								<image :src="cdnUrl+'/more.png'"></image>
							</view>	
						 </view>
						<view class="list" style="column-count: 2;column-gap: 40rpx;margin: 0 20rpx;">
							
							<view  style="height: auto;-webkit-column-break-inside: avoid;page-break-inside:avoid" v-for="(rowInfo, index1) in rowa.data" :key="index1">
								<view  class="column_icon">
									<image style="width: 80rpx;height: 80rpx;" :src="rowInfo.icon"></image>
								</view>
								<view  class="column" @click="info(rowInfo)">
								
									
									<view  class="top">
										
										<view class="title">{{rowInfo.name}}</view>
										
									</view>
									<view class="left">
										<view class="desc">{{rowInfo.desc}}</view>
									</view>
								
								</view>
								
								
							</view>
							
							
							
							
						
							
							
						</view>
						
						<view style="margin-top: 20px;"></view>
					</view>
				</u-transition>
			</view>
			

		</view>
		
	</view>
	
</template>

<script>

	export default {
		components: {
		},
		data() {
			return {
				cate:[],
				cateId:0,
				cateInfo: [],
				swiperList: [],
				showTrans:true,
				mode:'',
				cateIndex:0,
				hotCateAssistant:[],
				cdnUrl:this.$config.cdnUrl,
				empty : false,
				emptyText : '暂无数据 请查看其他分类',
				leftHeight:0,
				rightHeight:0,
				leftList:[],
				rightList:[],
				showLevel:false,
				level:[],
				levelText:'选择',
				levelId:0
			
				
			};
		},
		async onLoad(options) {
			await this.$onLaunched;
			
			uni.setNavigationBarTitle({
				title:this.$channelInfo.name
			})
			
		
			this.autoLogin();
			
		
			
			this.init();
			
		},
		
		
		methods: {
			async init(){
				this.empty = false;
				this.cateInfo = [];
				this.hotCateAssistant = [];
				this.cateIndex = 0;
				
				await this.getLevel();
				if(this.showLevel){
					return;
				}
				await this.getBanner();
				await this.getCate();
				await this.getLikeAssistantList();
				
				
				if(this.cateInfo.length == 0){
					await this.getHotCateAssistant();
					this.cateIndex = 1;
					if(this.hotCateAssistant.length == 0){
						this.empty = true;
					}
				}
			},
			clickLevel(){
				this.showLevel = true;
			},
			confirmLevel(e){
				uni.setStorageSync('level',this.level[0][e.indexs[0]]['id']);
				this.levelText = this.level[0][e.indexs[0]]['short_name']
				this.showLevel = false;
				this.levelId = this.level[0][e.indexs[0]]['id'];
				this.init();
				
			},
			cancelLevel(){
				if(!this.levelId){
					uni.showToast({
						title: '请选择教育阶段',
						icon: 'none'
					})
					return;
				}
				this.showLevel = false;
			},
			async getLevel(){
				
				var res = await this.$http.requestApi('GET', '/cate/levelList');
				this.level = [
					res.data
				];
				
				this.levelId = uni.getStorageSync('level')
				
				if(!this.levelId){
					uni.setStorageSync('level',res.data[0]['id']);
					this.levelId = res.data[0]['id'];
					this.levelText = res.data[0]['short_name'];
					this.showLevel = false;
				}else{
					let level = this.level[0].find(item => item.id === this.levelId);
					if(level==undefined){
						this.levelId = 0;
						this.showLevel = true;
						return;
					}
					this.levelText = level.short_name;
				}
			},
			async getCate(){
				var data = {};
				data.level_id = this.levelId;
				var res = await this.$http.requestApi('GET', '/cate/cateList',data);
				var cate = res.data;
				if(!cate.length){
					console.error('助手分类未设置，请前往后台设置');
					return;
				}
				
				this.cate = cate;
					
			},
			async getAssistantList(cateId){
				var data = {};
				data.cate_id = cateId;
				data.level_id = this.levelId;
				data.showLoading = false;
				var res = await this.$http.requestApi('GET', '/cate/assistantList',data);
				this.cateInfo = res.data;
				this.randerData(res.data);
			},
			
			async getHotCateAssistant(){
				var data = {};
				data.showLoading = false;
				data.level_id = this.levelId;
				var res = await this.$http.requestApi('GET', '/cate/hotCateAssistant',data);
				this.hotCateAssistant = res.data;
				this.randerData(res.data);
			},
			
			async getLikeAssistantList(){
				var data = {};
				data.showLoading = false;
				var res = await this.$http.requestApi('GET', '/cate/likeCateAssistant',data);

				this.cateInfo = res.data;
				this.randerData(res.data);
			},
		
			
			async getBanner(){
				var res = await this.$http.requestApi('GET', '/index/getBanner');
				this.swiperList = res.data.swiper ? res.data.swiper : [];
			},
			
			async getMsg(){
				var res = await this.$http.requestApi('GET', '/index/getMsg',{position:1});
				this.msg = res.data;
			},
			
		
			
			async tabsChange(data) {
				this.mode = 'slide-left';
				if(data.index >= this.cateIndex){
					this.mode = 'slide-right';
				}
				
				this.cateIndex = data.index;
				this.cateInfo = [];
				this.hotCateAssistant = [];
				this.empty = false;
				var type = data.type;
				var cateId = data.id;
				
				//type 1分类  2收藏  3热门
				this.showTrans = false;

					if(type == 1){
						await this.getAssistantList(cateId);
						if(this.cateInfo.length == 0){
							this.empty = true;
							this.emptyText = '暂无数据 请查看其他分类';
						}
					}
					
					if(type == 2){
						await this.getLikeAssistantList();
						if(this.cateInfo.length == 0){
							this.empty = true;
							this.emptyText = "暂无收藏 你可以在创作页面点击 ☆ 收藏";
						}
					}
					
					if(type == 3){
						await this.getHotCateAssistant();
						if(this.hotCateAssistant.length == 0){
							this.empty = true;
							this.emptyText = '暂无数据 请查看其他分类';
						}  
					}
					
					var that = this;
					setTimeout(()=>{
						that.showTrans = true;
					},100)
					

			},
			cateMore(cateId,index){
				this.cateIndex = index + 2;
				var data = {
					id : cateId,
					type : 1,
					index : this.cateIndex
					
				};
				this.tabsChange(data);
			},
			search(){
				uni.navigateTo({
					url: '/pages/search/index'
				});
			},
			
			assistantList(){
				uni.navigateTo({
					url: '/pages/assistant/index'+'?level='+this.levelId
				});
			},
			info(data){
				
				if(data.appid != ''){
					uni.navigateToMiniProgram({
						appId: data.appid,
						path: data.apppath,
						fail() {}
					})
				}else{
					uni.reLaunch({
						url: '/pages/create/index?assistant_id='+data.id,
					});
				}
				
			},
			async  randerData(data) {
				
				
				this.leftList = [];
				this.rightList = [];
				this.leftHeight = 0;
				this.rightHeight = 0;
				
				
				
				
				let list = data;
				let leftList  = this.leftList;
				let rightList = this.rightList;
				
				for (var i=0;i<list.length;i++) {
					
					let leftHeight = this.leftHeight;
					let rightHeight = this.rightHeight;
					
					
					leftHeight <= rightHeight ? leftList.push(list[i]) : rightList.push(list[i]);
					await this.getBoxHeight(leftList, rightList);
				}
				
				
			},
			
			getBoxHeight(list_left, list_right) { //获取左右两边高度
			    return new Promise((resolve, reject) => {
				  this.leftList = list_left;
				  this.rightList = list_right;
				  
				  this.$nextTick(function(){
					var query = uni.createSelectorQuery().in(this);
				  	query.select('.leftList').boundingClientRect();
				  	query.select('.rightList').boundingClientRect();
				  	query.exec((res) => {
						if (res[0] ){
						  this.leftHeight = res[0].height; //获取左边列表的高度
						  this.rightHeight = res[1].height; //获取右边列表的高度
						}
						resolve(res);
				  	});
				  })
			    })
			},
			

			toSwiper(index) {
				var swiper = this.swiperList[index];
				if(swiper.jump_type == 1){
					uni.navigateTo({
						url: swiper.path,
						fail: (res) => {
							uni.switchTab({  
								url: swiper.path,
							})  
						} 
					});
				}else{
					uni.navigateToMiniProgram({
						appId: swiper.appid,
						path: swiper.path,
						fail() {}
					})
				}
			},
		
			
			
			autoLogin(){
				let token = uni.getStorageSync('token');
				if (token == '' || token == undefined) {
					var that = this;
					uni.login({
						provider: "weixin",
						success: (res) => {
							var postData = {};
							postData.code = res.code;
							that.$http.requestApi('POST', '/user/wxMiniLogin', postData).then(result => {
								const resData = result.data;
								const resCode = result.code;
								if (resCode == 0) {
									uni.setStorageSync('token', resData.accessToken);
									uni.setStorageSync('userInfo', resData.userInfo);
								}
							})
						},
					});
				}
			}
		}
	};
</script>
<style lang="scss">
	page {
		padding-bottom: 30rpx;
		
	}

	.container {
		width: 100%;
		position: relative;
		overflow: scroll;

		margin: 10rpx auto;
	}
	
	.leftList{
		width: 45%;
		float: left;
		margin-left: 20rpx;
	}
	.rightList{
		width: 45%;
		float: right;
		margin-right: 20rpx;
	}

	.cateList {
		margin: 30rpx auto;

		.text {
			width: 100%;
			height: 80rpx;
			font-size: 34rpx;
			font-weight: 600;
			margin-top: -10rpx;
		}
		
		.list {
			margin-top: 10rpx;
			
			.column_icon {
				margin-left: 50rpx;
				margin-bottom: -20rpx;
				width: 60rpx;
				height: 60rpx;
				image {
					width: 60rpx;
					height: 60rpx;
				}
			}
			
			.column {
				padding: 5%;
				background-color: #fff;
				border-radius: 14rpx;
				margin-bottom: 40rpx;
				
				

				.top {
					width: 100%;
					margin-bottom: 5rpx;
					line-height: 60rpx;
					padding-top: 20rpx;
					

					.title {
						font-size: 30rpx;
						font-weight:bold;
						margin-left: 20rpx;
						margin-top: 30rpx;
					}
					
					.right {
						margin-left: 40rpx;
						margin-top: -70rpx;
						width: 60rpx;
						height: 60rpx;
						image {
							width: 60rpx;
							height: 60rpx;
						}
					}
					
				
					
				}

				.left {
					width: 100%;
					display: flex;
					flex-wrap: wrap;
					align-content: space-between;
					margin-bottom: 30rpx;
					.desc {
						margin-top: 5rpx;
						margin-left: 20rpx;
						width: 100%;
						font-size: 25rpx;
						color: #868888;
						white-space:pre-wrap;
					}
				}

				
			}
		}
	}
	.my-item-top{
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 30rpx;
		margin-top:-50rpx;
		.item-title{
			color: #1A1A1A;
			font-size: 32rpx;
			font-weight: bold;
		}
		.item-top-ret{
			display: flex;
			align-items: center;
			text{
				color: #828899;
				font-size: 24rpx;
			}
			image{
				width: 24rpx;
				height: 24rpx;
			}
		}
	}
</style>