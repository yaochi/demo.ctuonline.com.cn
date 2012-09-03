<?php
/* Function:查找人员
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/api/sphinx/sphinxapi.php';
$discuz = & discuz_core::instance();

$discuz->init();
$name=$_G['gp_name'];
$uid=$_G['gp_uid'];
$searchuid=$_G['gp_searchuid'];
$pagetype=$_G['gp_pagetype'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($name){
	if($_G[config]['sphinx']['used'] && $_G[config]['memory']['redis']['on'] && '1'=='0'){
		$result=sphinxdata($name,$shownum,$searchuid,$pagetype);
		$membercount=$result[total];
		if($result[matches]){
			$member=redisdata($result[matches]);
		}
	}elseif($_G[config]['sphinx']['used']){
		$result=sphinxdata($name,$shownum,$searchuid,$pagetype);
		$membercount=$result[total];
		if($result[matches]){
			for($i=0; $i<count($result[matches]); $i++){
				$uidarr[]=$result[matches][$i]['id'];
			}
			$query=DB::query("SELECT * FROM ".DB::table("common_member_status")." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and cms.uid in (".implode(',',$uidarr).") ORDER BY cms.uid desc limit 0,$shownum");
			while($uservalue=DB::fetch($query)){
				$newuser[type]='user';
				$uservalue[userimgurl]=useravatar($uservalue['uid']);
				$uservalue[info]=$uservalue['bio'];
				$uservalue[user][uid]=$uservalue[uid];
				$uservalue[user][username]=user_get_user_name($uservalue[uid]);
				$uservalue[user][iconImg]=$uservalue[userimgurl];
				$uservalue[follows]=$uservalue[follow];
				$uservalue[feeds]=$uservalue[blogs];
				$member[]=$uservalue;
			}
		}
	}else{
		$membercount=DB::result_first("SELECT count(*) FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$name."%'");
		if($membercount){
			$wheresql['1=1']=" 1=1 ";
			if($searchuid){
				if($pagetype=='up'){
					$wheresql['uid']=" cms.uid<".$searchuid;
				}elseif($pagetype=='down'){
					$wheresql['uid']=" cms.uid>".$searchuid;
				}
			}else{
				$pagetype='up';
			}
			$ordersql=updownsql($pagetype,'common_member_profile','cms.uid',$searchuid,$shownum,'uid'," realname like '%".$name."%' ");
			$res['refresh']=$ordersql['refresh'];
			$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE ".implode(' AND ', $wheresql)." and cms.uid=profile.uid and profile.realname LIKE '%".$name."%' ".$ordersql['ordersql']);
			/*if($pagetype=='up'){
				$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE ".implode(' AND ', $wheresql)." and cms.uid=profile.uid and profile.realname LIKE '%".$name."%' ORDER BY cms.uid desc limit 0,$shownum");
			}elseif($pagetype=='down'){
				$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE ".implode(' AND ', $wheresql)." and cms.uid=profile.uid and profile.realname LIKE '%".$name."%' ORDER BY cms.uid asc limit 0,$shownum");
			}*/
			while($uservalue=DB::fetch($query)){
				$newuser[type]='user';
				$uservalue[userimgurl]=useravatar($uservalue['uid']);
				$uservalue[info]=$uservalue['bio'];
				$uservalue[user][uid]=$uservalue[uid];
				$uservalue[user][username]=user_get_user_name($uservalue[uid]);
				$uservalue[user][iconImg]=$uservalue[userimgurl];
				$uservalue[follows]=$uservalue[follow];
				$uservalue[feeds]=$uservalue[blogs];
				$uidarr[]=$uservalue['uid'];
				$member[]=$uservalue;
			}
		}
	}
	if($uidarr){
		$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where uid=".$uid." and (type='1' or type='3') and fuid in (".implode(",",$uidarr).")");
		while($value=DB::fetch($query)){
			if($value[fuid]){
				for($i=0;$i<count($member);$i++){
					if($member[$i][uid]==$value[fuid]){
						$member[$i][isfollow]=1;
					}
				}
			}
		}
	}
	for($i=0;$i<count($member);$i++){
		if($member[$i][isfollow]){
		}else{
			$member[$i][isfollow]=0;
		}
	}
	$res[membercount]=$membercount;
	$res[member]=$member;
}

echo json_encode($res);



function sphinxdata($name,$shownum,$searchuid,$pagetype){
	global $_G;

	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "article";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	if($searchuid){
		if($pagetype=='up'){
			$sc->SetIDRange(0,$searchuid-1);
		}elseif($pagetype=='down'){
			$sc->SetIDRange($searchuid+1,10000000000);
		}
	}

	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_BOOLEAN );
	$sc->SetSortMode(SPH_SORT_EXTENDED,"@id DESC");
	$sc->SetArrayResult ( true );
	$result = $sc->Query ( $name, $index );

	return $result;

}

function redisdata($keysarr=array()){
	global $_G;
	$redis = new Redis();
	$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
	for($i=0; $i<count($keysarr); $i++){
		$list[]=$redis->hmget($keysarr[$i]['id'],array('uid','username','realname','follow','fans','blogs','friends','userprovince'));
	}
}
?>