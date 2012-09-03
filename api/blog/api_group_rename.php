<?php
/* Function: 好友的分组的重命名
 * Com.:
 * Author: yangyang
 * Date: 2011-9-26
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$groupname=$_G['gp_groupname'];
$groupname=urldecode($groupname);
$uid=$_G['gp_uid'];
$gid=$_G['gp_gid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$groupname.$gid.$uid)){
		$result[uid]=$uid;
		space_merge($result, 'field_home');
		$result['groupnames']=unserialize($result['groupnames']);
		
		if(in_array($groupname,$result['groupnames'])){
			$res[success]='N';
			$res[message]='该分组已存在！';
		}else{
			if($result['groupnames'][$gid]){
				$result['groupnames'][$gid]=$groupname;
				DB::update('common_member_field_home', array('groupnames'=>addslashes(serialize($result['groupnames']))), array('uid'=>$uid));
				$res['success']='Y';
				$res['message']='成功！';
			}
		}
		/*if($gid==0){
			$res[success]='N';
			$res[message]='你不能修改该分组名称！';
		}elseif($gid>$result[followgroups]){
			$res[success]='N';
			$res[message]='分组id有错误！';
		}else{
			if($result[privacy][groupname][0]){
				$arr[0]=$result[privacy][groupname][0];
			}else{
				$arr[0]='未分组';
			}
			if($result[privacy][groupname][1]){
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
			if(in_array($groupname,$arr)){
				$res[success]='N';
				$res[message]='该分组已存在！';
			}
			if(!$res){
				$result[privacy][groupname][$gid]=$groupname;
				DB::update('common_member_field_home', array('privacy'=>addslashes(serialize($result[privacy]))), array('uid'=>$uid));
				$res['success']='Y';
				$res['message']='成功！';
			}
		}*/
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
?>