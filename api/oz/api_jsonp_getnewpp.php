<?php
/**
 * author fumz
 * since 2010-9-19 10:37:53
 * 根据用户工号返回用户消息数,提醒数
 * get msg num by username
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$method=strtolower($_SERVER["REQUEST_METHOD"]);
$username='';
$newpp=array();
$callback ="";
$json;
if($method=="post"){
	$username=$_POST['username'];//用户工号
	$callback=isset($_POST['callback']) ? $_POST['callback'] : '';
}elseif($method=="get"){
	$username=$_GET['username'];
	$callback=isset($_GET['callback']) ? $_GET['callback'] : '';
}

if($username!=''){
	$sql="SELECT newpm,newprompt FROM pre_common_member WHERE username='$username';";
	//echo $sql;
	$query=DB::query($sql);
	$newpp=DB::fetch($query,0);
}
if(empty($newpp)){
	$result=array('success'=>false,'newpm'=>0,'newprompt'=>0);
	$json=json_encode($result);
}else{
	$result=array('success'=>true,'newpm'=>intval($newpp['newpm']),'newprompt'=>intval($newpp['newprompt']));
	$json=json_encode($result);
}
if(!empty($callback)){
	$json=$callback."(".$json.")";
}
echo $json;


?>