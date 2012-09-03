<?php
/*
 * 组件菜单配置文件信息
 * @author songsp
 * @since 2010-11-28
 * @version v1.0
 */

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['info']['subs']['pluginmenu'] = array(
	'name' => '组件菜单',
	'script' => array(
		$plugin_id."_pluginmenu" =>array(name=>"组件菜单",
										  style=> array(
										  array(name=>"固定式组件菜单 + 加入/退出/管理专区 按钮", key=>"style1"),
										  array(name=>"固定式组件菜单", key=>"style2"),
										  array(name=>"自适应式组件菜单", key=>"style3"),
										  array(name=>"加入/退出/管理专区 按钮", key=>"style4"),
										  array(name=>"加入/退出/管理专区 文字链接", key=>"style5"),
										  array(name=>"加入/退出/管理专区 按钮 信息卡片样式", key=>"style6"),

										  )),


		),
);


?>
