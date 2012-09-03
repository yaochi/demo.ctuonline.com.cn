<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["lecturerecord"] = array(
		"name" => "授课记录",
		"script" => array(
			$plugin_id."_lecturerecord"=>array(name => "创建授课记录",
            style=>array(array(name=>"独立样式", key=>"selfstyle"))),
            $plugin_id."_lecturerecord2"=>array(name => "授课积分排行榜",
            style=>array(array(name=>"独立样式", key=>"topcredit")))
		)
);
?>
