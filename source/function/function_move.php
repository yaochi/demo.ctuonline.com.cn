<?php
/* Function: 移动学习接口
 * 
 * Author: wujunjun
 * Date: 2011-6-23
 * */



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

//查询资源类别
function getcategory_find_by_parent($parent_category_id){
	global $_G;
	$key='1234567890';
	$timestamp=$_G['timestamp']*1000;
	 $sign=md5($parent_category_id.$timestamp.$key);
	 $url=" http://test.bestlinks.com.cn/market-i4s-web/i4s/category_find_by_parent.txt?timestamp=".$timestamp."&parent_category_id=".$parent_category_id."&sign=".$sign;
	 $str1 = openFileAPI($url);
	 if (empty ($str1)) {
		return;
	}
	return json_decode($str1, true);
}

//资源搜索
function getresource_find_by_keyword($keyword,$page_index,$page_size){
     global $_G;
        $params=array();
		 $timestamp=$_G['timestamp']*1000;
		 $key='1234567890';
		 $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_by_keyword?timestamp=".$timestamp."&";
      if(!empty($keyword)){	
			$params['keyword'] = $keyword;
		}
		if(!empty($page_index)){
			$params['page_index'] = $page_index;
		}
		if(!empty($page_size)){
			
			$params['page_size'] = $page_size;
		} 
	    $params['sign']=md5($keyword.$params[page_index].$params[page_size].$timestamp.$key);
		$url=$FILE_SEARCH_PAGE;
		foreach($params as $key => $value){
			$url .= $key ."=".$value."&";
		}
		$str1 = openFileAPI($url);
		 if (empty ($str1)) {
			return;
		}
		return json_decode($str1, true);
	}

	//查询最新资源接口
	function getresource_find_lastest($category_id,$page_index,$page_size){
	global $_G;
	 $timestamp=$_G['timestamp']*1000;
	 $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_lastest?timestamp=".$timestamp."&";
	
	 $key='1234567890';
	if(!empty($category_id)){	
	$params['category_id']=$category_id;
	}
	if(!empty($page_index)){
		$params['page_index'] = $page_index;
	}
	if(!empty($page_size)){	
		$params['page_size'] = $page_size;
	}
	 $params['sign']=md5($category_id.$params[page_index].$params[page_size].$timestamp.$key);
     $url=$FILE_SEARCH_PAGE;
     foreach($params as $key => $value){
	$url .= $key ."=".$value."&";
	}
	$str1 = openFileAPI($url);
	 if (empty ($str1)) {
		return;
	}
	return json_decode($str1, true);
	}
	
	//查询热门资源
	function getresource_find_hot($category_id,$page_index,$page_size){	
	global $_G;
	 $timestamp=$_G['timestamp']*1000;
	 $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_hot?timestamp=".$timestamp."&";
	 $key='1234567890';
	if(!empty($category_id)){	
	$params['category_id']=$category_id;
	}
	if(!empty($page_index)){
		$params['page_index'] = $page_index;
	}
	if(!empty($page_size)){	
		$params['page_size'] = $page_size;
	}
	 $params['sign']=md5($category_id.$params[page_index].$params[page_size].$timestamp.$key);
     $url=$FILE_SEARCH_PAGE;
     foreach($params as $key => $value){
	$url .= $key ."=".$value."&";
	}

	$str1 = openFileAPI($url);
	 if (empty ($str1)) {
		return;
	}

	return json_decode($str1, true);
	}
	
	//查询推荐资源
	function getresource_find_recommended($category_id,$page_index,$page_size){
	 global $_G;
	 $timestamp=$_G['timestamp']*1000;//获取字符戳的字符串格式
     $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_recommended.txt?timestamp=".$timestamp."&";
	 $key='1234567890';
	 $params = array();
	if(!empty($category_id)){	
		$params['category_id'] =$category_id;
	}
	if(!empty($page_index)){
	
		$params['page_index'] = $page_index;
	}
	if(!empty($page_size)){
		
		$params['page_size'] = $page_size;
	}
	$params['sign']=md5($category_id.$params[page_index].$params[page_size].$timestamp.$key);
	$url=$FILE_SEARCH_PAGE;
	  foreach($params as $key => $value){
	 $url .= $key ."=".$value."&";
	}
	$str1 = openFileAPI($url);

	if(empty($str1)){
		return;
	}
	return json_decode($str1,true);
	}
	//查询评价星级资源
	function getresource_find_stars($category_id,$page_index,$page_size){
	 global $_G;
	 $timestamp=$_G['timestamp']*1000;//获取字符戳的字符串格式	
	 $params = array();
     $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_stars.txt?timestamp=".$timestamp."&";
	 $key='1234567890';
	if(!empty($category_id)){	
	$params['category_id'] = $category_id;
	}
	if(!empty($page_index)){
		$params['page_index'] = $page_index;
	}
	if(!empty($page_size)){
		$params['page_size'] = $page_size;
	}
	$params['sign']=md5($category_id.$params[page_index].$params[page_size].$timestamp.$key);
	$url=$FILE_SEARCH_PAGE;
	foreach($params as $key => $value){	
		$url.=$key."=".$value."&";
	}
	
	$str1 = openFileAPI($url);

	if(empty($str1)){
		return $str1;	
	}
	return json_decode($str1,true);
	}
	
	//查询资源详情
	function getresource_find_by_id($resource_id,$version){
	 global  $_G;
	 $timestamp=$_G['timestamp']*1000;
	 $FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_find_by_id?timestamp=".$timestamp."&";
	 $key='1234567890';
	 $params=array();
	 if(!empty($resource_id)){
	 	$params['resource_id'] =$resource_id;
	}
	if(!empty($version)){
		$params['version']=$version;
	}
	$url = 	$FILE_SEARCH_PAGE;
	$params['sign']=md5($resource_id.$version.$timestamp.$key);
	foreach ($params as $key => $value){
		$url.=$key."=".$value."&";
	}
	$str1=openFileAPI($url);
	if(empty($str1)){
		return ;
	}

		return  json_decode($str1,true);
	}
	//获取内容资源下载
	function getresource_download_url($resource_id,$version,$user_id){
	global  $_G;
	$timestamp=$_G['timestamp']*1000;
	$FILE_SEARCH_PAGE="http://test.bestlinks.com.cn/market-i4s-web/i4s/resource_download_url?timestamp=".$timestamp."&";
	$key='1234567890';
	$params=array();
	if(!empty($resource_id)){
		$params['resource_id'] = $resource_id;
	}
	if(!empty($version)){
		$params['version']=$version;
	}
	if(!empty($user_id)){
		$params['user_id']=$user_id;
	}
	$url=$FILE_SEARCH_PAGE;
	$params['sign']=md5($resource_id.$params[version],$params[user_id].$timestamp.$key);
	foreach ($params as $key => $value){
		$url.=$key."=".$value."&";
	}

	$str1=openFileAPI($url);
	if(empty($str1)){
		return ;
	}
	return json_decode($str1,true);
	}
?>