<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_member.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_member {

	var $version = '1.0';
	var $name = 'member_name';
	var $description = 'member_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array(
		'act' => array(
			'title' => 'member_complete_var_act',
			'type' => 'mradio',
			'value' => array(
				array('buddy', 'member_complete_var_act_buddy'),
				array('favorite', 'member_complete_var_act_favorite'),
				array('magic', 'member_complete_var_act_magic'),
			),
			'default' => 'buddy',
			'sort' => 'complete',
		),
		'num' => array(
			'title' => 'member_complete_var_num',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		),
		'time' => array(
			'title' => 'member_complete_var_time',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		)
	);

	function preprocess($task) {
		global $_G;

		$act = DB::result_first("SELECT value FROM ".DB::table('common_taskvar')." WHERE taskid='$task[taskid]' AND variable='act'");
		if($act == 'buddy') {
			loaducenter();
			DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$_G[uid]', 'buddy$task[taskid]', '".(uc_friend_totalnum($_G['uid'], 1) + uc_friend_totalnum($_G['uid'], 3))."', '$_G[timestamp]')");
		} /*noteX
		 elseif($act == 'favorite') {
			DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$_G[uid]', 'favorite$task[taskid]', '".DB::result_first("SELECT COUNT(*) FROM ".DB::table('favorites')." WHERE uid='$_G[uid]' AND tid>'0'")."', '$_G[timestamp]')");
		}*/
	}

	function csc($task = array()) {
		global $_G;

		$taskvars = array('num' => 0);
		$num = 0;
		$query = DB::query("SELECT variable, value FROM ".DB::table('common_taskvar')." WHERE taskid='$task[taskid]'");
		while($taskvar = DB::fetch($query)) {
			if($taskvar['value']) {
				$taskvars[$taskvar['variable']] = $taskvar['value'];
			}
		}

		$taskvars['time'] = floatval($taskvars['time']);
		if($taskvars['act'] == 'buddy') {
			loaducenter();
			$num = uc_friend_totalnum($_G['uid'], 1) + uc_friend_totalnum($_G['uid'], 3) - DB::result_first("SELECT value FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='buddy$task[taskid]'");
		} /*noteX
		elseif($taskvars['act'] == 'favorite') {
			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('favorites')." WHERE uid='$_G[uid]' AND tid>'0'") - DB::result_first("SELECT value FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='favorite$task[taskid]'");
		} */elseif($taskvars['act'] == 'magic') {
			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_magiclog')." WHERE action='2' AND uid='$_G[uid]'".($taskvars['time'] ? " AND dateline BETWEEN $task[applytime] AND $task[applytime]+3600*$taskvars[time]" : " AND dateline>$task[applytime]"));
		}

		if($num && $num >= $taskvars['num']) {
			if(in_array($taskvars['act'], array('buddy', 'favorite'))) {
				DB::query("DELETE FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='$taskvars[act]$task[taskid]'");
			}
			return TRUE;
		} elseif($taskvars['time'] && TIMESTAMP >= $task['applytime'] + 3600 * $taskvars['time'] && (!$num || $num < $taskvars['num'])) {
			return FALSE;
		} else {
			return array('csc' => $num > 0 && $taskvars['num'] ? sprintf("%01.2f", $num / $taskvars['num'] * 100) : 0, 'remaintime' => $taskvars['time'] ? $task['applytime'] + $taskvars['time'] * 3600 - TIMESTAMP : 0);
		}
	}

	function view($task, $taskvars) {
		$return = lang('task/member', 'task_complete_time_start');
		if($taskvars['complete']['time']) {
			$return .= lang('task/member', 'task_complete_time_limit', array('value' => $taskvars['complete']['time']['value']));
		}
		if($taskvars['complete']['act']['value'] == 'buddy') {
			$return .= lang('task/member', 'task_complete_act_buddy', array('value' => $taskvars['complete']['num']['value']));
		} elseif($taskvars['complete']['act']['value'] == 'favorite') {
			$return .= lang('task/member', 'task_complete_act_favorite', array('value' => $taskvars['complete']['num']['value']));
		} else {
			$return .= lang('task/member', 'task_complete_act_magic', array('value' => $taskvars['complete']['num']['value']));
		}
		return $return;
	}

}


?>