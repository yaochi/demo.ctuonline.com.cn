<?php
/*
 * 用户关系
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$sql="select uid,fuid,dateline,note,gids,type from pre_home_friend order by uid asc";
$info=DB::query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while($value=DB::fetch($info)){

	$data=mysql_query("insert into esn_user_relation(fuid,uid,dateline,note,type) values(".$value[fuid].",".$value[uid].",".$value[dateline].",'".$value[note]."',".$value[type].")");
	if($value[gids]){
		$gidarr=explode(",",$value[gids]);
		foreach($gidarr as $gid){
			if($gid!=""){
				$data_0=mysql_query("insert into esn_user_grouprel(fuid,gid,uid) values(".$value[fuid].",".$gid.",".$value[uid].")");
			}
		}
	}
	$results[]=$data;

}
echo json_encode($results);