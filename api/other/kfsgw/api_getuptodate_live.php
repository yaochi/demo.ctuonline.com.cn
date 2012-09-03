<?php
/*
 * 获取最新的天意振翅的直播
 * @author fumz
 * @since 2010年10月11日8:48:26
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$method=strtolower($_SERVER["REQUEST_METHOD"]);
$livearray=array();


$parametercount="";//调用接口传递的查询记录条数参数
$parameterflag="";//标志符，标记调用接口方身份
$starttime="";
if($method=="get"){
	$parametercount=$_GET["count"];
	$parameterflag=$_GET['flag'];
	$starttime=$_GET['starttime'];
}elseif($mehtod="post"){
	$parametercount=$_POST["count"];
	$parameterflag=$_POST['flag'];
	$starttime=$_POST['starttime'];
}
if($parameterflag!="kfsgw"){
	exit("你没有权限访问,no permission to access!");
}
$sql="SELECT liveid,subject,url,viewnum FROM pre_group_live WHERE fid=36 ";
if($starttime&&preg_match("/^[0-9]+$/",$starttime)){
	$sql.=" AND starttime>$starttime ";
}
$sql.="ORDER BY starttime DESC";
if($parametercount&&preg_match("/^[0-9]+$/",$parametercount)){
	$sql.=" LIMIT 0,$parametercount";
}
//echo $sql;
$query=DB::query($sql);
while($row=DB::fetch($query)){
	$livearray[$row['liveid']]=$row;
}
//print_r($livearray);

$json=json_encode($livearray);
echo $json;

?>