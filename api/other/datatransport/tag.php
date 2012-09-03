<?php
/*
 * 标签
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$sql = "select id,tagname,content,dateline,updateline from pre_home_tag order by id asc";
$info = DB :: query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while ($value = DB :: fetch($info)) {
	$_sql = "INSERT INTO esn_tag (id,contentnum,dateline,tagname,update_dateline) VALUES (" . $value[id] . "," . $value[content] . "," . $value[dateline] . ",'" . $value[tagname] . "'," . $value[updateline] . ")";
	$a = mysql_query($_sql);
	$res[]=$a;
}
echo json_encode($res);
