<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['info']["subs"]["groupfeed"] = array(
        "name" => "专区动态" ,
        "script" => array(
			$plugin_id . "_groupfeed" => array(name => "最新动态",
	            style=>array(
	            	array(name=>"标准样式",key=>"standard"),
					array(name=>"紧凑样式 有简介",key=>"slim1"),
					array(name=>"紧凑样式 无简介",key=>"slim2"),
					array(name=>"滚动样式",key=>"ticker"),
					array(name=>"快速发布 + 紧凑样式 有简介",key=>"slim3"),
	            )
	        )
		),
);
?>
