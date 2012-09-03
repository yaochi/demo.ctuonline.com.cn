<?php
/*
 * 标签关联
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$sql = "select id,tagid,tagname,contentid from pre_home_tagrelation order by id asc";
$info = DB :: query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while ($value = DB :: fetch($info)) {
	$uid=DB::result_first("select uid from pre_home_feed where feedid=".$value[contentid]);
	$_sql = "INSERT INTO esn_tag_relation (id,feedid,tagid,uid) VALUES (" . $value[id] . "," . $value[contentid] . "," . $value[tagid] . "," . $uid . ")";
	$a = mysql_query($_sql);
	$res[]=$a;
}
echo json_encode($res);
