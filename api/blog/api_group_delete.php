<?php
/* Function: 好友的分组的删除
 * Com.:
 * Author: yangyang
 * Date: 2011-9-28
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$gid=$_G['gp_gid'];
$uid=$_G['gp_uid'];
$code=$_G['gp_code'];

if($code==md5('esn'.$gid.$uid)){
	$result[uid]=$uid;
	space_merge($result, 'field_home');
	$result['groupnames']=unserialize($result['groupnames']);
	$groupkey=array_keys($result['groupnames']);
	for($i=0;$i<$result[followgroups];$i++){
			if($gid!=$groupkey[$i]){
				$arr[$groupkey[$i]]=$result['groupnames'][$groupkey[$i]];
			}
	}
	DB::update('common_member_field_home', array('groupnames'=>addslashes(serialize($arr)),'followgroups'=>count($arr)), array('uid'=>$uid));
	
	$query=DB::query("select * from ".DB::TABLE("home_friend")." where gids like '%,".$gid.",%' and uid=".$uid);
	while($value=DB::fetch($query)){
		$arr=array();
		$newarr=array();
		$arr=explode(',',$value[gids]);
		
		if(count($arr)==3){
			
		}else{
			for($i=0;$i<count($arr);$i++){
				if($arr[$i]==$gid){
				}else{
					$newarr[]=$arr[$i];
				}
			}
			$gids=implode(',',$newarr);
		}
		DB::query("update ".DB::TABLE("home_friend")." set gids='".$gids."' where uid=".$value[uid]." and fuid=".$value[fuid]);
	}

	$res[success]='Y';
	$res[message]='成功！';
	
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}

echo json_encode($res);
?>