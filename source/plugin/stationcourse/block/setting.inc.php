<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs'][$plugin_id] = array (
	'name' => "岗位课程",
	'script' => array (
		$plugin_id."_station"=>array(name => "岗位课程",
		'style' => array(array('name'=>"标准样式", 'key'=>"standardstyle"),
			),
		),
	),
);
?>
