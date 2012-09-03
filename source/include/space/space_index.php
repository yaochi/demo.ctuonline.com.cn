<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_index.php 10793 2010-05-17 01:52:12Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/space');

space_merge($space, 'field_home');
$userdiy = getuserdiydata($space);

if ($_GET['op'] == 'getmusiclist') {
	if(empty($space['uid'])) {
		exit();
	}
	$reauthcode = substr(md5($_G['authkey'].$space['uid']), 6, 16);
	if($reauthcode == $_GET['hash']) {
		space_merge($space,'field_home');
		$userdiy = getuserdiydata($space);
		$musicmsgs = $userdiy['parameters']['music'];
		$outxml = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
		$outxml .= '<playlist version="1">'."\n";
		$outxml .= '<mp3config>'."\n";
		$showmod = 'big' == $musicmsgs['config']['showmod'] ? 'true' : 'false';
		$outxml .= '<showdisplay>'.$showmod.'</showdisplay>'."\n";
		$outxml .= '<autostart>'.$musicmsgs['config']['autorun'].'</autostart>'."\n";
		$outxml .= '<showplaylist>true</showplaylist>'."\n";
		$outxml .= '<shuffle>'.$musicmsgs['config']['shuffle'].'</shuffle>'."\n";
		$outxml .= '<repeat>all</repeat>'."\n";
		$outxml .= '<volume>100</volume>';
		$outxml .= '<linktarget>_top</linktarget> '."\n";
		$outxml .= '<backcolor>0x'.substr($musicmsgs['config']['crontabcolor'], -6).'</backcolor> '."\n";
		$outxml .= '<frontcolor>0x'.substr($musicmsgs['config']['buttoncolor'], -6).'</frontcolor>'."\n";
		$outxml .= '<lightcolor>0x'.substr($musicmsgs['config']['fontcolor'], -6).'</lightcolor>'."\n";
		$outxml .= '<jpgfile>'.$musicmsgs['config']['crontabbj'].'</jpgfile>'."\n";
		$outxml .= '<callback></callback> '."\n";
		$outxml .= '</mp3config>'."\n";
		$outxml .= '<trackList>'."\n";
		foreach ($musicmsgs['mp3list'] as $value){
			$outxml .= '<track><annotation>'.$value['mp3name'].'</annotation><location>'.$value['mp3url'].'</location><image>'.$value['cdbj'].'</image></track>'."\n";
		}
		$outxml .= '</trackList></playlist>';
		$outxml = diconv($outxml, CHARSET, 'UTF-8');
		obclean();
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("Content-type: application/xml; charset=utf-8");
		echo $outxml;
	}
	exit();

}else{

	$widths = getlayout($userdiy['currentlayout']);
	$leftlist = formatdata($userdiy, 'left');
	$centerlist = formatdata($userdiy, 'center');
	$rightlist = formatdata($userdiy, 'right');

	$viewuids = $_G['cookie']['viewuids']?explode('_', $_G['cookie']['viewuids']):array();
	if($_G['uid'] && !$space['self'] && !in_array($space['uid'], $viewuids)) {
		member_count_update($space['uid'], array('views' => 1));
		$viewuids[$space['uid']] = $space['uid'];
		dsetcookie('viewuids', implode('_', $viewuids));
	}

	if(!$space['self'] && $_G['uid']) {
		$query = DB::query("SELECT dateline FROM ".DB::table('home_visitor')." WHERE uid='$space[uid]' AND vuid='$_G[uid]'");
		$visitor = DB::fetch($query);
		$is_anonymous = empty($_G['cookie']['anonymous_visit_'.$_G['uid'].'_'.$space['uid']]) ? 0 : 1;
		if(empty($visitor['dateline'])) {
			$setarr = array(
				'uid' => $space['uid'],
				'vuid' => $_G['uid'],
				'vusername' => $is_anonymous ? '' : $_G['username'],
				'dateline' => $_G['timestamp']
			);
			DB::insert('home_visitor', $setarr, 0, true);
			//积分
			require_once libfile('function/credit'); 
			credit_create_credit_log($_G['uid'], 'visit', $space['uid']);
			
			show_credit();
		} else {
			if($_G['timestamp'] - $visitor['dateline'] >= 300) {
				DB::update('home_visitor', array('dateline'=>$_G['timestamp'], 'vusername'=>$is_anonymous ? '' : $_G['username']), array('uid'=>$space['uid'], 'vuid'=>$_G['uid']));
			}
			if($_G['timestamp'] - $visitor['dateline'] >= 3600) {
				//积分
				require_once libfile('function/credit'); 
				credit_create_credit_log($_G['uid'], 'visit', $space['uid']);
				
				show_credit();
			}
		}
		updatecreditbyaction('visit', 0, array(), $space['uid']);
	}

	dsetcookie('home_diymode', 1);
}


//修改  by songsp 2010-7-26 9:20:23

if( checkIsOfficial( $space['uid'] ) ) {
	$isOfficial = 1; 
	$_GET['from']="space";	
	$_GET['view']="me";
	require_once libfile('space/blog', 'include');
	
}else{
	include_once(template('home/space_index'));
}






//include_once(template('home/space_index'));

function formatdata($data, $position) {
	$list = array();
	foreach ((array)$data['block']['frame`frame1']['column`frame1_'.$position] as $blockname => $blockdata) {
		if (strpos($blockname, 'block`') === false) continue;
		$name = $blockdata['attr']['name'];
		$list[$name] = getblockhtml($name, $data['parameters'][$name]);
	}
	return $list;
}

function show_credit() {
	global $_G, $space;

	$showcredit = DB::result(DB::query("SELECT credit FROM ".DB::table('home_show')." WHERE uid='{$space[uid]}'"));
	if($showcredit>0) {
		if($showcredit == 1) {
			notification_add($space['uid'], 'show', 'show_out');
		}
		DB::query("UPDATE ".DB::table('home_show')." SET credit=credit-1 WHERE uid='{$space[uid]}' AND credit>0");
	}
}
?>