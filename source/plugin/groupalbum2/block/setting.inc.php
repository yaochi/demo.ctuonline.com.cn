<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs']['groupalbum2'] = array (
	'name' => '相册',
	'fields' => array (
		'url' => array (
			'name' => '相册链接',
			'formtype' => 'text',
			'datatype' => 'string'
		),
		'title' => array (
			'name' => '相册名称',
			'formtype' => 'title',
			'datatype' => 'title'
		),
		'pic' => array (
			'name' => '相册封面',
			'formtype' => 'pic',
			'datatype' => 'pic'
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
		'updatetime' => array (
			'name' => '更新日期',
			'formtype' => 'date',
			'datatype' => 'date'
		),
		'picnum' => array (
			'name' => '照片数',
			'formtype' => 'text',
			'datatype' => 'int'
		),
	),
	'script' => array (
		$plugin_id."_groupalbum"=>array('name' => "最新相册",
			'style' => array(array('name'=>"相册封面", 'key'=>"albumlist"), array('name'=>"相册封面 + 相册名称", 'key'=>"albumstyle2"))),
	),
);
?>
