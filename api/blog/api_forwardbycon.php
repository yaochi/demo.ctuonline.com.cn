<?php
/* Function: 转发没有原动态的微博接口
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();


$discuz->init();
$id=$_G["gp_id"];
$transpondContent=empty($_G["gp_transpondContent"])?"转发内容":$_G["gp_transpondContent"];
$uid=$_G["gp_uid"];
$username=$_G["gp_username"];
$fromwhere=$_G["gp_fromwhere"];
$icon=empty($_G["gp_icon"])?"common":$_G["gp_icon"];
$author=empty($_G["gp_author"])?"未知":$_G["gp_author"];
$title=$_G["gp_title"];
$titlelink=$_G["gp_titlelink"];
$context=$_G["gp_context"];
$imageurl=$_G["gp_imageurl"];
$imagelink=empty($_G["gp_imagelink"])?$_G["gp_titlelink"]:$_G["gp_imagelink"];

if($transpondContent && $uid && $icon && $author && $title && $titlelink && $context && $id){
	if(!$username){
		$userarr=getuserbyuid($uid);
		$username=$userarr[username];
	}
	$atarr=parseat($transpondContent,$uid);
	if($atarr['atfids']){
		$sharetofids=",".implode(',',$atarr['atfids']).",";
	}
	$feedvalue=DB::fetch_first("select * from ".DB::table("home_feed")." where id=".$id." and icon='".$icon."'");
	if($feedvalue[feedid]){
		$arr=$feedvalue;
		$feedid=$feedvalue[feedid];
	}else{
		$arr[icon]=$icon;
		$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
		$arr['body_data'] = array(
			'subject' => "<a href=\"".$titlelink."\">".$title."</a>",
			'author' => $author,
			'message' => cutstr($context,150)
		);
		$arr['body_data']=serialize(dstripslashes($arr['body_data']));
		$arr[image_1]=$imageurl;
		$arr[image_1_link]=$imagelink;
		$arr[idtype]=$icon;
		$arr[id]=$id;
		$arr[sharetimes]=1;
		$arr[fromwhere]=$fromwhere;
		DB::insert('home_feed', $arr);
		$feedid=DB::insert_id();
	}
	
	$feedarr = array(
			'appid' => '',
			'icon' => $arr[icon],
			'uid' => $uid,
			'username' => $username,
			'dateline' => time(),
			'body_template' => $arr['body_template'],
			'body_data' => $arr['body_data'],
			'body_general'=>$atarr[message],
			'image_1' => $arr[image_1],
			'image_1_link' => $arr[image_1_link],
			'id' => $feedid,
			'idtype' => 'feed',
			'olduid'=>0,
			'fromwhere'=>$fromwhere,
			'oldusername'=>'',
			'olddateline'=>'',
		);
		$feedarr=daddslashes($feedarr);
		
		DB::insert('home_feed', $feedarr);
		$newfeedid=DB::insert_id();
		DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$uid);
	

		/*if($atarr[atuids]){
			foreach(array_keys($atarr[atuids]) as $uidkey){
				notification_add($atarr[atuids][$uidkey],"zq_at",'“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”在发表的内容中<a href="'.$newfeedid.'">@了您</a>，赶快去看看吧', array(), 0);
			}
		}*/
	if($feedvalue[feedid]){
		DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid );
	}
	
	$res['success']="Y";
	$res['message']="成功！";		
}else{
	$res['success']="N";
	$res['message']="code不正确！";
}

echo json_encode($res);
?>