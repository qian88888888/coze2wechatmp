<script>
	export default {
		
		onLaunch: function() {
			var that = this;
			
			this.$http.requestApi('POST', '/index/getChannelInfo',{showLoading:0}).then(res => {
				that.$channelInfo.name = res.data.name;
				that.$channelInfo.desc = res.data.desc;
				that.$channelInfo.stream = res.data.stream;
				that.$channelInfo.showStream = res.data.showStream;
				that.$isResolve()
			});
			
			
		},
		onShow: function() {
			
		},
		onHide: function() {
			
		}
		
			
	}
	
	
</script>

<style lang="scss">
	@import "uview-ui/index.scss";
	page {
		font-size: 28rpx;
		background-color: #f5f5f5;
		left: 0;
		width: 100%;
		height: 100%;
		top:0
	}
</style>
