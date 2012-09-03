<?php
/*
 * @author fumz
 * @since 2010-9-25 9:07:15
 * 个人评论管理（记录回复，相册评论等）
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$title="评论管理";
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

if($_G['gp_method']=="index"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$countsql="SELECT COUNT(*) FROM pre_common_resource re,pre_home_comment co,pre_common_member_profile profile WHERE re.rid=co.cid AND re.rtype LIKE 'home%comment' AND re.rtype <> 'home_doing_comment' AND re.uid=profile.uid";
	$indexsql="SELECT 
		re.rid as cid,
		re.rtype as rtype,
		co.id as targetid,
		co.uid as targetuid,
		re.org_id as org_id,
		re.dateline as dateline,
		co.idtype as idtype,
		co.message as message,
		profile.uid as uid,
		profile.realname as realname  
		FROM pre_common_resource re,pre_home_comment co,pre_common_member_profile profile WHERE re.rid=co.cid AND re.rtype LIKE 'home%comment' AND re.rtype <> 'home_doing_comment' AND re.uid=profile.uid";
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
	
	$query=DB::query($indexsql." ORDER BY re.dateline DESC LIMIT $start,$pagesize  ");
	$list=array();
	while($row=DB::fetch($query)){
		$row['dateline']=dgmdate($row[dateline]);
		if($row['idtype']=='picid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=album&picid=".$row['targetid'];
		}elseif($row['idtype']=='blogid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=blog&id=".$row['targetid'];
		}elseif($row['idtype']=='nwktid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=nwkt&id=".$row['targetid'];
		}elseif($row['idtype']=='docid'){
			if($row['targetid']){
				include_once libfile("function/doc");
				$doc = getFile($row['targetid']);
				if($doc['titlelink']){
					$row['url']=$doc['titlelink'];	
				}							
			}
		}
		if(!$row['targetid']){
			$row['url']="#";
		}
		$list[$row['cid']]=$row;		
	}
	
	$usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=comment&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}elseif($_G['gp_method']=="search"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
    $countsql="SELECT COUNT(*) FROM pre_common_resource re,pre_home_comment co,pre_common_member_profile profile WHERE re.rid=co.cid AND re.rtype LIKE 'home%comment' AND re.rtype <> 'home_doing_comment' AND re.uid=profile.uid";
	$searchsql="SELECT 
		re.rid as cid,
		co.id as targetid,
		re.rtype as rtype,
		re.org_id as org_id,
		re.dateline as dateline,
		co.idtype as idtype,
		co.message as message,
		profile.uid as uid,
		profile.realname as realname  
		FROM pre_common_resource re,pre_home_comment co,pre_common_member_profile profile WHERE re.rid=co.cid AND re.rtype LIKE 'home%comment' AND re.rtype <> 'home_doing_comment' AND re.uid=profile.uid";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	//echo "username".$username;
	//$wheresql="";
	if(!empty($content)){
		$wheresql.=" AND co.message LIKE '%$content%'";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND b.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND b.dateline<$endtime";
	}
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	//echo $wheresql;
	$countsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	
	if($count){
		$searchsql.=$wheresql;
		$searchsql.="  ORDER BY re.dateline DESC LIMIT $start,$pagesize";
		$query=DB::query($searchsql);
		$list=array();		
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row['dateline']);
		if($row['idtype']=='picid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=album&picid=".$row['targetid'];
		}elseif($row['idtype']=='blogid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=blog&id=".$row['targetid'];
		}elseif($row['idtype']=='nwktid'){
			$row['url']="home.php?mod=space&uid=".$row['targetuid']."&do=nwkt&id=".$row['targetid'];
		}elseif($row['idtype']=='docid'){
			if($row['targetid']){
				include_once libfile("function/doc");
				$doc = getFile($row['targetid']);
				if($doc['titlelink']){
					$row['url']=$doc['titlelink'];	
				}							
			}
		}
		if(!$row['targetid']){
			$row['url']="#";
		}
			$list[$row['cid']]=$row;
		}		
	}
	//echo $searchsql;
	
   $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=comment&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
	
}elseif($_G['gp_method']=="delete"){
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		//print_r($_G['gp_ridarray']);
		deletecomments($_G['gp_ridarray']);
		//hook_delete_resources($_G['gp_ridarray'],'home%comment');
		cpmsg_mgr("操作成功，正在返回评论列表", "manage.php?mod=home&op=comment", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的评论", "manage.php?mod=home&op=comment", "succeed");
	}	
	
}

require template("manage/home_comment");
?>
