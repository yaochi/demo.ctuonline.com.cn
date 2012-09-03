<?php
/* Function: 直播数据源配置
 * Com.:
 * Author: wuhan
 * Date: 2010-7-19
 */
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs']['grouplive'] = array (
	'name' => '直播',
	'fields' => array (
		'url' => array (
			'name' => '直播链接',
			'formtype' => 'text',
			'datatype' => 'string'
		),
		'title' => array (
			'name' => '直播名称',
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
		'dateline' => array (
			'name' => '创建日期',
			'formtype' => 'date',
			'datatype' => 'date'
		),
		'starttime' => array (
			'name' => '开始时间',
			'formtype' => 'date',
			'datatype' => 'date'
		),
		'endtime' => array (
			'name' => '结束时间',
			'formtype' => 'date',
			'datatype' => 'date'
		),
	),
	'script' => array (
		$plugin_id."_grouplive"=>array('name' => "最新直播",
			'style' => array(array('name' =>"直播标题 + 主讲人 + 浏览数 + 回看按钮", 'key' => "livelist"), array('name'=>"直播时间 + 回看按钮", 'key'=>"livestyle2"))),
		$plugin_id."_grouplive2"=>array('name' => "指定直播",
			'style' => array(array('name' =>"直播标题 + 主讲人 + 浏览数 + 回看按钮", 'key' => "livelist"), array('name'=>"直播时间 + 回看按钮", 'key'=>"livestyle2"))),
	),
);
?>
