<?php
/**
 * @author fumz
 * @since 2010-9-21 12:11:49
 * 三级管理员管理日志
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$title="日志管理";
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
	$indexsql="SELECT re.id as id,b.hot AS hot,b.replynum AS replynum,b.blogid AS blogid,b.uid AS uid,
		b.subject AS subject,b.replynum AS replynum,b.viewnum AS viewnum,b.dateline AS dateline,
		p.realname AS realname 
		FROM (pre_home_blog b INNER JOIN pre_common_member_profile p ON b.uid=p.uid),pre_home_blogfield bf ,pre_common_resource re WHERE b.blogid=bf.blogid AND re.rid=b.blogid AND re.rtype='blog' AND re.uid!=1000";
	$countsql="SELECT count(*) FROM  (pre_home_blog b INNER JOIN pre_common_member_profile p ON b.uid=p.uid),pre_home_blogfield bf ,pre_common_resource re WHERE b.blogid=bf.blogid AND re.rid=b.blogid AND re.rtype='blog' AND re.uid!=1000";
	$wheresql="";
	//print_r($_G['validate']['orgidarray']);
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
	if($count){
		$query=DB::query($indexsql." ORDER BY b.dateline DESC LIMIT $start,$pagesize  ");
		$list=array();
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$list[$row['id']]=$row;		
		}		
	}
    //echo $indexsql;
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=blog&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}elseif($_G["gp_method"]=="search"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$searchsql="SELECT b.hot AS hot,b.replynum AS replynum,b.blogid AS blogid,b.uid AS uid, b.subject AS subject,b.replynum AS replynum,
		re.id as id,
		b.viewnum AS viewnum,b.dateline AS dateline,p.realname AS realname 
		FROM (pre_home_blog b INNER JOIN pre_common_member_profile p ON b.uid=p.uid),pre_home_blogfield bf ,pre_common_resource re WHERE b.blogid=bf.blogid AND re.rid=b.blogid AND re.rtype='blog' AND re.uid!=1000";
	$countsql="SELECT count(*) FROM  (pre_home_blog b INNER JOIN pre_common_member_profile p ON b.uid=p.uid),pre_home_blogfield bf,pre_common_resource re WHERE b.blogid=bf.blogid AND re.rid=b.blogid AND re.rtype='blog' AND re.uid!=1000";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	$wheresql='';
	if(!empty($content)){
		$wheresql.=" AND (b.subject LIKE '%$content%')";
	}
	if(!empty($usernames)){
		$wheresql.=" AND p.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND b.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND b.dateline<$endtime";
	}
	//print_r($_G['validate']['orgidarray']);
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
		$searchsql.="  ORDER BY b.dateline DESC LIMIT $start,$pagesize";
		$query=DB::query($searchsql);
		$list=array();
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row['dateline']);
			$list[$row['id']]=$row;
		}		
	}
	//echo $searchsql;
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=blog&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
	
}elseif($_G["gp_method"]=="delete"){
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		//print_r($_G['gp_ridarray']); 
		deleteblogs($_G['gp_ridarray']);
		//hook_delete_resources($_G['gp_ridarray'],'blog');
		cpmsg_mgr("操作成功，正在返回日志列表", "manage.php?mod=home&op=blog", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的日志", "manage.php?mod=home&op=blog", "succeed");
	}	
}
include template("manage/home_blog");
?>
