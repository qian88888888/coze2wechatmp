<template>
	<view>
		<view class="search-box">
			<u-search maxlength="8" v-model="searchValue" @search="search" @change="change" @clear="clear" color="#1acc89" borderColor="#1acc89" :focus="true" bgColor="#fff" margin="0 15rpx"  height="35" placeholder="输入关键字搜索" :showAction="false" shape="round"></u-search>
		
		</view>
		
		
		
		
		<view v-if="searchList.length == 0 && historyList.length>0" class="content">
			<view class="title">
				<text>历史搜索</text>
				<view class="clear-box">
		
					<uni-icons @click="deleteHistory()" type="trash" size="18"></uni-icons>
				</view>
			</view>
			<view class="child">
				<view class="item" v-for="(item, index) in historyList" :key="index">
					<view>
					<text @click="searchValue=item">{{item}}</text>
					<uni-icons @click="deleteHistory(item)" style="margin-left: 15rpx;" type="closeempty" size="10"></uni-icons>
					</view>
					
				</view>
			</view>
		</view>

		<view v-if="showHot && hotList.length > 0" class="content">
			<view class="title">
				<text>热门搜索</text>
			</view>
			<view class="list">
				<view @click="assistantInfo(item)" class="item" v-for="(item,index) in hotList" :key="index">
					<view  class="cover-box">
						<view class="label" :class="'label-'+ index">
							<text>{{index+1}}</text>
						</view>
						<image  :src="item.icon" mode=""></image>
						
					</view>
					<view class="item-info-box">
						<view class="search_title">{{item.name}}</view>
						<view class="search_desc">
							<text style="color:#B1B1B1">{{item.desc}}</text>
						</view>
						
					</view>
					<view class="hot">
						<uni-icons :type="index > 2 ? 'fire' : 'fire-filled'" size="15" :color="index>2 ? '#B1B1B1' : '#E05345'"></uni-icons>
						<text :style="index > 2 ? 'color:#B1B1B1':''">{{item.hot}}</text>
					</view>
				</view>
				
			</view>
		</view>
		
		<view v-if="searchList.length > 0" class="content">
			<view class="title">
				<text>搜索结果</text>
			</view>
			<view class="list">
				<view @click="assistantInfo(item)" class="item" v-for="(item,index) in searchList" :key="index">
					
					<view class="cover-box">
						<view class="label" :class="'label-'+ (index>4 ? 4 : index)">
							<text>{{index+1}}</text>
						</view>
						<image :src="item.icon" mode=""></image>
					</view>
					<view class="item-info-box">
						
						<view class="search_title" v-html="changeTextColor(item.name)"></view>
						<view class="search_desc" style="color: #B1B1B1;" v-html="changeTextColor(item.desc)"></view>
					</view>
					<view v-if="item.hot > 0" class="hot">
						<uni-icons type="fire" size="15" color="#B1B1B1"></uni-icons>
						<text style="color:#B1B1B1">{{item.hot}}</text>
					</view>
				</view>
			</view>
		</view>
		
		<u-empty iconColor="#e8e8e8" iconSize="180" marginTop="50" :show="empty" mode="search" text="暂无搜索结果~"></u-empty>
		

	</view>
</template>

