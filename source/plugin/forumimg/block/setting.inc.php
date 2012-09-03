<?php
/*
 * 组件菜单配置文件信息
 * @author songsp
 * @since 2010-11-28
 * @version v1.0
 */

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['info']['subs']['forumimg'] = array(
	'name' => '专区图片',
	'script' => array(
		$plugin_id."_img" =>array(name=>"专区图片",
										  style=> array(
										  array(name=>"默认", key=>"default"),
										 
										  )),


		),
);


?>
