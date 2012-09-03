<?php

/*
 * 通知
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$page = $_G[gp_page];
$start = ($page -1) * 500;
$sql = "select id,uid,type,ptype,new as is_new,authorid,note,dateline " .
		"from pre_home_notfication order by favid limit " . $start . ",500";
$info = DB :: query($sql);
$url = "http://localhost:8080/eksn_core_service/eksn.favorite.save;";
while($value=DB::fetch($info)){
	$param="";
	$param.="id=".$value[id].";";
	$param.="uid=".$value[uid].";";
	$param.="is_new=".$value[is_new].";";
	$param.="authorid=".$value[authorid].";";
	$param.="content=".$value[note].";";
	$param.="dateline=".$value[dateline].";";
//	$param.="id=".$value[id].";";
//	$param.="id=".$value[id].";";
	$data=postData($url.$param,"");
	$results[]=$data;
}