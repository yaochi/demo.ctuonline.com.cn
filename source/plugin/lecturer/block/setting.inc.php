<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["lecturer"] = array(
		"name" => "讲师",
		"script" => array(
//			$plugin_id."_lecturer"=>array(name => "讲师申请推荐",
//            style=>array(array(name=>"申请推荐", key=>"button"))),
            $plugin_id."_lecturer2"=>array(name => "指定讲师",
            style=>array(array(name=>"独立样式", key=>"selfstyle"))),
		)
);
?>
