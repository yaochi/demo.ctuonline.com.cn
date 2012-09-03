<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
	$fid = $_G["fid"];
	if (!$fid) {
		showmessage('请在专区中使用该组件', join_plugin_action('index'));
	}
	
	$ftype = $_GET["typeid"];
	$rtype = $_GET["rtype"];
	$timerange = $_GET["timerange"];
	$orderby = $_GET["orderby"];
	$orderseq = $_GET["orderseq"];
	
	$addsql = "";
	$addurl = "";
	if ($ftype) {
		$ftypesql = " AND fcategoryid=".$ftype;
		$ftypeurl = "&typeid=".$ftype;
	}
	if ($rtype) {
		$rtypesql = " AND typeid=".$rtype;
		$rtypeurl = "&rtype=".$rtype;
	}
	if ($timerange) {
		if ($timerange==1) {
			$tto = time();
			$tfrom = strtotime("+1 day"); 
			$tto -= (($tto+3600*8)%(3600*24));
			$tfrom -= (($tfrom+3600*8)%(3600*24));
			$timerangesql = " AND uploaddate BETWEEN ".$tfrom." AND ".$tto;
		} elseif ($timerange==2) {
			$tto = time();
			$mondaytime = $tto - ($tto + 86400 * 3 + 3600 * 8) % (7 * 86400);
			$timerangesql = " AND uploaddate BETWEEN ".$mondaytime." AND ".$tto;
		} elseif ($timerange==3) {
			$tto = time();
			$tfrom = $tto - (30 * 86400);
			$timerangesql = " AND uploaddate BETWEEN ".$tfrom." AND ".$tto;
		}
	}
	if ($orderby) {
		$orderbyurl = "&orderby=".$orderby;
		$orderby = " , ".$orderby;
		if (!$orderseq) {
			$orderby .= " DESC";
		}
	}else{
		$orderby = " , uploaddate";
		if (!$orderseq) {
			$orderby .= " DESC";
		}
	}
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
    $query = DB::query("SELECT * FROM ".DB::table("resourcelist")." WHERE fid=".$fid.$rtypesql.$ftypesql.$timerangesql." ORDER BY displayorder DESC".$orderby." LIMIT $start,$perpage ");
    $resources = array();
    $_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
    $rids=array();
	while ($resource = DB::fetch($query)) {
	    if($resource['highlight']) {
			$string = sprintf('%02d', $resource['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			
			$resource['highlight'] = ' style="';
			$resource['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$resource['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
			$resource['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
			$resource['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
			$resource['highlight'] .= '"';
		} else {
			$resource['highlight'] = '';
		}
		
		//分享
		$params['id'] = $resource['id'];
		$params['subject'] = base64_encode($resource['title']);
		$params['subjectlink'] = base64_encode($resource['titlelink']);
		$params['authorid'] = base64_encode("");
		$params['author'] = base64_encode("");
		$params['message'] = base64_encode($resource['about']);
		$params['image'] = base64_encode($resource['imglink']);
		$params['type'] = "resourceid";
		$params['fid'] = $resource['fid'];
		
		foreach($params as $key => $value){
			$url .= $key ."=".$value."&";
		}
		
		$resource['shareurl'] = "home.php?mod=spacecp&ac=share&handlekey=sharehk_1&".$url;
		
		$rid=$resource['resourceid'];
		$resources[] = $resource;
		$rids[]=$rid;
    }
    $filejson = getresources($rids);
   foreach($filejson['result']['list'] as $value){
		foreach($resources as $key=>$idvalue){
			if($idvalue['resourceid']==$value[id]){
				$idvalue['readnum']=$value[readnum];
				$idvalue['sharenum']=$value[sharenum];
				$idvalue['favoritenum']=$value[favoritenum];
				$idvalue['commentnum']=$value[commentnum];
				$idvalue['downloadnum']=$value[downloadnum];
				$idvalue['averagescore']=$value[averagescore];    
			}
			$resources[$key]=$idvalue;
		}
	}
    //print_r($resourcelist);
   /* foreach($resourcelist as $key=>$value){
    	$temprid=$key;
    	$resources[$temprid]['readnum']=$value[readnum];    	
    	$resources[$temprid]['sharenum']=$value[sharenum];
	    $resources[$temprid]['favoritenum']=$value[favoritenum];
	    $resources[$temprid]['commentnum']=$value[commentnum];
	    $resources[$temprid]['downloadnum']=$value[downloadnum];
	    $resources[$temprid]['averagescore']=$value[averagescore];    
		
    }*/
    /*
     * 快速排序
     */
     
    $sortarr=array();
    foreach($resources as $value){
    	$sortarr[]=$value;
    	
    }
    $resources=popsort($sortarr,$_GET["orderby"],''); 
/*    $i=0;
    foreach($sortarr as $value){
    	print_r($sortarr[$i]);
    	$i++;	
    }*/
    $addsql = $rtypesql.$ftypesql.$timerangesql;
    $addurl = $rtypeurl.$ftypeurl.$orderbyurl;
	$getcount = getresourcecount($fid, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=resourcelist&plugin_op=groupmenu".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
//	分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $is_enable_title_category = false;
    $pluginid = $_GET["plugin_name"];
	$other_plugin_info = common_category_is_other($fid, $pluginid);
//  if($other_plugin_info["state"]=='Y' && $other_plugin_info['prefix']=='Y'){
    if($other_plugin_info["state"]=='Y'){
        $is_enable_category = true;
        if ($other_plugin_info['prefix']=='Y') {
        	$is_enable_title_category = true;
        }
        $categorys = common_category_get_category($fid, $pluginid);
    }
	
	return array("multipage"=>$multipage,"resources"=>$resources,"categorys"=>$categorys,"category"=>$category,"is_enable_category"=>$is_enable_category,"is_enable_title_category"=>$is_enable_title_category,"getcount"=>$getcount);
}

function getresourcecount($fid, $addsql) {
	$query = DB::query("SELECT count(*) FROM ".DB::table('resourcelist')." WHERE fid=".$fid.$addsql);
	return DB::result($query, 0);
}
/*
 * added by fumz
 * 2010-12-7 11:24:20
 */
function getresources($ids){
	global $_G;
	if(empty($ids))return;
	$idstr='';
	$i=0;
	foreach($ids as $key=>$value){
		if($i!=0){
			$idstr.=",";
		}
		$idstr.=$value;
		$i++;
	}
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=property&id=".$idstr;
	//echo($FILE_SEARCH_PAGE);
	$str1 = openFileAPI($FILE_SEARCH_PAGE);
	if (empty ($str1)) {
		return;
	}
	return array("result"=>json_decode($str1, true));
}
function openFileAPI($url) {
	$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 30,
		)
	);
	$context = @stream_context_create($opts); 
	
	$result =  file_get_contents($url, false, $context);
	return $result;
}
function quicksort($resources,$begin,$end,$key,$order){
	$i=$begin;
	$j=$end;
	$index=round(($begin+$end)/2);
	$markerR=$resources[$index];
	do{
		while($resources[$i][$key]<$markerR[$key]&&$i<$end)$i++;
		while($resources[$j][$key]>$markerR[$key]&&$j>$begin)$j++;
		if($i<=$j){
			$temp=$resources[$i];
			$resources[$i]=$resources[$j];
			$resources[$j]=$temp;
			$i++;
			$j--;
		}		
	}while($i<=$j);
	if($i<$end){
		quicksort($resources,$i,$end,$key,$order);
	}
	if($j>$begin){
		quicksort($resources,$begin,$j,$key,$order);
	}
	return $resources;
}
function popsort($resources,$key,$order){
	$i=0;
	$j=0;
	$len=count($resources);
	for($i;$i<$len;$i++){
		for($j=$i+1;$j<$len;$j++){
			if($resources[$i][$key]<$resources[$j][$key]){
				$temp=$resources[$i];
				$resources[$i]=$resources[$j];
				$resources[$j]=$temp;				
			}
		}
	}
	return $resources;
}
function search(){
	 global  $_G;
	  $perpage = 20;
	  if($_POST['page']){
			$page = intval($_POST['page']) ? intval($_POST['page']) : 1;
		}else{
			$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		}
		$start = ($page - 1) * $perpage;
		if($_POST['posttype']){
			$rtype=$_POST['rtype'];
			$title=$_POST['title'];
			$uploadtimefrom=$_POST['uploadtimefrom'];
			$uploaddateto=$_POST['uploaddateto'];
		}else{
			$rtype=$_GET['rtype'];
			$title=$_GET['title'];
			$uploadtimefrom=$_GET['uploadtimefrom'];
			$uploaddateto=$_GET['uploaddateto'];
		}
	 $uploaddatefrom=strtotime($uploadtimefrom);
	 $uploaddateto=strtotime($uploadtimeto);
	 if($rtype){
	 	$rtypesql=' AND typeid='.$rtype;
		$rtypeurl = "&rtype=".$rtype;
	 }
	 if($title){
	 	$titlesql=" AND title like '%".$title."%'";
		$titleurl = "&title=".$title;
	 }
	  if($uploadtimefrom){
	 	$uploadtimefromsql=" AND uploaddate>=".$uploaddatefrom;
		$uploadtimefromurl='&uploadtimefrom='.$uploadtimefrom;
	 }
	 if($uploadtimeto){
	 	$uploadtimetosql=" AND uploaddate<=".$uploaddateto;
		$uploadtimetourl='&uploadtimeto='.$uploadtimeto;
	 }
	 //alter by qiaoyz,2011-4-15,EKSN-305 资源列表组件搜索范围覆盖到活动内
	 $fids="(".$_G[fid];
	 $query_fids=DB::query("SELECT fid FROM ".DB::TABLE("forum_forum")." WHERE fup =".$_G[fid]);	
	 while($value=DB::fetch($query_fids)){
	 	$fids.=",".$value[fid];
	 }
	 $fids.=")";
	 $count=DB::result_first("SELECT count(*) FROM ".DB::TABLE("resourcelist")." WHERE fid=$_G[fid]".$rtypesql.$titlesql.$uploadtimefromsql.$uploadtimetosql);
	 $query=DB::query("SELECT * FROM ".DB::TABLE("resourcelist")." WHERE fid in ".$fids.$rtypesql.$titlesql.$uploadtimefromsql.$uploadtimetosql." order by uploaddate DESC  LIMIT $start,$perpage ");
	 
	 while($resource=DB::fetch($query)){
	 	$resources[]=$resource;
	 }
	  	$getcount = $count;
		$addurl=$rtypeurl.$titleurl.$uploadtimefromurl.$uploadtimetourl;
		$url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&&plugin_name=resourcelist&plugin_op=groupmenu&diy=&resourcelist_action=search".$addurl;
		
		if($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}
	 return array("multipage"=>$multipage, "_G"=>$_G, "resources"=>$resources, "total"=>$count, "rtype"=>$rtype, "title"=>$title, "uploadtimefrom"=>$uploadtimefrom, "uploadtimeto"=>$uploadtimeto,"total"=>$count);

	 
}

?>
