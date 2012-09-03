<?php
/* Function: 转发微博接口
 * Com.:
 * Author: caimm
 * Date: 2012-4-24
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();


$discuz->init();
$feedid=$_G["gp_feedid"];
$transpondContent=$_G["gp_transpondContent"];
$uid=$_G["gp_uid"];
$username=$_G["gp_username"];
$fromwhere=$_G["gp_fromwhere"];
$anonymity=$_G['gp_anonymity'];
$code=$_G["gp_code"];

if(md5("esn".$feedid.$transpondContent.$uid.$username)==$code){
	if(!$username){
		$username=user_get_user_name($uid);
	}
	$atarr=parseat($transpondContent,$uid);
	if($atarr['atfids']){
		$sharetofids=",".implode(',',$atarr['atfids']).",";
	}

	$value=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if ($value['anonymity']) {
		if ($value['anonymity'] == -1) {
			$value['user']['uid'] = -1;
			$value['user']['username'] = '匿名';
			$value['user']['iconImg'] = useravatar(-1);
		} else {
			include_once libfile('function/repeats', 'plugin/repeats');
			$repeatsinfo = getforuminfo($value['anonymity']);
			$value['user']['uid'] = $repeatsinfo['fid'];
			$value['user']['username'] = $repeatsinfo['name'];
			if ($repeatsinfo['icon']) {
				$value['ficon'] = $_G['config']['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
			} else {
				$value['ficon'] = $_G['config']['image']['url'] . '/static/image/images/def_group.png';
			}
			$value['user']['iconImg'] = $value['ficon'];
		}
	} else {
		$value['user']['uid'] = $value['uid'];
		$value['user']['iconImg'] = useravatar($value['uid']);
		$value['user']['username'] = user_get_user_name($value['uid']);
	}
	if($value['idtype']=='feed'){
		$feedid1=$feedid;
		$feedid=$value['id'];
		$atarr[message]=$atarr[message].'//<a class="perPanel xw1" target="_blank" href="home.php?mod=space&uid='.$value[user][uid].'">@'.$value[user][username].'</a>:'.$value[body_general];
	}else{
		if($value['icon']=='forward'){
			$atarr[message]=$atarr[message].'//<a class="perPanel xw1" target="_blank" href="home.php?mod=space&uid='.$value[user][uid].'">@'.$value[user][username].'</a>:'.$value[body_general];
		}
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
			'sharetofids'=>$sharetofids,
			'anonymity'=>$anonymity
		);
		$feedarr=daddslashes($feedarr);

		DB::insert('home_feed', $feedarr);
		DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$uid);
		$newfeedid=DB::insert_id();

	if ($anonymity) {
		if ($anonymity == -1) {
			$user['user']['uid'] = -1;
			$user['user']['username'] = '匿名';
			$user['user']['iconImg'] = useravatar(-1);
		} else {
			include_once libfile('function/repeats', 'plugin/repeats');
			$repeatsinfo = getforuminfo($anonymity);
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
				DB::query("insert into pre_common_user_at(uid,feedids) values(".$atarr[atuids][$uidkey].",$newfeedid)");
			}else if($_id[feedids]==null){
				DB::query("update ".DB::TABLE("common_user_at")." set feedids='".$newfeedid."' where uid=".$atarr[atuids][$uidkey]);
			}else{
				$_id[feedids].=",".$newfeedid;
				DB::query("update ".DB::TABLE("common_user_at")." set feedids='".$_id[feedids]."' where uid=".$atarr[atuids][$uidkey]);
			}
		}
	}
	if($value[uid]!=$uid){
		notification_add($value[uid],"zq_at",'<a href="home.php?mod=space&uid='.$user['user']['uid'].'" target="_block">“'.$user['user']['username'].'”</a> 刚刚<a href="home.php?view=atme">转发了您的内容</a>', array(), 0);
		$_ids=DB::result_first("select feedids from pre_common_user_at where uid=".$value[uid]);
		$_ids.=",".$newfeedid;
		DB::query("update ".DB::TABLE("common_user_at")." set feedids='".$_ids."' where uid=".$value[uid] );
	}
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