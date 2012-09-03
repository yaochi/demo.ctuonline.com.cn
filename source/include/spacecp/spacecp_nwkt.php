<?php
/* Function: 你我课堂 创建,修改,删除等操作
 * Com.:
 * Author: wuhan
 * Date: 2010-7-20
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/nwkt');

$nwktid = empty($_GET['nwktid'])?0:intval($_GET['nwktid']);
$op = empty($_GET['op'])?'':$_GET['op'];
$nwkt = array();
if($nwktid) {
	$query = DB :: query("SELECT at.*, n.* FROM " . DB :: table('home_nwkt') . " n LEFT JOIN " . DB :: table('home_attachment') . " at ON at.aid=n.aid WHERE n.nwktid='$nwktid'");
	$nwkt = DB::fetch($query);
}

if(empty($nwkt)) {
//	TODO 权限检测,根据需要再修改
//	if(!checkperm('allownwkt')) {
//		showmessage('no_authority_to_add_log');
//	}
//
//	ckrealname('nwkt');
//
//	ckvideophoto('nwkt');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	$nwkt['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);
	$nwkt['message'] = empty($_GET['message'])?'':getstr($_GET['message'], 5000, 1, 0);

}else if($_GET['op'] == 'attach'){
}else if($_GET['op'] == 'join'){//直播你我课堂
	
	require_once libfile('function/live');
	if(!ckType($nwkt['uid'], $nwkt['type'], $nwkt['firstman_ids'], $nwkt['secondman_ids'], $nwkt['guestman_ids'])){
		include template('home/space_privacy');
		exit ();
	}
	else if($nwkt['starttime']-60*30 > $_G['timestamp']){//不允许提前进入直播
		showmessage('no_live_start','home.php?mod=space&uid='.$nwkt[uid].'&do=nwkt&id='.$nwkt[nwktid]);
	}
	else if($nwkt['maxnum'] > 0 && $nwkt['maxnum'] < getShengWeiClientCount($nwkt['nwktid'])){
		showmessage('live_max');
	}
	else{
		$duration=$nwkt[endtime]-$_G['timestamp'];
		//$duration=$nwkt[endtime]-$nwkt[starttime];
		$live_url = getShengWeiClient($nwkt['nwktid'], $nwkt['subject'], $_G['uid'], $_G['username'], $nwkt['firstman_ids'], $nwkt['secondman_ids'],$duration);
//		$live_url = getShengWeiUrl("nwkt$nwkt[nwktid]", $nwkt['subject'], '', $nwkt['starttime'], $nwkt['endtime'], $_G['uid'], $_G['username'], $nwkt['firstman_ids'], $nwkt['secondman_ids'], $nwkt['guest_ids']);
		if($live_url){
			if($nwkt['endtime'] >= $_G['timestamp']){
				//直播积分
				require_once libfile('function/credit');
				credit_create_credit_log($_G['uid'], 'joinyouibroadcast', $nwkt['nwktid']);
			}
			
			showmessage('live_start', $live_url);
		}
		else{
			showmessage('failed_to_get_live');
		}
		
	}
}else {

	if($_G['uid'] != $nwkt['uid'] && !checkperm('managenwkt')) {
		showmessage('no_authority_operation_of_the_nwkt');
	}
	
	if($nwkt['aid']) {
		if($nwkt['isimage']) {
			$nwkt['attachurl'] = ($nwkt['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/'.$nwkt['attachment'];
			$nwkt['thumb'] = $nwkt['attachurl'].($nwkt['thumb'] ? '.thumb.jpg' : '');
			$nwkt['width'] = $nwkt['thumb'] && $_G['setting']['thumbwidth'] < $nwkt['width'] ? $_G['setting']['thumbwidth'] : $nwkt['width'];
		}
	}
}

if(submitcheck('nwktsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(empty($nwkt['nwktid'])) {
		$nwkt = array();
	} else {
//TODO 权限
//		if(!checkperm('allownwkt')) {
//			showmessage('no_authority_to_add_log');
//		}
	}
	
	if($newnwkt = nwkt_post($_POST, $nwkt)) {
		updateImage($newnwkt, $nwkt);
		
		require_once libfile('function/home_attachment');
		$attachmentnum = updateattach('', $newnwkt['nwktid'], 'nwktid', $_G['gp_attachnew'], $_G['gp_attachdel']);
		
		DB::update('home_nwkt', array('attachmentnum' => $attachmentnum), array('nwktid' => $newnwkt['nwktid']));
		
		$url = 'home.php?mod=space&uid='.$newnwkt['uid'].'&do=nwkt&quickforward=1&id='.$newnwkt['nwktid'];
		showmessage('do_success', $url);
	}else {
		showmessage('that_should_at_least_write_things', NULL, array(), array('return'=>1));
	}
}

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		require_once libfile('function/nwkt');
		if(deletenwkts(array($nwktid))) {
			
			//删除积分
			require_once libfile('function/credit');
			credit_create_credit_log($nwkt['uid'], "deleteyouiclassroom", $nwkt['nwktid']);
			
			showmessage('do_success', "home.php?mod=space&uid=$nwkt[uid]&do=nwkt&view=me");
		} else {
			showmessage('failed_to_delete_operation');
		}
	}

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('managenwkt')) {
		showmessage('no_privilege');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_nwkt', array('hot'=>$_POST['hot']), array('nwktid'=>$nwkt['nwktid']));
		if($_POST['hot']>0) {
			require_once libfile('function/nwkt');
			feed_publish_nwkt($nwktarr['nwktid']);
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$nwkt['nwktid'], 'idtype'=>'nwktid'));
		}

		showmessage('do_success', "home.php?mod=space&uid=$nwkt[uid]&do=nwkt&quickforward=1&id=$nwkt[nwktid]");
	}

} else {
	$classarr = getnwktclassarr();

	$friendarr = array($nwkt['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($nwkt['friend'] == 4) {
		$passwordstyle = '';
	} elseif($nwkt['friend'] == 2) {
		$selectgroupstyle = '';
		if($nwkt['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($nwkt[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
			}
			$nwkt['target_names'] = implode(' ', $names);
		}
	}


	$nwkt['message'] = dhtmlspecialchars($nwkt['message']);

	$allowhtml = checkperm('allowhtml');

	require_once libfile('function/friend');
	$groups = friend_group_list();

	$menuactives = array('space'=>' class="active"');
	
	if($nwkt['starttime'])
		$nwkt['starttime'] = dgmdate($nwkt['starttime']);
	
	if($nwkt['endtime'])
		$nwkt['endtime'] = dgmdate($nwkt['endtime']);
	
	if(empty($nwkt['nwktid'])){
		$nwkt['firstman_ids'] = $_G['uid'];
	}
	
	
	if($nwkt['firstman_ids']) {
		$nwkt['firstman_names'] = getRealNameById($nwkt['firstman_ids']);
	}
	if($nwkt['secondman_ids']) {
		$nwkt['secondman_names'] = getRealNameById($nwkt['secondman_ids']);
	}
	if($nwkt['guest_ids']) {
		$nwkt['guest_names'] = getRealNameById($nwkt['guest_ids']);
	}
	require_once libfile('function/home_attachment');
	$attachlist = getattach($nwkt['nwktid'],'nwktid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];
	unset($attachlist);
}

include_once template("home/spacecp_nwkt");

//更新主题图片
function updateImage($newnwkt, $nwkt){
	//新建
	if($newnwkt['nwktid'] != $nwkt['nwktid'] && $newnwkt['aid']){
		DB::update('home_attachment', array('id' => $newnwkt['nwktid'], 'idtype' => 'nwktid'), array('aid'=> $newnwkt['aid']));
	}
	//更新
	else if($newnwkt['aid'] != $nwkt['aid']){
		DB::update('home_attachment', array('id' => $newnwkt['nwktid'], 'idtype' => 'nwktid'), array('aid'=> $newnwkt['aid']));
		
		if($nwkt['aid']){
			DB::delete('home_attachment', array('aid' => $nwkt['aid']));
			
			require_once libfile('function/home_attachment');
			dunlink_home($nwkt);
		}
	}
}
?>
