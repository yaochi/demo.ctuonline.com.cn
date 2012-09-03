<?php
/*
 * Com.:
 * Author: qiaoyz
 * Date: 2012-8-1
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
global $_G;
require_once (dirname(__FILE__)."/function/function_coursemap.php");
$type=empty($_G['gp_type'])?0:$_G['gp_type'];
if($type=='-1'){
	download();
}else if($type==1){
	$info=subupload();
}
showtips('statistics_coursemap_tips');
showtableheader();
showformheader('plugins&operation=config&do='.$pluginid.'&identifier=coursemap&pmod=admincp&type=1', 'enctype');
$record=getrecord();
echo '<tr class="header"><th>最近更新记录：'.$record[mes].'</th></tr>';
showsetting('import_file', 'mapxls', '', 'file');
showsubmit('importsubmit');
showformfooter();
showtablefooter();
if($type==1){
	if($info[status]==1){
		showtableheader();
		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=coursemap&pmod=admincp&type=2', 'enctype');
		showsubmit('repeatsubmit', "搜索", '基准岗位： </strong> <input name="searchname" value="" class="txt" style="width:300px;" />', $searchtext);
		showformfooter();
		showtablefooter();
	}else{
		$err=$info[error];
		showtableheader();
		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=coursemap&pmod=admincp&type=2', 'enctype');
		showformfooter();
		echo '<tr class="header"><th>错误信息</th></tr>';
		for($i=0;$i<count($err);$i++){
			$errinfo='第'.$err[$i][row]."行，第".$err[$i][col]."列——".$err[$i][mes];
			echo '<tr class="hover"><td>'.$errinfo.'</td></tr>';
		}
	}
}
if($type==0&&$record[status]==1){
	showtableheader();
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=coursemap&pmod=admincp&type=2', 'enctype');
	showsubmit('repeatsubmit', "搜索", '基准岗位： </strong> <input name="searchname" value="" class="txt" style="width:300px;" />', $searchtext);
	showformfooter();
	showtablefooter();
}
if($type==2){
	$searchname=$_G[gp_searchname];
	$courses=getrecommendbyname($searchname);
	showtableheader();
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=coursemap&pmod=admincp&type=2', 'enctype');
	showsubmit('repeatsubmit', "搜索", '基准岗位： </strong> <input name="searchname" value="'.htmlspecialchars(stripslashes($searchname)).'" class="txt" style="width:300px;" />', $searchtext);
	showformfooter();
	echo '<tr class="header"><th></th><th>基准岗位</th><th>课程编号</th><th>课程名称</th><th></th></tr>';
	for($i=0;$i<count($courses);$i++){
		echo '<tr class="hover"><td></td><td>'.$courses[$i][name].'</td><td>'.$courses[$i][coursecode].'</td><td>'.$courses[$i][coursename].'</td><td></td></tr>';
	}
	if(count($courses)==0){
		echo '<tr><td colspan="5" align="center">暂无查找结果</td></tr>';
	}
	showtablefooter();
}

function subupload(){
	global $_G;
	$status=0;
	$re1=uploadxls();
	if($re1[suc]){
		$re2=check();
		if($re2[suc]){
			init_coursemap();
			$status=1;
		}
	}else{
		cpmsg($re1[mes], '', 'error');
	}
	updaterecord(array("uid"=>$_G[uid],"username"=>$_G[username],"realname"=>user_get_user_name($_G[uid]),"status"=>$status,"dateline"=>time()));
	return array("status"=>$status,"error"=>$re2[mes]);
}
?>