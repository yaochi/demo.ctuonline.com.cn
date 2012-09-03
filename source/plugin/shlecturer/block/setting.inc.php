<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["shlecturer"] = array(
		"name" => "上海讲师",
		"script" => array(
            $plugin_id."_shlecturer"=>array(name => "指定讲师",
            style=>array(array(name=>"独立样式", key=>"selfstyle"))),
		)
);
?>
