<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['module']["subs"]["groupshare"] = array(
        "name" => "分享" ,
        "script" => array(
			$plugin_id . "_groupshare" => array(name => "最新分享",
	            style=>array(
	            	array(name=>"标准样式",key=>"standard"),
	            )
	        )
		),
);
?>
