<?php
/* Function: 直播 创建,修改,删除等操作
 * Com.:
 * Author: wuhan
 * Date: 2010-7-27
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$liveid = empty($_GET['liveid'])?0:intval($_GET['liveid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$live = array();
if($liveid) {
	$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid='$liveid'");
	$live = DB::fetch($query);
}

if(empty($live)) {
	//	TODO 权限检测

	$waittime = 0;
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	$live['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);

}else if($_GET['op'] == 'join'){//直播你我课堂
	require_once libfile('function/live');
	if($live['type']!=2 && $live['starttime']-60*60 > $_G['timestamp']){//不允许提前进入直播
		showmessage('本期直播尚未开始,您可以提前一小时进入直播','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=index&');
	}else if($live['endtime'] < $_G['timestamp']){//直播结束, 开始点播
		$live_url = $live['url'];
		if(empty($live_url)){

			if ( $live['type']!=3 ){
			    //qiaoyongzhi,2010-2-17;ESN社区中的光点星课堂直播结束，对应的课程URL还没有放链接时，提示信息有问题。提示信息显示的是一段代码且显示了盛维的logo，正确的应该是显示"目前课件正在制作中，敬请期待"
        	    showmessage('目前课件正在制作中，敬请期待...');
			}

			switch($live['type']){
				case 0://盛维直播 web
					$live_url = getShengWeiUrl("live$live[liveid]", $live['subject'], '', $live['starttime'], $live['endtime'], $_G['uid'], $_G['username'], $live['firstman_ids'], $live['secondman_ids'], $live['guest_ids']);
					break;
				case 1://盛维直播客户端
					$live_url = getShengWeiClient($live['liveid'], $live['subject'], $_G['uid'], $_G['username'], $live['firstman_ids'], $live['secondman_ids']);
					break;
				case 2://信产直播
					$live_url = $live['url'];
					break;
				case 3://新的直播
					$live_url = getNewLiveUrl($live['newliveid'],$live['fid']);
					break;
			}
		}
		if($live_url){
			DB :: query("UPDATE " . DB :: table('group_live') . " SET playnum=playnum+1 WHERE liveid='$live[liveid]'");

			//动态
			//feed_replay_live($live['liveid']);

			//点播 经验值
			require_once libfile('function/group');
			group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'unicast_join', $live['liveid']);

			//点播积分
			require_once libfile('function/credit');
			credit_create_credit_log($_G['uid'], 'joinvod', $live['liveid']);
			header("Location:".$live_url);
			//showmessage('relive_start', $live_url);
		}
		else{
			showmessage('failed_to_get_relive');
		}
	}else{
		$live_url = null;
		$duration=$live['endtime']-$_G['timestamp'];
		//$duration=$live['endtime']-$live['starttime'];
		switch($live['type']){
			case 0://盛维直播 web
				$live_url = getShengWeiUrl("live$live[liveid]", $live['subject'], '', $live['starttime'], $live['endtime'], $_G['uid'], $_G['username'], $live['firstman_ids'], $live['secondman_ids'], $live['guest_ids']);
				break;
			case 1://盛维直播客户端
				$live_url = getShengWeiClient($live['liveid'], $live['subject'], $_G['uid'], $_G['username'], $live['firstman_ids'], $live['secondman_ids'],$duration);
				break;
			case 2://信产直播
				$live_url = $live['url'];
				break;
			case 3://新的直播
				$live_url = getNewLiveUrl($live['newliveid'],$live['fid']);
				break;
		}
		if($live_url){
			DB :: query("UPDATE " . DB :: table('group_live') . " SET playnum=playnum+1 WHERE liveid='$live[liveid]'");

			//动态
			//feed_play_live($live['liveid']);

			//直播 经验值
			require_once libfile('function/group');
			group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'direct_join', $live['liveid']);

			//直播积分
			require_once libfile('function/credit');
			credit_create_credit_log($_G['uid'], 'joinbroadcast', $live['liveid']);
			header("Location:".$live_url);
			//showmessage('live_start', $live_url);
		}
		else{
			showmessage('failed_to_get_live');
		}
	}
}else {
	if($_G['uid'] != $live['uid'] && !checkperm_group('managelive')) {
		showmessage('no_privilege');
	}
}

if(submitcheck('livesubmit', 0, $seccodecheck, $secqaacheck)) {

	if(empty($live['liveid'])) {
		$live = array();
	}

	if($newlive = live_post($_POST, $live)) {
		$url = join_plugin_action2("index");
		if($_POST['isnext'] == 1){
			$url = join_plugin_action2('livecp', array('liveid' => $newlive['liveid'], 'op' => 'setinfoedit'));
		}
		showmessage('do_success', $url);
	} else {
		showmessage('that_should_at_least_write_things', NULL, array(), array('return'=>1));
	}
}

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		if(deletelives(array($liveid))) {
			showmessage('do_success', join_plugin_action2("index"));
		} else {
			showmessage('failed_to_delete_operation');
		}
	}

} elseif($_GET['op'] == 'edithot') {

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('group_live', array('hot'=>$_POST['hot']), array('liveid'=>$live['liveid']));
		if($_POST['hot']>0) {
			require_once libfile('function/live');
			feed_publish_live($live['liveid']);
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$live['liveid'], 'idtype'=>'liveid'));
		}

		showmessage('do_success', join_plugin_action2("index")."&id=$live[liveid]");
	}

} else {

	$friendarr = array($live['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($live['friend'] == 4) {
		$passwordstyle = '';
	} elseif($live['friend'] == 2) {
		$selectgroupstyle = '';
		if($live['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($live[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
			}
			$live['target_names'] = implode(' ', $names);
		}
	}


	$live['message'] = dhtmlspecialchars($live['message']);

	$allowhtml = checkperm('allowhtml');

	require_once libfile('function/friend');
	$groups = friend_group_list();

	$menuactives = array('space'=>' class="active"');
	$live['isstart'] = -1;
	if($live['starttime']){
		$live['isstart'] = ($live['starttime'] >  $_G[timestamp]) ? -1 : 1;
		$live['starttime'] = dgmdate($live['starttime'],'Y-m-d H:i');
	}
	$live['isend'] = -1;
	if($live['endtime']){
		$live['isend'] = ($live['endtime'] >  $_G[timestamp]) ? -1 : 1;
		$live['endtime'] = dgmdate($live['endtime'],'Y-m-d H:i');
	}
	if($live['firstman_ids']) {
		$live['firstman_names'] = getUserRealNameById($live['firstman_ids']);
	}
	if($live['secondman_ids']) {
		$live['secondman_names'] = getUserRealNameById($live['secondman_ids']);
	}
	if($live['guest_ids']) {
		$live['guest_names'] = getUserRealNameById($live['guest_ids']);
	}

	//分类
	require_once libfile("function/category");
    $pluginid = $_GET["plugin_name"];
    $allowrequired = common_category_is_required($_G['fid'], $pluginid);
    $categorys = array();
    if(common_category_is_enable($_G['fid'], $pluginid)){
        $categorys = common_category_get_category($_G['fid'], $pluginid);
    }
}
if($_GET['op'] == "setinfoedit"){
	include template("grouplive:newlivesetinfo");
}else{
	include template("grouplive:livecp");
}
dexit();
?>
