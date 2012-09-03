<?php
/* Function: 好友的分组的创建
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
$code=$_G['gp_code'];
if($code==md5('esn'.$groupname.$uid)){
	if($uid){
		$result[uid]=$uid;
		space_merge($result, 'field_home');
		$result['groupnames']=unserialize($result['groupnames']);
		if(in_array($groupname,$result['groupnames'])){
			$res[success]='N';
			$res[message]='该分组已存在！';
		}
		if(!$res){
			$groupkey=array_keys($result['groupnames']);
			if($result[followgroups]){
				for($i=0;$i<$result[followgroups]+1;$i++){
						if($groupkey[$i]==$i||$result['groupnames'][$i]){
						}else{
							$result['groupnames'][$i]=$groupname;
							$res['gid']=$i;
							break;
						}
				}
			}else{
				$result['groupnames'][0]=$groupname;
			}
			DB::update('common_member_field_home', array('groupnames'=>addslashes(serialize($result['groupnames'])),'followgroups'=>$result[followgroups]+1), array('uid'=>$uid));
			$res['success']='Y';
			$res['message']='成功！';
		}
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
?>