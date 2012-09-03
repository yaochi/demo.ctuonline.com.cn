<?php
/*
 * 专区描述配置文件信息
 * @author songsp
 * @since 2010-11-28
 * @version v1.0
 */

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['info']['subs']['forumremark'] = array(
	'name' => '专区描述',
	'script' => array(
		$plugin_id."_remark" =>array(name=>"专区描述信息",
										  style=> array(
										  array(name=>"默认", key=>"default"),
										 
										  )),


		),
);


?>
