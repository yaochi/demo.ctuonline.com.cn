<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

//获取标识
$join_plugin_action = $_REQUEST[$_G["gp_plugin_name"]."_action"];

//判断标识是否合法
if(empty($join_plugin_action) || !in_array($join_plugin_action, array('index', 'questionarycp','upload','create','answer'))){
	$join_plugin_action = 'index';
}

//执行标识的对应的代码
//会自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加
require_once (dirname(__FILE__)."/$join_plugin_action.php");

?>
