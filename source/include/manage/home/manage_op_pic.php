<?php
/**
 * @author fumz
 * @since 2010-9-24 18:00:20
 * @description 个人图片管理
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$title="图片管理";
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
		 pic.picid as id,
		 pic.albumid as albumid,
		 pic.uid as uid,
		 pic.dateline as dateline,
		 pic.size as size,
		 pic.hot as hot,
		 pic.thumb as thumb,
		 pic.filepath as filepath,
		 pic.title as title,
		 pic.hot as hot,
		 album.albumname as albumname,
		 re.org_id as org_id,
		 profile.realname as realname 
		 FROM pre_home_album album,pre_home_pic pic,pre_common_resource re,pre_common_member_profile profile WHERE pic.albumid=album.albumid AND pic.picid=re.rid AND re.rtype='home_pic' AND pic.uid=profile.uid";
	$countsql="SELECT count(*) FROM pre_home_album album,pre_home_pic pic,pre_common_resource re,pre_common_member_profile profile WHERE pic.albumid=album.albumid AND pic.picid=re.rid AND re.rtype='home_pic' AND pic.uid=profile.uid";
	$albumid=$_G[gp_albumid];
	if($_G['gp_albumid']){
		$indexsql.=" AND pic.albumid=".$_G[gp_albumid];
		$countsql.=" AND pic.albumid=".$_G[gp_albumid];
	}
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
	//echo $indexsql;
	$list=array();
	if($count){
		$query=DB::query($indexsql." ORDER BY pic.dateline DESC LIMIT $start,$pagesize  ");		
		include libfile('function/home');
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$row['pic'] = pic_get($row['filepath'], 'album', $row['thumb'], $row['remote']);	
			if($row['size']<1024){
				$row['size']=round($row['size'],2)."B";	
			}elseif($row[size]>=$row[size]&&$row[size]<1024*1024){
				$row['size']=round($row['size']/1024,2)."KB";
			}elseif($row[size]>=1024*1024){
				$row['size']=round($row['size']/(1024*1024),2)."MB";
			}
			$list[$row['id']]=$row;	
		}
	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=pic&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime&albumid=".$_G['gp_albumid'];
    $multipage = multi($count, $pagesize, $page, $url);	
}elseif($_G['gp_method']=="search"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize; 
    
	$searchsql="SELECT
		 pic.picid as id,
		 pic.albumid as albumid,
		 pic.uid as uid,
		 pic.dateline as dateline,
		 pic.size as size,
		 pic.hot as hot,
		 pic.thumb as thumb,
		 pic.filepath as filepath,
		 pic.title as title,
		 pic.hot as hot,
		 album.albumname as albumname,
		 re.org_id as org_id,
		 profile.realname as realname 
		 FROM pre_home_album album,pre_home_pic pic,pre_common_resource re,pre_common_member_profile profile WHERE pic.albumid=album.albumid AND pic.picid=re.rid AND re.rtype='home_pic' AND pic.uid=profile.uid";
	$countsql="SELECT count(*) FROM pre_home_album album,pre_home_pic pic,pre_common_resource re,pre_common_member_profile profile WHERE pic.albumid=album.albumid AND pic.picid=re.rid AND re.rtype='home_pic' AND pic.uid=profile.uid";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];	
	//exit($content);
	$wheresql="";
	if(!empty($content)){
		$wheresql.=" AND pic.title LIKE '%$content%'";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND pic.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND pic.dateline<$endtime";
	}
	$albumid=$_G[gp_albumid];
	if($_G['gp_albumid']){
		$searchsql.=" AND pic.albumid=".$_G[gp_albumid];
		$countsql.=" AND pic.albumid=".$_G[gp_albumid];
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
		$query=DB::query($searchsql." ORDER BY pic.dateline DESC LIMIT $start,$pagesize  ");		
		include libfile('function/home');
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);				
			if($row['size']<1024){
				$row['size']=round($row['size'],2)."B";	
			}elseif($row[size]>=$row[size]&&$row[size]<1024*1024){
				$row['size']=round($row['size']/1024,2)."KB";
			}elseif($row[size]>=1024*1024){
				$row['size']=round($row['size']/(1024*1024),2)."MB";
			}
			$row['pic'] = pic_get($row['filepath'], 'album', $row['thumb'], $row['remote']);
			$list[$row['id']]=$row;	
		}
	}	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=pic&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime&albumid=".$_G['gp_albumid'];
    $multipage = multi($count, $pagesize, $page, $url);	
}elseif($_G['gp_method']=="delete"){
	//exit("相册id:".$_G['gp_albumid']);
	require_once libfile("function/delete");
	if(!empty($_G['gp_ridarray'])){
		
		deletepics($_G['gp_ridarray']);
		//hook_delete_resources($_G['gp_ridarray'],'home_pic');//改在deletepics中调用
		cpmsg_mgr("操作成功，正在返回图片列表", "manage.php?mod=home&op=pic&albumid=".$_G['gp_albumid'], "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的图片", "manage.php?mod=home&op=pic&albumid=".$_G['gp_albumid'], "succeed");
	}		
}
include template("manage/home_pic");
?>
