<?php
/*
 * 收藏
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$page = $_G[gp_page];
$start = ($page -1) * 500;
$sql = "select uid,id,idtype,dateline from pre_home_favorite order by favid asc";
$info = DB :: query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while ($value = DB :: fetch($info)) {
	$fvalue = DB :: result_first("select feedid from pre_home_feed where id=" . $value[id] . " and idtype='" . $value[idtype] . "'");
	if ($fvalue) {
		$_sql = "INSERT INTO esn_favorite (id,dateline,feedid,uid) VALUES (" . $value[id] . "," . $value[dateline] . "," . $fvalue . "," . $value[uid] . ")";
		$a = mysql_query($_sql);
		$res[] = $a;
	}
}
echo json_encode($res);