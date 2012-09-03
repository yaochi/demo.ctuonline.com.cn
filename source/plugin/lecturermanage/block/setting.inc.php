<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["lecturermanage"] = array(
		"name" => "讲师管理",
		"script" => array(
			$plugin_id."_lecturermanage"=>array(name => "讲师搜索框",
            style=>array(array(name=>"内部讲师", key=>"searchbox1"), array(name=>"外部讲师", key=>"searchbox2"))),
            $plugin_id."_lecturermanage2"=>array(name => "讲师申请推荐",
            style=>array(array(name=>"申请推荐", key=>"button"))),
		)
);
?>
