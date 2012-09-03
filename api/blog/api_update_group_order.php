<?php
/* Function: 关注分组顺序的调整
 * Com.:
 * Author: yangyang
 * Date: 2011-9-26
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
global $_G;
$gids=$_G['gp_gids'];
$uid=empty($_G['gp_uid'])?$_G[uid]:$_G['gp_uid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$gids.$uid)){
	$gidarr=explode(',',$gids);
	$result[uid]=$uid;
	space_merge($result, 'field_home');
	$arr=$result['groupnames']=unserialize($result['groupnames']);
	if(count($arr)!=count($gidarr)){
		$res['success']='N';
		$res['message']='参数不正确！';
	}else{
		for($i=1;$i<=count($arr);$i++){
			$result['groupnames'][$i]=$arr[$gidarr[$i-1]];
		}
		DB::update('common_member_field_home', array('groupnames'=>addslashes(serialize($result[groupnames]))), array('uid'=>$uid));
		$res['success']='Y';
		$res['message']='成功！';
	}
	/*if($result[privacy][groupname][1]){
		$arr[1]=$result[privacy][groupname][1];
	}else{
		$arr[1]='特别关注';
	}
	if($result[privacy][groupname][2]){
		$arr[2]=$result[privacy][groupname][2];
	}else{
		$arr[2]='同事';
	}
	if($result[privacy][groupname][3]){
		$arr[3]=$result[privacy][groupname][3];
	}else{
		$arr[3]='认识的人';
	}
	for($i=4;$i<$result[followgroups];$i++){
		$arr[$i]=$result[privacy][groupname][$i];
	}
	if(count($arr)!=count($gidarr)){
		$res['success']='N';
		$res['message']='参数不正确！';
	}else{
		for($j=1;$j<=count($arr);$j++){
			$result[privacy][groupname][$j]=$arr[$gidarr[$j-1]];
		}
		DB::update('common_member_field_home', array('privacy'=>addslashes(serialize($result[privacy]))), array('uid'=>$uid));
		$res['success']='Y';
		$res['message']='成功！';
	}*/
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
?>