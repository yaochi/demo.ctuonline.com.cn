<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require dirname(dirname(dirname(dirname(__FILE__)))).'/api/sphinx/sphinxapi.php';

$perpage = 10;
$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$start = ($page - 1) * $perpage;
$searchusername=$_G['gp_searchusername'];
$searchrealname=$_G['gp_searchrealname'];
$typeselect=empty($_G['gp_typeselect'])?1:$_G['gp_typeselect'];
$orderby=empty($_GET["orderby"])?"desc":$_GET["orderby"];
$extra='';
$showsubmit=0;
showtips('statistics_userauth_tips');
showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=userauth&pmod=admincp', 'repeatsubmit');
if($typeselect=='1'){
showsubmit('repeatsubmit', 搜索, '<strong>姓名： </strong><input name="searchrealname" value="'.htmlspecialchars(stripslashes($searchrealname)).'" class="txt" />&nbsp;&nbsp;<strong style="margin-left:20px;">网大账号： </strong> <input name="searchusername" value="'.htmlspecialchars(stripslashes($searchusername)).'" class="txt" style="width:300px;" /><select name="typeselect" style="margin-left:10px;"><option value="1"  selected="selected" >认证用户</option><option value="2">未认证用户</option></select>', $searchtext);
}else{
showsubmit('repeatsubmit', 搜索, '<strong>姓名： </strong><input name="searchrealname" value="'.htmlspecialchars(stripslashes($searchrealname)).'" class="txt" />&nbsp;&nbsp;<strong style="margin-left:20px;">网大账号： </strong><input name="searchusername" value="'.htmlspecialchars(stripslashes($searchusername)).'" class="txt" style="width:300px;"/><select name="typeselect" style="margin-left:10px;"><option value="1">认证用户</option><option value="2"  selected="selected" >未认证用户</option></select>', $searchtext);
}
showformfooter();
showtablefooter();
if(submitcheck('changesubmit')){
	$choice=$_G[gp_choice];
	for($i=0;$i<count($choice);$i++){
		list($arr[uid],$arr[username],$arr[realname])=explode('ε',$choice[$i]);
		$arr[dateline]=time();
		if($_G['gp_delete']){
			DB::query("delete from ".DB::TABLE("authenticated_users")." where uid=".$arr[uid]);
		}
		if($_G['gp_add']){
			DB::insert('authenticated_users',$arr);
		}
	}
	
}



if($searchusername || $searchrealname){
	$wheresql='';
	if($searchrealname){
		$extra=$extra.'&searchrealname='.$searchrealname;
		$wheresql=" and realname like '%".$searchrealname."%'";
	}
	if($searchusername){
		$extra=$extra.'&searchusername='.$searchusername;
		$searchuserarray=explode(',',$searchusername);
		if(count($searchuserarray)>1){
			$newsearchusername=implode('\',\'',$searchuserarray);
			$wheresql=$wheresql." and username in ('".$newsearchusername."')";
		}else{
			$wheresql=$wheresql." and username like ('%".$searchusername."%')";
		}
	}
	if($typeselect=='1'){
		$count=DB::result_first("select count(*) from ".DB::TABLE("authenticated_users")." where 1=1 ".$wheresql." order by dateline desc");
		$sql="select * from ".DB::TABLE("authenticated_users")." where 1=1 ".$wheresql." order by dateline ".$orderby." limit $start,$perpage";
	}else{
		$extra=$extra.'&typeselect='.$typeselect;
		if(!$_config['sphinx']['used'] && empty($searchrealname)||$searchusername){
			$count=DB::result_first("select count(*) from ".DB::TABLE("common_member")." cm,".DB::TABLE("common_member_profile")." cmp where cm.uid=cmp.uid ".$wheresql." and cm.uid not in (select uid from ".DB::TABLE("authenticated_users")." where 1=1 ".$wheresql.") ");
			$sql="select cm.uid,cm.username,cmp.realname from ".DB::TABLE("common_member")." cm,".DB::TABLE("common_member_profile")." cmp where cm.uid=cmp.uid ".$wheresql." and cm.uid not in (select uid from ".DB::TABLE("authenticated_users")." where 1=1 ".$wheresql.")  limit $start,$perpage";
		}else{
			$autharray=array();
			$uidquery=DB::query("select uid from ".DB::TABLE("authenticated_users"));
			while($uidvalue=DB::fetch($uidquery)){
				$autharray[]=$uidvalue['uid'];
			}
			$result=sphinxdata($searchrealname,$autharray,$page,$perpage);
			$count=$result[total];
			if($result[matches]){
				for($i=0; $i<count($result[matches]); $i++){
					$uidarr[]=$result[matches][$i]['id'];
				}
				$sql="SELECT cm.uid,cm.username,cmp.realname FROM ".DB::table("common_member")." cm,".DB::table('common_member_profile')." cmp  WHERE cm.uid=cmp.uid ".$wheresql." and cm.uid in (".implode(',',$uidarr).") ORDER BY cm.uid asc";
			}
		}
	}
}else{
	if($typeselect=='1'){
		$count=DB::result_first("select count(*) from ".DB::TABLE("authenticated_users")." order by dateline desc");
		$sql="select * from ".DB::TABLE("authenticated_users")." order by dateline ".$orderby." limit $start,$perpage";
	}
}
showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=userauth&pmod=admincp', 'changesubmit');
if($typeselect=='1'){
	echo '<input type="hidden" value="yes" name="delete">';
}else{
	echo '<input type="hidden" value="yes" name="add">';
}

