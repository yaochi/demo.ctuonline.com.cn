<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs']['extraresource'] = array (
	'name' => '外部资源',
	'script' => array (
		$plugin_id."_extraresource"=>array('name' => "最新外部资源",
			'style' => array(array('name'=>"资源列表", 'key'=>"extralist"))),
	),
);
?>
