<?php
/**
 * @author fumz
 * @since 2010-9-21 13:26:04
 * 你我课堂管理
 */
$title="你我课堂管理";
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
$list=array();//返回的nwkt列表
if($_G['gp_method']=="index"){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$countsql="SELECT COUNT(*) FROM pre_common_resource re,pre_home_nwkt nwkt,pre_common_member_profile profile 
		WHERE re.rtype='nwkt' AND re.rid=nwkt.nwktid AND re.uid=profile.uid";
	$indexsql="SELECT 
		nwkt.firstman_ids AS firstman_ids,
		nwkt.secondman_ids AS secondman_ids,
		nwkt.guest_ids AS guest_ids,
		nwkt.starttime AS starttime,
		nwkt.endtime AS endtime,
		nwkt.dateline AS dateline,
		nwkt.nwktid AS nwktid,
		nwkt.uid AS uid,
		nwkt.subject AS subject,
		nwkt.message AS message,
		profile.realname AS realname 
		FROM pre_common_resource re,pre_home_nwkt nwkt,pre_common_member_profile profile 
		WHERE re.rtype='nwkt' AND re.rid=nwkt.nwktid AND re.uid=profile.uid";
	$wheresql="";
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	$countsql.=$wheresql;
	$indexsql.=$wheresql;
	
	$indexsql.=" ORDER BY nwkt.dateline DESC LIMIT $start,$pagesize";
	//echo $indexsql;
	
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	
	$query=DB::query($indexsql);
	
	while($row=DB::fetch($query)){
		$row[dateline]=dgmdate($row[dateline]);
		$row['starttime']=dgmdate($row['starttime']);
		$row['endtime']=dgmdate($row['endtime']);
		$list[$row['nwktid']]=$row;
		
		
	}
	require_once libfile("function/group");
 	/*
 	 * 根据主持人,主讲人，嘉宾id查找真实姓名
 	 */
	foreach($list as $value){
		if($value['firstman_ids']){//主持人id		
			$firstman_namesarray=user_get_user_realname(explode(',',trim($value['firstman_ids'])));
			$firstman_names="";
			foreach($firstman_namesarray as $user){
				$firstman_names.=$user[realname];
				$firstman_names.="&nbsp;";
			}
			$value['firstman_names']=$firstman_names;			
		}
		if(trim($value['secondman_ids'])){
			$secondman_namesarray=user_get_user_realname(explode(',',trim($value['secondman_ids'])));
			$secondman_names="";
			foreach($secondman_namesarray as $user){
				$secondman_names.=$user[realname];
				$secondman_names.="&nbsp;";
			}
			$value['secondman_names']=$secondman_names;			
		}
		if(trim($value['guest_ids'])){
			$guest_namesarray=user_get_user_realname(explode(',',trim($value['guest_ids'])));
			$guest_names="";
			foreach($guest_namesarray as $user){
				$guest_names.=$user[realname];
				$guest_names.="&nbsp;";
			}
			$value['guestman_names']=$guest_names;
		}
		$list[$value['nwktid']]=$value;
	}
	
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=nwkt&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}elseif($_G['gp_method']=='search'){
	$pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$countsql="SELECT COUNT(*) FROM pre_common_resource re,pre_home_nwkt nwkt,pre_common_member_profile profile 
		WHERE re.rtype='nwkt' AND re.rid=nwkt.nwktid AND re.uid=profile.uid";
	$indexsql="SELECT  
		nwkt.firstman_ids AS firstman_ids,
		nwkt.secondman_ids AS secondman_ids,
		nwkt.guest_ids AS guest_ids,
		nwkt.starttime AS starttime,
		nwkt.endtime AS endtime,
		nwkt.dateline AS dateline,
		nwkt.nwktid AS nwktid,
		nwkt.uid AS uid,
		nwkt.subject AS subject,
		nwkt.message AS message,
		profile.realname AS realname 
		FROM pre_common_resource re,pre_home_nwkt nwkt,pre_common_member_profile profile 
		WHERE re.rtype='nwkt' AND re.rid=nwkt.nwktid AND re.uid=profile.uid";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($content)){
		$wheresql.=" AND (nwkt.subject LIKE '%$content%')";
	}
	if(!empty($starttime)){
		$starttime=strtotime($starttime);
		$wheresql.=" AND nwkt.starttime>$starttime";
	}
	if(!empty($endtime)){
		$endtime=strtotime($endtime);
		$wheresql.=" AND nwkt.endtime<$endtime";
	}
	if($_G['gp_type']){
		$wheresql.=" AND nwkt.type=".$_G['gp_type'];
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
	$indexsql.=$wheresql;
	$indexsql.=" ORDER BY nwkt.dateline DESC LIMIT $start,$pagesize";
	$query=DB::query($indexsql);
	while($row=DB::fetch($query)){
		$row[dateline]=dgmdate($row[dateline]);
		$row['starttime']=dgmdate($row['starttime']);
		$row['endtime']=dgmdate($row['endtime']);
		$list[$row['nwktid']]=$row;
	}
	require_once libfile("function/group");
 	/*
 	 * 根据主持人,主讲人，嘉宾id查找真实姓名
 	 */
	foreach($list as $value){
		if($value['firstman_ids']){//主持人id		
			$firstman_namesarray=user_get_user_realname(explode(',',trim($value['firstman_ids'])));
			$firstman_names="";
			foreach($firstman_namesarray as $user){
				$firstman_names.=$user[realname];
				$firstman_names.="&nbsp;";
			}
			$value['firstman_names']=$firstman_names;			
		}
		if(trim($value['secondman_ids'])){
			$secondman_namesarray=user_get_user_realname(explode(',',trim($value['secondman_ids'])));
			$secondman_names="";
			foreach($secondman_namesarray as $user){
				$secondman_names.=$user[realname];
				$secondman_names.="&nbsp;";
			}
			$value['secondman_names']=$secondman_names;			
		}
		if(trim($value['guest_ids'])){
			$guest_namesarray=user_get_user_realname(explode(',',trim($value['guest_ids'])));
			$guest_names="";
			foreach($guest_namesarray as $user){
				$guest_names.=$user[realname];
				$guest_names.="&nbsp;";
			}
			$value['guestman_names']=$guest_names;
		}
		$list[$value['nwktid']]=$value;
	}
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=nwkt&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
    
}elseif($_G['gp_method']=='delete'){
	require_once libfile('function/nwkt');
	if(!empty($_G['gp_ridarray'])){
		deletenwkts($_G['gp_ridarray']);
		//hook_delete_resources($_G['gp_ridarray'],'nwkt');改为在deletenwkts函数中执行
		cpmsg_mgr("操作成功，正在返回你我课堂列表", "manage.php?mod=home&op=nwkt", "succeed");
	}else{
		cpmsg_mgr("请选择您要删除的你我课堂", "manage.php?mod=home&op=nwkt", "succeed");
	}
}
require template('manage/home_nwkt');
?>
