<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['module']["subs"]["resourcelist"] = array(
        "name" => "资源列表",
		"script" => array(
			$plugin_id."_resourcelist"=>array(name => "最新资源",
	            style=>array(
	            	array(name=>"资源标题", key=>"general"), 
	            	array(name=>"焦点模式", key=>"resfocus"),
	            	array(name=>"独立样式", key=>"selfstyle"),  
	            	array(name=>"类型 + 缩略图 + 标题",key=>"pic_title_cate"),
	            	array(name=>"[标题百搭] (分类) + 类型 + 标题 + 创建时间",key=>"cate_title_uploaddate"),
	            )
	        ),
	        $plugin_id."_resourcelist2"=>array(name => "指定资源",
	            style=>array(
	            	array(name=>"资源标题", key=>"general"),
	            	array(name=>"独立样式", key=>"selfstyle"),  
	            )
	        ),
		)
);
?>
