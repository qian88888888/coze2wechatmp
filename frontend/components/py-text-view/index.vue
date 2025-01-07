<template>
  <view @click="onTab" class="py-item-box">
    <block v-if="isChinese(data)">
      <view class="item" v-for="(item, index) in getDataList()" :key="index">
        <view style="font-size: 28rpx; height: 30rpx">
          {{ item.pin || '' }}
        </view>
        <view class="">
          {{ item.text || '' }}
        </view>
      </view>
    </block>
    <view v-else>
      {{ data }}
    </view>
  </view>
</template>

<script lang="ts">
import Vue from 'vue';
import { pinyin } from 'pinyin-pro';
export default Vue.extend({
  props: {
    data: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      chinese: true,
      textIndex: 0
    };
  },
  methods: {
    isChinese(temp: any) {
      var re = new RegExp('[\\u4E00-\\u9FFF]+', 'g');
      return re.test(temp);
    },
    getDataList() {
      var a = [];
      var noZhChar = '';

      for (var char of this.data) {
        var isCh = this.isChinese(char);
        if (isCh) {
          if (noZhChar) {
            a.push({ text: noZhChar });
            noZhChar = '';
          }
          a.push({ text: char, pin: pinyin(char) });
        } else {
          noZhChar = noZhChar + char;
        }
      }
      if (noZhChar) {
        a.push({ text: noZhChar });
        noZhChar = '';
      }
      return a;
    },

    onTab() {
      this.$emit('click');
    }
  }
});
</script>

<style lang="scss" scoped>
.py-item-box {
  display: flex;
  flex-wrap: wrap;
  align-items: center;

  .item {
    padding-inline-end: 16rpx;
    padding-bottom: 6rpx;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
}
</style>
