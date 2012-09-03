<?php

function topnewCourses($count) {
	global $_G;
	$cache_key = "group_top_diy_newcourse";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}

	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?privacy=1&type=4&orderby=uploadtime&pagesize=".$count."&currentpage=1";
	$str1 = openFileAPI($FILE_SEARCH_PAGE);
	$result = array(json_decode($str1, true));
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$result[0]['resources']);		
}

function topnewDocs($count) {
	global $_G;
	$cache_key = "group_top_diy_newdoc";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}

	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?privacy=1&type=1&orderby=uploadtime&pagesize=".$count."&currentpage=1";
	$str1 = openFileAPI($FILE_SEARCH_PAGE);
	$result = array(json_decode($str1, true));
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$result[0]['resources']);		
}
//资源列表
/**
 * 最新三门网大课程,公开课程
 * 这里仅仅指公开的资源：王聪
 */
function top3newCourses() {
	global $_G;
	$cache_key = "portal_index_top3newCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	
	
	
	
	$i=1;
	$j=1;	
	$processData=array();
	
	while(($i<=3)&&($j<=10)){
		$pagesize=5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?privacy=1&type=4&orderby=uploadtime&refer=1&pagesize=".$pagesize."&currentpage=".$j;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		
		//获得被屏蔽的
		foreach ($result['resources'] as $key=>$row){
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result['resources'] as $key=>$row) {
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[课程]';
					$ret['newcourse'.$i]=$row;								
					$i++;
					if($i>3){
						break;
					}	
				}
				
			}
			
		}
		
		
		
		
		/*
		
		foreach ($result['resources'] as $key=>$row) {
			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[课程]';
					$ret['newcourse'.$i]=$row;								
					$i++;
					if($i>3){
						break;
					}	
				}	
			}
					
		}
		*/
		
	}
	
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
	
//    $FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=top3newCourses";
//	$str1 = openFileAPI($FILE_SEARCH_PAGE);
//	if (empty ($str1)) {
//		return;
//	}
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 1200);
//	return array("result"=>$result);
}
/**
 * 最新三门官方文档
 * 这里仅仅指公开的资源：王聪
 */
function top3newSNSDocs() {
	global $_G;
	$cache_key = "portal_index_top3newSNSDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	
	$i=1;
	$j=1;	
	$processData=array();

	while(($i<=2)&&($j<=10)){
		$pagesize=5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?privacy=1&type=1&orderby=uploadtime&refer=1&pagesize=".$pagesize."&currentpage=".$j;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		//print_r ($result['resources']);
		
		
		
		//获得被屏蔽的
		foreach ($result['resources'] as $key=>$row){
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result['resources'] as $key=>$row) {
		
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[文档]';
					$ret['newsnsdoc'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}
		
		
		/*
		foreach ($result['resources'] as $key=>$row) {
			//print_r ("----++key+-----".$key);	
			//print_r ("----+++-----".$row['id']);
			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[文档]';
					$ret['newsnsdoc'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}
		*/
		
	}
	
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
	
//	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=top3newDocs";
//	//$param = "&type=4&currentpage=1&pagesize=2&orderby=uploadtime&orderseq=2";
//	$str1 = openFileAPI($FILE_SEARCH_PAGE);
//	if (empty ($str1)) {
//		return;
//	}
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 1200);
//	return array("result"=>$result);
	
}

/**
 * 最新三门网大案例
 * 这里仅仅指公开的资源：王聪
 */
function top3newCases() {
	global $_G;
	$cache_key = "portal_index_top3newCases";
	$cache = memory("get", $cache_key);
	
	if(!empty($cache)){		
		return unserialize($cache);
	}
	
	
	$i=1;
	$j=1;	
	$processData=array();
	
	while(($i<=2)&($j<10)){
		$pagesize=5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?privacy=1&type=2&orderby=uploadtime&refer=1&pagesize=".$pagesize."&currentpage=".$j;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		
		
		
	//获得被屏蔽的
		foreach ($result['resources'] as $key=>$row){
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result['resources'] as $key=>$row) {			
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[案例]';
					$ret['newcase'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}
		
		
		/*
		foreach ($result['resources'] as $key=>$row) {			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[案例]';
					$ret['newcase'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}	
		*/	
	}
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
//	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?";
//	$param = "type=2&orderby=uploadtime&pagesize=2";
//	$url = $FILE_SEARCH_PAGE.$param;
//	$str1 = openFileAPI($url);
//	if (empty ($str1)) {
//		return;
//	}
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 3600);
//	return array("result"=>$result);
	
}

/**
 * 最热三门网大课程
 */
