<?php
/* Function: 直播接口
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */
//function openFileAPI($url) {
//	$opts = array (
//		'http' => array (
//			'method' => 'GET',
//			'timeout' => 30,
//		)
//	);
//	$context = stream_context_create($opts);
//
//	return file_get_contents($url, false, $context);
//}

function openFileAPI($url) {
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);

	return curl_exec($ch);
}

//获取盛维（浏览器）的直播接口
// 即基础版直播
function getShengWeiUrl($sess_id, $sess_name, $sess_desc, $starttime, $endtime, $uid, $username, $firstman_ids, $secondman_ids, $guest_ids){
	global $_G;
	$head = "http://".$_G['config']['misc']['liveurlhost']."/CenwaveJoinJeeduSee.do?";
	$args = array();
	$args['site_id'] = "live";
	$time1970 = @strtotime("1970-01-01 00:00:00");
	$args['timestamp'] = $_G['timestamp'] - $time1970;
	$args['sess_id'] = $sess_id;
	$args['sess_name'] = $sess_name;
	$args['sess_desc'] = $sess_desc;
	$args['sess_start_time'] = dgmdate($starttime).":00";
	$args['sess_duration'] = ($endtime - $starttime)."000";
	$args['sess_video_size'] = "320,240";
	$args['sess_frame_rate'] = "7,64";
	$args['user_id'] = $uid;
	$args['user_name'] = user_get_user_name_by_username($username);

	$args['user_type'] = '1';

	if($guest_ids) {
		$guest_ids = explode(',', $guest_ids);
		if(in_array($_G['uid'], $guest_ids)) $args['user_type'] = '2';
	}
	if($secondman_ids) {
		$secondman_ids = explode(',', $secondman_ids);
		if(in_array($_G['uid'], $secondman_ids)) $args['user_type'] = '4';
	}
	if($firstman_ids) {
		$firstman_ids = explode(',', $firstman_ids);
		if(in_array($_G['uid'], $firstman_ids)) $args['user_type'] = '1';
	}
	$args['auth_id'] = md5("cenwave".$args['timestamp'].$args['site_id'].$args['sess_id'].$args['user_id'].$args['user_type'].$args['sess_start_time'].$args['sess_duration'].$args['sess_video_size'].$args['sess_frame_rate']);

	$url = $head;
	foreach($args as $key => $value){
		$url .= $key."=".$value."&";
	}
	$url = substr($url,0,-1);
	return $url;
}
//获取参加盛维（浏览器）的直播人数
function getShengWeiLiveCount($sess_id){
	global $_G;
	$head = "http://".$_G['config']['misc']['liveurlhost']."/CenwaveSessOnlineNum.do?";
	$args = array();
	$args['site_id'] = "live";
	$time1970 = @strtotime("1970-01-01 00:00:00");
	$args['timestamp'] = $_G['timestamp'] - $time1970;
	$args['sess_id'] = $sess_id;

	$args['auth_id'] = md5("cenwave".$args['timestamp'].$args['site_id'].$args['sess_id']);

	$url = $head;
	foreach($args as $key => $value){
		$url .= $key."=".$value."&";
	}
	$url = substr($url,0,-1);
	$request = openFileAPI($url);

	$result = explode(",", $request);

	if($result){
		return intval($result[0]);
	}
	else{
		return 0;
	}
}

//获取盛维(客户端)的直播接口
// 即专家版直播
function getShengWeiClient($mtg_key, $mtg_title, $uid, $username, $firstman_ids, $secondman_ids,$duration){
	global $_G;
	$mtg_title=implode('-',explode('&amp;',$mtg_title));
	$mtg_title=implode('-',explode('*',$mtg_title));
	$head = "http://".$_G['config']['misc']['liveclienthost']."/join_mtg_new.asp?";//alter by qiaoyz,2011-3-30
	$args = array();
	$args['SiteID'] = "cenwave";
	$args['ProductorFlag'] = "2";
	$time1970 = @strtotime("1970-01-01 00:00:00");
	$args['Timestamp'] = ($_G['timestamp'] - $time1970);
	$args['MtgTitle'] = urlencode($mtg_title);
	$args['MtgKey'] = "100000".$mtg_key;
	$args['UserID'] = $uid;
	//$args['MtgPwd'] = "";
	//$args['HostPwd'] = "";
	//$args['PhnConfID'] = "";
	$args['Timestamp']=date("Y-m-d H:i:s");//alter by qiaoyz,2011-3-30
	$args['UserName'] = urlencode(user_get_user_name_by_username($username));
	//$args['UserType'] = '1';
	if($duration){
		$args['Duration'] = $duration;
	}
	if($secondman_ids) {
		$secondman_ids = explode(',', $secondman_ids);
		if(in_array($_G['uid'], $secondman_ids)) $args['UserType'] = 2;
	}
	if($firstman_ids) {
		$firstman_ids = explode(',', $firstman_ids);
		if(in_array($_G['uid'], $firstman_ids)) $args['UserType'] = 1;
	}
	if($firstman_ids&&$secondman_ids) {
		$firstman_ids = explode(',', $firstman_ids);
		$secondman_ids = explode(',', $secondman_ids);
		if(in_array($_G['uid'], $firstman_ids)&&in_array($_G['uid'], $secondman_ids)) $args['UserType'] = 3;
	}
	if($args['UserType'] == 0){
		$args['UserType'] = 8;
	}
	$args['authid'] = md5("md5cenwavepublickey".$args['SiteID'].$args['MtgKey'].$args['UserID'].$args['UserType'].$args['Timestamp']);

	$url = $head;
	foreach($args as $key => $value){
		$url .= $key."=".$value."&";
	}
	$url = substr($url,0,-1);
	//print_r($url);exit;
	return $url;
}

//获取参加盛维（客户端）的直播人数
function getShengWeiClientCount($mtg_key){
	global $_G;
	$head = "http://".$_G['config']['misc']['liveclienthost']."/getOnlineNumByMtg.asp?";
	$args = array();
	$time1970 = @strtotime("1970-01-01 00:00:00");
	$args['timestamp'] = ($_G['timestamp'] - $time1970);
	$args['site_id'] = "cenwave";
	$args['sess_id'] = "100000".$mtg_key;
	$args['authid'] = md5("md5cenwavepublickey".$args['timestamp'].$args['site_id'].$args['sess_id']);

	$url = $head;
	foreach($args as $key => $value){
		$url .= $key."=".$value."&";
	}
	$url = substr($url,0,-1);
	$request = openFileAPI($url);

	$result = explode(",", $request);
	//print_r($result);exit;
	if($result){
		return intval($result[0]);
	}
	else{
		return 0;
	}
}

//get newliveurl create 2011.08.11
function getNewLiveUrl($newliveid,$fid,$videotype = 1){
	global $_G;
	$liveurl = null;
	if($newliveid && $fid){
		$liveurl = $_G[config][expert][liveurl]."/live/beforelive.do?liveid=".$newliveid;
	}
	return $liveurl;
}

?>
