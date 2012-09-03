<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_admingroup.php 9349 2010-04-28 06:27:21Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(submitcheck('groupsubmit') && $ids = dimplode($_G['gp_delete'])) {
		$gids = array();
		$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='special' AND radminid>'0'");
		while($g = DB::fetch($query)) {
			$gids[] = $g['groupid'];
		}
		if($ids = dimplode($gids)) {
			DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_usergroup_field')." WHERE groupid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_admingroup')." WHERE admingid IN ($ids)");
			$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
			DB::query("UPDATE ".DB::table('common_member')." SET groupid='$newgroupid', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
			deletegroupcache($gids);
		}
	}

	$grouplist = array();
	$query = DB::query("SELECT a.*, u.groupid, u.radminid, u.grouptitle, u.stars, u.color, u.icon, u.type FROM ".DB::table('common_admingroup')." a
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid
			ORDER BY u.type, u.radminid, a.admingid");
	while ($group = DB::fetch($query)) {
		$grouplist[$group['groupid']] = $group;
	}

	if(!submitcheck('groupsubmit')) {

		shownav('user', 'nav_admingroups');
		showsubmenu('nav_admingroups');
		showtips('admingroup_tips');

		showformheader('admingroup');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'name', 'type', 'admingroup_level', 'usergroups_stars', 'usergroups_color', '', ''));

		foreach($grouplist as $gid => $group) {
			showtablerow('', array('', '', 'class="td25"', '', 'class="td25"'), array(
				$group['type'] == 'system' ? '' : "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$group[groupid]\">",
				$group['grouptitle'],
				$group['type'] == 'system' ? cplang('inbuilt') : cplang('custom'),
				$lang['usergroups_system_'.$group['radminid']],
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" class=\"txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\">",
				"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group[admingid]}&return=admin\">$lang[admingroup_setting_user]</a>",
				"<a href=\"".ADMINSCRIPT."?action=admingroup&operation=edit&id=$group[admingid]\">$lang[admingroup_setting_admin]</a>"
			));
		}

		showtablerow('', array('class="td25"', '', '', 'colspan="6"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="12" name="grouptitlenew">',
			cplang('custom'),
			"<select name=\"radminidnew\"><option value=\"1\">$lang[usergroups_system_1]</option><option value=\"2\">$lang[usergroups_system_2]</option><option value=\"3\" selected=\"selected\">$lang[usergroups_system_3]</option>",
		));
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		foreach($grouplist as $gid => $group) {
			$stars = intval($_G['gp_group_stars'][$gid]);
			$color = dhtmlspecialchars($_G['gp_group_color'][$gid]);
			if($group['color'] != $color || $group['stars'] != $stars || $group['icon'] != $avatar) {
				DB::query("UPDATE ".DB::table('common_usergroup')." SET stars='$stars', color='$color' WHERE groupid='$gid'");
			}
		}

		$grouptitlenew = dhtmlspecialchars(trim($_G['gp_grouptitlenew']));
		$radminidnew = intval($_G['gp_radminidnew']);

		if($grouptitlenew && in_array($radminidnew, array(1, 2, 3))) {

			$data = array();
			$usergroup = DB::fetch_first("SELECT * FROM ".DB::table('common_usergroup')." WHERE groupid='$radminidnew'");
			foreach ($usergroup as $key => $val) {
				if(!in_array($key, array('groupid', 'radminid', 'type', 'system', 'grouptitle'))) {
					$val = addslashes($val);
					$data[$key] = $val;
				}
			}

			$adata = array();
			$admingroup = DB::fetch_first("SELECT * FROM ".DB::table('common_admingroup')." WHERE admingid='$radminidnew'");
			foreach ($admingroup as $key => $val) {
				if(!in_array($key, array('admingid'))) {
					$val = addslashes($val);
					$adata[$key] = $val;
				}
			}

			$data['radminid'] = $radminidnew;
			$data['type'] = 'special';
			$data['grouptitle'] = $grouptitlenew;
			$newgroupid = DB::insert('common_usergroup', $data, 1);
			if($newgroupid) {
				$adata['admingid'] = $newgroupid;
				DB::insert('common_admingroup', $adata);
				DB::insert('common_usergroup_field', array('groupid' => $newgroupid));
			}
		}

		cpmsg('admingroups_edit_succeed', 'action=admingroup', 'succeed');

	}

} elseif($operation == 'edit') {

	$submitcheck = submitcheck('groupsubmit');
	$id = isset($_G['gp_id']) ? intval($_G['gp_id']) : 0;
	if(!$submitcheck) {
		if(empty($id)) {
			$grouplist = "<select name=\"id\" style=\"width: 150px\">\n";
			$query = DB::query("SELECT u.groupid, u.grouptitle FROM ".DB::table('common_admingroup')." a LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid ORDER BY u.type, u.radminid, a.admingid");
			while($group = DB::fetch($query)) {
				$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
			$grouplist .= '</select>';
			cpmsg('admingroups_edit_nonexistence', 'action=admingroup&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : ''), 'form', array(), $grouplist);
		}

		$group = DB::fetch_first("SELECT a.*, u.radminid, u.grouptitle FROM ".DB::table('common_admingroup')." a
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid
			WHERE a.admingid='$id'");

		if(!$group) {
			cpmsg('undefined_action', '', 'error');
		}

		$query = DB::query("SELECT u.radminid, u.groupid, u.grouptitle FROM ".DB::table('common_admingroup')." a LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid ORDER BY u.radminid, a.admingid");
		$grouplist = $gutype = '';
		while($ggroup = DB::fetch($query)) {
			if($gutype != $ggroup['radminid']) {
				$grouplist .= '<em>'.($ggroup['radminid'] == 1 ? $lang['usergroups_system_1'] : ($ggroup['radminid'] == 2 ? $lang['usergroups_system_2'] : $lang['usergroups_system_3'])).'</em>';
				$gutype = $ggroup['radminid'];
			}
			$grouplist .= '<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+document.documentElement.scrollTop"'.($id == $ggroup['groupid'] ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-document.documentElement.scrollTop)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.$grouplist.'</div>';

		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('threadperm', 'postperm', 'modcpperm', 'portalperm', 'otherperm')) ? $_G['gp_anchor'] : 'threadperm';
		$anchorarray = array(
			array('admingroup_edit_threadperm', 'threadperm', $_G['gp_anchor'] == 'threadperm'),
			array('admingroup_edit_postperm', 'postperm', $_G['gp_anchor'] == 'postperm'),
			array('admingroup_edit_modcpperm', 'modcpperm', $_G['gp_anchor'] == 'modcpperm'),
			array('admingroup_edit_spaceperm', 'spaceperm', $_G['gp_anchor'] == 'spaceperm'),
			array('admingroup_edit_portalperm', 'portalperm', $_G['gp_anchor'] == 'portalperm'),
			array('admingroup_edit_otherperm', 'otherperm', $_G['gp_anchor'] == 'otherperm'),
		);

		showsubmenuanchors($lang['admingroup_edit'].' - '.$group['grouptitle'], $anchorarray, $gselect);
		if(!empty($_G['gp_switch'])) {
			echo '<script type="text/javascript">showMenu({\'ctrlid\':\'ugselect\',\'pos\':\'34\'});</script>';
		}
		showformheader("admingroup&operation=edit&id=$id");

		showtableheader();
		showtagheader('tbody', 'threadperm', $_G['gp_anchor'] == 'threadperm');
		showtitle('admingroup_edit_threadperm');
		showsetting('admingroup_edit_stick_thread', array('allowstickthreadnew', array(
			array(0, $lang['admingroup_edit_stick_thread_none']),
			array(1, $lang['admingroup_edit_stick_thread_1']),
			array(2, $lang['admingroup_edit_stick_thread_2']),
			array(3, $lang['admingroup_edit_stick_thread_3'])
		)), $group['allowstickthread'], 'mradio');
		showsetting('admingroup_edit_digest_thread', array('allowdigestthreadnew', array(
			array(0, $lang['admingroup_edit_digest_thread_none']),
			array(1, $lang['admingroup_edit_digest_thread_1']),
			array(2, $lang['admingroup_edit_digest_thread_2']),
			array(3, $lang['admingroup_edit_digest_thread_3'])
		)), $group['allowdigestthread'], 'mradio');
		showsetting('admingroup_edit_bump_thread', 'allowbumpthreadnew', $group['allowbumpthread'], 'radio');
		showsetting('admingroup_edit_highlight_thread', 'allowhighlightthreadnew', $group['allowhighlightthread'], 'radio');
		showsetting('admingroup_edit_recommend_thread', 'allowrecommendthreadnew', $group['allowrecommendthread'], 'radio');
		showsetting('admingroup_edit_stamp_thread', 'allowstampthreadnew', $group['allowstampthread'], 'radio');
		showsetting('admingroup_edit_close_thread', 'allowclosethreadnew', $group['allowclosethread'], 'radio');
		showsetting('admingroup_edit_move_thread', 'allowmovethreadnew', $group['allowmovethread'], 'radio');
		showsetting('admingroup_edit_edittype_thread', 'allowedittypethreadnew', $group['allowedittypethread'], 'radio');
		showsetting('admingroup_edit_copy_thread', 'allowcopythreadnew', $group['allowcopythread'], 'radio');
		showsetting('admingroup_edit_merge_thread', 'allowmergethreadnew', $group['allowmergethread'], 'radio');
		showsetting('admingroup_edit_split_thread', 'allowsplitthreadnew', $group['allowsplitthread'], 'radio');
		showsetting('admingroup_edit_repair_thread', 'allowrepairthreadnew', $group['allowrepairthread'], 'radio');
		showsetting('admingroup_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
		showsetting('admingroup_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
		showsetting('admingroup_edit_remove_reward', 'allowremoverewardnew', $group['allowremovereward'], 'radio');
		showsetting('admingroup_edit_edit_activity', 'alloweditactivitynew', $group['alloweditactivity'], 'radio');
		showsetting('admingroup_edit_edit_trade', 'allowedittradenew', $group['allowedittrade'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'postperm', $_G['gp_anchor'] == 'postperm');
		showtitle('admingroup_edit_postperm');
		showsetting('admingroup_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
		showsetting('admingroup_edit_warn_post', 'allowwarnpostnew', $group['allowwarnpost'], 'radio');
		showsetting('admingroup_edit_ban_post', 'allowbanpostnew', $group['allowbanpost'], 'radio');
		showsetting('admingroup_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
		showsetting('admingroup_edit_stick_post', 'allowstickreplynew', $group['allowstickreply'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'modcpperm', $_G['gp_anchor'] == 'modcpperm');
		showtitle('admingroup_edit_modcpperm');
		showsetting('admingroup_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
		showsetting('admingroup_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
		showsetting('admingroup_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
		showsetting('admingroup_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
		showsetting('admingroup_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
		showsetting('admingroup_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
		showsetting('admingroup_edit_edit_forum', 'alloweditforumnew', $group['alloweditforum'], 'radio');
		showsetting('admingroup_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
		showsetting('admingroup_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'spaceperm', $_G['gp_anchor'] == 'spaceperm');
		showsetting('admingroup_edit_manage_feed', 'managefeednew', $group['managefeed'], 'radio');
		showsetting('admingroup_edit_manage_doing', 'managedoingnew', $group['managedoing'], 'radio');
		showsetting('admingroup_edit_manage_share', 'managesharenew', $group['manageshare'], 'radio');
		showsetting('admingroup_edit_manage_blog', 'manageblognew', $group['manageblog'], 'radio');
		showsetting('admingroup_edit_manage_album', 'managealbumnew', $group['managealbum'], 'radio');
		showsetting('admingroup_edit_manage_comment', 'managecommentnew', $group['managecomment'], 'radio');
		showsetting('admingroup_edit_manage_magiclog', 'managemagiclognew', $group['managemagiclog'], 'radio');
		showsetting('admingroup_edit_manage_report', 'managereportnew', $group['managereport'], 'radio');
		showsetting('admingroup_edit_manage_hotuser', 'managehotusernew', $group['managehotuser'], 'radio');
		showsetting('admingroup_edit_manage_defaultuser', 'managedefaultusernew', $group['managedefaultuser'], 'radio');
		showsetting('admingroup_edit_manage_videophoto', 'managevideophotonew', $group['managevideophoto'], 'radio');
		showsetting('admingroup_edit_manage_magic', 'managemagicnew', $group['managemagic'], 'radio');
		showsetting('admingroup_edit_manage_click', 'manageclicknew', $group['manageclick'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'portalperm', $_G['gp_anchor'] == 'portalperm');
		showtitle('admingroup_edit_portalperm');
		showsetting('admingroup_edit_manage_article', 'allowmanagearticlenew', $group['allowmanagearticle'], 'radio');
		showsetting('admingroup_edit_add_topic', 'allowaddtopicnew', $group['allowaddtopic'], 'radio');
		showsetting('admingroup_edit_manage_topic', 'allowmanagetopicnew', $group['allowmanagetopic'], 'radio');
		showsetting('admingroup_edit_diy', 'allowdiynew', $group['allowdiy'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'otherperm', $_G['gp_anchor'] == 'otherperm');
		showtitle('admingroup_edit_otherperm');
		showsetting('admingroup_edit_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
		showsetting('admingroup_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
		showtagfooter('tbody');

		showsubmit('groupsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$group = DB::fetch_first("SELECT groupid, radminid FROM ".DB::table('common_usergroup')." WHERE groupid='$id'");
		if(!$group) {
			cpmsg('undefined_action', '', 'error');
		}

		DB::update('common_admingroup', array(
			'alloweditpost' => $_G['gp_alloweditpostnew'],
			'alloweditpoll' => $_G['gp_alloweditpollnew'],
			'allowedittrade' => $_G['gp_allowedittradenew'],
			'allowremovereward' => $_G['gp_allowremoverewardnew'],
			'alloweditactivity' => $_G['gp_alloweditactivitynew'],
			'allowstickthread' => $_G['gp_allowstickthreadnew'],
			'allowmodpost' => $_G['gp_allowmodpostnew'],
			'allowbanpost' => $_G['gp_allowbanpostnew'],
			'allowdelpost' => $_G['gp_allowdelpostnew'],
			'allowmassprune' => $_G['gp_allowmassprunenew'],
			'allowrefund' => $_G['gp_allowrefundnew'],
			'allowcensorword' => $_G['gp_allowcensorwordnew'],
			'allowviewip' => $_G['gp_allowviewipnew'],
			'allowbanip' => $_G['gp_allowbanipnew'],
			'allowedituser' => $_G['gp_alloweditusernew'],
			'allowbanuser' => $_G['gp_allowbanusernew'],
			'allowmoduser' => $_G['gp_allowmodusernew'],
			'allowpostannounce' => $_G['gp_allowpostannouncenew'],
			'allowhighlightthread' => $_G['gp_allowhighlightthreadnew'],
			'allowdigestthread' => $_G['gp_allowdigestthreadnew'],
			'allowrecommendthread' => $_G['gp_allowrecommendthreadnew'],
			'allowbumpthread' => $_G['gp_allowbumpthreadnew'],
			'allowclosethread' => $_G['gp_allowclosethreadnew'],
			'allowmovethread' => $_G['gp_allowmovethreadnew'],
			'allowedittypethread' => $_G['gp_allowedittypethreadnew'],
			'allowstampthread' => $_G['gp_allowstampthreadnew'],
			'allowcopythread' => $_G['gp_allowcopythreadnew'],
			'allowmergethread' => $_G['gp_allowmergethreadnew'],
			'allowsplitthread' => $_G['gp_allowsplitthreadnew'],
			'allowrepairthread' => $_G['gp_allowrepairthreadnew'],
			'allowwarnpost' => $_G['gp_allowwarnpostnew'],
			'alloweditforum' => $_G['gp_alloweditforumnew'],
			'allowviewlog' => $_G['gp_allowviewlognew'],
			'disablepostctrl' => $_G['gp_disablepostctrlnew'],
			'allowmanagearticle' => $_G['gp_allowmanagearticlenew'],
			'allowaddtopic' => $_G['gp_allowaddtopicnew'],
			'allowmanagetopic' => $_G['gp_allowmanagetopicnew'],
			'allowdiy' => $_G['gp_allowdiynew'],
			'allowstickreply' => $_G['gp_allowstickreplynew'],
			'managefeed' => $_G['gp_managefeednew'],
			'managedoing' => $_G['gp_managedoingnew'],
			'manageshare' => $_G['gp_managesharenew'],
			'manageblog' => $_G['gp_manageblognew'],
			'managealbum' => $_G['gp_managealbumnew'],
			'managecomment' => $_G['gp_managecommentnew'],
			'managemagiclog' => $_G['gp_managemagiclognew'],
			'managereport' => $_G['gp_managereportnew'],
			'managehotuser' => $_G['gp_managehotusernew'],
			'managedefaultuser' => $_G['gp_managedefaultusernew'],
			'managevideophoto' => $_G['gp_managevideophotonew'],
			'managemagic' => $_G['gp_managemagicnew'],
			'manageclick' => $_G['gp_manageclicknew'],
		), "admingid='$group[groupid]'");

		updatecache('usergroups');
		updatecache('admingroups');
		cpmsg('admingroups_edit_succeed', 'action=admingroup&operation=edit&id='.$group['groupid'].'&anchor='.$_G['gp_anchor'], 'succeed');
	}
}

function deletegroupcache($groupidarray) {
	if(!empty($groupidarray) && is_array($groupidarray)) {
		foreach ($groupidarray as $id) {
			if(is_numeric($id) && $id = intval($id)) {
				@unlink(DISCUZ_ROOT.'./data/cache/forum_usergroup_'.$id.'.php');
				@unlink(DISCUZ_ROOT.'./data/cache/forum_admingroup_'.$id.'.php');
			}
		}
	}
}

?>