function top3hotCourses() {
	global $_G;
	$cache_key = "portal_index_top3hotCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	
	$i=1;
	$j=1;	
	$processData=array();
	
	while(($i<=3)&($j<=10)){
		$pagesize=$j*5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotcourses&n=".$pagesize;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		
		
		foreach ($result as $key=>$row) {
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result as $key=>$row) {		
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[课程]';
					$ret['hotcourse'.$i]=$row;								
					$i++;
					if($i>3){
						break;
					}	
				}	
			}
					
		}
		
		
		/*
		foreach ($result as $key=>$row) {			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[课程]';
					$ret['hotcourse'.$i]=$row;								
					$i++;
					if($i>3){
						break;
					}	
				}	
			}
					
		}	
		*/	
	}
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
//	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotcourses&n=3";
//	//$param = "&currentpage=1&pagesize=3&type=4&orderby=read&orderseq=2";
//	$url = $FILE_SEARCH_PAGE;
//	$str1 = openFileAPI($url);
//	if (empty ($str1)) {
//		return;
//	}
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 3600);
//	return array("result"=>$result);
}



/**
 * 最热三门社区文档
 */
function top3hotSNSDocs() {
	global $_G;
	$cache_key = "portal_index_top3hotSNSDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	
	
	
	
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotsnsdocs&n=2";
	//$param = "type=1&orderby=uploadtime&pagesize=3&privacy=1&refer=6";
	$url = $FILE_SEARCH_PAGE;
	$str1 = openFileAPI($url);
	if (empty ($str1)) {
		return;
	}
	$result = json_decode($str1, true);
	memory("set", $cache_key, serialize($result), 3600);
	return array("result"=>$result);
}

/**
 * 最热三门网大案例
 */
function top3hotCases() {
	global $_G;
	$cache_key = "portal_index_top3hotCases";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	$i=1;
	$j=1;	
	$processData=array();
	
	while(($i<=2)&($j<=10)){
		$pagesize=$j*5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotcases&n=".$pagesize;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		
		
		foreach ($result as $key=>$row) {
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result as $key=>$row) {		
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[案例]';
					$ret['hotcase'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}
		
		
		
		
		/*
		foreach ($result as $key=>$row) {			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[案例]';
					$ret['hotcase'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}	
		*/	
	}
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
//	
//	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotcases&n=2";
//	//$param = "&currentpage=1&pagesize=3&type=2&refer=1&orderby=read&orderseq=2";
//	$url = $FILE_SEARCH_PAGE;
//	$str1 = openFileAPI($url);
//	if (empty ($str1)) {
//		return;
//	}
//	
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 3600);
//	return array("result"=>$result);
}

/**
 * 最热三门官方文档
 */
