<?php
/* Function: 转发微博接口
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();


$discuz->init();
$feedid=$_G["gp_feedid"];
$transpondContent=$_G["gp_transpondContent"];
$uid=$_G["gp_uid"];
$username=$_G["gp_username"];
$fromwhere=$_G["gp_fromwhere"];
$code=$_G["gp_code"];

if(md5("esn".$feedid.$transpondContent.$uid.$username)==$code){
	if(!$username){
		$userarr=getuserbyuid($uid);
		$username=$userarr[username];
	}
	$atarr=parseat($transpondContent,$uid);
	if($atarr['atfids']){
		$sharetofids=",".implode(',',$atarr['atfids']).",";
	}
	
	$value=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($value['idtype']=='feed'){
		$feedid1=$feedid;
		$feedid=$value['id'];
		$value['uid']=$value['olduid'];
		$value['username']=$value['oldusername'];
		$value['dateline']=$value['olddateline'];
		$atarr[message]=$atarr[message].'//<a class="perPanel xw1" target="_blank" href="home.php?mod=space&uid='.$value[uid].'" id="'.$value[uid].'">@'.$value[username].'</a>:'.$value[body_general];
	}
	$feedarr = array(
			'appid' => '',
			'icon' => $value['icon'],
			'uid' => $uid,
			'username' => $username,
			'dateline' => time(),
			'title_template' => $value['title_template'],
			'title_data' => $value['title_data'],
			'body_template' => $value['body_template'],
			'body_data' => $value['body_data'],
			'body_general'=>$atarr[message],
			'image_1' => $value['image_1'],
			'image_1_link' => $value['image_1_link'],
			'image_2' => $value['image_2'],
			'image_2_link' => $value['image_2_link'],
			'image_3' => $value['image_3'],
			'image_3_link' => $value['image_3_link'],
			'image_4' =>$value['image_4'] ,
			'image_4_link' =>$value['image_4_link'],
			'image_5' =>$value['image_5'] ,
			'image_5_link' =>$value['image_5_link'],
			'target_ids'=>$value['target_ids'],
			'id' => $feedid,
			'idtype' => 'feed',
			'olduid'=>$value['uid'],
			'fromwhere'=>$fromwhere,
			'oldusername'=>$value['username'],
			'olddateline'=>$value['dateline'],
			'sharetofids'=>$sharetofids
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
	
	DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid );
	if($feedid1){
		DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid1 );
	}
	
	$res['success']="Y";
	$res['message']="成功！";		
}else{
	$res['success']="N";
	$res['message']="code不正确！";
}

echo json_encode($res);
?>