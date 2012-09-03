<?php

/**
 * @author fumz
 * @since 2010-9-21 13:26:04
 * 专区管理
 */
$title="专区管理";
$method=empty($_G['gp_method'])?'index':$_G['gp_method'];
$_G['gp_method']=$method;
if($_G["gp_code"]=="true"){
      $_G[gp_usernames] = urldecode($_G[gp_usernames]);
	$_G[gp_usernames_uids] = urldecode($_G[gp_usernames_uids]);
    $_G[gp_content] = urldecode($_G[gp_content]);
    $_G[gp_starttime] = urldecode($_G[gp_starttime]);
    $_G[gp_endtime] = urldecode($_G[gp_endtime]);
}
$count=0;//符合记录的总数据条数
$list=array();//返回的专区列表

if($_G['gp_method']=='index'){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$countsql="SELECT count(*) FROM pre_forum_forum forum,pre_forum_forumfield forumfield,pre_common_member_profile profile,pre_common_resource re WHERE forum.fid=forumfield.fid AND profile.uid=forumfield.founderuid AND re.rid=forum.fid ";
	$indexsql="SELECT forum.fid AS fid,forum.fup AS fup,forum.name AS name,forum.org_id AS org_id,forumfield.dateline AS dateline,
		profile.realname AS realname,
		profile.uid as uid,
		forumfield.membernum AS membernum,
		forumfield.group_type AS group_type 
		FROM pre_forum_forum forum,pre_forum_forumfield forumfield,pre_common_member_profile profile,pre_common_resource re WHERE forum.fid=forumfield.fid AND profile.uid=forumfield.founderuid AND re.rid=forum.fid";
	$wheresql=" AND forum.type='sub' AND re.rtype='group'";
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
	
	$indexsql.=" ORDER BY forumfield.dateline DESC  LIMIT $start,$pagesize";
	$query=DB::query($indexsql);
	while($row=DB::fetch($query)){
		$row[dateline]=dgmdate($row['dateline']);
		if ($row["group_type"] >= 1 && $row["group_type"] < 20) {
	        $row['type_flag'] = 'biz';
	    } elseif ($row["group_type"] >= 20 && $row["group_type"] < 60) {
	        $row['type_flag'] = 'org';
	    }
		$list[$row['fid']]=$row;		

	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=group&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
	//echo $indexsql;
}elseif($_G['gp_method']=='search'){ 
	$pagesize=10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	
	$countsql="SELECT count(*) FROM pre_forum_forum forum,pre_forum_forumfield forumfield,pre_common_member_profile profile,pre_common_resource re WHERE forum.fid=forumfield.fid AND profile.uid=forumfield.founderuid AND re.rid=forum.fid ";
	$indexsql="SELECT forum.fid AS fid,forum.fup AS fup,forum.name AS name,forum.org_id AS org_id,forumfield.dateline AS dateline,
		profile.realname AS realname,
		profile.uid as uid,
		forumfield.membernum AS membernum,
		forumfield.group_type AS group_type  
		FROM pre_forum_forum forum,pre_forum_forumfield forumfield,pre_common_member_profile profile,pre_common_resource re WHERE forum.fid=forumfield.fid AND profile.uid=forumfield.founderuid  AND re.rid=forum.fid ";
	$wheresql=" AND forum.type='sub' AND re.rtype='group'";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	if(!empty($content)){
		$wheresql.=" AND (forum.name LIKE '%$content%')";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND forumfield.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND forumfield.dateline<$endtime";
	}
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
	
	$indexsql.=" ORDER BY forumfield.dateline DESC LIMIT $start,$pagesize";
	$query=DB::query($indexsql);
	while($row=DB::fetch($query)){
		$row[dateline]=dgmdate($row['dateline']);
		if ($row["group_type"] >= 1 && $row["group_type"] < 20) {
	        $row['type_flag'] = 'biz';
	    } elseif ($row["group_type"] >= 20 && $row["group_type"] < 60) {
	        $row['type_flag'] = 'org';
	    }
		$list[$row['fid']]=$row;

	}
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=group&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
	//echo $indexsql;
}elseif($_G['gp_method']=='delete'){
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		//print_r($_G['gp_ridarray']);
		group_delete_by_useradmin($_G['gp_ridarray'],true);
		hook_delete_resources($_G['gp_ridarray'],'group');
		cpmsg_mgr("操作成功，正在返回专区列表", "manage.php?mod=home&op=group", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的专区", "manage.php?mod=home&op=group", "succeed");
	}
}
include template('manage/home_group');
?>