function top3hotDocs() {
	global $_G;
	$cache_key = "portal_index_top3hotDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	
	$i=1;
	$j=1;	
	$processData=array();
	
	while(($i<=2)&($j<=10)){ //修改 by songsp  2011-3-17 10:04:22
		$pagesize=$j*5;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotdocs&n=".$pagesize;
		$j++;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			break;
		}
		$result = json_decode($str1, true);
		
		
		foreach ($result as $key=>$row) {
			$tmpids[] = $row['id'];
		}
		$tmpignoreids = listIgnore($tmpids,'resourcelist');
		foreach ($result as $key=>$row) {		
			if(!in_array($row,$processData)){
				if(!in_array($row['id'],$tmpignoreids)){//没有在屏蔽ids中
					$processData[]=	$row;			
					$row['fortitle']='[文档]';
					$ret['hotdoc'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}
		
		/*
		foreach ($result as $key=>$row) {			
			if(!in_array($row,$processData)){
				if(portalIsignore($row['id'],'resourcelist')==  1){
					$processData[]=	$row;			
					$row['fortitle']='[文档]';
					$ret['hotdoc'.$i]=$row;								
					$i++;
					if($i>2){
						break;
					}	
				}	
			}
					
		}	
		*/	
	}
	memory("set", $cache_key, serialize($ret), 1200);
	return array("result"=>$ret);
	
	
//	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=ranks&ranktype=hotdocs&n=2";
//	//$param = "&currentpage=1&pagesize=3&type=1&refer=1&orderby=read&orderseq=2";
//	$url = $FILE_SEARCH_PAGE;
//	$str1 = openFileAPI($url);
//	if (empty ($str1)) {
//		return;
//	}
//	
//	$result = json_decode($str1, true);
//	memory("set", $cache_key, serialize($result), 3600);
//	return array("result"=>$result);
}

function openFileAPI($url) {
	/*$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 30,
		)
	);
	$context = @stream_context_create($opts); 
	
	$result =  file_get_contents($url, false, $context);
	return $result;*/
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	return curl_exec($ch);
}

function getNewResourceList() {
	/*$newlist = array();
	$query=DB::query("SELECT fid from ".DB::TABLE("forum_forumfield")." where gviewperm='1'");
	//获取首页资源列表
	//======最新列表======
	//最新课程
	$cache_key = "portal_index_top3newCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newcourse = unserialize($cache);
	} else {
		$top3newCourses = top3newCourses();
		foreach ($top3newCourses['result'] as $row) {
			$newcourse = $row;
			break;
		}
		memory("set", $cache_key, serialize($newcourse), 1200);
	}
	//最新社区文档
	$cache_key = "portal_index_top3newSNSDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newsnsdoc = unserialize($cache);
	} else {
		$top3newSNSDocs = top3newSNSDocs();
		foreach ($top3newSNSDocs['result']['resources'] as $row) {
			$newsnsdoc = $row;
			break;
		}
		memory("set", $cache_key, serialize($newsnsdoc), 1200);
	}
	//最新网大案例
	$cache_key = "portal_index_top3newCases";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newcase = unserialize($cache);
	} else {
		$top3newCases = top3newCases();
		foreach ($top3newCases['result']['resources'] as $row) {
			$newcase = $row;
			break;
		}
		memory("set", $cache_key, serialize($newcase), 1200);
	}
	//最新直播
	$cache_key = "portal_index_newlive";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newlive = unserialize($cache);
	} else {
		$query = DB::query("SELECT * FROM ".DB::table("group_live")." gl ,".DB::table("forum_forumfield")." fff WHERE gl.fid=fff.fid AND fff.gviewperm='1' ORDER BY gl.dateline DESC LIMIT 1");
		$newlive = DB::fetch($query);
		memory("set", $cache_key, serialize($newlive), 1200);
	}
	//最新你我课堂
	$cache_key = "portal_index_newnwkt";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newnwkt = unserialize($cache);
	} else {
		$query = DB::query("SELECT * FROM ".DB::table("home_nwkt")." where type='0' ORDER BY starttime DESC LIMIT 1");
		$newnwkt = DB::fetch($query);
		memory("set", $cache_key, serialize($newnwkt), 1200);
	}
	//最新话题
	$cache_key = "portal_index_newtopic1";
	$cache_key2 = "portal_index_newtopic2";
	$cache = memory("get", $cache_key);
	$cache2 = memory("get", $cache_key2);
	if(!empty($cache)){
		$newtopic1 = unserialize($cache);
		$newtopic2 = unserialize($cache2);
	} else {
		$topics = DB::query("SELECT * FROM ".DB::table("forum_thread")." ft,".DB::table("forum_forumfield")." fff WHERE special=0 AND ft.fid=fff.fid AND fff.gviewperm='1' ORDER BY ft.dateline DESC LIMIT 2");
		while ($row = DB::fetch($topics)) {
			//1
			if (!$newtopic1) {
				$newtopic1 = $row;
				continue;
			}
			//2
			if (!$newtopic2) {
				$newtopic2 = $row;
				continue;
			}
		}
		memory("set", $cache_key, serialize($newtopic1), 1200);
		memory("set", $cache_key2, serialize($newtopic2), 1200);
	}
	//最新活动
	$cache_key = "portal_index_newactivity";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newactivity = unserialize($cache);
	} else {
		$query = DB::query("SELECT * FROM ".DB::table("forum_forum")." ff,".DB::table("forum_forumfield")." fff WHERE type='activity' AND ff.fid=fff.fid AND fff.gviewperm='1' ORDER BY ff.fid DESC LIMIT 1");
		$newactivity = DB::fetch($query);
		memory("set", $cache_key, serialize($newactivity), 1200);
	}
	
	$newlist["newcourse"] = $newcourse;
	$newlist["newsnsdoc"] = $newsnsdoc;//最新需求中改为最新官方文档，待知识中心相关接口完成后修改
	//增加最新案例
	$newlist["newcase"] = $newcase;
	$newlist["newlive"] = $newlive;
	$newlist["newnwkt"] = $newnwkt;
	$newlist["newtopic1"] = $newtopic1;
	$newlist["newtopic2"] = $newtopic2;
	$newlist["newactivity"] = $newactivity;*/
	
	
	$newlist = array();
	//获取首页资源列表
	//======最新列表======
	//最新课程
	$cache_key = "portal_index_top3newCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3newCourses=unserialize($cache);
	}else{
		$top3newCourses = top3newCourses();
		memory("set", $cache_key, serialize($top3newCourses), 1200);
	}
	$i=1;
	foreach ($top3newCourses['result'] as $key=>$row) {
		//if(portalIsignore($row['id'],'resourcelist')==  1){
			$row['fortitle']='[课程]';
			$newlist['newcourse'.$i]=$row;
		//}
		
		$i=$i+1;
		}
	//最新社区文档
	$cache_key = "portal_index_top3newSNSDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3newSNSDocs=unserialize($cache);
	}else{
		$top3newSNSDocs = top3newSNSDocs();
		memory("set", $cache_key, serialize($top3newSNSDocs), 1200);
	}
	$i=1;
	foreach ($top3newSNSDocs['result'] as $key=>$row) {
		//if(portalIsignore($row['id'],'resourcelist')==  1){
					$row['fortitle']='[文档]';
					$newlist['newsnsdoc'.$i]=$row;		
		//}

		$i=$i+1;
		if($key==1)break;
	}
	//最新网大案例
	$cache_key = "portal_index_top3newCases";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3newCases=unserialize($cache);
	}else{
		$top3newCases = top3newCases();
		memory("set", $cache_key, serialize($top3newCases), 1200);
	}
	$i=1;
	foreach ($top3newCases['result'] as $key=>$row) {
		
		//if(portalIsignore($row['id'],'resourcelist')==  1){
			$row['fortitle']='[案例]';
			$newlist['newcase'.$i]=$row;
		//}
		
		$i=$i+1;
	}
	return $newlist;
}

