<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
   global $_G;
   $classid=empty($_G[gp_classid])?0:intval($_G[gp_classid]);
   $url = "forum.php?mod=post&action=newthread&fid=".$_G["gp_fid"]."&classid=".$classid."&plugin_name=topic&plugin_op=groupmenu&special=0";
   header("Location:".$url); 
}
?>
