<?php
/**
 * @author fumz
 * @since 2010-9-24 18:00:20
 * @description 个人相册管理
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$title="相册管理";
$method=$_G["gp_method"]?$_G["gp_method"]:'index';
$_G["gp_method"]=$method;
if($_G["gp_code"]=="true"){
      $_G[gp_usernames] = urldecode($_G[gp_usernames]);
	$_G[gp_usernames_uids] = urldecode($_G[gp_usernames_uids]);
    $_G[gp_content] = urldecode($_G[gp_content]);
    $_G[gp_starttime] = urldecode($_G[gp_starttime]);
    $_G[gp_endtime] = urldecode($_G[gp_endtime]);
}
$count=0;//符合条件的记录数
if($_G['gp_method']=="index"){
    $pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$indexsql="SELECT
		 album.albumid as id,
		 album.albumname as name,
		 album.picnum as picnum,
		 album.uid as uid,
		 album.username as username,
		 album.dateline as dateline,
		 album.updatetime as updatetime,
		 profile.realname as realname,
		 re.org_id as org_id
		 FROM pre_home_album album,pre_common_resource re,pre_common_member_profile profile WHERE album.albumid=re.rid AND re.rtype='home_album' AND album.uid=profile.uid";
	$countsql="SELECT count(*) FROM pre_home_album album,pre_common_resource re WHERE album.albumid=re.rid AND re.rtype='home_album'";
	
	$wheresql="";
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	$countsql.=$wheresql;
	$indexsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	$list=array();
	if($count){
		$query=DB::query($indexsql." ORDER BY album.dateline DESC LIMIT $start,$pagesize  ");		
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$row['updatetime']=dgmdate($row[updatetime]);
			$list[$row['id']]=$row;		
		}
	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=album&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);	
}elseif($_G['gp_method']=="search"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;   
     
	$searchsql="SELECT
		 album.albumid as id,
		 album.albumname as name,
		 album.picnum as picnum,
		 album.uid as uid,
		 album.username as username,
		 album.dateline as dateline,
		 album.updatetime as updatetime,
		 profile.realname as realname,
		 re.org_id as org_id
		 FROM pre_home_album album,pre_common_resource re,pre_common_member_profile profile WHERE album.albumid=re.rid AND re.rtype='home_album' AND album.uid=profile.uid";
	$countsql="SELECT count(*) FROM pre_home_album album,pre_common_resource re,pre_common_member_profile profile WHERE album.albumid=re.rid AND re.rtype='home_album' AND album.uid=profile.uid";
		$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	$wheresql='';
	if(!empty($content)){
		$wheresql.=" AND album.albumname LIKE '%$content%'";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND album.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND album.dateline<$endtime";
	}
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	$countsql.=$wheresql;
	$searchsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	$list=array();
	if($count){
		$query=DB::query($searchsql." ORDER BY album.dateline DESC LIMIT $start,$pagesize  ");		
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$row['updatetime']=dgmdate($row[updatetime]);
			$list[$row['id']]=$row;		
		}
	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=album&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);		
}elseif($_G['gp_method']=="delete"){
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		deletealbums($_G['gp_ridarray']);
		//hook_delete_resources($_G['gp_ridarray'],'home_album');//改到deletealbums函数中调用
		cpmsg_mgr("操作成功，正在返回相册列表", "manage.php?mod=home&op=album", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的相册", "manage.php?mod=home&op=album", "succeed");
	}		
}
include template("manage/home_album");
?>
