<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['info']["subs"]["grouptop"] = array(
        "name" => "专区置顶" ,
        "script" => array(
			$plugin_id . "_grouptop" => array(name => "专区组件置顶条目",
			style=>array(
				array(name=>"标准样式",key=>"standard"),
	            )
	        )
		),
);
?>