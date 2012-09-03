<?php
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/discuzcode');
require_once libfile('function/post');
	$fid = $_GET["fid"];
	$gettype = $_GET["gettype"];
	$getrole = $_GET["getrole"];
	$getorder = $_GET["getorder"];
	$mod=$_GET["mod"];
	$addsql = "";
	$addurl = "";
	if($getrole ==''){
		$getrole=DESC;
	}
	if($getorder ==''){
		$getorder=dateline;
	}
	if ($gettype!='') {
		$addsql = " AND classid='".$gettype."'";
		$addurl = "&gettype=".$gettype;
	}
	//	·ÖÒ³
    $perpage = 15;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
    $query = DB::query("SELECT * FROM ".DB::table("questionary")." WHERE moderated>=0 AND fid=".$fid.$addsql." ORDER BY displayorder DESC, $getorder $getrole LIMIT $start,$perpage ");
    $questionary = array();
    $_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
    while ($questionary = DB::fetch($query)) {
	    if($questionary['highlight']) {
			$string = sprintf('%02d', $questionary['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			
			$questionary['highlight'] = ' style="';
			$questionary['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$questionary['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
			$questionary['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
			$questionary['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
			$questionary['highlight'] .= '"';
		} else {
			$questionary['highlight'] = '';
		}
		$questionary[questdescr]=discuzcode($questionary[questdescr]);
		$questionary[questdescr]=messagecutstr($questionary[questdescr],150);
		$relationid[]=$questionary[questid];
		$questionaries[$questionary[questid]] = $questionary;
    }
    if($relationid){
		$query=DB::query("select anonymity,id from ".DB::TABLE("home_feed")." where icon='questionary' and idtype!='feed' and id in (".implode(',',$relationid).")");
		while($value=DB::fetch($query)){
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$questionaries[$value[id]][repeats]=getforuminfo($value[anonymity]);
			}
		}
	}
	$getcount = DB::result_first("SELECT count(*) FROM ".DB::table('questionary')." WHERE fid=".$fid.$addsql." AND moderated>=0 ORDER BY displayorder DESC, $getorder $getrole");
	$url = "forum.php?mod=".$mod."&action=plugin&fid=".$fid."&plugin_name=questionary&plugin_op=groupmenu".$addurl."&getorder=".$getorder."&getrole=".$getrole;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
    
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
    if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
	$prefix=common_category_is_prefix($_G["fid"], $pluginid);
    return array("multipage"=>$multipage,"questionaries"=>$questionaries,"gettype"=>$gettype,"categorys"=>$categorys,"getorder"=>$getorder,"getrole"=>$getrole,'mod'=>$mod,'getcount'=>$getcount);


//if (empty ($_G['fid'])) {
//	showmessage('group_rediret_now', 'group.php');
//}
//
//$questid = empty ($_GET['questid']) ? 0 : intval($_GET['questid']);
//
//$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
//if ($page < 1)
//	$page = 1;
//
//	$perpage = 15;
//	$perpage = mob_perpage($perpage);
//
//	$start = ($page -1) * $perpage;
//
//	ckstart($start, $perpage);
//
//
//	$default = array ();
//	$f_index = '';
//	$list = array ();
//	$pricount = 0;
//
//	$gets = array (
//		mod' => 'space',
//		uid' => $_G['uid'],
//		do' => 'questionary',
//		view' => $_GET['view'],
//		order' => $_GET['order'],
//		searchkey' => $_GET['searchkey'],
//		from' => $_GET['from']
//	);
//	$theurl = join_plugin_action('index');
//
//	$need_count = true;
//
//	if ($_GET['from'] == 'space')
//		$diymode = 1;
//
//	$wheresql = "fid='$_G[fid]'";
//
//	if ($need_count) {
//
//		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
//			$wheresql .= " AND albumname LIKE '%$searchkey%'";
//		}
//
//		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('questionary') . " WHERE $wheresql"), 0);
//
//		if ($count) {
//			$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " $f_index WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
//			while ($value = DB :: fetch($query)) {
//				$list[] = $value;
//			}
//		}
//	}
//
//	$multi = multi($count, $perpage, $page, $theurl);

?>
