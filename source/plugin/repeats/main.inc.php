<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");


function index(){
	global $_G;
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;
	
	require_once (dirname(__FILE__)."/function/function_repeats.php");
	$repeats=createrepeats($_G[forum][name],$_G['fid'],$_G['uid'],'1');
	$membernumber=DB::result_first("SELECT count(*) FROM ".DB::TABLE("forum_groupuser")." WHERE ( pre_forum_groupuser.uid in ( SELECT pre_repeats_relation.uid FROM pre_repeats_relation where fid=".$_G['fid'].") ) and fid=".$_G['fid']);
	$query=DB::query("SELECT * FROM ".DB::TABLE("forum_groupuser")." WHERE ( pre_forum_groupuser.uid in ( SELECT pre_repeats_relation.uid FROM pre_repeats_relation where fid=".$_G['fid'].") ) and fid=".$_G['fid']." order by level limit $start,$perpage");
	while($value=DB::fetch($query)){
		$alluserlist[$value[uid]]=$value;
		$alluseruids[] = $value[uid];
	}
	$alluserrealname = user_get_user_realname($alluseruids);
	$multipage = multi($membernumber, $perpage, $page, 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=repeats&plugin_op=groupmenu&repeats_action=authorize');
	
	/*require_once (dirname(__FILE__)."/function/function_repeats.php");
	$repeats=createrepeats($_G[forum][name],$_G['fid'],$_G['uid'],'1');
	$count=getrepeatscount($repeats['id']);
	if($count){
		$list=viewmemberbyrepeatsid($repeats['id'],$start,$perpage);
	}
	$multipage = multi($count, $perpage, $page, 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=repeats&plugin_op=groupmenu');*/

	return array("repeats"=>$repeats,"alluserlist"=>$alluserlist,"alluserrealname"=>$alluserrealname,"multipage"=>$multipage,"perpage"=>$perpage,"page"=>$page);
}

function authorize(){
	global $_G;
	
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;
	
	$membernumber=DB::result_first("SELECT count(*) FROM ".DB::TABLE("forum_groupuser")." WHERE ( pre_forum_groupuser.uid not in ( SELECT pre_repeats_relation.uid FROM pre_repeats_relation where fid=".$_G['fid'].") ) and fid=".$_G['fid']);
	$query=DB::query("SELECT * FROM ".DB::TABLE("forum_groupuser")." WHERE ( pre_forum_groupuser.uid not in ( SELECT pre_repeats_relation.uid FROM pre_repeats_relation where fid=".$_G['fid'].") ) and fid=".$_G['fid']." order by level limit $start,$perpage");
	while($value=DB::fetch($query)){
		$alluserlist[$value[uid]]=$value;
		$alluseruids[] = $value[uid];
	}
	$multipage = multi($membernumber, $perpage, $page, 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=repeats&plugin_op=groupmenu&repeats_action=authorize');

	$alluserrealname = user_get_user_realname($alluseruids);
	
	if(submitcheck('authorizesubmit')){
		require_once (dirname(__FILE__)."/function/function_repeats.php");
		$muid=$_G['gp_muid'];
		if($muid){
			azarepeatsbyfid($_G['fid'],$muid);
		}
		showmessage('授权成功！','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=repeats&plugin_op=groupmenu');
	}

	return array("alluserlist"=>$alluserlist,"alluserrealname"=>$alluserrealname,"multipage"=>$multipage,"perpage"=>$perpage,"page"=>$page);
}

function changename(){
	global $_G;
	$repeatsname=$_G['gp_repeatsname'];
	$repeatsid=$_G['gp_repeatsid'];
	require_once (dirname(__FILE__)."/function/function_repeats.php");
	$result=modifyrepeats($repeatsid,$repeatsname);
	if($result['success']=='1'){
		showmessage("改名成功");
	}else{
		showmessage($result['message']);
	}
}



function delete(){
	global $_G;
	require_once (dirname(__FILE__)."/function/function_repeats.php");
	$muid=$_G['gp_muid'];
	if($muid){
		delazarepeatsbyuid($muid,$_G['fid']);
	}
	showmessage('取消授权成功！','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=repeats&plugin_op=groupmenu');
}

function checkrepeatsname(){
	global $_G;
	$name=$_G[gp_name];
	$res=DB::result_first("select count(*) from ".DB::TABLE("forum_repeats")." where name='".$name."'");
	
	$arraydata=array("s"=>$res);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	dexit();
}

?>
