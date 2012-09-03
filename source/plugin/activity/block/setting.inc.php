<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['others']['subs']['activity_list'] = array(
					"name" => "活动",
					"script" => array(
						$plugin_id."_activitylist"=>array(name => "最新活动",
                        style=> array(
                            array(name=>"活动标题", key=>"general"), array(name=>"焦点模式", key=>"activityfocus"), array(name=>"热议", key=>"hot"),
                            array(name=>"活动图片 + 标题", key=>"bigpic_title"),
                            array(name=>"独立样式", key=>"selfstyle")
                            )
                        ),
					)
);
?>
