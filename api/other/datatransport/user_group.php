<?php
/*
 * 用户分组
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$sql="select uid,groupnames from pre_common_member_field_home order by uid asc";
$info=DB::query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while($value=DB::fetch($info)){
	$value[groupnames]=unserialize($value[groupnames]);
	$order="uid=".$value[uid].";";
	$gids="";
	while(list($key, $val) = each($value[groupnames])){
		$_sql = "INSERT INTO esn_user_followgroup (groupid,uid,groupname) VALUES (" . $key . "," . $value[uid] . ",'" . $val ."')";
		mysql_query($_sql);
		$gids.=$key.",";
	}
	$data_0=mysql_query("update esn_user_status set group_order='".$gids."'");
	$results[]=$data_0;
}
echo json_encode($results);
