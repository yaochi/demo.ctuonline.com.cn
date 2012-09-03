<?php
/**
 * @author fumz
 * @since 2010-9-21 12:11:49
 * 三级管理员管理记录回复
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$title="记录回复管理";
$method=$_G["gp_method"]?$_G["gp_method"]:'index';
$_G["gp_method"]=$method;
if($_G["gp_code"]=="true"){
     $_G[gp_usernames] = urldecode($_G[gp_usernames]);
	$_G[gp_usernames_uids] = urldecode($_G[gp_usernames_uids]);
    $_G[gp_content] = urldecode($_G[gp_content]);
    $_G[gp_starttime] = urldecode($_G[gp_starttime]);
    $_G[gp_endtime] = urldecode($_G[gp_endtime]);
}
$count=0;
if($_G["gp_method"]=="index"){	
    $pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$indexsql="SELECT 
		docomment.id as id,
		docomment.doid as doid,
		docomment.dateline as dateline,
		docomment.username as username,
		docomment.uid as uid,
		docomment.message as message,
		doing.message as domessage,
		doing.username as dousername,
		doing.uid as douid,
		re.org_id as org_id,
		profile.realname as realname    
		FROM pre_common_resource re,pre_home_docomment docomment,pre_home_doing doing,pre_common_member_profile profile WHERE re.uid=profile.uid AND re.rid=docomment.id AND doing.doid=docomment.doid AND re.rtype='home_doing_comment'";
	$countsql="SELECT count(*) FROM pre_common_resource re,pre_home_docomment docomment,pre_home_doing doing,pre_common_member_profile profile  WHERE re.uid=profile.uid AND re.rid=docomment.id AND doing.doid=docomment.doid AND re.rtype='home_doing_comment'";
	
	$wheresql="";
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	$doid=$_G['gp_doid'];
	if($doid){
		$wheresql.=" AND docomment.doid=$doid";
	}
	
	$countsql.=$wheresql;
	$indexsql.=$wheresql;
	
	
	
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	if($count){
		$query=DB::query($indexsql." ORDER BY re.dateline DESC LIMIT $start,$pagesize  ");
		$list=array();
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$list[$row['id']]=$row;		
		}		
	}

	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=docomment&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime&doid=$doid";
    $multipage = multi($count, $pagesize, $page, $url);
}elseif($_G["gp_method"]=="search"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;    
	$searchsql="SELECT 
		docomment.id as id,
		docomment.doid as doid,
		docomment.dateline as dateline,
		docomment.username as username,
		docomment.uid as uid,
		docomment.message as message,
		doing.message as domessage,
		re.org_id as org_id,
		profile.realname as realname    
		FROM pre_common_resource re,pre_home_docomment docomment,pre_home_doing doing,pre_common_member_profile profile WHERE re.uid=profile.uid AND re.rid=docomment.id AND doing.doid=docomment.doid AND re.rtype='home_doing_comment'";
	$countsql="SELECT count(*) FROM pre_common_resource re,pre_home_docomment docomment,pre_home_doing doing,pre_common_member_profile profile  WHERE re.uid=profile.uid AND re.rid=docomment.id AND doing.doid=docomment.doid AND re.rtype='home_doing_comment'";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	$wheresql='';
	if(!empty($content)){
		$wheresql.=" AND (docomment.message LIKE '%$content%' OR doing.message like '%$content%')";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND docomment.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND docomment.dateline<$endtime";
	}
	$doid=$_G['gp_doid'];
	if($doid){
		$wheresql.=" AND docomment.doid=$doid";
	}
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	$countsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	if($count){//如果符合条件的记录数为0
		$searchsql.=$wheresql;
		$searchsql.="  ORDER BY docomment.dateline DESC LIMIT $start , $pagesize";
		$query=DB::query($searchsql);
		$list=array();
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row['dateline']);
			$list[$row['id']]=$row;
		}		
	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=docomment&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime&doid=$doid";
    $multipage = multi($count, $pagesize, $page, $url);
	
}elseif($_G["gp_method"]=="delete"){
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		//print_r($_G['gp_ridarray']);
		deletedocomments($_G['gp_ridarray']);
		hook_delete_resources($_G['gp_ridarray'],'home_doing_comment');
		cpmsg_mgr("操作成功，正在返回记录列表", "manage.php?mod=home&op=docomment", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的记录回复", "manage.php?mod=home&op=docomment", "succeed");
	}	
}
include template("manage/home_docomment");
?>
