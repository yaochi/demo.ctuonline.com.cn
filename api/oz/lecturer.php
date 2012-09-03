<?php
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$action=empty ($_GET['action']) ? 'alive' : $_GET['action'];
if($action=='alive'){
    echo 'alive';
} else if($action=='search'){
	$uid=$_G[gp_uid];
	$username=$_G[gp_regname];
	$arr=array();
	$isexist=0;
	$arr[isexist]=$isexist;
	if(!$uid||!$username) echo 0;
	else{
	if($uid)
    	$sql="SELECT name,gender,rank,province_rank,orgid,orgname,tel,email,experience FROM `pre_lecturer` where lecid='".$uid."';";
    else if($username)
    	$sql="SELECT name,gender,rank,province_rank,orgid,orgname,tel,email,experience FROM `pre_lecturer` where tempusername='".$username."';";
	$lec = DB::query($sql);
	$lecturer = DB::fetch($lec);
	if($lecturer) {
		$isexist=1;
		$arr[isexist]=$isexist;
		$arr[info]=$lecturer;
	}
	
	$info=json_encode($arr);
    echo $info;
	}
}


?>