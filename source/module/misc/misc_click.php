<?php
/*
 * Created on 2012-4-11
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$id=$_GET["id"];
if($id){
	$value = DB :: fetch(DB::query("select url from pre_click_stats where id=$id"));
	DB::query("update pre_click_stats set clicks=clicks+1 where id=$id");
	dheader("Location:".$value[url]."");
}
?>
