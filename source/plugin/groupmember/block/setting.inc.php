<?php
/*
 * 专区成员插件配置文件信息
 * @author fumz
 * @since 2010-7-21 9:00:48
 * @version v1.0
 */
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['info']['subs'][$plugin_id] = array(
	'name' => '专区成员',
	'script' => array(
		$plugin_id."_groupmember" =>array(name=>"新加入 + 活跃成员",
										  style=> array(array(name=>"头像列表", key=>"memberlists_avatar"))),
		$plugin_id."_groupmembernew" =>array(name=>"新加入成员",
										  style=> array(array(name=>"头像列表", key=>"memberlist_avatar"),
										  				array(name=>"头像列表 紧凑样式", key=>"member_new_avatar"),
										  				array(name=>"用户名列表", key=>"memberlist_username")
										  				)),
		$plugin_id."_groupmemberactivity" =>array(name=>"活跃成员",
										  style=> array(array(name=>"头像列表", key=>"memberlist_avatar"),
										  				array(name=>"头像列表 紧凑样式", key=>"member_activity_avatar"),
										  				array(name=>"用户名列表", key=>"memberlist_username")
										  				)),
		$plugin_id."_groupmemberonline" =>array(name=>"在线成员",
										  style=> array(array(name=>"头像列表 紧凑样式", key=>"memberlist_online_avatar"))),
		),
);

?>
