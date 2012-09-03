<?php
/* Function: 我的马甲
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */

require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
if($uid){
	$realname=user_get_user_name($uid);
	include_once libfile('function/repeats','plugin/repeats');
	if($_G[member][repeatstauts]){
		if($_G['setting']['plugins']['available_plugins']['repeats']){
			$list=viewrepeatsbyuid($uid);
			$list[count($list)]=array('repeatsid'=>0,'name'=>$realname);
		}
	}else{
		if($_G['setting']['plugins']['available_plugins']['repeats']){
			$list=viewrepeatsbyuid($uid);
		}
	}
}
for($i=0;$i<count($list);$i++){
	$ids[]=$list[$i]['fid'];
	$newlist[$list[$i]['fid']]=$list[$i];
}

if(count($ids)){
	$query=DB::query("select fid,icon from ".DB::TABLE("forum_forumfield")." where fid in (".implode(",",$ids).") and jointype!='-1'");
	while($value=DB::fetch($query)){
		if($value['icon']){
			$repeatlist[$value['fid']]=$newlist[$value['fid']];
			$repeatlist[$value['fid']]['icon']=$_G[config]['image']['url'].'/data/attachment/group/'.$value['icon'];
		}else{
			$repeatlist[$value['fid']]=$newlist[$value['fid']];
			$repeatlist[$value['fid']]['icon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
		}	
	}
}
$res['list']=$repeatlist;

echo json_encode($res);
?>