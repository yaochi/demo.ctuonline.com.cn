<?php
/* Function: 关注分组的调整
 * Com.:
 * Author: yangyang
 * Date: 2011-9-13
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
global $_G;
$fuid=$_G['gp_fuid'];
$gid=$_G['gp_gid'];
$action=$_G['gp_action'];
$uid=empty($_G['gp_uid'])?$_G[uid]:$_G['gp_uid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$action.$fuid.$gid.$uid)){

	$tag=1;
	$number=DB::result_first("select followgroups from ".DB::TABLE("common_member_field_home")." where uid=$uid");
	$gidarr=explode(',',$gid);
	for($i=0;$i<count($gidarr);$i++){
		if($gidarr[$i]>$number){
			$tag=0;
		}
	}
	if($tag){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$uid." and fuid in (".$fuid.")");
		while($value=DB::fetch($query)){
			if($action=='add'){
				if($value['gids']){
					$oldgidarr=explode(',',$value['gids']);
					$newgidarr=array_unique(array_merge($gidarr,$oldgidarr));
					sort($newgidarr);
					$value['gids']=implode(',',$newgidarr).',';
					$flag=DB::update('home_friend',$value,"uid=".$value[uid]." and fuid=".$value[fuid]);
				}else{
					$value['gids']=','.$gid.',';
					$flag=DB::update('home_friend',$value,"uid=".$value[uid]." and fuid=".$value[fuid]);
				}
			}elseif($action=='delete'){
				$oldgidarr=explode(',',$value['gids']);
				$newgidarr=array_diff($oldgidarr,$gidarr);
				if(count($newgidarr)>2){
					$value['gids']=implode(',',$newgidarr);
				}else{
					$value['gids']='';
				}
				$flag=DB::update('home_friend',$value,"uid=".$value[uid]." and fuid=".$value[fuid]);
			}else{
				$res['message']='不存在的操作！';
			}
		}
	}else{
		$res['message']='分组不存在！';
	}
	if($flag){
		$res['success']='Y';
		$res['message']='成功！';
	}else{
		$res['success']='N';
		$res['message']='不存在的关系！';
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}

echo json_encode($res);
?>