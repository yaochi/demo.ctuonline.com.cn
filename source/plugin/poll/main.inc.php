<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
    $url = "forum.php?mod=forumdisplay&action=list&fid=".$_GET['fid']."&special=1&plugin_name=poll&plugin_op=createmenu";
    header("Location:".$url);
}
?>
