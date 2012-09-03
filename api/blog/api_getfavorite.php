<?php
/*
 * function:获取收藏
 * author:caimm
 * date:2012-1-17
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$uid = empty($_GET['uid']) ? $_G['uid'] : $_GET['uid'];//当前用户uid
$typesql = empty($_GET['type']) ? "" : " and fe.icon=".$_GET['type'];//收藏的动态的类型
$shownum = empty($_GET['shownum']) ? 20 : $_GET['shownum'];//显示的数量，默认20条
if($_GET['page']){//分页
	$start = ($_GET['page']-1)*$shownum;
	$limitsql = " limit ".$start.",".$shownum;
}else{
	$limitsql = " limit 0,".$shownum;
}
$sql = "select fe.* from ".DB::table("home_favorite")." fa,".DB::table("home_feed")." fe " .
		"where fa.feedid=fe.feedid and fa.uid=".$uid.$typesql." order by fa.dateline desc".$limitsql;
$info = DB::query($sql);
while($value = DB::fetch($info)){
	$favorite[] = $value;
}
echo json_encode($favorite);
?>