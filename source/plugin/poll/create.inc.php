<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
    global $_G;
    $url = "forum.php?mod=post&action=newthread&fid=".$_G['fid']."&special=1&plugin_name=poll&plugin_op=groupmenu";
    header("Location:".$url); 
}
?>
