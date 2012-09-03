<?php
//require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
set_time_limit(0);
$type=$_GET[type];
$fid=$_GET[fid];
if($type==0) $filename="station".$fid;
else if($type==1) $filename="stationcase".$fid;
else $filename="data";
require './source/plugin/stationcourse/function/function_stationcourse.php';
header("Content-type:application/vnd.ms-excel");
header("Content-Disposition:attachment;filename=".$filename.".xls");

if($type==0){//导出岗位树
	echo   "first"."\t";
	echo   "second"."\t";
	echo   "third"."\t";
	$list=getAllStation_Order($fid);
		foreach ( $list as $value){
			echo   "\n";
			$value[first]=mb_convert_encoding($value[first],'GB2312','UTF-8'); 
			$value[second]=mb_convert_encoding($value[second],'GB2312','UTF-8');
			$value[third]=mb_convert_encoding($value[third],'GB2312','UTF-8'); 
			echo $value[first]."\t";
			echo $value[second]."\t";
			echo $value[third]."\t";
		}
	}

if($type==1){//导出成员岗位设置情况
	echo   "username"."\t";
	echo   "mystation"."\t";
	echo   "intereststation"."\t";
	$list=getSetCase();
		foreach ( $list as $value){
			echo   "\n";
			$value[username]=mb_convert_encoding($value[username],'GB2312','UTF-8'); 
			$value[mystation]=mb_convert_encoding($value[mystation],'GB2312','UTF-8');
			$value[intereststation]=mb_convert_encoding($value[intereststation],'GB2312','UTF-8'); 
			echo $value[username]."\t";
			echo $value[mystation]."\t";
			echo $value[intereststation]."\t";
		}
	}
?>