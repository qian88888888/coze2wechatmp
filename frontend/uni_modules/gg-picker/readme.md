# gg-picker
+ 简单支持单列，多列的级联选择器

## 1.使用示例

### 1.组件引用

```
<gg-picker ref="ggPicker" rangeKey="name" :list="list" :deepth="2" title="简单多列级联选择器"></gg-picker>

```
### 2.通过点击事件打开

```
methods:{
	showPicker(){
	 this.$refs.ggPicker.open();
	}
}
```
## 2.属性说明
字段|类型|必填|默认值|描述
--|:--:|:--:|:--:|:--:
title|String|否|空|左上显示标题
rangeKey|String|否|空|传入对象数组,必须填此项,显示字段对应的key值
list|Array<Object\|String>|是|[]|需要展示的数据数组,支持普通的字符串数组,对象数组
deepth|Number|否|1|展示的列数,默认只展示一列
childrenKey|String|否|children|子项对应的key值,默认为children

## 3.事件说明
事件|描述
--|--
confirm|选择后确定 值为每列对应下标数组
