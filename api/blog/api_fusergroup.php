<?php
/* Function: 根据用户的uid和fuid获取follow用户的好友分组
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_follow.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$fuid=$_G['gp_fuid'];

if($uid && $fuid){
	$result[uid]=$uid;
	space_merge($result, 'field_home');
	$result['groupnames']=unserialize($result['groupnames']);
	$gids=DB::result_first("select gids from ".DB::TABLE("home_friend")." where uid=".$uid." and fuid=".$fuid);
	$gidarr=explode(',',$gids);
	for($i=0;$i<count($gidarr);$i++){
		if($gidarr[$i] || $gidarr[$i]==='0'){
			if($result['groupnames'][$gidarr[$i]]){
				$res[group][$gidarr[$i]]=$result['groupnames'][$gidarr[$i]];
			}
		}
	}
	
}

echo json_encode($res);
?>