$html='<tr class="header"><th><input type="checkbox" onclick="checkAll(\'prefix\', this.form, \'choice\')" id="chkall" name="chkall" class="checkbox"></th><th>姓名</th><th>账号</th><th>公司信息</th><th>认证状态</th>';
if($typeselect=='1'){
	if($orderby=='desc'){
		$extra=$extra.'&orderby=asc';
		$html=$html.'<th><div style="width:60px; float:left;">认证日期</div><div style="width:70px; float:left; height:19px; line-height:19px; border:1px solid #CCC;font-weight:normal; font-size:12px; color:#666; text-indent:8px; background-image:url(source/plugin/userauth/images/icon0711.gif); background-repeat:no-repeat; background-position:58px -19px; cursor:pointer;" onclick="javasript:location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=userauth&pmod=admincp'.$extra.'\'">从旧到新</div></th>';
	}else{
		$html=$html.'<th><div style="width:60px; float:left;">认证日期</div><div style="width:70px; float:left; height:19px;font-weight:normal; line-height:19px; border:1px solid #CCC; font-size:12px; color:#666; text-indent:8px; background-image:url(source/plugin/userauth/images/icon0711.gif); background-repeat:no-repeat; background-position:58px 0px; cursor:pointer;"  onclick="javasript:location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=userauth&pmod=admincp'.$extra.'\'">从新到旧</div></th>';
	}
	
}
$html=$html.'</tr>';
echo($html);

$resulthtml='';
if($sql){
	$query=DB::query($sql);
	while($value=DB::fetch($query)){
		$uidarray[]=$value[uid];
		$list[$value[uid]]=$value;
	}
	if(count($uidarray)){
		$showsubmit=1;
		$uids=implode(',',$uidarray);
		$url=$_G[config]['api']['url'].'/api/sso/getusersprovince.php?uids='.$uids;
		$usercompany=json_decode(openURL($url),true);
		$resulthtml='';
		for($i=0;$i<count($uidarray);$i++){
			$provcie=empty($usercompany[$uidarray[$i]][groupName])?'中国电信':$usercompany[$uidarray[$i]][groupName];
			$resulthtml=$resulthtml.'<tr class="hover"><td class="td25"><input type="checkbox" value="'.$uidarray[$i].'ε'.$list[$uidarray[$i]][username].'ε'.$list[$uidarray[$i]][realname].'" name="choice[]" class="checkbox"></td><td >'.$list[$uidarray[$i]][realname].'</td><td name="username[]" >'.$list[$uidarray[$i]][username].'</td><td>'.$provcie.'</td>';
			if($typeselect=='1'){
				$resulthtml=$resulthtml.'<td>已认证</td><td>'.dgmdate($list[$uidarray[$i]][dateline],'Y.m.d').'</td>';
			}else{
				$resulthtml=$resulthtml.'<td>未认证</td>';
			}
			$resulthtml=$resulthtml.'</tr>';
		}
		
	}	
}

if(empty($resulthtml)){
	$resulthtml='<tr><td colspan="6" align="center">暂无查找结果</td></tr>';
}
	
echo($resulthtml);

if($showsubmit){
	if($typeselect=='1'){
		showsubmit('changesubmit', '删除所选用户');
	}else{
		showsubmit('changesubmit', '认证所选用户');
	}
}
showformfooter();
showtablefooter();

echo multi($count, $perpage, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=userauth&pmod=admincp$extra");


function sphinxdata($name,$autharray,$page,$pageSize){
	global $_G;

	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "article";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient();
	$sc->SetServer($host,$port);
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	
	$sc->SetArrayResult ( true );
	if(count($autharray)){
		$sc->SetFilter('@id',$autharray,true);
	}
	$result = $sc->Query ( $name, $index );

	return $result;

}

?>