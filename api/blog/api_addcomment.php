<?php
/* Function: 发表评论接口
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G["gp_feedid"];
$commentContent=$_G["gp_commentContent"];
$uid=$_G["gp_uid"];
$username=$_G["gp_username"];
$code=$_G["gp_code"];
if(md5("esn".$feedid.$commentContent.$uid.$username)==$code){
	if(!$username){
		$userarr=getuserbyuid($uid);
		$username=$userarr[username];
	}
	$feedarr=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($feedarr[icon]=='thread'||$feedarr[icon]=='poll'||$feedarr[icon]=='reward'){
		$atarr=parseat($commentContent,$uid);
		$pid = insertpost(array(
			'fid' => $feedarr[fid],
			'tid' => $feedarr[id],
			'first' => '0',
			'author' => $username,
			'authorid' => $uid,
			'dateline' => $_G['timestamp'],
			'message' => $atarr[message],
			'invisible' => 0,
			'anonymous' => 0,
			'usesig' => 0,
			'htmlon' =>0,
			'bbcodeoff' => -1,
			'smileyoff' => -1,
			'parseurloff' =>0,
			'attachment' => '0',
			'feedid' =>$feedid,
		));
		DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$username', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$feedarr[id]'", 'UNBUFFERED');

	}else{
		$atarr=parseat($commentContent,$uid);
		$commentarr=array("id"=>$feedid,
					"idtype"=>'feed',
					"authorid"=>$uid,
					"author"=>$username,
					"dateline"=>time(),
					"message"=>$atarr[message]
				);
		DB::insert("home_comment",$commentarr);
	}
	DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where feedid=".$feedid);
	$res['success']="Y";
	$res['message']="成功！";
}else{
	$res['success']="N";
	$res['message']="code不正确！";
}

echo json_encode($res);
?>