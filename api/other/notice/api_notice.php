<?php
/* Function: 社区中通知
 * Com.:
 * Author: yangyang
 * Date: 2011-3-22
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
global $_G;
$uid=$_G['gp_uid'];
$noticeid=$_G['gp_noticeid'];
$flag=1;
$allowmem = memory('check');
$cache_key= 'viewnotice_'.$_G['uid'] ;

if($uid && $noticeid){
	$result=DB::fetch_first("select * from ".DB::TABLE("member_notice")." where uid=".$uid);
	if($result){
		$noticearray=explode(',',$result[notice]);
		foreach($noticearray as $id){
			if($noticeid==$id){
				$flag=0;
			}
		}
		if($flag){
			$noticeid=$result[notice].",".$noticeid;
			DB::query("update ".DB::TABLE("member_notice")." set notice='".$noticeid."',dateline='".$_G["timestamp"]."' where uid=".$uid);
		}
	}else{
		$data = array(
				'uid' => $uid,
				'notice' => $noticeid,
				'dateline' => $_G["timestamp"],
			);
			DB::insert('member_notice', $data);
	}
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			$viewnotice=unserialize($cache);
			foreach($viewnotice as $notice){
				if($noticeid==$notice[id]){
				}else{
					$viewnoticenew[]=$notice;
				}
			}
			memory("set", $cache_key, serialize($viewnoticenew));
		}
	}
	
	
	
}
?>