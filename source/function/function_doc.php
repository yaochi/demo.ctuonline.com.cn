<?php
/* Function: 知识中心接口
 * Com.:
 * Author: wuhan
 * Date: 2010-8-3
 */

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

function openFileAPI($url, $basicauth = false) {
	/*$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 30,
		)
	);
	$context = @stream_context_create($opts);*/
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	if($basicauth){
    	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    	curl_setopt($ch, CURLOPT_USERPWD, 'demo:changeit');
	}
	
	//$result =  @file_get_contents($url, false, $context);
	return curl_exec($ch);
}
/*
 * 删除文档，传入的SSO id
 */
function changeFolder($uid, $folderid = 0, $ids) {
	if (empty($uid) || empty ($ids)) {
		return;
	}
	global $_G;
	
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=changeFolder&";

	$str1 = openFileAPI($FILE_SEARCH_PAGE . "userid=$uid&folderid=$folderid&ids=" . implode(",", $ids), true);
	if (empty ($str1)) {
		return false;
	}
	
	$json =  json_decode($str1, true);
	if($json){
		return $json['return'];
	}
	else{
		return false;
	}
}
/*
 * 删除文档，传入的SSO id
 */
function deleteFiles($uid, $ids) {
	if (empty ($uid) || empty ($ids)) {
		return;
	}
	global $_G;
	
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=delete&";

	$str1 = openFileAPI($FILE_SEARCH_PAGE . "userid=$uid&ids=" . implode(",", $ids), true);
	if (empty ($str1)) {
		return false;
	}
	
	$json =  json_decode($str1, true);
	if($json){
		return $json['return'];
	}
	else{
		return false;
	}
}

/*
 * 查询文档列表
 */
function getFileList($pagesize, $page, $uids, $folderid, $fid, $manager, $orderby, $orderseq, $uploadtimefrom, $uploadtimeto, $sizemin, $sizemax, $title, $securitys) {
	$resourses = array ();
	$count = 0;

	if(is_array($uids)){
		$uids = implode(" ", $uids);
	}
	global $_G;
	$se = true;
	if(!empty($fid)||$uids==$_G[uid]){
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=search&se=db&manager=true&refer=6&";
}else{
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=search&refer=6&";
}
	$params = array();
	if(!empty($pagesize)){
		$params['pagesize'] = $pagesize;
	}
	if(!empty($page)){
		$params['currentpage'] = $page;
	}
	if(!empty($uids)){
		$params['userid'] = $uids;
	}
	if(!empty($folderid)){
		$params['folderid'] = $folderid;
	}
	if(!empty($fid)){
		$params['zoneid'] = $fid;
	}
	if(!empty($manager)){
		$params['manager'] = $manager;
	}
	if(!empty($orderby)){
		$params['orderby'] = $orderby;
	}
	if(!empty($orderseq)){
		$params['orderseq'] = $orderseq;
	}
	if(!empty($uploadtimefrom)){
		$params['uploadtimefrom'] = $uploadtimefrom;
	}
	if(!empty($uploadtimeto)){
		$params['uploadtimeto'] = $uploadtimeto;
	}
	if(!empty($sizemin)){
		$params['sizemin'] = $sizemin;
	}
	if(!empty($sizemax)){
		$params['sizemax'] = $sizemax;
	}
	if(!empty($securitys)){
		$params['privacy'] = $securitys;
	}
	if(!empty($title)){
		$params['q'] = $title;
		$se = false;
	}
	if($se){
		$params['se'] = 'db';
	}
	
	$url = 	$FILE_SEARCH_PAGE;
	foreach($params as $key => $value){
		$url .= $key ."=".$value."&";
	}
	
	$str1 = openFileAPI($url);
	if (empty ($str1)) {
		return;
	}
	return json_decode($str1, true);
}

/*
 * 查询单个文档
 * modified by fumz,2010-12-7 14:10:42,the form of data which returned by OZ was changed
 */
function getFile($id) {
	if (empty ($id)) {
		return;
	}
	global $_G;
	
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=property&";

	$str1 = openFileAPI($FILE_SEARCH_PAGE . "id=$id");
	if (empty ($str1)) {
		return;
	}

	$result=json_decode($str1, true);
	return $result['list'][0];
}

//增加评论计数
function addCommentAmount($id){
	if (empty ($id)) {
		return;
	}
	global $_G;
	//DB::query("UPDATE pre_resourcelist SET commentnum=commentnum+1 WHERE resourceid=$id");
	$FILE_URL = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=addCommentAmount&";
	openFileAPI($FILE_URL . "id=$id", true);
}

//增加收藏计数
function addFavoriteAmount($id){
	if (empty ($id)) {
		return;
	}
	global $_G;
	//DB::query("UPDATE pre_resourcelist SET favoritenum=favoritenum+1 WHERE resourceid=$id");
	$FILE_URL = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=addFavoriteAmount&";
	
	openFileAPI($FILE_URL . "id=$id", true);
}

//增加分享计数
function addShareAmount($id){
	if (empty ($id)) {
		return;
	}
	global $_G;
	//DB::query("UPDATE pre_resourcelist SET sharenum=sharenum+1 WHERE resourceid=$id");
	$FILE_URL = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=addShareAmount&";
	
	openFileAPI($FILE_URL . "id=$id", true);
}

function ckstart_max($start, $prepage){
	if($start < 0) {
		showmessage('length_is_not_within_the_scope_of');
	}
}

function HTTP_GET($url){
	$URL_Info=parse_url($url);


	$request.="GET ".$URL_Info["path"]." HTTP/1.1\r\n";
	$request.="Host: ".$URL_Info["host"]."\r\n";
	$request.="Authorization: Basic ".base64_encode('demo:changeit')."\r\n";
	$request.="Connection: close\r\n";
	$request.="\r\n\r\n";
	
	echo $request;
	
	$fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
	fwrite($fp, $request);
	while(!feof($fp)) {
		$result .= fgets($fp, 1024);
	}
	fclose($fp);

	return $result;
}
?>
