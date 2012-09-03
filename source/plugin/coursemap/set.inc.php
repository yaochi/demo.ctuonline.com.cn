<?php
/*
 * Com.:
 * Author: qiaoyz
 * Date: 2012-8-1
 */
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

/**
 *访问url：http://localhost/forum/home.php?mod=space&do=plugin&plugin_name=coursemap&coursemap_action=index
 */
function index(){
	global $_G;
	require_once (dirname(__FILE__)."/function/function_coursemap.php");
	$my=getustation($_G[uid]);
	$path=getstationpath($my[res]);
	$path[mystation]=$my[set];
	//print_r(getchildstation(152));
	$info=array("self"=>$path,"one"=>getchildstation(-1),"two"=>getchildstation($path[one]),"three"=>getchildstation($path[two]),"courses"=>getrecommends($path[three]));
	//print_r($info);
	include template("coursemap:index");
	dexit();
}

/**
 *访问url：http://localhost/forum/home.php?mod=space&do=plugin&plugin_name=coursemap&coursemap_action=index
 */
function forward(){
	global $_G;
	require_once (dirname(__FILE__)."/function/function_coursemap.php");
	echo($_G[uid]);
	$course=getcourseinfo($_GET[code]);
	print_r($course);
	include template("coursemap:forward");
	dexit();
}

function test(){
	global $_G;
	require_once (dirname(__FILE__)."/function/function_coursemap.php");
	//echo getstationid($stationname);
	//init_coursemap();
	//include template("coursemap:test");
	//print_r(getcourseinfo('CW-A-00-000-SN0117'));
	//echo urlencode("action=setstation");
	//echo md5("setstation_5244799");
	echo spell("产");
	echo urlencode("action=search&key=产品");

	dexit();
}
?>