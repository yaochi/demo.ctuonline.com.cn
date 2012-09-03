<?php
/*
 * 组件菜单配置文件信息
 * @author songsp
 * @since 2010-11-28
 * @version v1.0
 */

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['info']['subs']['foruminfo'] = array(
	'name' => '专区信息',
	'script' => array(
		$plugin_id."_info" =>array(name=>"专区信息",
										  style=> array(
										  array(name=>"横向放置", key=>"style1"),
										  array(name=>"竖向放置", key=>"style2"),
										  array(name=>"信息卡片", key=>"style3"),
										  )),


		),
);


?>
