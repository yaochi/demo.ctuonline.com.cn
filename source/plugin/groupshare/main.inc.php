<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");
function index(){
	global  $_G;
	require_once libfile('function/home');
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$gets = array(
		'mod' => 'group',
		'action'=>'plugin',
		'fid'=>$_G[fid],
		'plugin_name' => 'groupshare',
		'plugin_op' => 'groupmenu',
		'type' => $_GET['type'],
		'from' => $_GET['from']
	);
	$theurl = 'forum.php?'.url_implode($gets);

	$f_index = 'USE INDEX(fid)';
	$need_count = true;

		
	if($_GET['type'] && $_GET['type'] != 'all' ) {
		$sub_actives = array('type_'.$_GET['type'] => ' class="a"');
		if($_GET[type]=="galbum"){
			$_GET[type]="album";
		}
		if($_GET[type]=="gpic"){
			$_GET[type]="pic";
		}
		$wheresql .= " AND type='$_GET[type]'";
	} else {
		$sub_actives = array('type_all' => ' class="a"');
	}
	$wheresql .= " AND status=1 ";
	$list = array();

	$sid = empty($_GET['sid'])?0:intval($_GET['sid']);
	$sharesql = $sid?"sid='$sid' AND":'';
    
	if($need_count) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('group_share')." WHERE fid=$_G[fid] $sharesql $wheresql"),0);
		if($count) {
			require_once libfile('function/share');
			$query = DB::query("SELECT * FROM ".DB::table('group_share')." $f_index 
				WHERE fid=$_G[fid] $sharesql $wheresql
				ORDER BY dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value = mkshare($value);
				if($value[anonymity]>0){
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($value[anonymity]);
					$value[repeats]=$repeatsinfo;
				}
				$list[] = $value;
			}
		}
	}

	$multi = multi($count, $perpage, $page, $theurl);
		
	 return array("list"=>$list, "_G"=>$_G,"multi"=>$multi,'theurl'=>$theurl,'sub_actives'=>$sub_actives,'count'=>$count);


}

function view(){
	global  $_G;
	require_once libfile('function/home');
	$id=$_GET['id'];
	$query = DB::query("SELECT * FROM ".DB::table('group_share')." WHERE sid='$id'");
	$share = DB::fetch($query);
	if(empty($share)) {
		showmessage('要查看的分享不存在');
	}
	if($share[anonymity]>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeats=getforuminfo($share[anonymity]);
	}
	require_once libfile('function/share');
	$share = mkshare($share);
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;

	$perpage = 50;
	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$list = array();
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='gsid'"),0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='gsid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$value[repeats]=$repeatsinfo;
			}
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$share[uid]&do=share&id=$id", '', 'comment_ul');
		
	 return array("share"=>$share,"id"=>$id,"list"=>$list,"multi"=>$multi,'repeats'=>$repeats);
}

function delete(){
	global  $_G;
	$id=$_GET['id'];
	$fid=$_G[fid];
	if(submitcheck('deletesubmit')){
		DB::query("DELETE FROM ".DB::table('group_share')." WHERE sid = $id");
		DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id =$id AND idtype='gsid'");
		showmessage('删除成功',"forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=groupshare&plugin_op=groupmenu");
	}else{
		include template("groupshare:delete");
		dexit();
	}
	
}



?>
