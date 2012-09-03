<?php
/* Function: 专区直播，用于分发请求，根据请求执行不同的代码
 * 1	获取标识 $_G["gp_plugin_name"]."_action"；注意默认首页必须是index
 * 2	判断标识是否合法；
 * 3	执行标识的对应的代码；
 * 4	自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加；
 * Com.:
 * Author: wuhan
 * Date: 2010-7-12
 * 
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
//获取标识, 如: grouplive:main_action=?
$join_plugin_action = $_REQUEST[$_G["gp_plugin_name"]."_action"];

//判断标识是否合法
if(empty($join_plugin_action) || !in_array($join_plugin_action, array('index', 'livecp', 'topicadmin'))){
	$join_plugin_action = 'index';
}

require_once (dirname(__FILE__)."/function/function_live.php");

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");

//执行标识的对应的代码
//会自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加

require_once (dirname(__FILE__)."/$join_plugin_action.php");
?>
