<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["notice"] = array(
		"name" => "通知公告",
		"script" => array(
			$plugin_id."_notice"=>array(name => "最新通知公告",
	            style=>array(
	            	array(name=>"通知公告标题", key=>"general"),
	            	array(name=>"焦点模式", key=>"focus"),
	            	array(name=>"独立样式", key=>"selfstyle"),  
	            	array(name=>"全文滚动",key=>"full_text_scroll"),
	            	array(name=>"[标题百搭] (分类) + 标题 + 创建时间",key=>"cate_title_posttime"),
	            	array(name=>"[标题百搭] (分类) + 标题 + 摘要",key=>"cate_title_author_desc"),
	            	
	            )
	        ),
	        $plugin_id."_notice2"=>array(name => "指定通知公告",
	            style=>array(
	            	array(name=>"通知公告标题", key=>"general"),
	            	array(name=>"全文滚动",key=>"full_text_scroll"),
	            	array(name=>"独立样式", key=>"selfstyle"),  
	            )
	        ),
		)
);
?>
