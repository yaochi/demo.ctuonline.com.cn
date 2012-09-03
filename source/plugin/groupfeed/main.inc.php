<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
	 require_once libfile("function/feed");
	 require_once libfile("function/home");
	$_G['home_today'] = $_G['timestamp'] - $_G['timestamp']%(3600*24);
	
	$fid = $_G["fid"];
	$perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;

	$sql = "SELECT * FROM ".DB::table('home_feed')." WHERE fid=".$fid." or sharetofids like '%,".$fid.",%' order by dateline desc LIMIT $start,$perpage";
	$query = DB::query($sql);
	$hash_datas = array();
	//$more_list = array();
	$uid_feedcount = array();
	$feed_users = $feed_list = $user_list = $filter_list  = $list = $mlist = array();
	$count = $filtercount =0;
	while ($groupfeed = DB::fetch($query)) {
		$groupfeed[username]=user_get_user_name($groupfeed[uid]);
		$groupfeed = mkfeed($groupfeed);
		if(ckicon_uid($groupfeed)) {
			if($groupfeed['dateline']>=$_G['home_today']) {
				$dkey = 'today';
			} elseif ($groupfeed['dateline']>=$_G['home_today']-3600*24) {
				$dkey = 'yesterday';
			} else {
				$dkey = dgmdate($groupfeed['dateline'], 'Y-m-d');
			}


			if(empty($groupfeed['hash_data'])) {
				if(empty($feed_users[$dkey][$groupfeed['uid']])) $feed_users[$dkey][$groupfeed['uid']] = $groupfeed;
				if(empty($uid_feedcount[$dkey][$groupfeed['uid']])) $uid_feedcount[$dkey][$groupfeed['uid']] = 0;

				$uid_feedcount[$dkey][$groupfeed['uid']]++;

				$feed_list[$dkey][$groupfeed['uid']][] = $groupfeed;

			} elseif(empty($hash_datas[$groupfeed['hash_data']])) {
				$hash_datas[$groupfeed['hash_data']] = 1;
				if(empty($feed_users[$dkey][$groupfeed['uid']])) $feed_users[$dkey][$groupfeed['uid']] = $groupfeed;
				if(empty($uid_feedcount[$dkey][$groupfeed['uid']])) $uid_feedcount[$dkey][$groupfeed['uid']] = 0;


				$uid_feedcount[$dkey][$groupfeed['uid']] ++;

				
				$feed_list[$dkey][$groupfeed['uid']][$groupfeed['hash_data']] = $groupfeed;

			} else {
				$user_list[$groupfeed['hash_data']][] = "<a href=\"home.php?mod=space&uid=$groupfeed[uid]\">$groupfeed[username]</a>";
			}


		} else {
			$filtercount++;
			$filter_list[] = $groupfeed;
		}
		$count++;
	}
	
	$getcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE fid=".$fid." or sharetofids like '%,".$fid.",%'");
	$url = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=groupfeed&plugin_op=groupmenu";
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	
	return array("feed_users"=>$feed_users,"feed_list"=>$feed_list,"multipage"=>$multipage);

}

?>
