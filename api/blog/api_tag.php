<?php
/* Function: #接口提示
 * Com.:
 * Author: yangyang
 * Date: 2011-10-24
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$name=$_G['gp_name'];

if($name){
	$query=DB::query("select * from ".DB::TABLE("home_tag")." where tagname like '%".$name."%' order by yearcc desc limit 0,10 ");
	while($value=DB::fetch($query)){
		$tag[][name]=$value[tagname];
	}
	$res[keyword]=$name;
	$res[tag]=$tag;
}
echo json_encode($res);
?>