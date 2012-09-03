<?php
/* Function: 用户关注某人
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
require_once libfile("function/follow");
require_once libfile("function/core");
$fromuid=$_G['gp_fromuid'];
$touid=$_G['gp_touid'];
$code=$_G['gp_code'];
if($code==md5('esn'.$fromuid.$touid)){
	if($fromuid==$touid){
		$res[success]='N';
		$res['message']='不能关注自己';
	}else{
		$touser=DB::fetch_first("SELECT cm.*, cmp.realname FROM ".DB::table("common_member")." cm, ".DB::table("common_member_profile")." cmp WHERE cm.uid=cmp.uid AND cm.uid=".$touid);
		$fromuser=DB::fetch_first("SELECT cm.*, cmp.realname FROM ".DB::table("common_member")." cm, ".DB::table("common_member_profile")." cmp WHERE cm.uid=cmp.uid AND cm.uid=".$fromuid);
		$result=DB::fetch_first("select * from ".DB::TABLE("home_friend")." where uid=$fromuid and fuid=$touid ");
		if($result[type]=='1'||$result[type]=='3'){
			$res[success]='N';
			$res['message']='您已关注对方';
		}elseif($result[type]=='2'){
			DB::query("update ".DB::TABLE("home_friend")." set type=3,updatetime='".time()."',fansmark='1',friendmark='1' where uid=$fromuid and fuid=$touid");
			DB::query("update ".DB::TABLE("home_friend")." set type=3,updatetime='".time()."',fansmark='0',friendmark='0' where fuid=$fromuid and uid=$touid");
			DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans+1 WHERE uid='$touid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow+1 WHERE uid='$fromuid'");
			$res[success]='Y';
			$res[message]='成功';
			notification_add($touid,'follow',"<a href='home.php?mod=space&uid=".$fromuid."' target='_block'>“".$fromuser[realname]."”</a> 已成为您的新粉丝");
		}elseif($result[type]=='0'){
			DB::query("update ".DB::TABLE("home_friend")." set isdelete='0',type=1,updatetime='".time()."',fansmark='1',friendmark='0' where uid=$fromuid and fuid=$touid");
			DB::query("update ".DB::TABLE("home_friend")." set isdelete='0',type=2,updatetime='".time()."',fansmark='0',friendmark='0' where fuid=$fromuid and uid=$touid");
			DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans+1 WHERE uid='$touid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow+1 WHERE uid='$fromuid'");
			$res[success]='Y';
			$res[message]='成功';
			notification_add($touid,'follow',"<a href='home.php?mod=space&uid=".$fromuid."' target='_block'>“".$fromuser[realname]."”</a> 已成为您的新粉丝");	
		
		}else{
			$setarr = array(
				'uid' =>  $fromuid,
				'fuid' => $touid,
				'fusername' => $touser[username],
				//'nickname'=>$touser[nickname],
				'type' => '1',
				'dateline' => $_G['timestamp'],
				'updatetime'=>$_G['timestamp'],
				'fansmark'=>'1'
			);
			DB::insert('home_friend', $setarr);
			$setarr = array(
				'uid' => $touid,
				'fuid' => $fromuid,
				'fusername' => $fromuser[username],
				//'nickname'=> $fromuser[nickname],
				'type' => '2',
				'dateline' => $_G['timestamp'],
				'updatetime'=>$_G['timestamp']
			);
			DB::insert('home_friend', $setarr);
			DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans+1 WHERE uid='$touid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow+1 WHERE uid='$fromuid'");
		
			notification_add($touid,'follow',"<a href='home.php?mod=space&uid=".$fromuid."' target='_block'>“".$fromuser[realname]."”</a> 已成为您的新粉丝");		
			$res[success]='Y';
			$res[message]='成功';
		}
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}
echo json_encode($res);
if($fromuid!=$touid){
			$query = DB::query("SELECT uid,fuid,type FROM ".DB::table('home_friend')." WHERE (uid='".$fromuid."' or uid='".$touid."') and (type='1' or type='3') and isdelete!='1' ORDER BY num DESC, dateline DESC");
			$fcount=0;
			$tcount=0;
			while ($value = DB::fetch($query)) {
				if($value['uid']==$fromuid){
					$uids[]=$value['fuid'];
					if($value['type']=='3'){
						$fcount++;
					}
				}else{
					$tuids[]=$value['fuid'];
					if($value['type']=='3'){
						$tcount++;
					}
				}
			}
	DB::update('common_member_field_home', array('feedfollow'=>implode(',', $uids)), array('uid'=>$fromuid));
	DB::update('common_member_status', array('friends'=>$fcount), array('uid'=>$fromuid));
	DB::update('common_member_field_home', array('feedfollow'=>implode(',', $tuids)), array('uid'=>$touid));
	DB::update('common_member_status', array('friends'=>$tcount), array('uid'=>$touid));
	}
	if($_G[config]['memory']['redis']['on'] && count($uids)>10){
		$redis = new Redis();
		$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
		$interestarr=$redis->hmget($fromuid,array('interestpl'));
		$arraydata=unserialize($interestarr['interestpl']);
		$newarraydata=array_diff($arraydata,$uids);
		if(count($newarraydata)<$_G[config]['suggest']['number']){
			 $userquery= DB :: query("select distinct fuid  from pre_home_friend where uid in(select fuid from pre_home_friend where uid=" . $fromuid . " and type in(3,1)) and type in(3,1) and fuid not in (select fuid from pre_home_friend where uid=" . $fromuid . " and type in(3,1)) and fuid!=".$fromuid." limit 0,200");
			 while($uservalue=DB::fetch($userquery)){
				  $list[$fromuid][]=$uservalue['fuid'];
			 }
			  $intervalue=serialize($list[$fromuid]);
		}else{
			$intervalue=serialize($newarraydata);
		}
		$redis->hset($fromuid,'interestpl',$intervalue);
	}
	
	

?>