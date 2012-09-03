<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_moderate.php 11775 2010-06-13 01:00:06Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}
cpheader();

$ignore = $_G['gp_ignore'];
$filter = $_G['gp_filter'];
$modfid = $_G['gp_modfid'];
$modsubmit = $_G['gp_modsubmit'];
$moderate = $_G['gp_moderate'];
$pm = $_G['gp_pm'];

$_G['setting']['memberperpage'] = 100;
if($operation == 'members') {

	$do = empty($do) ? 'mod' : $do;
	if($do == 'mod') {

		if(!submitcheck('modsubmit')) {

			$query = DB::query("SELECT status, COUNT(*) AS count FROM ".DB::table('common_member_validate')." GROUP BY status");
			while($num = DB::fetch($query)) {
				$count[$num['status']] = $num['count'];
			}

			$sendemail = isset($_G['gp_sendemail']) ? $_G['gp_sendemail'] : 1;
			$checksendemail = $sendemail ? 'checked' : '';

			$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

			$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'");
			$multipage = multi(DB::result($query, 0), $_G['setting']['memberperpage'], $page, 'action=moderate&operation=members&sendemail=$sendemail');

			$vuids = '0';
			$members = '';
			$query = DB::query("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, ms.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark
				FROM ".DB::table('common_member_validate')." v
				LEFT JOIN ".DB::table('common_member')." m ON v.uid=m.uid
				LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid
				WHERE v.status='0' ORDER BY v.submitdate DESC LIMIT $start_limit, ".$_G['setting']['memberperpage']);
			while($member = DB::fetch($query)) {
				if($member['groupid'] != 8) {
					$vuids .= ','.$member['uid'];
					continue;
				}
				$member['regdate'] = dgmdate($member['regdate']);
				$member['submitdate'] = dgmdate($member['submitdate']);
				$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
				$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
				$members .= "<tr class=\"smalltxt\"><td><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"invalidate\"> $lang[invalidate]<br /><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"validate\" checked> $lang[validate]<br />\n".
					"<input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"delete\"> $lang[delete]<br /><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"ignore\"> $lang[ignore]</td><td><b><a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".
					"<br />$lang[members_edit_regdate] $member[regdate]<br />$lang[members_edit_regip] $member[regip]<br />Email: $member[email]</td>\n".
					"<td align=\"center\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[message]</textarea></td>\n".
					"<td>$lang[moderate_members_submit_times]: $member[submittimes]<br />$lang[moderate_members_submit_time]: $member[submitdate]<br />$lang[moderate_members_admin]: $member[admin]<br />\n".
					"$lang[moderate_members_mod_time]: $member[moddate]</td><td><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
			}

			if($vuids) {
				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($vuids)", 'UNBUFFERED');
			}

			shownav('user', 'nav_modmembers');
			showsubmenu('nav_moderate_users', array(
				array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 1),
				array('clean', 'moderate&operation=members&do=del', 0)
			));
			showtips('moderate_members_tips');
			showformheader('moderate&operation=members&do=mod');
			showtableheader('moderate_members', 'fixpadding');
			showsubtitle(array('operation', 'members_edit_info', 'moderate_members_message', 'moderate_members_info', 'moderate_members_remark'));
			echo $members;
			showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'invalidate\')">'.cplang('moderate_all_invalidate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<input class="checkbox" type="checkbox" name="sendemail" id="sendemail" value="1" '.$checksendemail.' /><label for="sendemail"> '.cplang('moderate_members_email').'</label>', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

			$uids = 0;
			if(is_array($_G['gp_modtype'])) {
				foreach($_G['gp_modtype'] as $uid => $act) {
					$uid = intval($uid);
					$uids .= ','.$uid;
					$moderation[$act][] = $uid;
				}
			}

			$members = array();
			$uidarray = array(0);
			$query = DB::query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
				WHERE v.uid IN ($uids) AND m.uid=v.uid AND m.groupid='8'");
			while($member = DB::fetch($query)) {
				$members[$member['uid']] = $member;
				$uidarray[] = $member['uid'];
			}

			$uids = implode(',', $uidarray);
			$numdeleted = $numinvalidated = $numvalidated = 0;

			if(!empty($moderation['delete']) && is_array($moderation['delete'])) {
				$deleteuids = '\''.implode('\',\'', $moderation['delete']).'\'';
				DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");
				$numdeleted = DB::affected_rows();

				DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");
				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");
			} else {
				$moderation['delete'] = array();
			}

			if(!empty($moderation['validate']) && is_array($moderation['validate'])) {
				$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=0 AND 0<creditslower LIMIT 1");
				$validateuids = '\''.implode('\',\'', $moderation['validate']).'\'';
				DB::query("UPDATE ".DB::table('common_member')." SET adminid='0', groupid='$newgroupid' WHERE uid IN ($validateuids) AND uid IN ($uids)");
				$numvalidated = DB::affected_rows();

				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($validateuids) AND uid IN ($uids)");
			} else {
				$moderation['validate'] = array();
			}

			if(!empty($moderation['invalidate']) && is_array($moderation['invalidate'])) {
				foreach($moderation['invalidate'] as $uid) {
					$numinvalidated++;
					DB::query("UPDATE ".DB::table('common_member_validate')." SET moddate='$_G[timestamp]', admin='$_G[username]', status='1', remark='".dhtmlspecialchars($_G['gp_remark'][$uid])."' WHERE uid='$uid' AND uid IN ($uids)");
				}
			} else {
				$moderation['invalidate'] = array();
			}

			if($_G['gp_sendemail']) {
				if(!function_exists('sendmail')) {
					include libfile('function/mail');
				}
				foreach(array('delete', 'validate', 'invalidate') as $o) {
					foreach($moderation[$o] as $uid) {
						if(isset($members[$uid])) {
							$member = $members[$uid];
							$member['regdate'] = dgmdate($member['regdate']);
							$member['submitdate'] = dgmdate($member['submitdate']);
							$member['moddate'] = dgmdate(TIMESTAMP);
							$member['operation'] = $o;
							$member['remark'] = $_G['gp_remark'][$uid] ? dhtmlspecialchars($_G['gp_remark'][$uid]) : $lang['none'];
							$moderate_member_message = lang('email', 'moderate_member_message', array(
								'username' => $member['username'],
								'bbname' => $_G['setting']['bbname'],
								'regdate' => $member['regdate'],
								'submitdate' => $member['submitdate'],
								'submittimes' => $member['submittimes'],
								'message' => $member['message'],
								'modresult' => lang('email', 'moderate_member_'.$member['operation']),
								'moddate' => $member['moddate'],
								'adminusername' => $_G['member']['username'],
								'remark' => $member['remark'],
								'siteurl' => $_G['siteurl'],
							));

							sendmail("$member[username] <$member[email]>", lang('email', 'moderate_member_subject'), $moderate_member_message);
						}
					}
				}
			}

			cpmsg('moderate_members_succeed', "action=moderate&operation=members&page=$page", 'succeed', array('numvalidated' => $numvalidated, 'numinvalidated' => $numinvalidated, 'numdeleted' => $numdeleted));

		}


	} elseif($do == 'del') {


		if(!submitcheck('prunesubmit', 1)) {

			shownav('user', 'nav_modmembers');
			showsubmenu('nav_moderate_users', array(
				array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 0),
				array('clean', 'moderate&operation=members&do=del', 1)
			));
			showtips('moderate_members_tips');
			showformheader('moderate&operation=members&do=del');
			showtableheader('moderate_members_prune');
			showsetting('moderate_members_prune_submitmore', 'submitmore', '5', 'text');
			showsetting('moderate_members_prune_regbefore', 'regbefore', '30', 'text');
			showsetting('moderate_members_prune_modbefore', 'modbefore', '15', 'text');
			showsetting('moderate_members_prune_regip', 'regip', '', 'text');
			showsubmit('prunesubmit');
			showtablefooter();
			showformfooter();

		} else {

			$sql = '1';
			$sql .= $_G['gp_submitmore'] ? " AND v.submittimes>'{$_G['gp_submitmore']}'" : '';
			$sql .= $_G['gp_regbefore'] ? " AND m.regdate<'".(TIMESTAMP - $_G['gp_regbefore'] * 86400)."'" : '';
			$sql .= $_G['gp_modbefore'] ? " AND v.moddate<'".(TIMESTAMP - $_G['gp_modbefore'] * 86400)."'" : '';
			$sql .= $_G['gp_regip'] ? " AND m.regip LIKE '{$_G['gp_regip']}%'" : '';

			$query = DB::query("SELECT v.uid FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
				WHERE $sql AND m.uid=v.uid AND m.groupid='8'");

			if(!$membernum = DB::num_rows($query)) {
				cpmsg('members_search_noresults', '', 'error');
			} elseif(!$_G['gp_confirmed']) {
				cpmsg('members_delete_confirm', "action=moderate&operation=members&do=del&submitmore=".rawurlencode($_G['gp_submitmore'])."&regbefore=".rawurlencode($_G['gp_regbefore'])."&regip=".rawurlencode($_G['gp_regip'])."&prunesubmit=yes", 'form', array('membernum' => $membernum));
			} else {
				$uids = 0;
				while($member = DB::fetch($query)) {
					$uids .= ','.$member['uid'];
				}

				DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
				$numdeleted = DB::affected_rows();

				DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)");
				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)");

				cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));
			}

		}
	}

} else {

	require_once libfile('function/forumlist');
	require_once libfile('function/post');

	$modfid = !empty($modfid) ? intval($modfid) : 0;

	$fids = 0;
	$recyclebins = $forumlist = array();
	if($_G['adminid'] == 3) {
		$query = DB::query("SELECT m.fid, f.name, f.recyclebin FROM ".DB::table('forum_moderator')." m LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=m.fid  WHERE m.uid='$_G[uid]'");
		while($forum = DB::fetch($query)) {
			$fids .= ','.$forum['fid'];
			$recyclebins[$forum['fid']] = $forum['recyclebin'];
			$forumlist[$forum['fid']] = strip_tags($forum['name']);
		}

		if(empty($forumlist)) {
			cpmsg('moderate_posts_no_access_all', '', 'error');
		} elseif($modfid && empty($forumlist[$modfid])) {
			cpmsg('moderate_posts_no_access_this', '', 'error');
		}

	} else {
		$query = DB::query("SELECT fid, name, recyclebin FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group'");
		while($forum = DB::fetch($query)) {
			$recyclebins[$forum['fid']] = $forum['recyclebin'];
			$forumlist[$forum['fid']] = $forum['name'];
		}
	}

	if($modfid) {
		$fidadd = array('fids' => "fid='$modfid'", 'and' => ' AND ', 't' => 't.', 'p' => 'p.');
	} else {
		$fidadd = $fids ? array('fids' => "fid IN ($fids)", 'and' => ' AND ', 't' => 't.', 'p' => 'p.') : array();
	}

	if(isset($filter) && $filter == 'ignore') {
		$displayorder = -3;
		$filteroptions = '<option value="normal">'.$lang['moderate_none'].'</option><option value="ignore" selected>'.$lang['moderate_ignore'].'</option>';
	} else {
		$displayorder = -2;
		$filter = 'normal';
		$filteroptions = '<option value="normal" selected>'.$lang['moderate_none'].'</option><option value="ignore">'.$lang['moderate_ignore'].'</option>';
	}

	$forumoptions = '<option value="all"'.(empty($modfid) ? ' selected' : '').'>'.$lang['moderate_all_fields'].'</option>';
	foreach($forumlist as $fid => $forumname) {
		$selected = $modfid == $fid ? ' selected' : '';
		$forumoptions .= '<option value="'.$fid.'" '.$selected.'>'.$forumname.'</option>'."\n";
	}

	require_once libfile('function/misc');
	$modreasonoptions = '<option value="">'.$lang['none'].'</option><option value="">--------</option>'.modreasonselect(1);

	echo <<<EOT
<style type="text/css">
	.mod_validate td{ background: #FFFFFF !important; }
	.mod_delete td{	background: #FFEBE7 !important; }
	.mod_ignore td{	background: #EEEEEE !important; }
</style>
<script type="text/JavaScript">
	function mod_setbg(tid, value) {
		$('mod_' + tid + '_row1').className = 'mod_' + value;
		$('mod_' + tid + '_row2').className = 'mod_' + value;
		$('mod_' + tid + '_row3').className = 'mod_' + value;
	}
	function mod_setbg_all(value) {
		checkAll('option', $('cpform'), value);
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.substr(0, 4) == 'mod_') {
				trs[i].className = 'mod_' + value;
			}
		}
	}
	function attachimg() {}
</script>
EOT;

}

if($operation == 'threads') {

	if(!submitcheck('modsubmit')) {

		require_once libfile('function/discuzcode');

		$tpp = 10;
		$start_limit = ($page - 1) * $tpp;

		$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE $fidadd[fids]$fidadd[and] displayorder='$displayorder'");
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=threads&filter=$filter&modfid=$modfid");

		shownav('topic', $lang['moderate_threads']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 1),
			array('nav_moderate_replies', 'moderate&operation=replies', 0)
		));
		showformheader("moderate&operation=threads&page=$page");
		showhiddenfields(array('ignore' => $ignore, 'filter' => $filter, 'modfid' => $modfid));
		showtableheader("$lang[select]: <select style=\"margin: 0px;\" onchange=\"if(this.options[this.selectedIndex].value != '') {window.location='".ADMINSCRIPT."?action=moderate&operation=threads&modfid=$modfid&filter='+this.options[this.selectedIndex].value;}\">$filteroptions</select>
		<select style=\"margin: 0px;\" onchange=\"if(this.options[this.selectedIndex].value != '') {window.location='".ADMINSCRIPT."?action=moderate&operation=threads&filter=$filter&modfid='+this.options[this.selectedIndex].value;}\">$forumoptions</select>");

		$query = DB::query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
				t.tid, t.fid, t.posttableid, t.sortid, t.author, t.authorid, t.subject, t.dateline, t.attachment
				FROM ".DB::table('forum_thread')." t
				LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
				WHERE $fidadd[t]$fidadd[fids]$fidadd[and] t.displayorder='$displayorder'
				ORDER BY t.dateline DESC LIMIT $start_limit, $tpp");

		while($thread = DB::fetch($query)) {
			$threadsortinfo = '';
			$posttable = $thread['posttableid'] ? "forum_post_{$thread['posttableid']}" : 'forum_post';
			$post = DB::fetch_first("SELECT pid, message, useip, attachment, htmlon, smileyoff, bbcodeoff FROM ".DB::table($posttable)." WHERE tid='{$thread['tid']}' AND first='1'");
			$thread = array_merge($thread, $post);
			if($thread['authorid'] && $thread['author']) {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>";
			} elseif($thread['authorid'] && !$thread['author']) {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$lang[anonymous]</a>";
			} else {
				$thread['author'] = $lang['guest'];
			}

			$thread['dateline'] = dgmdate($thread['dateline']);
			$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], sprintf('%00b', $thread['htmlon']), $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);

			$thread['modthreadkey'] = modthreadkey($thread['tid']);

			if($thread['attachment']) {
				require_once libfile('function/attachment');

				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE tid='$thread[tid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
							? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
							 : "<a href=\"".$_G['setting']['attachurl']."$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}

			$optiondata = $optionlist = array();
			if($thread['sortid']) {
				if(@include DISCUZ_ROOT.'./data/cache/forum_threadsort_'.$thread['sortid'].'.php') {
					$sortquery = DB::query("SELECT optionid, value FROM ".DB::table('forum_typeoptionvar')." WHERE tid='$thread[tid]'");
					while($option = DB::fetch($sortquery)) {
						$optiondata[$option['optionid']] = $option['value'];
					}

					foreach($_G['forum_dtype'] as $optionid => $option) {
						$optionlist[$option['identifier']]['title'] = $_G['forum_dtype'][$optionid]['title'];
						if($_G['forum_dtype'][$optionid]['type'] == 'checkbox') {
							$optionlist[$option['identifier']]['value'] = '';
							foreach(explode("\t", $optiondata[$optionid]) as $choiceid) {
								$optionlist[$option['identifier']]['value'] .= $_G['forum_dtype'][$optionid]['choices'][$choiceid].'&nbsp;';
							}
						} elseif(in_array($_G['forum_dtype'][$optionid]['type'], array('radio', 'select'))) {
							$optionlist[$option['identifier']]['value'] = $_G['forum_dtype'][$optionid]['choices'][$optiondata[$optionid]];
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'image') {
							$maxwidth = $_G['forum_dtype'][$optionid]['maxwidth'] ? 'width="'.$_G['forum_dtype'][$optionid]['maxwidth'].'"' : '';
							$maxheight = $_G['forum_dtype'][$optionid]['maxheight'] ? 'height="'.$_G['forum_dtype'][$optionid]['maxheight'].'"' : '';
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\"><img src=\"$optiondata[$optionid]\"  $maxwidth $maxheight border=\"0\"></a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'url') {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\">$optiondata[$optionid]</a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'textarea') {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? nl2br($optiondata[$optionid]) : '';
						} else {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid];
						}
					}
				}

				foreach($optionlist as $option) {
					$threadsortinfo .= $option['title'].' '.$option['value']."<br />";
				}
			}

			showtablerow("id=\"mod_$thread[tid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"validate\" checked=\"checked\" onclick=\"mod_setbg($thread[tid], 'validate');\"><label for=\"mod_$thread[tid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"delete\" onclick=\"mod_setbg($thread[tid], 'delete');\"><label for=\"mod_$thread[tid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" onclick=\"mod_setbg($thread[tid], 'ignore');\"><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$thread[tid]&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">$thread[subject]</a></h3><p><span class=\"bold\">$lang[author]:</span> $thread[author] ($thread[useip]) &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $thread[dateline]</p>"
			));
			showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'<br /><br />'.$threadsortinfo.'</div>');
			showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "<a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&page=1&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">".$lang['moderate_edit_thread']."</a> &nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$thread[tid]\" id=\"pm_$thread[tid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$thread[tid]').value=this.value\">$modreasonoptions</select>");
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a>', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		$validates = $ignores = $recycles = $deletes = 0;
		$validatedthreads = $pmlist = array();
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

		if(is_array($moderate)) {
			foreach($moderate as $tid => $act) {
				$moderation[$act][] = intval($tid);
			}
		}

		if($moderation['ignore']) {
			$ignoretids = '\''.implode('\',\'', $moderation['ignore']).'\'';
			DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-3' WHERE tid IN ($ignoretids) AND displayorder='-2'");
			$ignores = DB::affected_rows();
		}

		if($moderation['delete']) {
			$deletetids = '0';
			$recyclebintids = '0';
			$query = DB::query("SELECT tid, fid, authorid, subject FROM ".DB::table('forum_thread')." WHERE tid IN ('".implode('\',\'', $moderation['delete'])."') AND displayorder='$displayorder' $fidadd[and]$fidadd[fids]");
			while($thread = DB::fetch($query)) {
				my_thread_log('delete', array('tid' => $thread['tid']));
				if($recyclebins[$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				} else {
					$deletetids .= ','.$thread['tid'];
				}
				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
						'action' => 'modthreads_delete',
						'notevar' => array('threadsubject' => $threadsubject, 'reason' => stripslashes($reason)),
						'authorid' => $thread['authorid'],
						'thread' =>  $thread['subject'],
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($recyclebintids) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
				$recycles = DB::affected_rows();
				updatemodworks('MOD', $recycles);

				updatepost(array('invisible' => '-1'), "tid IN ($recyclebintids)");
				updatemodlog($recyclebintids, 'DEL');
			}

			require_once libfile('function/delete');
			deletepost("tid IN ($deletetids)");
			$deletes = deletethread("tid IN ($deletetids)");
		}

		if($moderation['validate']) {

			$forums = array();
			$validatetids = '\''.implode('\',\'', $moderation['validate']).'\'';

			$tids = $comma = $comma2 = '';
			$authoridarray = $moderatedthread = array();
			$query = DB::query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline FROM ".DB::table('forum_thread')." t
				WHERE t.tid IN ($validatetids) AND t.displayorder='$displayorder' $fidadd[and]$fidadd[t]$fidadd[fids]");
			while($thread = DB::fetch($query)) {
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				my_thread_log('validate', array('tid' => $thread['tid']));
				updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);

				$forums[] = $thread['fid'];
				$validatedthreads[] = $thread;

				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
							'action' => 'modthreads_validate',
							'notevar' => array('tid' => $_G['tid'], 'threadsubject' => $threadsubject, 'reason' => stripslashes($reason)),
							'authorid' => $thread['authorid'],
							'tid' => $thread['tid'],
							'thread' => $thread['subject'],
							'reason' => dhtmlspecialchars($$pm)
							);
				}
			}

			if($tids) {

				updatepost(array('invisible' => '0'), "tid IN ($tids)");
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$validates = DB::affected_rows();

				foreach(array_unique($forums) as $fid) {
					updateforumcount($fid);
				}

				updatemodworks('MOD', $validates);
				updatemodlog($tids, 'MOD');

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$threadsubject = $pm['thread'];
				$tid = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['action'], $pm['notvar'], 1);
			}
		}

		if($validates) {
			showsubmenu('nav_moderate_posts', array(
				array('nav_moderate_threads', 'moderate&operation=threads', 0),
				array('nav_moderate_replies', 'moderate&operation=replies', 0)
			));
			echo '<form id="topicadmin" name="topicadmin" method="post" autocomplete="off" action="forum.php?mod=topicadmin" target="_blank">';
			showhiddenfields(array('action'=> '', 'fid'=> '', 'tid'=> ''));
			showtableheader();
			showtablerow('', 'class="lineheight" colspan="4"', cplang('moderate_validate_list', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes)));
			showsubtitle(array('Tid', 'subject', 'author', 'dateline'));

			if(!empty($validatedthreads)) {
				foreach($validatedthreads as $thread) {
					showtablerow('', '', array(
						$thread['tid'],
						'<a href="forum.php?mod=viewthread&tid='.$thread['tid'].'&modthreadkey='.modthreadkey($thread['tid']).'" target="_blank">'.$thread['subject'].'</a>',
						'<a href="home.php?mod=space&uid='.$thread['authorid'].'" target="_blank">'.$thread['author'].'</a>',
						dgmdate($thread['dateline'])
					));
				}
			}

			showtablefooter();
			showformfooter();
		} else {
			cpmsg('moderate_threads_succeed', 'action=moderate&operation=threads', 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
		}

	}

} elseif($operation == 'replies') {

	if(!submitcheck('modsubmit')) {

		require_once libfile('function/discuzcode');
		$ppp = 10;
		$start_limit = ($page - 1) * $ppp;

		$modcount = getcountofposts(DB::table('forum_post'), "invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]");
		$multipage = multi($modcount, $ppp, $page, ADMINSCRIPT."?action=moderate&operation=replies&filter=$filter&modfid=$modfid");

		shownav('topic', $lang['moderate_replies']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 1)
		));

		showformheader("moderate&operation=replies&page=$page");
		showhiddenfields(array('filter' => $filter, 'modfid' => $modfid));
		showtableheader("$lang[select]: <select style=\"margin: 0px;\" onchange=\"if(this.options[this.selectedIndex].value != '') {window.location='".ADMINSCRIPT."?action=moderate&operation=replies&modfid=$modfid&filter='+this.options[this.selectedIndex].value;}\">$filteroptions</select> <select style=\"margin: 0px;\" onchange=\"if(this.options[this.selectedIndex].value != '') {window.location='".ADMINSCRIPT."?action=moderate&operation=replies&filter=$filter&modfid='+this.options[this.selectedIndex].value;}\">$forumoptions</select>");

		$postarray = getallwithposts(array(
			'select' => 'f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject',
			'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=p.fid",
			'where' => "p.invisible='$displayorder' AND p.first='0' $fidadd[and]$fidadd[p]$fidadd[fids]",
			'order' => "p.dateline DESC",
			'limit' => "$start_limit, $ppp",
		));

		foreach($postarray as $post) {
			$post['dateline'] = dgmdate($post['dateline']);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);
			$post['modthreadkey'] = modthreadkey($post['tid']);

			if($post['attachment']) {
				require_once libfile('function/attachment');

				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE pid='$post[pid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					 		? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 	 : "<a href=\"".$_G['setting']['attachurl']."$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}

			showtablerow("id=\"mod_$post[pid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"validate\" checked=\"checked\" onclick=\"mod_setbg($post[pid], 'validate');\"><label for=\"mod_$post[pid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"delete\" onclick=\"mod_setbg($post[pid], 'delete');\"><label for=\"mod_$post[pid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" onclick=\"mod_setbg($post[pid], 'ignore');\"><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">$post[forumname]</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$post[tid]&modthreadkey=$post[modthreadkey]\" target=\"_blank\">$post[tsubject]</a> &raquo; <b>$post[subject]</b></h3><p><span class=\"bold\">$lang[author]:</span> $post[author] ($post[useip]) &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $post[dateline]</p>"
			));
			showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$post[message].'</div>');
			showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "<a href=\"forum.php?mod=post&action=edit&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&page=1&modthreadkey=$post[modthreadkey]\" target=\"_blank\">".$lang['moderate_edit_post']."</a> &nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$post[pid]\" id=\"pm_$post[pid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$post[pid]').value=this.value\">$modreasonoptions</select>");

		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a>', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$pmlist = array();
		$validates = $ignores = $deletes = 0;

		if(is_array($moderate)) {
			foreach($moderate as $pid => $act) {
				$moderation[$act][] = intval($pid);
			}
		}

		if($ignorepids = dimplode($moderation['ignore'])) {
			updatepost(array('invisible' => '-3'), "pid IN ($ignorepids) AND invisible='-2' AND first='0' $fidadd[and]$fidadd[fids]");
			$ignores = DB::affected_rows();
		}

		if($deletepids = dimplode($moderation['delete'])) {
			$postarray = getfieldsofposts('pid, authorid, tid, message', "pid IN ($deletepids) AND invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]");
			$pids = $comma = '';
			foreach($postarray as $post) {
				$pids .= $comma.$post['pid'];
				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'action' => 'modreplies_delete',
						'notevar' => array('post' => $post, 'reason' => stripslashes($reason)),
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
				$comma = ',';
			}

			if($pids) {
				$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE pid IN ($deletepids)");
				while($attach = DB::fetch($query)) {
					dunlink($attach);
				}
				DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)", 'UNBUFFERED');
				DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE pid IN ($pids)", 'UNBUFFERED');
				require_once libfile('function/delete');
				$deletes = deletepost("pid IN ($pids)");
				DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE pid IN ($pids)", 'UNBUFFERED');
			}
			updatemodworks('DLP', count($moderation['delete']));
		}

		if($validatepids = dimplode($moderation['validate'])) {
			$forums = $threads = $lastpost = $attachments = $pidarray = $authoridarray = array();
			$postarray = getallwithposts(array(
				'select' => 't.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous',
				'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid",
				'where' => "pid IN ($validatepids) AND p.invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[0]$fidadd[fids]"
			));

			foreach($postarray as $post) {
				$pidarray[] = $post['pid'];
				updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);

				$forums[] = $post['fid'];

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
					", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'action' => 'modreplies_validate',
						'notevar' => array('tid' => $_G['tid'], 'post' => $post, 'reason' => stripslashes($reason)),
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			foreach($threads as $tid => $thread) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			if(!empty($pidarray)) {
				updatepost(array('invisible' => '0'), "pid IN (0,".implode(',', $pidarray).")");
				$validates = DB::affected_rows();
				updatemodworks('MOD', $validates);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$post = $pm['post'];
				$tid = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['action'], $pm['notvar'], 1);
			}
		}

		cpmsg('moderate_replies_succeed', "action=moderate&operation=replies&page=$page&filter=$filter&modfid=$modfid", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));

	}

}

?>