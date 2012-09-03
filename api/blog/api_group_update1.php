<?php
/* Function: 关注分组的调整
 * Com.:
 * Author: yangyang
 * Date: 2011-9-13
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_follow.php';
$discuz = & discuz_core::instance();

$discuz->init();
global $_G;
$fuid=empty($_G['gp_fuid'])?"-1":$_G['gp_fuid'];
$gid=$_G['gp_gid'];
$action=$_G['gp_action'];
$uid=empty($_G['gp_uid'])?$_G[uid]:$_G['gp_uid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$action.$fuid.$gid.$uid)||1==1){
	$tag=1;
	$gidarr=explode(',',$gid);
	if ($uid) {
		$groups = follow_group_list_new($uid);
	}
	while(list($key, $val) = each($groups)){
		$group[]=$key;
	}
	for($i=0;$i<count($gidarr);$i++){
		if(!in_array($gidarr[$i],$group)){
			$tag=0;
			break;
		}
	}
	if($action=="clear"){
		$wheresql=" and gids like '%,".$gid.",%'";
	}else{
		$wheresql=" and fuid in (".$fuid.")";
	}
	if($tag){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$uid.$wheresql." and (type=1 or type=3)");
		$count=DB::result_first("select count(*) from ".DB::TABLE("home_friend")." where uid=".$uid.$wheresql." and (type=1 or type=3)");
		if($count){
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
					if($flag){
						$res['success']='Y';
						$res['message']='成功！';
					}else{
						$res['success']='N';
						$res['message']='不存在的关系！';
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
					if($flag){
						$res['success']='Y';
						$res['message']='成功！';
					}else{
						$res['success']='N';
						$res['message']='不存在的关系！';
					}
				}elseif($action=="clear"){
					$oldgidarr=explode(',',$value['gids']);
					$newgidarr=array_diff($oldgidarr,$gidarr);
					if(count($newgidarr)>2){
						$value['gids']=implode(',',$newgidarr);
					}else{
						$value['gids']='';
					}
					$wheresql=" and fuid =".$value[fuid];
					$flag=DB::update('home_friend',$value,"uid=".$value[uid]." and fuid=".$value[fuid]);
					if($flag){
						$res['success']='Y';
						$res['message']='成功！';
					}else{
						$res['success']='N';
						$res['message']='不存在的关系！';
					}
				}else{
					$res['success']='N';
					$res['message']='不存在的操作！';
				}
			}
		}else{
			if($action=="clear"){
				$res['success']='Y';
				$res['message']='成功！';
			}else{
				$res['success']='N';
				$res['message']='不存在的关系！';
			}
			
		}
	}else{
		$res['success']='N';
		$res['message']='分组不存在！';
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}

echo json_encode($res);
?>