<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_usergroups.php 11592 2010-06-09 00:13:59Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('groupsubmit')) {

		$sgroups = $smembers = array();
		$sgroupids = '0';
		$smembernum = $membergroup = $specialgroup = $sysgroup = $membergroupoption = $specialgroupoption = '';

		$query = DB::query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower, stars, color, icon FROM ".DB::table('common_usergroup')." ORDER BY creditshigher");
		while($group = DB::fetch($query)) {
			if($group['type'] == 'member') {

				$membergroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$membergroup .= showtablerow('', array('class="td25"', '', 'class="td28"', 'class=td28'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditslower]\" value=\"$group[creditslower]\" disabled />",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\">",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>"
				), TRUE);
			} elseif($group['type'] == 'system') {
				$sysgroup .= showtablerow('', array('', '', 'class="td28"'), array(
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
					$lang['usergroups_system_'.$group['groupid']],
					"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\">",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gsystem\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>"
				), TRUE);
			} elseif($group['type'] == 'special' && $group['radminid'] == '0') {

				$specialgroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$sgroups[] = $group;
				$sgroupids .= ','.$group['groupid'];
			}
		}

		foreach($sgroups as $group) {
			if(is_array($smembers[$group['groupid']])) {
				$num = count($smembers[$group['groupid']]);
				$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
				unset($smembers[$group['groupid']]);
			} else {
				$specifiedusers = '';
				$num = 0;
			}
			$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";

			$specialgroup .= showtablerow('', array('class="td25"', '', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" class=\"txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\">",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gspecial\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
				"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=viewsgroup&sgroupid=$group[groupid]\" onclick=\"ajaxget(this.href, 'sgroup_$group[groupid]', 'sgroup_$group[groupid]', 'auto');doane(event);\" class=\"act\">$lang[view]</a> &nbsp;"
			), TRUE);
			$specialgroup .= showtablerow('', array('colspan="5" id="sgroup_'.$group['groupid'].'" style="display: none"'), array(
				''
			), TRUE);
		}

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="groupnewadd[grouptitle][]">'],
		[1,'<input type="text" class="txt" size="6" name="groupnewadd[creditshigher][]">', 'td28'],
		[1,'<input type="text" class="txt" size="2" name="groupnewadd[stars][]">', 'td28'],
		[2,'<select name="groupnewadd[projectid][]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$membergroupoption</select>']
	],
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="grouptitlenewadd[]">'],
		[1,'<input type="text" class="txt" size="2" name="starsnewadd[]">', 'td28'],
		[1,'<input type="text" class="txt" size="6" name="colornewadd[]">'],
		[1,'<select name="groupnewaddproject[]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$specialgroupoption</select>'],
	]
];
</script>
EOT;
		shownav('user', 'nav_usergroups');
		showsubmenuanchors('nav_usergroups', array(
			array('usergroups_member', 'membergroups', !$_G['gp_type'] || $_G['gp_type'] == 'member'),
			array('usergroups_special', 'specialgroups', $_G['gp_type'] == 'special'),
			array('usergroups_system', 'systemgroups', $_G['gp_type'] == 'system')
		));
		showtips('usergroups_tips');

		showformheader('usergroups&type=member');
		showtableheader('usergroups_member', 'fixpadding', 'id="membergroups"'.($_G['gp_type'] && $_G['gp_type'] != 'member' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', 'usergroups_creditsrange', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcmember" onclick="checkAll(\'value\', this.form, \'gmember\', \'gcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $membergroup;
		echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=special');
		showtableheader('usergroups_special', 'fixpadding', 'id="specialgroups"'.($_G['gp_type'] != 'special' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcspecial" onclick="checkAll(\'value\', this.form, \'gspecial\', \'gcspecial\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $specialgroup;
		echo '<tr><td>&nbsp;</td><td colspan="5"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=system');
		showtableheader('usergroups_system', 'fixpadding', 'id="systemgroups"'.($_G['gp_type'] != 'system' ? ' style="display: none"' : ''));
		showsubtitle(array('usergroups_title', 'usergroups_status', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcsystem" onclick="checkAll(\'value\', this.form, \'gsystem\', \'gcsystem\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $sysgroup;
		showsubmit('groupsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(empty($_G['gp_type']) || !in_array($_G['gp_type'], array('member', 'special', 'system'))) {
			cpmsg('usergroups_type_nonexistence');
		}

		$oldgroups = $extadd = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." WHERE `type`='{$_G['gp_type']}'");
		while ($gp = DB::fetch($query)) {
			$oldgroups[$gp['groupid']] = $gp;
		}

		foreach($oldgroups as $id => $vals) {
			$data = array();
			foreach($vals as $k => $v) {
				$v = addslashes($v);
				if(!in_array($k, array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'icon'))) {
					$data[$k] = $v;
				}
			}
			$extadd['g'.$id] = $data;
		}

		if($_G['gp_type'] == 'member') {
			$groupnewadd = array_flip_keys($_G['gp_groupnewadd']);
			foreach($groupnewadd as $k => $v) {
				if(!$v['grouptitle'] || !$v['creditshigher']) {
					unset($groupnewadd[$k]);
				}
			}
			$groupnewkeys = array_keys($_G['gp_groupnew']);
			$maxgroupid = max($groupnewkeys);
			foreach($groupnewadd as $k=>$v) {
				$_G['gp_groupnew'][$k+$maxgroupid+1] = $v;
			}
			$orderarray = array();
			if(is_array($_G['gp_groupnew'])) {
				foreach($_G['gp_groupnew'] as $id => $group) {
					if((is_array($_G['gp_delete']) && in_array($id, $_G['gp_delete'])) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
						unset($_G['gp_groupnew'][$id]);
					} else {
						$orderarray[$group['creditshigher']] = $id;
					}
				}
			}

			if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
				cpmsg('usergroups_update_credits_invalid', '', 'error');
			}

			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array
					(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
					);
			}

			foreach($_G['gp_groupnew'] as $id => $group) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('usergroups_update_credits_duplicate', '', 'error');
				}
				if(in_array($id, $groupnewkeys)) {
					DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]', color='$group[color]', icon='$group[icon]' WHERE groupid='$id' AND type='member'");
					DB::update('forum_onlinelist', array(
						'title' => $group['grouptitle'],
					), "groupid='$id'");

				} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
					$data = array(
						'grouptitle' => $group['grouptitle'],
						'creditshigher' => $creditshighernew,
						'creditslower' => $creditslowernew,
						'stars' => $group['stars'],
					);
					if(!empty($group['projectid']) && !empty($extadd[$group['projectid']])) {
						$data = array_merge($data, $extadd[$group['projectid']]);
					}
					$newgid = DB::insert('common_usergroup', $data, 1);
					DB::insert('common_usergroup_field', array('groupid' => $newgid));
					DB::insert('forum_onlinelist', array(
						'groupid' => $newgid,
						'title' => $data['grouptitle'],
						'displayorder' => '0',
						'url' => '',
					), false, true);
					if($sqladd) {
						$query = DB::query("SELECT fid, viewperm, postperm, replyperm, getattachperm, postattachperm, postimageperm FROM ".DB::table('forum_forumfield')."");
						while($row = DB::fetch($query)) {
							$upforumperm = array();
							$projectid = substr($group['projectid'], 1);
							if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
								$upforumperm[] = "viewperm='$row[viewperm]$newgid\t'";
							}
							if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
								$upforumperm[] = "postperm='$row[postperm]$newgid\t'";
							}
							if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
								$upforumperm[] = "replyperm='$row[replyperm]$newgid\t'";
							}
							if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
								$upforumperm[] = "getattachperm='$row[getattachperm]$newgid\t'";
							}
							if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
								$upforumperm[] = "postattachperm='$row[postattachperm]$newgid\t'";
							}
							if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
								$upforumperm[] = "postimageperm='$row[postimageperm]$newgid\t'";
							}
							if($upforumperm) {
								DB::query("UPDATE ".DB::table('forum_forumfield')." SET ".implode(',', $upforumperm)." WHERE fid='$row[fid]'");
							}
						}
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='member'");
				DB::query("DELETE FROM ".DB::table('common_usergroup_field')." WHERE groupid IN ($ids)");
				DB::delete('forum_onlinelist', "groupid IN ($ids)");
				deletegroupcache($_G['gp_delete']);
			}

		} elseif($_G['gp_type'] == 'special') {
			if(is_array($_G['gp_grouptitlenewadd'])) {
				foreach($_G['gp_grouptitlenewadd'] as $k => $v) {
					if($v) {
						$sqladd = !empty($_G['gp_groupnewaddproject'][$k]) && !empty($extadd[$_G['gp_groupnewaddproject'][$k]]) ? $extadd[$_G['gp_groupnewaddproject'][$k]] : '';
						$data = array(
							'type' => 'special',
							'grouptitle' => $_G['gp_grouptitlenewadd'][$k],
							'color' => $_G['gp_colornewadd'][$k],
							'stars' => $_G['gp_starsnewadd'][$k],
						);
						if(!empty($_G['gp_groupnewaddproject'][$k]) && !empty($extadd[$_G['gp_groupnewaddproject'][$k]])) {
							$data = array_merge($data, $extadd[$_G['gp_groupnewaddproject'][$k]]);
						}
						$newgroupid = DB::insert('common_usergroup', $data, true);
						DB::insert('common_usergroup_field', array('groupid' => $newgroupid));
						DB::insert('forum_onlinelist', array(
							'groupid' => $newgroupid,
							'title' => $data['grouptitle'],
							'url' => '',
						), false, true);
					}
				}
			}

			if(is_array($_G['gp_group_title'])) {
				foreach($_G['gp_group_title'] as $id => $title) {
					if(!$_G['gp_delete'][$id]) {
						DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='{$_G['gp_group_title'][$id]}', stars='{$_G['gp_group_stars'][$id]}', color='{$_G['gp_group_color'][$id]}' WHERE groupid='$id'");
						DB::update('forum_onlinelist', array(
							'title' => $_G['gp_group_title'][$id],
						), "groupid='$id'");
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='special'");
				DB::delete('forum_onlinelist', "groupid IN ($ids)");
				DB::query("DELETE FROM ".DB::table('common_admingroup')." WHERE admingid IN ($ids)");
				$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
				DB::query("UPDATE ".DB::table('common_member')." SET groupid='$newgroupid', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
				deletegroupcache($_G['gp_delete']);
			}

		} elseif($_G['gp_type'] == 'system') {
			if(is_array($_G['gp_group_title'])) {
				foreach($_G['gp_group_title'] as $id => $title) {
					DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='{$_G['gp_group_title'][$id]}', stars='{$_G['gp_group_stars'][$id]}', color='{$_G['gp_group_color'][$id]}', icon='{$_G['gp_group_icon'][$id]}' WHERE groupid='$id'");
					DB::update('forum_onlinelist', array(
						'title' => $_G['gp_group_title'][$id],
					), "groupid='$id'");
				}
			}
		}

		updatecache(array('usergroups', 'onlinelist'));
		cpmsg('usergroups_update_succeed', 'action=usergroups&type='.$_G['gp_type'], 'succeed');
	}

} elseif($operation == 'viewsgroup') {

	$sgroupid = $_G['gp_sgroupid'];
	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE groupid='$sgroupid'");
	$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE groupid='$sgroupid' LIMIT 80");
	$sgroups = '';
	while($member = DB::fetch($query)) {
		$sgroups .= '<li><a href="home.php?mod=space&uid='.$member['uid'].'" target="_blank">'.$member['username'].'</a></li>';
	}
	ajaxshowheader();
	echo '<ul class="userlist"><li class="unum">'.$lang['usernum'].$num.($num > 80 ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$sgroupid.'">'.$lang['more'].'&raquo;</a>' : '').'</li>'.$sgroups.'</ul>';
	ajaxshowfooter();

} elseif($operation == 'edit') {

	$return = isset($_G['gp_return']) && $_G['gp_return'] ? 'admin' : '';

	if(empty($_G['gp_multi'])) {
		$multiset = 0;
		$gids = $_G['gp_id'];
	} else {
		$multiset = 1;
		if(is_array($_G['gp_multi'])) {
			$gids = dimplode($_G['gp_multi']);
		} else {
			$_G['gp_multi'] = explode(',', $_G['gp_multi']);
			array_walk($_G['gp_multi'], 'intval');
			$gids = dimplode($_G['gp_multi']);
		}
	}

	if(empty($gids)) {
		$grouplist = "<select name=\"id\" style=\"width:150px\">\n";
		$conditions = !empty($_G['gp_anchor']) && $_G['gp_anchor'] == 'system' ? "WHERE type='special'" : '';
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." $conditions");
		while($group = DB::fetch($query)) {
			$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$grouplist .= '</select>';
		cpmsg('usergroups_edit_nonexistence', 'action=usergroups&operation=edit'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : '').(!empty($_G['gp_highlight']) ? "&anchor={$_G['gp_anchor']}" : ''), 'form', array(), $grouplist);
	}

	$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." u
		LEFT JOIN ".DB::table('common_usergroup_field')." uf USING(groupid)
		WHERE u.groupid IN ($gids)");
	if(!DB::num_rows($query)) {
		cpmsg('usergroups_nonexistence', '', 'error');
	} else {
		while($group = DB::fetch($query)) {
			$mgroup[] = $group;
		}
	}

	$allowthreadplugin = $_G['setting']['threadplugins'] ? unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'")) : array();

	if(!submitcheck('detailsubmit')) {

		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		$grouplist = $groupcount = array();
		while($ggroup = DB::fetch($query)) {
			$ggroup['type'] = $ggroup['type'] == 'special' && $ggroup['radminid'] ? 'specialadmin' : $ggroup['type'];
			$groupcount[$ggroup['type']]++;
			$grouplist[$ggroup['type']] .= '<input class="left checkbox ck" chkvalue="'.$ggroup['type'].'" name="multi[]" value="'.$ggroup['groupid'].'" type="checkbox" />'.
				'<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+scrollTopBody()"'.($_G['gp_id'] == $ggroup['groupid'] || in_array($ggroup['groupid'], $_G['gp_multi']) ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
			if(!($groupcount[$ggroup['type']] % 3)) {
				$grouplist[$ggroup['type']] .= '<br style="clear:both" />';
			}
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'ugselect_menu\').style.left=(parseInt($(\'ugselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.
			'<span class="pointer right" onclick="$(\'menuform\').submit()"><b>'.cplang('usergroups_multiedit').'</b></span>'.
			'<em><span class="right"><input name="checkall_member" onclick="checkAll(\'value\', this.form, \'member\', \'checkall_member\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_member'].'</em>'.$grouplist['member'].'<br />'.
			($grouplist['special'] ? '<em><span class="right"><input name="checkall_special" onclick="checkAll(\'value\', this.form, \'special\', \'checkall_special\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_special'].'</em>'.$grouplist['special'].'<br />' : '').
			($grouplist['specialadmin'] ? '<em><span class="right"><input name="checkall_specialadmin" onclick="checkAll(\'value\', this.form, \'specialadmin\', \'checkall_specialadmin\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_specialadmin'].'</em>'.$grouplist['specialadmin'].'<br />' : '').
			'<em><span class="right"><input name="checkall_system" onclick="checkAll(\'value\', this.form, \'system\', \'checkall_system\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_system'].'</em>'.$grouplist['system'].'</div>';

		$anchor = in_array($_G['gp_anchor'], array('basic', 'system', 'special', 'post', 'attach', 'magic', 'invite', 'credit', 'home', 'group', 'portal')) ? $_G['gp_anchor'] : 'basic';
		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'usergroups', 'operation' => 'edit'));
		showsubmenuanchors(cplang('usergroups_edit').(count($mgroup) == 1 ? ' - '.$mgroup[0]['grouptitle'] : ''), array(
			array('usergroups_edit_basic', 'basic', $anchor == 'basic'),
			count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1 ? array('usergroups_edit_system', 'system', $anchor == 'system') : array(),
			array(array('menu' => 'usergroups_edit_forum', 'submenu' => array(
				array('usergroups_edit_post', 'post', $anchor == 'post'),
				array('usergroups_edit_attach', 'attach', $anchor == 'attach'),
				array('usergroups_edit_special', 'special', $anchor == 'special')
			))),
			array('usergroups_edit_group', 'group', $anchor == 'group'),
			array('usergroups_edit_portal', 'portal', $anchor == 'portal'),
			array('usergroups_edit_home', 'home', $anchor == 'home'),
			array(array('menu' => 'usergroups_edit_other', 'submenu' => array(
				!$multiset ? array('usergroups_edit_credit', 'credit', $anchor == 'credit') : array(),
				array('usergroups_edit_magic', 'magic', $anchor == 'magic'),
				array('usergroups_edit_invite', 'invite', $anchor == 'invite'),
			))),
		), $gselect);
		showformfooter();

		if(count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1) {
			showtips('usergroups_edit_system_tips', 'system_tips', $anchor == 'system');
		}
		if($multiset) {
			showtips('setting_multi_tips');
		}

		showtips('usergroups_edit_magic_tips', 'magic_tips', $anchor == 'magic');
		showtips('usergroups_edit_invite_tips', 'invite_tips', $anchor == 'invite');
		showformheader("usergroups&operation=edit&id={$_G['gp_id']}&return=$return", 'enctype');

		if($multiset) {
			$_G['showsetting_multi'] = 0;
			$_G['showsetting_multicount'] = count($mgroup);
			foreach($mgroup as $group) {
				$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&id='.$group['groupid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$group['grouptitle'].'</a>';
			}
		}
		$mgids = array();
		foreach($mgroup as $group) {
		$_G['gp_id'] = $gid = $group['groupid'];
		$mgids[] = $gid;

		if(!$multiset && $group['type'] == 'special' && $group['radminid'] < 1) {
			showtagheader('div', 'system', $anchor == 'system');
			showtableheader();
			if($group['system'] == 'private') {
				$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
			} else {
				$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
				list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
			}
			showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio', 0, 1);
			showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
			showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
			showtablefooter();
			showtagfooter('div');
		}

		showtableheader();
		showtagheader('tbody', 'basic', $anchor == 'basic');
		showtitle('usergroups_edit_basic');
		showsetting('usergroups_edit_basic_title', 'grouptitlenew', $group['grouptitle'], 'text');
		if(!$multiset) {
			$group['exempt'] = strrev(sprintf('%0'.strlen($group['exempt']).'b', $group['exempt']));
			if($group['icon']) {
				$valueparse = parse_url($group['icon']);
				if(isset($valueparse['host'])) {
					$groupicon = $group['icon'];
				} else {
					$groupicon = $_G['setting']['attachurl'].'common/'.$group['icon'].'?'.random(6);
				}
				$groupiconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon[$group[groupid]]" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$groupicon.'" />';
			}
			showsetting('usergroups_icon', 'iconnew', $group['icon'], 'filetext', '', 0, $groupiconhtml);
		}

		showsetting('usergroups_edit_basic_visit', 'allowvisitnew', $group['allowvisit'], 'radio', in_array($group['groupid'], array(1)));

		showsetting('usergroups_edit_basic_read_access', 'readaccessnew', $group['readaccess'], 'text');
		showsetting('usergroups_edit_basic_max_friend_number', 'maxfriendnumnew', $group['maxfriendnum'], 'text');
		showsetting('usergroups_edit_basic_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
		showsetting('usergroups_edit_basic_multigroups', 'allowmultigroupsnew', $group['allowmultigroups'], 'radio');
		showsetting('usergroups_edit_basic_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
		showsetting('usergroups_edit_basic_allowsendpm', 'allowsendpmnew', $group['allowsendpm'], 'radio');
		showsetting('usergroups_edit_post_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
		showsetting('usergroups_edit_basic_allow_stat', 'allowstatnew', $group['allowstat'], 'radio');
		showsetting('usergroups_edit_basic_search_post', 'allowfulltextnew', $group['allowsearch'] & 32, 'radio');
		showsetting('usergroups_edit_basic_search', array('allowsearchnew', array(
			cplang('setting_search_status_portal'),
			cplang('setting_search_status_forum'),
			cplang('setting_search_status_blog'),
			cplang('setting_search_status_album'),
			cplang('setting_search_status_group')
		)), $group['allowsearch'], 'binmcheckbox');
		showsetting('usergroups_edit_basic_reasonpm', array('reasonpmnew', array(
			array(0, $lang['usergroups_edit_basic_reasonpm_none']),
			array(1, $lang['usergroups_edit_basic_reasonpm_reason']),
			array(2, $lang['usergroups_edit_basic_reasonpm_pm']),
			array(3, $lang['usergroups_edit_basic_reasonpm_both'])
		)), $group['reasonpm'], 'mradio');
		showsetting('usergroups_edit_basic_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
		showsetting('usergroups_edit_basic_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
		showsetting('usergroups_edit_basic_hour_posts', 'maxpostsperhournew', $group['maxpostsperhour'], 'text');
		showsetting('usergroups_edit_basic_seccode', 'seccodenew', $group['seccode'], 'radio');
		showsetting('usergroups_edit_basic_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'special', $anchor == 'special');
		showtitle('usergroups_edit_special');
		showsetting('usergroups_edit_special_activity', 'allowpostactivitynew', $group['allowpostactivity'], 'radio');
		showsetting('usergroups_edit_special_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
		showsetting('usergroups_edit_special_vote', 'allowvotenew', $group['allowvote'], 'radio');
		showsetting('usergroups_edit_special_reward', 'allowpostrewardnew', $group['allowpostreward'], 'radio');
		showsetting('usergroups_edit_special_reward_min', 'minrewardpricenew', $group['minrewardprice'], "text");
		showsetting('usergroups_edit_special_reward_max', 'maxrewardpricenew', $group['maxrewardprice'], "text");
		showsetting('usergroups_edit_special_trade', 'allowposttradenew', $group['allowposttrade'], 'radio');
		showsetting('usergroups_edit_special_trade_min', 'mintradepricenew', $group['mintradeprice'], "text");
		showsetting('usergroups_edit_special_trade_max', 'maxtradepricenew', $group['maxtradeprice'], "text");
		showsetting('usergroups_edit_special_trade_stick', 'tradesticknew', $group['tradestick'], "text");
		showsetting('usergroups_edit_special_debate', 'allowpostdebatenew', $group['allowpostdebate'], "radio");
		showsetting('usergroups_edit_special_rushreply', 'allowpostrushreplynew', $group['allowpostrushreply'], "radio");
		$threadpluginselect = array();
		if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
			$threadpluginselect[] = array($tpid, $data['name']);
		}
		if($threadpluginselect) {
			showsetting('usergroups_edit_special_allowthreadplugin', array('allowthreadpluginnew', $threadpluginselect), $allowthreadplugin[$_G['gp_id']], 'mcheckbox');
		}
		showtagfooter('tbody');

		showtagheader('tbody', 'post', $anchor == 'post');
		showtitle('usergroups_edit_post');
		showsetting('usergroups_edit_post_new', 'allowpostnew', $group['allowpost'], 'radio');
		showsetting('usergroups_edit_post_reply', 'allowreplynew', $group['allowreply'], 'radio');
		showsetting('usergroups_edit_post_direct', array('allowdirectpostnew', array(
			array(0, $lang['usergroups_edit_post_direct_none']),
			array(1, $lang['usergroups_edit_post_direct_reply']),
			array(2, $lang['usergroups_edit_post_direct_thread']),
			array(3, $lang['usergroups_edit_post_direct_all'])
		)), $group['allowdirectpost'], 'mradio');
		showsetting('usergroups_edit_post_url', array('allowposturlnew', array(
			array(0, $lang['usergroups_edit_post_url_banned']),
			array(1, $lang['usergroups_edit_post_url_mod']),
			array(2, $lang['usergroups_edit_post_url_unhandle']),
			array(3, $lang['usergroups_edit_post_url_enable'])
		)), $group['allowposturl'], 'mradio');
		showsetting('usergroups_edit_post_anonymous', 'allowanonymousnew', $group['allowanonymous'], 'radio');
		showsetting('usergroups_edit_post_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
		showsetting('usergroups_edit_post_maxprice', 'maxpricenew', $group['maxprice'], 'text');
		showsetting('usergroups_edit_post_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
		showsetting('usergroups_edit_post_custom_bbcode', 'allowcusbbcodenew', $group['allowcusbbcode'], 'radio');
		showsetting('usergroups_edit_post_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
		showsetting('usergroups_edit_post_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
		showsetting('usergroups_edit_post_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');
		if($group['groupid'] != 7) {
			showsetting('usergroups_edit_post_recommend', 'allowrecommendnew', $group['allowrecommend'], 'text');
		}
		showsetting('usergroups_edit_post_edit_time_limit', 'edittimelimit', intval($group['edittimelimit']), 'text');
		showsetting('usergroups_edit_post_allowcommentpost', array('allowcommentpostnew', array(
			array(0, $lang['usergroups_edit_post_allowcommentpost_none']),
			array(1, $lang['usergroups_edit_post_allowcommentpost_firstpost']),
			array(2, $lang['usergroups_edit_post_allowcommentpost_reply']),
			array(3, $lang['usergroups_edit_post_allowcommentpost_all']),
		)), $group['allowcommentpost'], 'mradio');
		showsetting('usergroups_edit_post_allowcommentitem', 'allowcommentitemnew', $group['allowcommentitem'], 'radio');
		showtagfooter('tbody');

		$group['maxattachsize'] = intval($group['maxattachsize'] / 1024);
		$group['maxsizeperday'] = intval($group['maxsizeperday'] / 1024);

		showtagheader('tbody', 'attach', $anchor == 'attach');
		showtitle('usergroups_edit_attach');
		showsetting('usergroups_edit_attach_get', 'allowgetattachnew', $group['allowgetattach'], 'radio');
		showsetting('usergroups_edit_attach_post', 'allowpostattachnew', $group['allowpostattach'], 'radio');
		showsetting('usergroups_edit_attach_set_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
		showsetting('usergroups_edit_image_post', 'allowpostimagenew', $group['allowpostimage'], 'radio');
		showsetting('usergroups_edit_attach_max_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
		showsetting('usergroups_edit_attach_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
		showsetting('usergroups_edit_attach_max_number_per_day', 'maxattachnumnew', $group['maxattachnum'], 'text');
		showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'magic', $anchor == 'magic');
		showtitle('usergroups_edit_magic');
		showsetting('usergroups_edit_magic_permission', array('allowmagicsnew', array(
			array(0, $lang['usergroups_edit_magic_unallowed']),
			array(1, $lang['usergroups_edit_magic_allow']),
			array(2, $lang['usergroups_edit_magic_allow_and_pass'])
		)), $group['allowmagics'], 'mradio');
		showsetting('usergroups_edit_magic_discount', 'magicsdiscountnew', $group['magicsdiscount'], 'text');
		showsetting('usergroups_edit_magic_max', 'maxmagicsweightnew', $group['maxmagicsweight'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'invite', $anchor == 'invite');
		showtitle('usergroups_edit_invite');
		showsetting('usergroups_edit_invite_permission', 'allowinvitenew', $group['allowinvite'], 'radio');
		showsetting('usergroups_edit_invite_send_permission', 'allowmailinvitenew', $group['allowmailinvite'], 'radio');
		showsetting('usergroups_edit_invite_price', 'invitepricenew', $group['inviteprice'], 'text');
		showsetting('usergroups_edit_invite_buynum', 'maxinvitenumnew', $group['maxinvitenum'], 'text');
		showsetting('usergroups_edit_invite_maxinviteday', 'maxinvitedaynew', $group['maxinviteday'], 'text');
		showtagfooter('tbody');

		if(!$multiset) {
			showtagheader('tbody', 'credit', $anchor == 'credit');
			showtitle('usergroups_edit_credit');
			showsetting('usergroups_edit_credit_exempt_sendpm', 'exemptnew[0]', $group['exempt'][0], 'radio');
			showsetting('usergroups_edit_credit_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			if($group['radminid']) {
				if($group['radminid'] == 3) {
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio');
				} else {
					echo '<input name="exemptnew[2]" type="hidden" value="1" /><input name="exemptnew[3]" type="hidden" value="1" /><input name="exemptnew[4]" type="hidden" value="1" />'.
						'<input name="exemptnew[5]" type="hidden" value="1" /><input name="exemptnew[6]" type="hidden" value="1" /><input name="exemptnew[7]" type="hidden" value="1" />';
				}
			} else {
				showsetting('usergroups_edit_credit_exempt_getattch', 'exemptnew[2]', $group['exempt'][2], 'radio');
				showsetting('usergroups_edit_credit_exempt_attachpay', 'exemptnew[3]', $group['exempt'][3], 'radio');
				showsetting('usergroups_edit_credit_exempt_threadpay', 'exemptnew[4]', $group['exempt'][4], 'radio');
			}

			echo '<tr><td colspan="2">'.$lang['usergroups_edit_credit_exempt_comment'].'</td></tr>';

			$raterangearray = array();
			foreach(explode("\n", $group['raterange']) as $range) {
				$range = explode("\t", $range);
				$raterangearray[$range[0]] = array('min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]);
			}

			echo '<tr><td colspan="2">';
			showtableheader('usergroups_edit_credit_allowrate', 'noborder');
			showsubtitle(array('', 'credits_id', 'credits_title', 'usergroups_edit_credit_rate_min', 'usergroups_edit_credit_rate_max', 'usergroups_edit_credit_rate_mrpd'));
			for($i = 1; $i <= 8; $i++) {
				if(isset($_G['setting']['extcredits'][$i])) {
					echo '<tr><td><input class="checkbox" type="checkbox" name="raterangenew['.$i.'][allowrate]" value="1" '.(empty($raterangearray[$i]) ? '' : 'checked').'></td>'.
						'<td>extcredits'.$i.'</td>'.
						'<td>'.$_G['setting']['extcredits'][$i]['title'].'</td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][min]" size="3" value="'.$raterangearray[$i]['min'].'"></td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][max]" size="3" value="'.$raterangearray[$i]['max'].'"></td>'.
						'<td><input type="text" class="txt" name="raterangenew['.$i.'][mrpd]" size="3" value="'.$raterangearray[$i]['mrpd'].'"></td></tr>';
				}
			}
			echo '<tr><td colspan="6">'.$lang['usergroups_edit_credit_allowrate_comment'].'</td></tr></td></tr>';
			showtablefooter();
			echo '</td></tr>';
		}
		showtagfooter('tbody');

		showtagheader('tbody', 'home', $anchor == 'home');
		showtitle('usergroups_edit_home');
		showsetting('usergroups_edit_home_domain_length', 'domainlengthnew', $group['domainlength'], 'text');
		showsetting('usergroups_edit_attach_max_space_size', 'maxspacesizenew', $group['maxspacesize'], 'text');
		showsetting('usergroups_edit_home_allow_blog', 'allowblognew', $group['allowblog'], 'radio');
		showsetting('usergroups_edit_home_allow_doing', 'allowdoingnew', $group['allowdoing'], 'radio');
		showsetting('usergroups_edit_home_allow_upload', 'allowuploadnew', $group['allowupload'], 'radio');
		showsetting('usergroups_edit_home_allow_share', 'allowsharenew', $group['allowshare'], 'radio');
		showsetting('usergroups_edit_home_allow_poke', 'allowpokenew', $group['allowpoke'], 'radio');
		showsetting('usergroups_edit_home_allow_friend', 'allowfriendnew', $group['allowfriend'], 'radio');
		showsetting('usergroups_edit_home_allow_click', 'allowclicknew', $group['allowclick'], 'radio');
		showsetting('usergroups_edit_home_allow_comment', 'allowcommentnew', $group['allowcomment'], 'radio');
		showsetting('usergroups_edit_home_allow_myop', 'allowmyopnew', $group['allowmyop'], 'radio');
		showsetting('usergroups_edit_home_allow_video_photo_ignore', 'videophotoignorenew', $group['videophotoignore'], 'radio');
		showsetting('usergroups_edit_home_allow_view_video_photo', 'allowviewvideophotonew', $group['allowviewvideophoto'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_html', 'allowspacediyhtmlnew', $group['allowspacediyhtml'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_bbcode', 'allowspacediybbcodenew', $group['allowspacediybbcode'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_imgcode', 'allowspacediyimgcodenew', $group['allowspacediyimgcode'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'group', $anchor == 'group');
		showtitle('usergroups_edit_group');
		showsetting('usergroups_edit_group_build', 'allowbuildgroupnew', $group['allowbuildgroup'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'portal', $anchor == 'portal');
		showtitle('usergroups_edit_portal');
		showsetting('usergroups_edit_portal_allow_post_article', 'allowpostarticlenew', $group['allowpostarticle'], 'radio');
		showtagfooter('tbody');

		showsubmit('detailsubmit', 'submit');
		showtablefooter();
		$_G['showsetting_multi']++;
		}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mgids)));
		}
		showformfooter();

	} else {

		if(!$multiset) {
			$_G['gp_multinew'] = array(0 => array('single' => 1));
		}
		foreach($_G['gp_multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_G['gp_'.$key] = $value;
			}
			$_G['gp_id'] = $_G['gp_multi'][$k];
		}
		$group = $mgroup[$k];

		$systemnew = 'private';

		if($group['type'] == 'special' && $group['radminid'] > 0) {

			$radminidnew = $group['radminid'];

		} elseif($group['type'] == 'special') {

			$radminidnew = '0';
			if(!$multiset && $_G['gp_system_publicnew']) {
				if($_G['gp_system_dailypricenew'] > 0) {
					if(!$_G['setting']['creditstrans']) {
						cpmsg('usergroups_edit_creditstrans_disabled', '', 'error');
					} else {
						$system_minspannew = $_G['gp_system_minspannew'] <= 0 ? 1 : $_G['gp_system_minspannew'];
						$systemnew = intval($_G['gp_system_dailypricenew'])."\t".intval($system_minspannew);
					}
				} else {
					$systemnew = "0\t0";
				}
			}

		} else {
			$radminidnew = in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
		}

		if(!$multiset && is_array($_G['gp_raterangenew'])) {
			foreach($_G['gp_raterangenew'] as $key => $rate) {
				if($key >= 1 && $key <= 8 && $rate['allowrate']) {
					$rate['min'] = intval($rate['min'] < -999 ? -999 : $rate['min']);
					$rate['max'] = intval($rate['max'] > 999 ? 999 : $rate['max']);
					$rate['mrpd'] = intval($rate['mrpd'] > 99999 ? 99999 : $rate['mrpd']);
					if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
						cpmsg('usergroups_edit_rate_invalid', '', 'error');
					} else {
						$_G['gp_raterangenew'][$key] = implode("\t", array($key, $rate['min'], $rate['max'], $rate['mrpd']));
					}
				} else {
					unset($_G['gp_raterangenew'][$key]);
				}
			}
		}

		if(in_array($group['groupid'], array(1))) {
			$_G['gp_allowvisitnew'] = 1;
		}

		!$multiset && $raterangenew = $_G['gp_raterangenew'] ? implode("\n", $_G['gp_raterangenew']) : '';
		$maxpricenew = $_G['gp_maxpricenew'] < 0 ? 0 : intval($_G['gp_maxpricenew']);
		$maxpostsperhournew = $_G['gp_maxpostsperhournew'] > 255 ? 255 : intval($_G['gp_maxpostsperhournew']);

		$extensionarray = array();
		foreach(explode(',', $_G['gp_attachextensionsnew']) as $extension) {
			if($extension = trim($extension)) {
				$extensionarray[] = $extension;
			}
		}
		$attachextensionsnew = implode(', ', $extensionarray);

		if($_G['gp_maxtradepricenew'] == $_G['gp_mintradepricenew'] || $_G['gp_maxtradepricenew'] < 0 || $_G['gp_mintradepricenew'] <= 0 || ($_G['gp_maxtradepricenew'] && $_G['gp_maxtradepricenew'] < $_G['gp_mintradepricenew'])) {
		} elseif(($_G['gp_maxrewardpricenew'] != 0 && $_G['gp_minrewardpricenew'] >= $_G['gp_maxrewardpricenew']) || $_G['gp_minrewardpricenew'] < 1 || $_G['gp_minrewardpricenew'] < 0 || $_G['gp_maxrewardpricenew'] < 0) {
			cpmsg('reward_credits_error', '', 'error');
		}

		if(!$multiset) {
			$exemptnewbin = '';
			for($i = 0;$i < 8;$i++) {
				$exemptnewbin = intval($_G['gp_exemptnew'][$i]).$exemptnewbin;
			}
			$exemptnew = bindec($exemptnewbin);
		}

		$tradesticknew = $_G['gp_tradesticknew'] > 0 ? intval($_G['gp_tradesticknew']) : 0;
		$maxinvitedaynew = $_G['gp_maxinvitedaynew'] > 0 ? intval($_G['gp_maxinvitedaynew']) : 10;
		$maxattachsizenew = $_G['gp_maxattachsizenew'] > 0 ? intval($_G['gp_maxattachsizenew'] * 1024) : 0;
		$maxsizeperdaynew = $_G['gp_maxsizeperdaynew'] > 0 ? intval($_G['gp_maxsizeperdaynew'] * 1024) : 0;
		$maxattachnumnew = $_G['gp_maxattachnumnew'] > 0 ? intval($_G['gp_maxattachnumnew']) : 0;
		$allowrecommendnew = $_G['gp_allowrecommendnew'] > 0 ? intval($_G['gp_allowrecommendnew']) : 0;
		$dataarr = array(
			'grouptitle' => $_G['gp_grouptitlenew'],
			'radminid' => $radminidnew,
			'allowvisit' => $_G['gp_allowvisitnew'],
			'allowsendpm' => $_G['gp_allowsendpmnew'],
			'maxinvitenum' => $_G['gp_maxinvitenumnew'],
			'maxinviteday' => $maxinvitedaynew,
			'allowinvite' => $_G['gp_allowinvitenew'],
			'allowmailinvite' => $_G['gp_allowmailinvitenew'],
			'inviteprice' => $_G['gp_invitepricenew']
		);
		if(!$multiset) {
			$dataarr['system'] = $systemnew;
			if($_FILES['iconnew']) {
				$data = array('extid' => "$_G[gp_id]");
				$iconnew = upload_icon_banner($data, $_FILES['iconnew'], 'usergroup_icon');
			} else {
				$iconnew = $_G['gp_iconnew'];
			}
			if($iconnew) {
				$dataarr['icon'] = $iconnew;
			}
			if($_G['gp_deleteicon']) {
				$valueparse = parse_url($group['icon']);
				if(!isset($valueparse['host'])) {
					@unlink($_G['setting']['attachurl'].'common/'.$group['icon']);
				}
				$dataarr['icon'] = '';
			}
		}
		DB::update('common_usergroup', $dataarr, array('groupid' => $_G['gp_id']));

		DB::update('forum_onlinelist', array(
			'title' => $_G['gp_grouptitlenew'],
		), "groupid='{$_G['gp_id']}'");

		$dataarr = array(
			'readaccess' => $_G['gp_readaccessnew'],
			'allowpost' => $_G['gp_allowpostnew'],
			'allowreply' => $_G['gp_allowreplynew'],
			'allowpostpoll' => $_G['gp_allowpostpollnew'],
			'allowpostreward' => $_G['gp_allowpostrewardnew'],
			'allowposttrade' => $_G['gp_allowposttradenew'],
			'allowpostactivity' => $_G['gp_allowpostactivitynew'],
			'allowdirectpost' => $_G['gp_allowdirectpostnew'],
			'allowgetattach' => $_G['gp_allowgetattachnew'],
			'allowpostattach' => $_G['gp_allowpostattachnew'],
			'allowvote' => $_G['gp_allowvotenew'],
			'allowmultigroups' => $_G['gp_allowmultigroupsnew'],
			'allowsearch' => bindec(intval($_G['gp_allowfulltextnew']).intval($_G['gp_allowsearchnew'][5]).intval($_G['gp_allowsearchnew'][4]).intval($_G['gp_allowsearchnew'][3]).intval($_G['gp_allowsearchnew'][2]).intval($_G['gp_allowsearchnew'][1])),
			'allowcstatus' => $_G['gp_allowcstatusnew'],
			'allowinvisible' => $_G['gp_allowinvisiblenew'],
			'allowtransfer' => $_G['gp_allowtransfernew'],
			'allowsetreadperm' => $_G['gp_allowsetreadpermnew'],
			'allowsetattachperm' => $_G['gp_allowsetattachpermnew'],
			'allowpostimage' => $_G['gp_allowpostimagenew'],
			'allowhidecode' => $_G['gp_allowhidecodenew'],
			'allowhtml' => $_G['gp_allowhtmlnew'],
			'allowcusbbcode' => $_G['gp_allowcusbbcodenew'],
			'allowanonymous' => $_G['gp_allowanonymousnew'],
			'allowsigbbcode' => $_G['gp_allowsigbbcodenew'],
			'allowsigimgcode' => $_G['gp_allowsigimgcodenew'],
			'allowmagics' => $_G['gp_allowmagicsnew'],
			'disableperiodctrl' => $_G['gp_disableperiodctrlnew'],
			'reasonpm' => $_G['gp_reasonpmnew'],
			'maxprice' => $maxpricenew,
			'maxsigsize' => $_G['gp_maxsigsizenew'],
			'maxspacesize' => $_G['gp_maxspacesizenew'],
			'maxattachsize' => $maxattachsizenew,
			'maxsizeperday' => $maxsizeperdaynew,
			'maxpostsperhour' => $maxpostsperhournew,
			'attachextensions' => $attachextensionsnew,
			'mintradeprice' => $_G['gp_mintradepricenew'],
			'maxtradeprice' => $_G['gp_maxtradepricenew'],
			'minrewardprice' => $_G['gp_minrewardpricenew'],
			'maxrewardprice' => $_G['gp_maxrewardpricenew'],
			'magicsdiscount' => $_G['gp_magicsdiscountnew'] >= 0 && $_G['gp_magicsdiscountnew'] < 10 ? $_G['gp_magicsdiscountnew'] : 0,
			'maxmagicsweight' => $_G['gp_maxmagicsweightnew'] >= 0 && $_G['gp_maxmagicsweightnew'] <= 60000 ? $_G['gp_maxmagicsweightnew'] : 1,
			'allowpostdebate' => $_G['gp_allowpostdebatenew'],
			'tradestick' => $tradesticknew,
			'maxattachnum' => $maxattachnumnew,
			'allowposturl' => $_G['gp_allowposturlnew'],
			'allowrecommend' => $allowrecommendnew,
			'allowpostrushreply' => $_G['gp_allowpostrushreplynew'],
			'maxfriendnum' => $_G['gp_maxfriendnumnew'],
			'seccode' => $_G['gp_seccodenew'],
			'domainlength' => $_G['gp_domainlengthnew'],
			'disablepostctrl' => $_G['gp_disablepostctrlnew'],
			'allowblog' => $_G['gp_allowblognew'],
			'allowdoing' => $_G['gp_allowdoingnew'],
			'allowupload' => $_G['gp_allowuploadnew'],
			'allowshare' => $_G['gp_allowsharenew'],
			'allowpoke' => $_G['gp_allowpokenew'],
			'allowfriend' => $_G['gp_allowfriendnew'],
			'allowclick' => $_G['gp_allowclicknew'],
			'allowcomment' => $_G['gp_allowcommentnew'],
			'allowmyop' => $_G['gp_allowmyopnew'],
			'allowcommentpost' => $_G['gp_allowcommentpostnew'],
			'videophotoignore' => $_G['gp_videophotoignorenew'],
			'allowviewvideophoto' => $_G['gp_allowviewvideophotonew'],
			'allowspacediyhtml' => $_G['gp_allowspacediyhtmlnew'],
			'allowspacediybbcode' => $_G['gp_allowspacediybbcodenew'],
			'allowspacediyimgcode' => $_G['gp_allowspacediyimgcodenew'],
			'allowstat' => $_G['gp_allowstatnew'],
			'allowpostarticle' => $_G['gp_allowpostarticlenew'],
			'allowbuildgroup' => $_G['gp_allowbuildgroupnew'],
			'edittimelimit' => intval($_G['gp_edittimelimit']),
			'allowcommentpost' => intval($_G['gp_allowcommentpostnew']),
			'allowcommentitem' => intval($_G['gp_allowcommentitemnew'])
		);
		if(!$multiset) {
			$dataarr = array_merge($dataarr, array(
				'raterange' => $raterangenew,
				'exempt' => $exemptnew,
			));
		}
		DB::update('common_usergroup_field', $dataarr, array('groupid' => $_G['gp_id']));

		if($_G['setting']['threadplugins']) {
			$allowthreadplugin = unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'"));
			$allowthreadplugin[$_G['gp_id']] = $_G['gp_allowthreadpluginnew'];
			$allowthreadpluginnew = addslashes(serialize($allowthreadplugin));
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('allowthreadplugin', '$allowthreadpluginnew')");
		}
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				unset($_G['gp_'.$key]);
			}
		}
		}

		updatecache(array('usergroups', 'onlinelist'));

		if($_G['gp_return'] == 'admin') {
			cpmsg('usergroups_edit_succeed', 'action=admingroup', 'succeed');
		} else {
			cpmsg('usergroups_edit_succeed', 'action=usergroups&operation=edit&'.($multiset ? 'multi='.implode(',', $_G['gp_multi']) : 'id='.$_G['gp_id']).'&anchor='.$_G['gp_anchor'], 'succeed');
		}
	}

}

function array_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = @array_keys($arr);
	list(, $first) = @each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
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