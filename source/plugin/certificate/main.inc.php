<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");
require_once libfile('function/group');
function index(){
	global $_G;
	$uid=$_G[uid];
	$teacher=DB::fetch_first("SELECT * FROM ".DB::table("lecturer")." where lecid=$uid");
	$lecid=$teacher[id];
	//if(!$lecid) showmessage('您不是讲师', 'forum.php?mod=group&action=plugin&fid='.$_G["fid"]);
	if($lecid){
		if(!checkinfo($lecid)) {
			//header("Location: forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecid=".$lecid."&lecturermanage_action=edit");
			$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecid=".$lecid."&lecturermanage_action=edit&certificate=1";
			showmessage("系统现自动引导您进入讲师资料编辑页面...",$url);
			exit();
		}
	}
	$count=DB::result_first("select count(*) from ".DB::table("synchro_cert_info")." where User_id=".$uid);
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table("synchro_cert_info")." where User_id=".$uid);
		$tcount=0;
		while($value=DB::fetch($query)){
			$value[Create_date]=dgmdate($value[Create_date],'Y-m-d');
			if($value['Certificate_type']=='3'){
				$value['certype']="培训证书";
				$cerlist[]=$value;
			}else{
				if($teacher){
					if($teacher[bginfo]){
					}else{
						$teacher[bginfo]=$teacher[about].$teacher[experience];
						if($teacher[bginfo]){
							DB::query("update ".DB::table("lecturer")." set bginfo=".$teacher[bginfo]." where id=".$teacher[id]);
						}
					}
					if($teacher[bginfo]&&$teacher[tel]&&$teacher[email]&&$teacher[imgurl]){
						if($value['Certificate_type']=='2'){
							$value['certype']="课程授权讲师证书";
						}elseif($value['Certificate_type']=='1'){
							$value['certype']="课程认证讲师证书";
						}
						$cerlist[]=$value;
					}else{
						$tcount=$tcount+1;
					}
				}else{
					//获取机构岗位信息
					require_once libfile('function/org');
					$orgname = getOrgNameByUser($value[Regname]);
					$station = org_get_stattion($uid); 
					$station = implode(",", $station);
					DB::query("INSERT into ".DB::table("lecturer")." (lecid,orgname,position,tempusername,name,gender,isinnerlec,fid,fname,uid,dateline,ischeck) values ('".$value[User_id]."','".$orgname."','".$station."','".$value[Regname]."','".user_get_user_name($value[User_id])."','0','1','197','课程师资平台','".$value[User_id]."','".time()."','1')");
					$tcount=$tcount+1;
				}
			}
		}
	
	}
	 return array("cerlist"=>$cerlist, "_G"=>$_G,"multi"=>$multi,"count"=>$count,"tcount"=>$tcount,'lecid'=>$lecid);
}

function checkinfo($lecid)
{//判断信息是否完整
    $sql="SELECT * FROM `pre_lecturer` where id=".$lecid.";";
	$info = DB::query($sql);
	$value = DB::fetch($info);
    if(!$value[bginfo]) 
        return false;
    if(!$value[imgurl]) 
        return false;
    if(!$value[tel]) 
        return false;
    if(!$value[email]) 
        return false;

		
	/*$info = DB::query("SELECT count(*) as count FROM `pre_train_course` where lecid=".$lecid);
    $courses = DB::fetch($info);
	if($courses[count]==0)
        return false;*/
	return true;
}


?>
