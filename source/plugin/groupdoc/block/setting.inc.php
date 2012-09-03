<?php
/* Function: 文档数据源配置
 * Com.:
 * Author: wuhan
 * Date: 2010-7-19
 */
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs']['groupdoc'] = array (
	'name' => '文档',
	'fields' => array (
		'title' => array (
			'name' => '文档标题',
			'formtype' => 'title',
			'datatype' => 'title'
		),
		'uid' => array (
			'name' => '用户UID',
			'formtype' => 'text',
			'datatype' => 'int'
		),
		'username' => array (
			'name' => '用户名',
			'formtype' => 'text',
			'datatype' => 'string'
		),
		'uploadtime' => array (
			'name' => '上传日期',
			'formtype' => 'date',
			'datatype' => 'date'
		),
	),
	'script' => array (
		$plugin_id."_groupdoc" => array( 'name' => "最新文档",
			'style' => array(
				array('name' => "纯列表", 'key' => "general"), 
				array('name' => "独立样式", 'key' => "docstyle2"),
				array('name' => "焦点模式", 'key' => "docfocus"),
				array('name' => "缩略图 + 标题", 'key' => 'docstyle3'), 
				array( 'name' =>"[标题百搭] (分类) + 标题 + 作者", 'key' =>"doclist"), 
				array( 'name' =>"[标题百搭] (分类) + 标题 + 作者 + 摘要", 'key' =>"doclist2"), 
				array( 'name' =>"[标题百搭] (分类) + 标题 + 作者 + 摘要 + 头像", 'key' =>"doclist3"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 上传时间", 'key' =>"doclist4"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 查看数", 'key' =>"doclist5"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 回复数", 'key' =>"doclist6"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 转发数", 'key' =>"doclist7"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 收藏数", 'key' =>"doclist8"),
				array( 'name' =>"[标题百搭] (分类) + 标题 + 平均分", 'key' =>"doclist9"),
				
			)),
	),
);
?>
