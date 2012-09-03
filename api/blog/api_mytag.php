<?php
/* Function: 我的标签
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$number=empty($_G[gp_number])?10:$_G[gp_number];
$uid=$_G[gp_uid];
if($uid){
	$value=DB::fetch_first("select * from ".DB::TABLE("common_user_tag")." where uid=".$uid);
	$query=DB::query("select * from ".DB::TABLE("home_tag")." where tagname!='' and id in (".implode(',',unserialize($value[tags])).") order by content desc limit 0,$number");
	while($value=DB::fetch($query)){
		$tags[]=$value;
	}

}
$res['mytags']=$tags;
echo json_encode($res);
?>