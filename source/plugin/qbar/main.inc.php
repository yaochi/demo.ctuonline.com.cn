<?php 

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

//获取标识, 如: groupalbum2:main_action=?

$join_plugin_action = $_REQUEST[$_G["gp_plugin_name"]."_action"];


//判断标识是否合法
if(empty($join_plugin_action) || !in_array($join_plugin_action, array('index', 'qbarcp','show_insert','submit_insert'))){
	$join_plugin_action = 'index';
}

//require_once (dirname(__FILE__)."/function/function_qbar.php");

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");

//执行标识的对应的代码
//会自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加

//exit("$join_plugin_action.php");
require_once (dirname(__FILE__)."/$join_plugin_action.php");


?>