<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs']['grouppic'] = array (
	'name' => '相册图片',
	'script' => array (
		$plugin_id."_grouppic"=>array('name' => "相册图片",
			'style' => array(array('name'=>"相册图片", 'key'=>"selfstyle"),
				array('name'=>"相册图片+图片描述", 'key'=>"pic_title"),
				array('name'=>"图片幻灯", 'key'=>"selfstyle2"),
			),
		),
	),
);
?>
