<?php
/* Function: 根据用户的uid用户的好友分组
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_follow.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$groupid=empty($_G['gp_groupid'])?0:$_G['gp_groupid'];
$pagetype=$_G['gp_pagetype'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
if($uid){
	$groups = follow_group_list_new($uid);
	$count=count($groups);
	if($shownum>$count){
		$shownum=$count;
	}
	if($groupid=='0'&&$pagetype=='up'){
		$res[groups]=null;
	}else{
		if(!$groupid){
			$pagetype='up';
		}
		if($groupid<$count){
			if($pagetype=='up'){
				if($groupid){
				}else{
					$groupid=count($groups);
				}
				if(($groupid-$shownum)<0){
					$j=0;
				}else{
					$j=$groupid-$shownum;
				}
				
				for($i=$groupid-1;$i>=$j;$i--){
					if($groups[$i]){
						$newgroup['id']="$i";
						$newgroup['name']=$groups[$i];
						$newgroups[]=$newgroup;
					}
				}
			}elseif($pagetype=='down'){
				if($count-$groupid>$shownum){
					$count=$groupid+$shownum;
				}
				for($i=$groupid+1;$i<=$count;$i++){
					if($groups[$i]){
						$newgroup['id']="$i";
						$newgroup['name']=$groups[$i];
						$newgroups[]=$newgroup;
					}
				}
				/*if(($groupid-$shownum)<0){
					$j=0;
				}else{
					$j=$shownum;
				}
				if($groupid || $groupid===0){
					if($groupid){
						$groupid=$groupid+1;
					}
					for($i=$groupid;$i>=$j;$i--){
						if($groups[$i]){
							$newgroup['id']="$i";
							$newgroup['name']=$groups[$i];
							$newgroups[]=$newgroup;
						}
					}
				}*/
			}
			$res[groups]=$newgroups;
		}
	}
}
echo json_encode($res);
?>