<script>
	export default {
		data() {
			return {
				historyList: [],
				hotList: [],
				searchValue:'',
				showHot:true,
				searchList:[],
				empty:false
				
			}
		},
		onLoad() {
			var searchHistoryList = uni.getStorageSync('searchHistoryList');
			this.historyList = searchHistoryList ? searchHistoryList : [];
			this.$http.requestApi('GET', '/assistants/hotSearch',{}).then(res=>{
				this.hotList = res.data;
			});
		},
		methods: {
			
			change(){
				this.empty = false;
				this.searchList = [];
				this.searchValue = this.searchValue.replace(/\s/g, "");
				this.showHot = this.searchValue ? false : true;
				if(this.searchValue.length == 0){
					return;
				}
				var data = {
					title : this.searchValue
				};
				this.$http.requestApi('GET', '/assistants/search',data).then(res=>{
					this.searchList = res.data;
					if(res.data.length == 0){
						this.empty = true;
					}
				});
			},
			search(){
				this.searchValue = this.searchValue.replace(/\s/g, "");
				if(this.searchValue.length == 0){
					return;
				}
				var value = this.searchValue;
				this.historyList = this.historyList.filter(item => item !== value);
				this.historyList.unshift(value);
				this.historyList = this.historyList.slice(0, 8);
				uni.setStorageSync('searchHistoryList',this.historyList)
			},
			deleteHistory(text){
				var that = this;
				uni.showModal({
					title: '温馨提示',
					content: '历史搜索记录将被清除',
					confirmText: '确定',
					success: function(res) {
						if (res.confirm) {
							if(text){
								that.historyList = that.historyList.filter(item => item !== text);
								uni.setStorageSync('searchHistoryList',that.historyList)
							}else{
								that.historyList = [];
								uni.setStorageSync('searchHistoryList',[])
							}
						} else if (res.cancel) {
							
						}
					}
				});
				
			},
			clear(){
				this.searchValue = '';
			},
			changeTextColor(text){
				let res = new RegExp("("+this.searchValue+")",'g');
				text = text.replace(res,"<font style='color:#1acc89'>" + this.searchValue+"</font>");
				return text;
			},
			assistantInfo(item){
				if(item.appid != ''){
					uni.navigateToMiniProgram({
						appId: item.appid,
						path: item.apppath,
						fail() {}
					})
				}else{
					uni.reLaunch({
						url: '/pages/create/index?assistant_id='+item.id,
					});
				}
			}
		},
		
	}
</script>

<style>
	


	.input-box {
		display: flex;
		align-items: center;
		background: #fff;
		height: 72rpx;
		border-radius: 72rpx;
		padding: 0 30rpx;
		/*  #ifndef MP-WEIXIN  */
		width: 100%;
		/* #endif */
		margin: 0 24rpx;
	}

	.input-box input {
		font-size: 30rpx;
		width: 100%;
	}

	.input-box .placeholder {
		color: #c6c6c6;
	}


	.input-box image {
		width: 48rpx;
		height: 48rpx;
	}

	.content {
		margin: 24rpx;
	}

	.title {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin: 24rpx 0;
	}

	.title>text {
		font-size: 30rpx;
		font-weight: bold;
	}

	.clear-box {
		color: #868686;
		font-size: 28rpx;
		display: flex;
		align-items: center;
	}

	.clear-box image {
		width: 32rpx;
		height: 32rpx;
		margin-right: 2rpx;
	}

	.child {
		display: flex;
		flex-wrap: wrap;
	}

	.child .item {
		background: #fff;
		font-size: 28rpx;
		height: 62rpx;
		border-radius: 54rpx;
		padding: 0 28rpx;
		color: #525252;
		margin: 0 24rpx 20rpx 0;
		display: flex;
		align-items: center;
	}

	.child .item-a {
		padding: 0 28rpx 0 14rpx;
	}

	.child .item image {
		width: 40rpx;
		height: 40rpx;
		border-radius: 40rpx;
		margin-right: 10rpx;
	}

	.list {
		
	}

	.list .item {
		background: #fff;
		border-radius: 24rpx;
		margin-top: 15rpx;
		display: flex;
		padding: 15rpx 0;
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
	}

	.item-info-box .search_desc {
		-webkit-text-stroke-width: 0.3px;
		display: flex;
		align-items: center;
		margin-top: 10rpx;
		font-size: 25rpx;
		color: #868888;
	}

	.item-info-box>view image {
		width: 30rpx;
		height: 30rpx;
		margin-right: 4rpx;
	}

	.cover-box {
		position: relative;
		margin: auto 0;
	}

	.cover-box .label {
		position: absolute;
		top: 0;
		left: 0;
		width: 44rpx;
		height: 44rpx;
		z-index: 1;
		border-top-left-radius: 14rpx;
		border-bottom-right-radius: 14rpx;
		text-align: center;
		line-height: 44rpx;
		color: #fff;
		font-size: 28rpx;
		-webkit-text-stroke-width: 0.1px;
	}

	.label-0 {
		background: #E05345;
	}

	.label-1 {
		background: #EB9941;
	}

	.label-2 {
		background: #EDC744;
	}

	.label-3 {
		background: #b0adaa;
	}
	
	.label-4 {
		background: #e2dfdc;
	}

	.cover-box image {
		width: 70rpx;
		height: 70rpx;
		flex-shrink: 0;
		border-radius: 14rpx;
		margin: 0 30rpx 0 60rpx;
	}
	.hot{
		position: relative;
		margin: auto 0;
		
	}
</style>