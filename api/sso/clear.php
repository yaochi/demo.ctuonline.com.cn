<?php

require dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz->init();

$fid = $_GET[fid];
if($_GET["method"]=="cleargroupuser"){
    include_once libfile('function/group');
    updateactivity($fid, 0);
    include_once libfile('function/stat');
    updatestat('groupjoin');
    delgroupcache($fid, array('activityuser', 'newuserlist'));
    echo "ok";
}
?>
