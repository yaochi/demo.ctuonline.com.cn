<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$join_plugin_actionname = $_G["gp_plugin_name"]."_action";
$join_plugin_action = $_REQUEST[$join_plugin_actionname]?$_REQUEST[$join_plugin_actionname]:"index";
if(function_exists($join_plugin_action)){
    $result = $join_plugin_action();
    extract($result);
}else{
    echo sprintf("function %s not found in %s", $join_plugin_action, $_GET["plugin_name"]);
}
?>