<?php
/* Function: 发表评论接口
 * Com.:
 * Author: caimm
 * Date: 2012-4-24
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G["gp_feedid"];
$commentContent=$_G["gp_commentContent"];
$uid=$_G["gp_uid"];
$username=$_G["gp_username"];
$anonymous=$_G["gp_anonymous"];
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
			'anonymous' => $anonymous,
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
					"uid"=>$feedarr[uid],
					"idtype"=>'feed',
					"authorid"=>$uid,
					"author"=>$username,
					"dateline"=>time(),
					"message"=>$atarr[message],
					"anonymity"=>$anonymous
				);
		DB::insert("home_comment",$commentarr);
	}
	if ($anonymous) {
		if ($anonymous == -1) {
			$user['user']['uid'] = -1;
			$user['user']['username'] = '匿名';
			$user['user']['iconImg'] = useravatar(-1);
		} else {
			include_once libfile('function/repeats', 'plugin/repeats');
			$repeatsinfo = getforuminfo($anonymous);
			$user['user']['uid'] = $repeatsinfo['fid'];
			$user['user']['username'] = $repeatsinfo['name'];
			if ($repeatsinfo['icon']) {
				$user['ficon'] = $_G['config']['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
			} else {
				$user['ficon'] = $_G['config']['image']['url'] . '/static/image/images/def_group.png';
			}
			$user['user']['iconImg'] = $user['ficon'];
		}
	} else {
		$user['user']['uid'] = $uid;
		$user['user']['iconImg'] = useravatar($uid);
		$user['user']['username'] = user_get_user_name($uid);
	}
	if($atarr[atuids]){
		foreach(array_keys($atarr[atuids]) as $uidkey){
			notification_add($atarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$user['user']['uid'].'" target="_block">“'.$user['user']['username'].'”</a> 在其<a href="home.php?view=atme">发表的内容</a>中提到了您，赶快去看看吧', array(), 0);
			$_id=DB::fetch_first("select feedids from pre_common_user_at where uid=".$atarr[atuids][$uidkey]);
			if($_id==null){
				DB::query("insert into pre_common_user_at(uid,feedids) values(".$atarr[atuids][$uidkey].",$feedid)");
			}else if($_id[feedids]==null){
				DB::query("update ".DB::TABLE("common_user_at")." set feedids='".$feedid."' where uid=".$atarr[atuids][$uidkey]);
			}else{
				$_id[feedids].=",".$feedid;
				DB::query("update ".DB::TABLE("common_user_at")." set feedids='".$_id[feedids]."' where uid=".$atarr[atuids][$uidkey]);
			}
		}
	}
	if($feedarr[uid]!=$uid){
		notification_add($feedarr[uid],"zq_comment",'您刚刚收到了来自<a href="home.php?mod=space&uid='.$user['user']['uid'].'" target="_block">“'.$user['user']['username'].'”</a> 的评论,<a href="home.php?view=rcomment">赶快去看看吧</a>', array(), 0);
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