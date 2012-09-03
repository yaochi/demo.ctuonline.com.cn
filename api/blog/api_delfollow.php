<?php
/* Function: 用户取消关注某人
 * Com.:
 * Author: yangyang
 * Date: 2011-9-14
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
require_once libfile("function/follow");
$fromuid=$_G['gp_fromuid'];
$touid=$_G['gp_touid'];
$code=$_G['gp_code'];

if($code==md5('esn'.$fromuid.$touid)){
	if($fromuid==$touid){
		$res[success]='N';
		$res['message']='你不能对自己进行关注！';
	}else{
		$result=DB::fetch_first("select * from ".DB::TABLE("home_friend")." where uid=$fromuid and fuid=$touid and (type=1 or type=3) and isdelete!=1");
		if($result[type]=='1'){
			//DB::query("delete from ".DB::TABLE("home_friend")." where (uid=$fromuid and fuid=$touid) or (uid=$touid and fuid=$fromuid)");
			DB::query("update ".DB::TABLE("home_friend")." set type=0,isdelete=1,updatetime='".time()."',gids='',fansmark='-1',friendmark='0' where (uid=$fromuid and fuid=$touid)");
			DB::query("update ".DB::TABLE("home_friend")." set type=0,isdelete=1,updatetime='".time()."',gids='',fansmark='0',friendmark='0' where (uid=$touid and fuid=$fromuid)");
			DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans-1 WHERE uid='$touid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow-1 WHERE uid='$fromuid'");
			$res[success]='Y';
			$res[message]='成功';
		}elseif($result[type]=='3'){
			DB::query("update ".DB::TABLE("home_friend")." set type=2,gids='',updatetime='".time()."',fansmark='-1',friendmark='-1' where uid=$fromuid and fuid=$touid ");
			DB::query("update ".DB::TABLE("home_friend")." set type=1,updatetime='".time()."',fansmark='0',friendmark='0' where uid=$touid and fuid=$fromuid ");
			DB::query("UPDATE ".DB::table('common_member_status')." SET fans=fans-1 WHERE uid='$touid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET follow=follow-1 WHERE uid='$fromuid'");
			$res[success]='Y';
			$res[message]='成功';
		}else{
			$res[success]='N';
			$res['message']='你没有关注对方！';
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
?>