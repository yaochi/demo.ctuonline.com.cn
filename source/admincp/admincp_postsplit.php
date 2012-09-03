<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postsplit.php 10329 2010-05-10 09:32:19Z wangjinbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IN_DEBUG', false);

define('MAX_POSTS_MOVE', 100000);
cpheader();
$topicperpage = 50;

if(empty($operation)) {
	$operation == 'manage';
}

$posttable_info = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='posttable_info'");
if(empty($posttable_info)) {
	$posttable_info = array();
	$posttable_info[0]['type'] = 'primary';
} else {
	$posttable_info = unserialize($posttable_info);
}

$posttableids = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='posttableids'");
if(empty($posttableids)) {
	$posttableids = array();
} else {
	$posttableids = unserialize($posttableids);
}

if($operation == 'manage') {
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_manage')) {
		showsubmenu('nav_postsplit', array(
			array('nav_postsplit_manage', 'postsplit&operation=manage', 1),
			array('nav_postsplit_move', 'postsplit&operation=move', 0),
		));

		showtips('postsplit_manage_tips');
		showformheader('postsplit&operation=manage');
		showtableheader();

		showsubtitle(array('postsplit_manage_tablename', $lang['type'], 'postsplit_manage_postcount', 'postsplit_manage_datalength', 'postsplit_manage_indexlength', 'postsplit_manage_table_createtime', 'postsplit_manage_table_memo', ''));
		$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");

		$tablename = DB::table('forum_post');
		$tableid = 0;
		$tablestatus = gettablestatus($tablename);
		$postcount = $tablestatus['Rows'];
		$data_length = $tablestatus['Data_length'];
		$index_length = $tablestatus['Index_length'];

		$primarychecked = $posttable_info[$tableid]['type'] == 'primary' ? 'checked="checked"' : '';
		$additionchecked = $posttable_info[$tableid]['type'] == 'addition' ? 'checked="checked"' : '';
		$archivechecked = $posttable_info[$tableid]['type'] == 'archive' ? 'checked="checked"' : '';

		$tabletypeselect = "<select name=\"tabletype[$tableid]\">";
		foreach(array('primary', 'addition', 'archive') as $typename) {
			$typename_zhs = $lang['postsplit_manage_'.$typename.'_table'];
			$tabletypeselect .= "<option value=\"$typename\"".($posttable_info[$tableid]['type'] == $typename ? ' selected="selected"' : '').">$typename_zhs</option>";
		}
		$tabletypeselect .= "</select>";
		showtablerow('', array('', '', 'class="td25"'), array($tablename, $tabletypeselect, $postcount, $data_length, $index_length, $tablestatus['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$posttable_info[0]['memo']}\" />", ''));

		while($table = DB::fetch($query)) {
			list($tempkey, $tablename) = each($table);
			$tableid = gettableid($tablename);
			if(in_array($tableid, array('tableid'))) {
				continue;
			}
			$tablestatus = gettablestatus($tablename);
			$postcount = $tablestatus['Rows'];
			$data_length = $tablestatus['Data_length'];
			$index_length = $tablestatus['Index_length'];

			$primarychecked = $posttable_info[$tableid]['type'] == 'primary' ? 'checked="checked"' : '';
			$additionchecked = $posttable_info[$tableid]['type'] == 'addition' ? 'checked="checked"' : '';
			$archivechecked = $posttable_info[$tableid]['type'] == 'archive' ? 'checked="checked"' : '';

			$tabletypeselect = "<select name=\"tabletype[$tableid]\">";
			foreach(array('primary', 'addition', 'archive') as $typename) {
				$typename_zhs = $lang['postsplit_manage_'.$typename.'_table'];
				$tabletypeselect .= "<option value=\"$typename\"".($posttable_info[$tableid]['type'] == $typename ? ' selected="selected"' : '').">$typename_zhs</option>";
			}
			$tabletypeselect .= "</select>";

			showtablerow('', array('style="width: 160px;"'), array($tablename, $tabletypeselect, $postcount, $data_length, $index_length, $tablestatus['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$posttable_info[$tableid]['memo']}\" />", '<a href="'.ADMINSCRIPT."?action=postsplit&operation=droptable&tableid={$tableid}\"> {$lang['delete']} </a>"));
		}
		showsubmit('postsplit_manage', 'postsplit_manage_update_tabletype_submit', '', "<a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=addnewtable&tabletype=primary\">{$lang['postsplit_manage_primary_table_add']}</a>&nbsp;&nbsp;<a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=addnewtable&tabletype=archive\">{$lang['postsplit_manage_archive_table_add']}</a> <a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=pidreset\">{$lang['postsplit_manage_pidreset']}</a>");
		showtablefooter();
		showformfooter();
	} else {
		$primary_count = $addition_count = $archive_count = 0;
		foreach($_G['gp_tabletype'] as $key => $value) {
			$posttable_info[$key]['memo'] = $_G['gp_memo'][$key];
			switch($value) {
				case 'primary':
					$posttable_info[$key]['type'] = 'primary';
					$primary_count ++;
					break;
				case 'addition':
					$posttable_info[$key]['type'] = 'addition';
					$addition_count ++;
					break;
				case 'archive':
					$posttable_info[$key]['type'] = 'archive';
					$archive_count ++;
					break;
			}
		}
		foreach($posttable_info as $key => $value) {
			var_dump($key);
			if($key === '') {
				unset($posttable_info[$key]);
			}
		}

		if($primary_count != 1) {
			cpmsg('postsplit_no_prime_table', 'action=postsplit&operation=manage', 'error');
		}
		if($addition_count > 1) {
			cpmsg('postsplit_more_addition_table', 'action=postsplit&operation=manage', 'error');
		}

		DB::insert('common_setting', array(
			'skey' => 'posttable_info',
			'svalue' => serialize($posttable_info),
		), false, true);
		save_syscache('posttable_info', $posttable_info);
		updatecache('setting');
		cpmsg('postsplit_table_type_update_succeed', 'action=postsplit&operation=manage', 'succeed');
	}

} elseif($operation == 'move') {
	if(!$_G['setting']['bbclosed'] && !IN_DEBUG) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	require_once libfile('function/forumlist');
	$threadtableselect = '<select name="threadtableid"><option value="0">'.DB::table('forum_thread')."</option>";
	$threadtableids = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='threadtableids'");

	if(!$threadtableids) {
		$threadtableids = array();
	} else {
		$threadtableids = unserialize($threadtableids);
	}
	foreach($threadtableids as $threadtableid) {
		$selected = $_G['gp_threadtableid'] == $threadtableid ? 'selected="selected"' : '';
		$threadtableselect .= "<option value=\"$threadtableid\" $selected>".DB::table("forum_thread_$threadtableid")."</option>";
	}
	$threadtableselect .= '</select>';

	$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	if(isset($_G['gp_inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"{$_G['gp_inforum']}\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}
	$posttableselect = '<select name="sourcetableid"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>';
	foreach(array_keys($posttable_info) as $tableid) {
		$selected = $_G['gp_sourcetableid'] == $tableid ? 'selected="selected"' : '';
		$tablename = $tableid ? "forum_post_$tableid" : 'forum_post';
		$typename = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
		$posttableselect .= "<option value=\"$tableid\" $selected>".DB::table($tablename)." ($typename)</option>";
	}

	$typeselect = $sortselect = '';
	$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
	while($type = DB::fetch($query)) {
		if($type['special']) {
			$sortselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		} else {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		}
	}

	if(isset($_G['gp_insort'])) {
		$sortselect = preg_replace("/(\<option value=\"{$_G['gp_insort']}\")(\>)/", "\\1 selected=\"selected\" \\2", $sortselect);
	}

	if(isset($_G['gp_intype'])) {
		$typeselect = preg_replace("/(\<option value=\"{$_G['gp_intype']}\")(\>)/", "\\1 selected=\"selected\" \\2", $typeselect);
	}
echo <<<EOT
<script src="static/js/forum_calendar.js"></script>
<script type="text/JavaScript">
	function page(number) {
		$('threadforum').page.value=number;
		$('threadforum').postsplit_move_search.click();
	}
</script>
EOT;
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_move_submit') && !$_G['gp_moving']) {
		showsubmenu('nav_postsplit', array(
			array('nav_postsplit_manage', 'postsplit&operation=manage', 0),
			array('nav_postsplit_move', 'postsplit&operation=move', 1),
		));
		showtips('postsplit_move_tips');
		showtagheader('div', 'threadsearch', !submitcheck('postsplit_move_search'));
		showformheader('postsplit&operation=move', '', 'threadforum');
		showhiddenfields(array('page' => $_G['gp_page']));
		showtableheader();
		showsetting('threads_search_detail', 'detail', $_G['gp_detail'], 'radio');
		showsetting('postsplit_move_threadtable', '', '', $threadtableselect);
		showsetting('postsplit_move_threads_search_posttable', '', '', $posttableselect);
		showsetting('threads_search_forum', '', '', $forumselect);
		showsetting('postsplit_move_tidrange', array('tidmin', 'tidmax'), array($_G['gp_tidmin'], $_G['gp_tidmax']), 'range');
		showsetting('threads_search_time', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');

		showtagheader('tbody', 'advanceoption');
		showsetting('threads_search_keyword', 'keywords', $_G['gp_keywords'], 'text');
		showsetting('threads_search_user', 'users', $_G['gp_users'], 'text');
		showsetting('threads_search_type', '', '', '<select name="intype"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$typeselect.'</select>');
		showsetting('threads_search_sort', '', '', '<select name="insort"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>');
		showsetting('threads_search_viewrange', array('viewsmore', 'viewsless'), array($_G['gp_viewsmore'], $_G['gp_viewsless']), 'range');
		showsetting('threads_search_replyrange', array('repliesmore', 'repliesless'), array($_G['gp_repliesmore'], $_G['gp_repliesless']), 'range');
		showsetting('threads_search_readpermmore', 'readpermmore', $_G['gp_readpermmore'], 'text');
		showsetting('threads_search_pricemore', 'pricemore', $_G['gp_pricemore'], 'text');
		showsetting('threads_search_noreplyday', 'noreplydays', $_G['gp_noreplydays'], 'text');
		showsetting('threads_search_type', array('specialthread', array(
			array(0, cplang('unlimited'), array('showspecial' => 'none')),
			array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
			array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
		), TRUE), $_G['gp_specialthread'], 'mradio');
		showtablerow('id="showspecial" style="display:'.($_G['gp_specialthread'] ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
			1 => cplang('thread_poll'),
			2 => cplang('thread_trade'),
			3 => cplang('thread_reward'),
			4 => cplang('thread_activity'),
			5 => cplang('thread_debate')
		), $_G['gp_special'] ? $_G['gp_special'] : array(0)));
		showsetting('threads_search_sticky', array('sticky', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), $_G['gp_sticky'], 'mradio');
		showsetting('threads_search_digest', array('digest', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), $_G['gp_digest'], 'mradio');
		showsetting('threads_search_attach', array('attach', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), $_G['gp_attach'], 'mradio');
		showsetting('threads_rate', array('rate', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), $_G['gp_rate'], 'mradio');
		showsetting('threads_highlight', array('highlight', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), $_G['gp_highlight'], 'mradio');
		showtagfooter('tbody');

		showsubmit('postsplit_move_search', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		if(submitcheck('postsplit_move_search')) {
			$searchurladd = array();
			if($_G['gp_detail']) {
				$pagetmp = $page;
				$threadlist = postsplit_search_threads(($pagetmp - 1) * $topicperpage, $topicperpage);
			} else {
				$threadlist = postsplit_search_threads();
			}

			$fids = array();
			$tids = '0';
			if($_G['gp_detail']) {
				$threads = '';
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$thread['lastpost'] = dgmdate($thread['lastpost']);
					$threads .= showtablerow('', array('class="td25"', '', '', '', '', ''), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" checked=\"checked\" />",
						"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
						"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">{$_G['cache'][forums][$thread[fid]][name]}</a>",
						$thread['posttableid'] ? DB::table("forum_post_{$thread['posttableid']}") : DB::table("forum_post"),
						"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
						$thread['replies'],
						$thread['views']
					), TRUE);
				}
				$multi = multi($threadcount, $topicperpage, $page, ADMINSCRIPT."?action=postsplit&amp;operation=move");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=postsplit&amp;operation=move&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=postsplit&amp;operation=move&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$tids .= ','.$thread['tid'];
				}
				$multi = '';
			}
			$fids = implode(',', array_unique($fids));

			showtagheader('div', 'threadlist', TRUE);
			$urladd = implode('&', $searchurladd);
			showformheader("postsplit&operation=move&threadtableid={$_G['gp_threadtableid']}&threadtomove=".count($threadlist)."&{$urladd}");
			showhiddenfields($_G['gp_detail'] ? array('fids' => $fids) : array('fids' => $fids, 'tids' => $tids));
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
			showsubtitle(array('', 'postsplit_move_to', 'postsplit_manage_postcount', 'postsplit_manage_datalength', 'postsplit_manage_indexlength', 'postsplit_manage_table_createtime', 'postsplit_manage_table_memo'));

			if(!$threadcount) {

				showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

			} else {
				$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");

				$tablename = DB::table('forum_post');
				$tableid = 0;
				$tablestatus = gettablestatus($tablename);
				$postcount = $tablestatus['Rows'];
				$data_length = $tablestatus['Data_length'];
				$index_length = $tablestatus['Index_length'];

				$tabletype = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
				showtablerow('', array('class="td25"'), array("<input type=\"radio\" name=\"tableid\" value=\"0\" />", $tablename." ($tabletype)", $postcount, $data_length, $index_length, $tablestatus['Create_time'], $posttable_info[$tableid]['memo']));
				while($table = DB::fetch($query)) {
					list($tempkey, $tablename) = each($table);
					$tableid = gettableid($tablename);
					if(in_array($tableid, array('tableid'))) {
						continue;
					}
					$tablestatus = gettablestatus($tablename);
					$postcount = $tablestatus['Rows'];
					$data_length = $tablestatus['Data_length'];
					$index_length = $tablestatus['Index_length'];

					$tabletype = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
					showtablerow('', array(), array("<input type=\"radio\" name=\"tableid\" value=\"$tableid\" />", $tablename." ($tabletype)", $postcount, $data_length, $index_length, $tablestatus['Create_time'], $posttable_info[$tableid]['memo']));
				}

				if($_G['gp_detail']) {

					showtablefooter();
					showtableheader('threads_list', 'notop');
					showsubtitle(array('', 'subject', 'forum', 'postsplit_move_thread_table', 'author', 'threads_replies', 'threads_views'));
					echo $threads;

				}

			}
			showtablefooter();
			if($threadcount) {
				showsubmit('postsplit_move_submit', 'submit', '<input name="chkall" id="chkall" type="checkbox" class="checkbox" checked="checked" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>', '', $multi);

			}
			showformfooter();
			showtagfooter('div');

		}
	} else {
		if(!isset($_G['gp_tableid'])) {
			cpmsg('postsplit_no_target_table', '', 'error');
		}
		$continue = false;

		$tidsarray = !empty($_G['gp_tidarray']) ? $_G['gp_tidarray'] : explode(',', $_G['gp_tids']);
		if($tidsarray[0] == '0') {
			array_shift($tidsarray);
		}
		$threadtable = $_G['gp_threadtableid'] ? "forum_thread_{$_G['gp_threadtableid']}" : 'forum_thread';
		$query = DB::query("SELECT tid, replies, posttableid FROM ".DB::table($threadtable)." WHERE tid IN(".dimplode($tidsarray).")");
		$threadlist = array();
		while($thread = DB::fetch($query)) {
			$threadlist[$thread['tid']] = $thread;
		}
		$firstlist = getatidheap($threadlist);

		$tidsarray = array_keys($threadlist);

		if(!empty($firstlist['tids'])) {
			$continue = true;
		}
		if($continue) {
			foreach($firstlist['tids'] as $tid) {
				$posttableid = $threadlist[$tid]['posttableid'];
				if($posttableid == $_G['gp_tableid']) {
					continue;
				}
				$posttable_source = $posttableid ? "forum_post_$posttableid" : 'forum_post';
				$posttable_target = $_G['gp_tableid'] ? "forum_post_{$_G['gp_tableid']}" : 'forum_post';

				DB::query("INSERT INTO ".DB::table($posttable_target)." SELECT * FROM ".DB::table($posttable_source)." WHERE tid='$tid'");
				DB::delete($posttable_source, "tid='$tid'");
			}
			DB::update($threadtable, array(
				'posttableid' => $_G['gp_tableid'],
			), "tid IN(".dimplode($firstlist['tids']).")");

			$completed = intval($_G['gp_completed']) + count($firstlist['tids']);

			foreach($firstlist['tids'] as $tid) {
				unset($threadlist[$tid]);
			}
			$nextstep = $step + 1;
			cpmsg('postsplit_moving', "action=postsplit&operation=move&{$_G['gp_urladd']}&tableid={$_G['gp_tableid']}&completed=$completed&threadtomove={$_G['gp_threadtomove']}&step=$nextstep&moving=1", 'loadingform', array('count' => $completed, 'total' => intval($_G['gp_threadtomove']), 'tids' => implode(',', array_keys($threadlist))));
		}
		cpmsg('postsplit_move_succeed', "action=postsplit&operation=manage", 'succeed');
	}
} elseif($operation == 'addnewtable') {
	$maxtableid = getmaxposttableid();

	DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
	$db = & DB::object();
	$query = DB::query("SHOW CREATE TABLE ".DB::table('forum_post'));
	$create = $db->fetch_row($query);
	$createsql = $create[1];
	$tableid = $maxtableid + 1;
	$createsql = str_replace(DB::table('forum_post'), DB::table('forum_post').'_'.$tableid, $createsql);
	DB::query($createsql);

	update_posttableids();
	if($_G['gp_tabletype'] == 'primary'){
		$atableid = getposttableid('a');
		$ptableid = getposttableid('p');
		if($ptableid === NULL) {
			$ptableid = 0;
		}
		$posttable_info[$ptableid]['type'] = 'addition';
		if($atableid !== NULL) {
			$posttable_info[$atableid]['type'] = 'archive';
		}
		$posttable_info[$tableid]['type'] = 'primary';
	} elseif($_G['gp_tabletype'] == 'archive') {
		$posttable_info[$tableid]['type'] = 'archive';
	}
	DB::insert('common_setting', array(
		'skey' => 'posttable_info',
		'svalue' => serialize($posttable_info),
	), false, true);
	save_syscache('posttable_info', $posttable_info);
	updatecache('setting');
	cpmsg('postsplit_table_create_succeed', 'action=postsplit&operation=manage', 'succeed');
} elseif($operation == 'droptable') {
	if($_G['gp_tableid'] > 0) {
		$tablename = DB::table("forum_post_".$_G['gp_tableid']);
	} else {
		$tablename = DB::table('forum_post');
	}
	$query = DB::query("SHOW TABLES LIKE '$tablename'");
	if(!DB::num_rows($query)) {
		cpmsg('postsplit_table_no_exists', 'action=postsplit&operation=manage', 'error', array('table' => $_G['gp_tablename']));
	}
	if($tablename == DB::table('forum_post')) {
		cpmsg('postsplit_drop_table_forum_post_error', 'action=postsplit&operation=manage', 'error', array('table' => DB::table('forum_post')));
	} else {
		if(DB::result_first("SELECT COUNT(*) FROM $tablename") > 0) {
			cpmsg('postsplit_drop_table_no_empty_error', 'action=postsplit&operation=manage', 'error');
		} else {
			$tableid = $_G['gp_tableid'];

			DB::query("DROP TABLE $tablename");
			if($posttable_info[$tableid]['type'] == 'primary') {
				$maxtableid = getmaxposttableid();
				$posttable_info[$maxtableid]['type'] = 'primary';
			}
			unset($posttable_info[$tableid]);
			DB::insert('common_setting', array(
				'skey' => 'posttable_info',
				'svalue' => serialize($posttable_info),
			), false, true);
			save_syscache('posttable_info', $posttable_info);

			update_posttableids();

			updatecache('setting');
			cpmsg('postsplit_drop_table_succeed', 'action=postsplit&operation=manage', 'succeed', array('table' => $tablename));
		}
	}
} else if($operation == 'pidreset'){
	loadcache('posttableids');
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	$pidmax = 0;
	foreach($posttableids as $id) {
		if($id == 0) {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table('forum_post'));
		} else {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table("forum_post_$id"));
		}
		if($pidtmp > $pidmax) {
			$pidmax = $pidtmp;
		}
	}
	$auto_increment = $pidmax + 1;
	DB::query("ALTER TABLE ".DB::table('forum_post_tableid')." AUTO_INCREMENT=$auto_increment");
	cpmsg('postsplit_resetpid_succeed', 'action=postsplit&operation=manage', 'succeed');
}

function gettableid($tablename) {
	$tableid = substr($tablename, strrpos($tablename, '_') + 1);
	return $tableid;
}

function getmaxposttableid() {
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	$maxtableid = 0;
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = intval(gettableid($tablename));
		if($tableid > $maxtableid) {
			$maxtableid = $tableid;
		}
	}
	return $maxtableid;
}

function update_posttableids() {
	$tableids = array(0);
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = gettableid($tablename);
		if(!$tableid) {
			continue;
		}
		$tableids[] = $tableid;
	}
	DB::insert('common_setting', array(
		'skey' => 'posttableids',
		'svalue' => serialize($tableids),
	), false, true);
	save_syscache('posttableids', $tableids);
}

