<?php
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uids=$_G['gp_uid'];
if($uids){
	$query=DB::query("select * from ".DB::TABLE("authenticated_users")." where uid in (".$uids.")");
	$authuserarr=array();
	while($value=DB::fetch($query)){
		$data[$value[uid]][isauth]=1;
		$authuserarr[]=$value[uid];
	}
	$uidarr=explode(',',$uids);
	$newarr=array_diff($uidarr,$authuserarr);
	foreach($newarr as $key=>$value){
		$data[$value][isauth]=0;
	}
}
if($data){
	$res[data]=$data;
	$res[error]='0';
}else{
	$res[data]=array();
	$res[error]='1';
}

echo json_encode($res);

?>