function getHotResourceList() {
	/*$hotlist = array();
	//30天
	$startTime = time() - 60*60*24*30;
	//======最热列表======
	//最热课程
	$cache_key = "portal_index_top3hotCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hotcourse = unserialize($cache);
	} else {
		$top3hotCourses = top3hotCourses();
		foreach ($top3hotCourses['result'] as $row) {
			$hotcourse = $row;
			break;
		}
		memory("set", $cache_key, serialize($hotcourse), 3600);
	}
	//最热官方文档
	$cache_key = "portal_index_top3hotDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hotdoc = unserialize($cache);
	} else {
		$top3hotDocs = top3hotDocs();
		foreach ($top3hotDocs['result'] as $row) {
			$hotdoc = $row;
			break;
		}
		memory("set", $cache_key, serialize($hotdoc), 3600);
	}
	//最热案例
	$cache_key = "portal_index_top3hotCases";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hotcase = unserialize($cache);
	} else {
		$top3hotCases = top3hotCases();
		foreach ($top3hotCases['result'] as $row) {
			$hotcase = $row;
			break;
		}
		memory("set", $cache_key, serialize($hotcase), 3600);
	}
	//最热社区文档
	$cache_key = "portal_index_top3hotSNSDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hotsnsdoc = unserialize($cache);
	} else {
		$top3hotSNSDocs = top3hotSNSDocs();
		foreach ($top3hotSNSDocs['result'] as $row) {
			$hotsnsdoc = $row;
			break;
		}
		memory("set", $cache_key, serialize($hotsnsdoc), 3600);
	}
	//最热活动——增加30天内限制
	$cache_key = "portal_index_hotactivity1";
	$cache_key2 = "portal_index_hotactivity2";
	$cache = memory("get", $cache_key);
	$cache2 = memory("get", $cache_key2);
	if(!empty($cache)){
		$hotactivity1 = unserialize($cache);
		$hotactivity2 = unserialize($cache2);
	} else {
		//最热活动1
		$activity1 = DB::query("SELECT * FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff WHERE ff.type='activity' AND ff.fid=fff.fid AND fff.gviewperm='1' AND fff.dateline > ".$startTime." ORDER BY fff.membernum DESC LIMIT 1");
		while ($row = DB::fetch($activity1)) {
			$hotactivity1 = $row;
		}
		//最热活动2——修改为活动中最热的帖子
		$activity2 = DB::query("SELECT ft.* FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_thread")." ft, ".DB::table("forum_forumfield")." fff  WHERE ff.type='activity' AND ff.fid=ft.fid AND fff.fid=ff.fid AND fff.gviewperm='1' AND ft.dateline > ".$startTime." AND ft.special=0 ORDER BY ft.replies DESC LIMIT 1");
		while ($row = DB::fetch($activity2)) {
			$hotactivity2 = $row;
		}
		memory("set", $cache_key, serialize($hotactivity1), 3600);
		memory("set", $cache_key2, serialize($hotactivity2), 3600);
	}
	//最热专区
	$cache_key = "portal_index_hotforum";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hotforum = unserialize($cache);
	} else {
		$query = DB::query("SELECT ff.fid fid, ff.name name, (ff.threads*0.4+ff.posts*0.3+fff.membernum*0.1) hot FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff WHERE ff.type='sub' AND ff.fid=fff.fid AND fff.gviewperm='1' ORDER BY hot DESC LIMIT 1");
		$hotforum = DB::fetch($query);
		memory("set", $cache_key, serialize($hotforum), 3600);
	}
	//最热话题——增加30天内限制
	$cache_key = "portal_index_hottopic";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$hottopic = unserialize($cache);
	} else {
		$query = DB::query("SELECT ft.* FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_thread")." ft, ".DB::table("forum_forumfield")." fff  WHERE ff.type='sub' AND ff.fid=ft.fid AND fff.fid=ff.fid AND fff.gviewperm='1' AND ft.dateline > ".$startTime." AND ft.special=0 ORDER BY ft.replies DESC LIMIT 1");
		$hottopic = DB::fetch($query);
		memory("set", $cache_key, serialize($hottopic), 3600);
	}
	$hotlist["hotcourse"] = $hotcourse;
	$hotlist["hotdoc"] = $hotdoc;
	$hotlist["hotcase"] = $hotcase;
	//最新需求中新增最热社区文档，待知识中心相关接口完成后增加
	$hotlist["hotsnsdoc"] = $hotsnsdoc;
	$hotlist["hotactivity1"] = $hotactivity1;
	$hotlist["hotactivity2"] = $hotactivity2;
	$hotlist["hotforum"] = $hotforum;
	$hotlist["hottopic"] = $hottopic;*/
	$hotlist = array();
	//获取首页资源列表
	//======最新列表======
	//最新课程
	//最热课程
	$cache_key = "portal_index_top3hotCourses";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3hotCourses=unserialize($cache);
	}else{
		$top3hotCourses = top3hotCourses();
		memory("set", $cache_key, serialize($top3hotCourses), 1200);
	}

	$i=1;
	foreach ($top3hotCourses['result'] as $row) {
		//if(portalIsignore($row['id'],'resourcelist')==  1){
			$row['fortitle']='[课程]';		
		    $hotlist['hotcourse'.$i]=$row;
		//}
		
		$i=$i+1;
	}
	//最热官方文档
	$cache_key = "portal_index_top3hotDocs";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3hotDocs=unserialize($cache);
	}else{
		$top3hotDocs = top3hotDocs();
		memory("set", $cache_key, serialize($top3hotDocs), 1200);
	}
	$i=1;
	foreach ($top3hotDocs['result'] as $row) {
		//if(portalIsignore($row['id'],'resourcelist')==  1){
			$row['fortitle']='[文档]';		
		    $hotlist['hotdoc'.$i]=$row;
		//}
		
		$i=$i+1;
	}
	//最热案例
	$cache_key = "portal_index_top3hotCases";
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$top3hotCases=unserialize($cache);
	}else{
		$top3hotCases = top3hotCases();
		memory("set", $cache_key, serialize($top3hotCases), 1200);
	}
	$i=1;
	foreach ($top3hotCases['result'] as $row) {
	      //if(portalIsignore($row['id'],'resourcelist')==  1){
			$row['fortitle']='[案例]';		
		    $hotlist['hotcase'.$i]=$row;
		// }
		
		$i=$i+1;
	}
	return $hotlist;
}

	/*
	 * 判断内容是否屏蔽
	 * $contentid  内容的id
	 * $contenttype 内容的类型
	 * 如果已经屏蔽了，那么返回-1，否则返回1
	 * //if(portalIsignore($row['id']),'hotcase')==1){
			
		//}
	 */
	function portalIsignore($contentid,$contenttype){
		//print_r($contentid."---contentid");
		//print_r($contenttype."----contenttype");
	    $ignore = DB::fetch_first(" SELECT * FROM ".DB::table('protal_ignore')." WHERE contentid=".$contentid." AND contenttype='".$contenttype."'");
		
	    if($ignore) {
			return -1;
		}
		return 1;
	}
	
	/*
	 * 判断内容是否屏蔽,返回被屏蔽的内容id
	 * $contentid  内容的ids
	 * $contenttype 内容的类型
	 */
	function listIgnore($contentids , $contenttype){
		
		if(!$contentids){
			return null;
		}
		
		$query = DB::query(" SELECT contentid FROM ".DB::table('protal_ignore')." WHERE contentid in (".dimplode($contentids).")  AND contenttype='".$contenttype."'");
		$ignores = array();
		while ($row = DB::fetch($query)) {
			$ignores[] = $row["contentid"];
		}
		return $ignores;	
	}

?>
