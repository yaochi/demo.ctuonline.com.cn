<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs'][$plugin_id] = array (
	'name' => "课程分享",
	'script' => array (
		$plugin_id."_sharesource"=>array(name => "课程分享",
		'style' => array(array('name'=>"分享榜", 'key'=>"rank"),
						 array('name'=>"分享列表", 'key'=>"list"),
			),
		),
	),
);
?>