function postsplit_search_threads($offset = null, $length = null) {
	global $_G, $searchurladd, $page, $threadcount;
	$sql = '';
	if($_G['gp_sourcetableid'] != '' && $_G['gp_sourcetableid'] != 'all') {
		$sql .= "AND posttableid='{$_G['gp_sourcetableid']}'";
		$searchurladd[] = "sourcetableid={$_G['gp_sourcetableid']}";
	}
	if($_G['gp_inforum'] != '' && $_G['gp_inforum'] != 'all') {
		$sql .= " AND fid='{$_G['gp_inforum']}'";
		$searchurladd[] = "inforum={$_G['gp_inforum']}";
	}
	if($_G['gp_intype'] != '' && $_G['gp_intype'] != 'all') {
		$sql .= " AND typeid='{$_G['gp_intype']}'";
		$searchurladd[] = "intype={$_G['gp_intype']}";
	}
	if($_G['gp_insort'] != '' && $_G['gp_insort'] != 'all') {
		$sql .= " AND sortid='{$_G['gp_insort']}'";
		$searchurladd[] = "insort={$_G['gp_insort']}";
	}
	if($_G['gp_tidmin'] != '') {
		$sql .= " AND tid>='{$_G['gp_tidmin']}'";
		$searchurladd[] = "tidmin={$_G['gp_tidmin']}";
	}
	if($_G['gp_tidmax'] != '') {
		$sql .= " AND tid<='{$_G['gp_tidmax']}'";
		$searchurladd[] = "tidmax={$_G['gp_tidmax']}";
	}
	if($_G['gp_viewsless'] != '') {
		$sql .= " AND views<'{$_G['gp_viewsless']}'";
		$searchurladd[] = "viewsless={$_G['gp_viewsless']}";
	}
	if($_G['gp_viewsmore'] != '') {
		$sql .= " AND views>'{$_G['gp_viewsmore']}'";
		$searchurladd[] = "viewsmore={$_G['gp_viewsmore']}";
	}
	if($_G['gp_repliesless'] != '') {
		$sql .= " AND replies<'{$_G['gp_repliesless']}'";
		$searchurladd[] = "repliesless={$_G['gp_repliesless']}";
	}
	if($_G['gp_repliesmore'] != '') {
		$sql .= " AND replies>'{$_G['gp_repliesmore']}'";
		$searchurladd[] = "repliesmore={$_G['gp_repliesmore']}";
	}
	if($_G['gp_readpermmore'] != '') {
		$sql .= " AND readperm>'{$_G['gp_readpermmore']}'";
		$searchurladd[] = "readpermmore={$_G['gp_readpermmore']}";
	}
	if($_G['gp_pricemore'] != '') {
		$sql .= " AND price>'{$_G['gp_pricemore']}'";
		$searchurladd[] = "pricemore={$_G['gp_pricemore']}";
	}
	if($_G['gp_beforedays'] != '') {
		$sql .= " AND dateline<'{$_G['timestamp']}'-'{$_G['gp_beforedays']}'*86400";
		$searchurladd[] = "beforedays={$_G['gp_beforedays']}";
	}
	if($_G['gp_noreplydays'] != '') {
		$sql .= " AND lastpost<'{$_G['timestamp']}'-'{$_G['gp_noreplydays']}'*86400";
		$searchurladd[] = "noreplydays={$_G['gp_noreplydays']}";
	}
	if($_G['gp_starttime'] != '') {
		$sql .= " AND dateline>'".strtotime($_G['gp_starttime'])."'";
		$searchurladd[] = "starttime={$_G['gp_starttime']}";
	}
	if($_G['gp_endtime'] != '') {
		$sql .= " AND dateline<='".strtotime($_G['gp_endtime'])."'";
		$searchurladd[] = "endtime={$_G['gp_endtime']}";
	}

	if(trim($_G['gp_keywords'])) {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $_G['gp_keywords']));
		for($i = 0; $i < count($keywords); $i++) {
			$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
		$searchurladd[] = "keywords={$_G['gp_keywords']}";
	}

	if($_G['gp_users'] != '') {
		$sql .= trim($_G['gp_users']) ? " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($_G['gp_users'])))."')" : '';
		$searchurladd[] = "users={$_G['gp_users']}";
	}

	if($_G['gp_sticky'] == 1) {
		$sql .= " AND displayorder>'0'";
		$searchurladd[] = "sticky=1";
	} elseif($_G['gp_sticky'] == 2) {
		$sql .= " AND displayorder='0'";
		$searchurladd[] = "sticky=2";
	}
	if($_G['gp_digest'] == 1) {
		$sql .= " AND digest>'0'";
		$searchurladd[] = "digest=1";
	} elseif($_G['gp_digest'] == 2) {
		$sql .= " AND digest='0'";
		$searchurladd[] = "digest=2";
	}
	if($_G['gp_attach'] == 1) {
		$sql .= " AND attachment>'0'";
		$searchurladd[] = "attach=1";
	} elseif($_G['gp_attach'] == 2) {
		$sql .= " AND attachment='0'";
		$searchurladd[] = "attach=2";
	}
	if($_G['gp_rate'] == 1) {
		$sql .= " AND rate>'0'";
		$searchurladd[] = "rate=1";
	} elseif($_G['gp_rate'] == 2) {
		$sql .= " AND rate='0'";
		$searchurladd[] = "rate=2";
	}
	if($_G['gp_highlight'] == 1) {
		$sql .= " AND highlight>'0'";
		$searchurladd[] = "highlight=1";
	} elseif($_G['gp_highlight'] == 2) {
		$sql .= " AND highlight='0'";
		$searchurladd[] = "highlight=2";
	}
	if(!empty($_G['gp_special'])) {
		$specials = $comma = '';
		$searchurladd[] = "special={$_G['gp_special']}";
		foreach($_G['gp_special'] as $val) {
			$specials .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		if($_G['gp_specialthread'] == 1) {
			$sql .=  " AND special IN ($specials)";
			$searchurladd[] = "specialthread=1";
		} elseif($_G['gp_specialthread'] == 2) {
			$sql .=  " AND special NOT IN ($specials)";
			$searchurladd[] = "specialthread=2";
		}
	}
	$threadtable = $_G['gp_threadtableid'] ? "forum_thread_{$_G['gp_threadtableid']}" : 'forum_thread';
	$threadlist = array();
	if($sql || $_G['gp_threadtableid']) {
		$sql = "digest>='0' AND displayorder>='0' $sql";
		$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table($threadtable)." WHERE $sql");
		if(isset($offset) && isset($length)) {
			$sql .= " LIMIT $offset, $length";
		}
		$pagetmp = $page;
		do {
			$query = DB::query("SELECT fid, tid, posttableid, subject, authorid, author, views, replies, lastpost FROM ".DB::table($threadtable)." WHERE $sql");
			$pagetmp--;
		} while(!DB::num_rows($query) && $pagetmp);

		while($thread = DB::fetch($query)) {
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			$threadlist[] = $thread;
		}
	}
	return $threadlist;
}

function gettablestatus($tablename) {
	$status = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");
	$status['Data_length'] = $status['Data_length'] / 1024 / 1024;
	$nums = intval(log($status['Data_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$status['Data_length'] = number_format($status['Data_length'], $digits).' MB';

	$status['Index_length'] = $status['Index_length'] / 1024 / 1024;
	$nums = intval(log($status['Index_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$status['Index_length'] = number_format($status['Index_length'], $digits).' MB';
	return $status;
}

function getatidheap($threadlist) {
	$heap = array();
	$heap['num'] = 0;
	$heap['tids'] = array();
	$index = 0;
	foreach($threadlist as $thread) {
		if($heap['num'] && $heap['num'] + $thread['replies'] > MAX_POSTS_MOVE) {
			break;
		}
		$heap['num'] += $thread['replies'] + 1;
		$heap['tids'][] = $thread['tid'];
		$index ++;
	}
	return $heap;
}
?>