<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_cache.php 11590 2010-06-08 09:41:10Z monkey $
 */

function updatecache($cachename = '') {

	global $_G;

	include_once DISCUZ_ROOT.'./source/discuz_version.php';


	static $cachelist = array('setting','forums','stamps','usergroups','heats',
		'medals','magics','modreasons','stamptypeid','advs','ipctrl','faqs',
		'secqaa','censor','ipbanned','smilies_js','styles','announcements','onlinelist',
		'smileytypes','bbcodes','custominfo','groupicon','focus','bbcodes_display',
		'forumlinks','smilies','announcements_forum','globalstick','forumstick',
		'smileycodes','domainwhitelist','fields_required','fields_optional',
		'plugin','grouptype', 'profilesetting', 'userapp', 'creditrule', 'click', 'blockclass',
		'portalcategory','blogcategory','albumcategory', 'threadsorts', 'grouplevels',
		'admingroups','forumrecommend','userstats', 'updatediytemplate', 'myapp'
	);

	$updatelist = empty($cachename) ? $cachelist : (is_array($cachename) ? $cachename : array($cachename));
	foreach($updatelist as $value) {
		getcachearray($value);
	}

	if(in_array('usergroups', $updatelist)) {
		$allowthreadplugin = unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'"));

		$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." u
					LEFT JOIN ".DB::table('common_usergroup_field')." uf ON u.groupid=uf.groupid
					LEFT JOIN ".DB::table('common_admingroup')." a ON u.groupid=a.admingid");
		while($data = DB::fetch($query)) {
			$ratearray = array();
			if($data['raterange']) {
				foreach(explode("\n", $data['raterange']) as $rating) {
					$rating = explode("\t", $rating);
					$ratearray[$rating[0]] = array('min' => $rating[1], 'max' => $rating[2], 'mrpd' => $rating[3]);
				}
			}
			$data['raterange'] = $ratearray;
			$data['grouptitle'] = $data['color'] ? '<font color="'.$data['color'].'">'.$data['grouptitle'].'</font>' : $data['grouptitle'];
			$data['grouptype'] = $data['type'];
			$data['grouppublic'] = $data['system'] != 'private';
			$data['groupcreditshigher'] = $data['creditshigher'];
			$data['groupcreditslower'] = $data['creditslower'];
			$data['maxspacesize'] = intval($data['maxspacesize']) * 1024 * 1024;
			$data['allowthreadplugin'] = !empty($allowthreadplugin[$data['groupid']]) ? $allowthreadplugin[$data['groupid']] : array();
			unset($data['type'], $data['system'], $data['creditshigher'], $data['creditslower'], $data['groupavatar'], $data['admingid']);
			save_syscache('usergroup_'.$data['groupid'], $data);
		}
	}


	if(in_array('admingroups', $updatelist) || in_array('usergroups', $updatelist)) {
		$query = DB::query("SELECT * FROM ".DB::table('common_admingroup')."");
		while($data = DB::fetch($query)) {
			save_syscache('admingroup_'.$data['admingid'], $data);
		}
	}

	if(in_array('threadsorts', $updatelist)) {
		$sortlist = $templatedata = $stemplatedata = $ptemplatedata = $btemplatedata = $template = array();
		$query = DB::query("SELECT t.typeid AS sortid, tt.optionid, tt.title, tt.type, tt.unit, tt.rules, tt.identifier, tt.description, tv.required, tv.unchangeable, tv.search, tv.subjectshow, tt.expiration, tt.protect
			FROM ".DB::table('forum_threadtype')." t
			LEFT JOIN ".DB::table('forum_typevar')." tv ON t.typeid=tv.sortid
			LEFT JOIN ".DB::table('forum_typeoption')." tt ON tv.optionid=tt.optionid
			WHERE t.special='1' AND tv.available='1'
			ORDER BY tv.displayorder");
		while($data = DB::fetch($query)) {
			$data['rules'] = unserialize($data['rules']);
			$sortid = $data['sortid'];
			$optionid = $data['optionid'];
			$sortlist[$sortid][$optionid] = array(
				'title' => dhtmlspecialchars($data['title']),
				'type' => dhtmlspecialchars($data['type']),
				'unit' => dhtmlspecialchars($data['unit']),
				'identifier' => dhtmlspecialchars($data['identifier']),
				'description' => dhtmlspecialchars($data['description']),
				'required' => intval($data['required']),
				'unchangeable' => intval($data['unchangeable']),
				'search' => intval($data['search']),
				'subjectshow' => intval($data['subjectshow']),
				'expiration' => intval($data['expiration']),
				'protect' => unserialize($data['protect']),
				);

			if(in_array($data['type'], array('select', 'checkbox', 'radio'))) {
				if($data['rules']['choices']) {
					$choices = array();
					foreach(explode("\n", $data['rules']['choices']) as $item) {
						list($index, $choice) = explode('=', $item);
						$choices[trim($index)] = trim($choice);
					}
					$sortlist[$sortid][$optionid]['choices'] = $choices;
				} else {
					$sortlist[$sortid][$optionid]['choices'] = array();
				}
				if($data['type'] == 'select') {
					$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 108;
				}
			} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
				$sortlist[$sortid][$optionid]['maxlength'] = intval($data['rules']['maxlength']);
				if($data['type'] == 'textarea') {
					$sortlist[$sortid][$optionid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 5;
					$sortlist[$sortid][$optionid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 50;
				} else {
					$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
				}
				if(in_array($data['type'], array('text', 'textarea'))) {
					$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
				}
			} elseif($data['type'] == 'image') {
				$sortlist[$sortid][$optionid]['maxwidth'] = intval($data['rules']['maxwidth']);
				$sortlist[$sortid][$optionid]['maxheight'] = intval($data['rules']['maxheight']);
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			} elseif(in_array($data['type'], array('number', 'range'))) {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
				$sortlist[$sortid][$optionid]['maxnum'] = intval($data['rules']['maxnum']);
				$sortlist[$sortid][$optionid]['minnum'] = intval($data['rules']['minnum']);
				if($data['rules']['searchtxt']) {
					$sortlist[$sortid][$optionid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
				}
				if($data['type'] == 'number') {
					$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
				}
			}
		}
		$query = DB::query("SELECT typeid, description, template, stemplate, ptemplate, btemplate FROM ".DB::table('forum_threadtype')." WHERE special='1'");
		while($data = DB::fetch($query)) {
			$templatedata[$data['typeid']] = str_replace('"', '\"', $data['template']);
			$stemplatedata[$data['typeid']] = str_replace('"', '\"', $data['stemplate']);
			$ptemplatedata[$data['typeid']] = str_replace('"', '\"', $data['ptemplate']);
			$btemplatedata[$data['typeid']] = str_replace('"', '\"', $data['btemplate']);
		}

		$data['sortoption'] = $data['template'] = array();

		foreach($sortlist as $sortid => $option) {
			$template['viewthread'] =  $templatedata[$sortid];
			$template['subject'] = $stemplatedata[$sortid];
			$template['post'] = $ptemplatedata[$sortid];
			$template['block'] = $btemplatedata[$sortid];

			save_syscache('threadsort_option_'.$sortid, $option);
			save_syscache('threadsort_template_'.$sortid, $template);
		}

	}

	if(in_array('forumrecommend', $updatelist)) {
		$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type<>'group' AND status<>3");
		while($row = DB::fetch($query)) {
			require_once libfile('function/group');
			$squery = DB::query("SELECT f.fid, f.name, ff.icon FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE recommend='$row[fid]'");
			while($group = DB::fetch($squery)) {
				$group['icon'] = get_groupimg($group['icon'], 'icon');
				$data[$row['fid']][] = $group;
			}
		}
		save_syscache('forumrecommend', $data);
	}
	if(in_array('updatediytemplate', $updatelist)) {
		updatediytemplate();
	}
}

function setcssbackground(&$data, $code) {
	$codes = explode(' ', $data[$code]);
	$css = $codevalue = '';
	for($i = 0; $i < count($codes); $i++) {
		if($i < 2) {
			if($codes[$i] != '') {
				if($codes[$i]{0} == '#') {
					$css .= strtoupper($codes[$i]).' ';
					$codevalue = strtoupper($codes[$i]);
				} elseif(preg_match('/^http:\/\//i', $codes[$i])) {
					$css .= 'url(\"'.$codes[$i].'\") ';
				} else {
					$css .= 'url("'.$data['styleimgdir'].'/'.$codes[$i].'") ';
				}
			}
		} else {
			$css .= $codes[$i].' ';
		}
	}
	$data[$code] = $codevalue;
	$css = trim($css);
	return $css ? 'background: '.$css : '';
}

function updatesettings() {
	global $_G;
	loadcache('setting', true);
	save_syscache('setting', $_G['setting']);
}

function updateadvtype() {
	global $_G;
	$query = DB::query("SELECT type FROM ".DB::table('common_advertisement')." WHERE available>'0' AND starttime<='".TIMESTAMP."' ORDER BY displayorder");
	$advtype = array();
	while($row = DB::fetch($query)) {
		$advtype[$row['type']] = 1;
	}
	$_G['setting']['advtype'] = $advtype = array_keys($advtype);
	$advtype = addslashes(serialize($advtype));
	if(!DB::result_first("SELECT count(*) FROM ".DB::table('common_setting')." WHERE skey='advtype'")) {
		DB::query("INSERT INTO ".DB::table('common_setting')." SET skey='advtype', svalue='$advtype'");
	} else {
		DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$advtype' WHERE skey='advtype'");
	}
}

function writetocache($script, $cachenames, $cachedata = '', $prefix = 'cache_') {
	global $_G;
	if(is_array($cachenames) && !$cachedata) {
		foreach($cachenames as $name) {
			$cachedata .= getcachearray($name, $script);
		}
	}

	$dir = DISCUZ_ROOT.'./data/cache/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if($fp = @fopen("$dir$prefix$script.php", 'wb')) {
		fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!".
			"\n//Created: ".date("M j, Y, G:i").
			"\n//Identify: ".md5($prefix.$script.'.php'.$cachedata.$_G['config']['security']['authkey'])."\n\n$cachedata?>");
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}
}

function writetocsscache($data) {
	global $_G;
	$dir = DISCUZ_ROOT.'./template/default/common/';
	$dh = opendir($dir);
	$data['staticurl'] = STATICURL;
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'css') {
			$cssfile = DISCUZ_ROOT.'./'.$data['tpldir'].'/common/'.$entry;
			!file_exists($cssfile) && $cssfile = $dir.$entry;
			$cssdata = @implode('', file($cssfile));
			if(file_exists($cssfile = DISCUZ_ROOT.'./'.$data['tpldir'].'/common/extend_'.$entry)) {
				$cssdata .= @implode('', file($cssfile));
			}
			if(is_array($_G['setting']['plugins']['available']) && $_G['setting']['plugins']['available']) {
				foreach($_G['setting']['plugins']['available'] as $plugin) {
					if(file_exists($cssfile = DISCUZ_ROOT.'./source/plugin/'.$plugin.'/template/extend_'.$entry)) {
						$cssdata .= @implode('', file($cssfile));
					}
				}
			}
			$cssdata = preg_replace("/\{([A-Z0-9]+)\}/e", '\$data[strtolower(\'\1\')]', $cssdata);
			$cssdata = preg_replace("/<\?.+?\?>\s*/", '', $cssdata);
			$cssdata = !preg_match('/^http:\/\//i', $data['styleimgdir']) ? str_replace("url(\"$data[styleimgdir]", "url(\"../../$data[styleimgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^http:\/\//i', $data['styleimgdir']) ? str_replace("url($data[styleimgdir]", "url(../../$data[styleimgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^http:\/\//i', $data['imgdir']) ? str_replace("url(\"$data[imgdir]", "url(\"../../$data[imgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^http:\/\//i', $data['imgdir']) ? str_replace("url($data[imgdir]", "url(../../$data[imgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^http:\/\//i', $data['staticurl']) ? str_replace("url(\"$data[staticurl]", "url(\"../../$data[staticurl]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^http:\/\//i', $data['staticurl']) ? str_replace("url($data[staticurl]", "url(../../$data[staticurl]", $cssdata) : $cssdata;
			if($entry == 'module.css') {
				$cssdata = preg_replace('/\/\*\*\s*(.+?)\s*\*\*\//', '[\\1]', $cssdata);
			}
			$cssdata = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $cssdata);
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.$data['styleid'].'_'.$entry.'', 'w')) {
				fwrite($fp, $cssdata);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
		}
	}
}

function writetojscache() {
	$dir = DISCUZ_ROOT.'static/js/';
	$dh = opendir($dir);
	$remove = array(
		'/(^|\r|\n)\/\*.+?\*\/(\r|\n)/is',
		'/\/\/note.+?(\r|\n)/i',
		'/\/\/debug.+?(\r|\n)/i',
		'/(^|\r|\n)(\s|\t)+/',
		'/(\r|\n)/',
	);
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'js') {
			$jsfile = $dir.$entry;
			$fp = fopen($jsfile, 'r');
			$jsdata = @fread($fp, filesize($jsfile));
			fclose($fp);
			$jsdata = preg_replace($remove, '', $jsdata);
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/'.$entry, 'w')) {
				fwrite($fp, $jsdata);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
		}
	}
}

function getcachearray($cachename, $script = '') {
	global $_G;

	$cols = '*';
	$conditions = '';
	$timestamp = TIMESTAMP;
	switch($cachename) {
		case 'setting':
			$table = 'common_setting';
			$conditions = "WHERE skey NOT IN ('siteuniqueid', 'mastermobile', 'bbrules', 'bbrulestxt', 'closedreason', 'creditsnotify', 'backupdir', 'custombackup', 'jswizard', 'maxonlines', 'modreasons', 'newsletter', 'welcomemsg', 'welcomemsgtxt', 'postno', 'postnocustom', 'customauthorinfo', 'domainwhitelist', 'ipregctrl', 'ipverifywhite', 'fastsmiley')";
			break;
		case 'ipctrl':
			$table = 'common_setting';
			$conditions = "WHERE skey IN ('ipregctrl', 'ipverifywhite')";
			break;
		case 'custominfo':
			$table = 'common_setting';
			$conditions = "WHERE skey IN ('extcredits', 'customauthorinfo', 'postno', 'postnocustom')";
			break;
		case 'usergroups':
			$table = 'common_usergroup';
			$cols = 'u.groupid, u.type, u.grouptitle, u.creditshigher, u.creditslower, u.stars, u.color, u.icon, uf.readaccess, uf.allowcusbbcode, uf.allowgetattach';
			$conditions = "u LEFT JOIN ".DB::table('common_usergroup_field')." uf ON u.groupid=uf.groupid ORDER BY u.creditslower";
			break;
		case 'announcements':
			$table = 'forum_announcement';
			$cols = 'id, subject, type, starttime, endtime, displayorder, groups, message';
			$conditions = "WHERE starttime<='$timestamp' AND (endtime>='$timestamp' OR endtime='0') ORDER BY displayorder, starttime DESC, id DESC";
			break;
		case 'announcements_forum':
			$table = 'forum_announcement';
			$cols = 'a.id, a.author, m.uid AS authorid, a.subject, a.message, a.type, a.starttime, a.displayorder';
			$conditions = "a LEFT JOIN ".DB::table('common_member')." m ON m.username=a.author WHERE a.type!=2 AND a.groups = '' AND a.starttime<='$timestamp' ORDER BY a.displayorder, a.starttime DESC, a.id DESC LIMIT 1";
			break;
		case 'globalstick':
			$table = 'forum_forum';
			$cols = 'fid, type, fup';
			$conditions = "WHERE status='1' AND type IN ('forum', 'sub') ORDER BY type";
			break;
		case 'forumstick':
			$table = 'common_setting';
			$cols = 'svalue';
			$conditions = "WHERE skey='forumstickthreads'";
		case 'forums':
			$table = 'forum_forum f';
			$cols = 'f.fid, f.type, f.name, f.fup, f.simple, f.status, f.allowpostspecial, ff.viewperm, ff.formulaperm, ff.viewperm, ff.postperm, ff.replyperm, ff.getattachperm, ff.postattachperm, ff.extra, ff.commentitem, a.uid';
			$conditions = "LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid LEFT JOIN ".DB::table('forum_access')." a ON a.fid=f.fid AND a.allowview>'0' WHERE f.status<>'3' ORDER BY f.type, f.displayorder";
			break;
		case 'onlinelist':
			$table = 'forum_onlinelist';
			$conditions = "ORDER BY displayorder";
			break;
		case 'groupicon':
			$table = 'forum_onlinelist';
			$conditions = "ORDER BY displayorder";
			break;
		case 'forumlinks':
			$table = 'common_friendlink';
			$conditions = "ORDER BY displayorder";
			break;
		case 'bbcodes':
			$table = 'forum_bbcode';
			$conditions = "WHERE available>'0'";
			break;
		case 'bbcodes_display':
			$table = 'forum_bbcode';
			$cols = 'tag, icon, explanation, params, prompt';
			$conditions = "WHERE available='2' AND icon!='' ORDER BY displayorder";
			break;
		case 'smilies':
			$table = 'common_smiley s';
			$cols = 's.id, s.code, s.url, t.typeid';
			$conditions = "LEFT JOIN ".DB::table('forum_imagetype')." t ON t.typeid=s.typeid WHERE s.type='smiley' AND s.code<>'' AND t.available='1' ORDER BY LENGTH(s.code) DESC";
			break;
		case 'smileycodes':
			$table = 'forum_imagetype';
			$cols = 'typeid, directory';
			$conditions = "WHERE type='smiley' AND available='1' ORDER BY displayorder";
			break;
		case 'smileytypes':
			$table = 'forum_imagetype';
			$cols = 'typeid, name, directory';
			$conditions = "WHERE type='smiley' AND available='1' ORDER BY displayorder";
			break;
		case 'smilies_js':
			$table = 'forum_imagetype';
			$cols = 'typeid, name, directory';
			$conditions = "WHERE type='smiley' AND available='1' ORDER BY displayorder";
			break;
		case 'stamps':
			$table = 'common_smiley';
			$cols = 'id, url, displayorder';
			$conditions = "WHERE type='stamp' ORDER BY displayorder";
			break;
		case 'stamptypeid':
			$table = 'common_smiley';
			$cols = 'displayorder, typeid';
			$conditions = "WHERE type='stamp' AND typeid>'0'";
			break;
		case 'fields_required':
			$table = 'common_member_profile_setting';
			$conditions = "WHERE available='1' AND required='1' ORDER BY displayorder";
			break;
		case 'fields_optional':
			$table = 'common_member_profile_setting';
			$conditions = "WHERE available='1' AND required='0' ORDER BY displayorder";
			break;
		case 'ipbanned':
			DB::query("DELETE FROM ".DB::table('common_banned')." WHERE expiration<'$timestamp'");
			$table = 'common_banned';
			$cols = 'ip1, ip2, ip3, ip4, expiration';
			break;
		case 'censor':
			$table = 'common_word';
			$cols = 'find, replacement, extra';
			break;
		case 'medals':
			$table = 'forum_medal';
			$cols = 'medalid, name, image';
			$conditions = "WHERE available='1'";
			break;
		case 'magics':
			$table = 'common_magic';
			$conditions = "WHERE available='1'";
			break;

		case 'modreasons':
			$table = 'common_setting';
			$cols = 'svalue';
			$conditions = "WHERE skey='modreasons'";
			break;
		case 'faqs':
			$table = 'forum_faq';
			$cols = 'fpid, id, identifier, keyword';
			$conditions = "WHERE identifier!='' AND keyword!=''";
			break;
		case 'domainwhitelist':
			$table = 'common_setting';
			$cols = 'svalue';
			$conditions = "WHERE skey='domainwhitelist'";
			break;
		case 'plugin':
			$table = 'common_plugin';
			$conditions = "WHERE available='1'";
			break;
		case 'grouptype':
			$table = 'forum_forum f';
			$cols = 'f.fid, f.fup, f.name, f.forumcolumns, ff.membernum, ff.groupnum, ff.extra';
			$conditions = "LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.type IN('group', 'forum') AND f.status='3' ORDER BY f.type, f.displayorder";
			break;
		case 'profilesetting':
			$table = 'common_member_profile_setting';
			$conditions = "WHERE available='1' ORDER BY displayorder DESC";
			break;
		case 'myapp':
		case 'userapp':
			$table = 'common_myapp';
			$flag = $cachename == 'myapp' ? 'flag!=\'-1\'' : 'flag=\'1\'';
			$conditions = "WHERE $flag ORDER BY displayorder";
			break;
		case 'creditrule':
			$table = 'common_credit_rule';
			break;
		case 'click':
			$table = 'home_click';
			break;
		case 'advs':
			$table = 'common_advertisement';
			$conditions = "WHERE available>'0' AND starttime<='$timestamp' ORDER BY displayorder";
			break;
		case 'blockclass':
			$table = 'common_block_style';
			break;
		case 'portalcategory':
			$table = 'portal_category';
			break;
		case 'blogcategory':
			$table = 'home_blog_category';
			break;
		case 'albumcategory':
			$table = 'home_album_category';
			break;
		case 'grouplevels':
			$table = 'forum_grouplevel';
			break;
	}

	$data = array();
	if(!in_array($cachename, array('focus', 'secqaa', 'heats', 'styles', 'userstats'))) {
		if(empty($table) || empty($cols) || ($cachename == 'userapp' && !$_G['setting']['my_app_status'])) return '';
		$query = DB::query("SELECT $cols FROM ".DB::table($table)." $conditions");
	}

	switch($cachename) {
		case 'setting':
			while($setting = DB::fetch($query)) {
				if($setting['skey'] == 'extcredits') {
					if(is_array($setting['svalue'] = unserialize($setting['svalue']))) {
						foreach($setting['svalue'] as $key => $value) {
							if($value['available']) {
								unset($setting['svalue'][$key]['available']);
							} else {
								unset($setting['svalue'][$key]);
							}
						}
					}
				} elseif($setting['skey'] == 'creditsformula') {
					if(!checkformulacredits($setting['svalue'])) {
						$setting['svalue'] = '$member[\'extcredits1\']';
					} else {
						$setting['svalue'] = preg_replace("/(friends|doings|blogs|albums|polls|sharings|digestposts|posts|threads|extcredits[1-8])/", "\$member['\\1']", $setting['svalue']);
					}
				} elseif($setting['skey'] == 'maxsmilies') {
					$setting['svalue'] = $setting['svalue'] <= 0 ? -1 : $setting['svalue'];
				} elseif($setting['skey'] == 'threadsticky') {
					$setting['svalue'] = explode(',', $setting['svalue']);
				} elseif($setting['skey'] == 'attachdir') {
					$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
					$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DISCUZ_ROOT.$setting['svalue'] : $setting['svalue']);
					$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
				} elseif($setting['skey'] == 'attachurl') {
					$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
				} elseif($setting['skey'] == 'onlinehold') {
					$setting['svalue'] = $setting['svalue'] * 60;
				} elseif(in_array($setting['skey'], array('memory', 'search', 'creditspolicy', 'ftp', 'secqaa', 'ec_credit', 'qihoo', 'spacedata', 'infosidestatus', 'uc', 'indexhot', 'outextcredits', 'relatedtag', 'sitemessage', 'uchome', 'heatthread', 'recommendthread', 'disallowfloat', 'allowviewuserthread', 'advtype', 'click', 'rewritestatus', 'rewriterule', 'privacy', 'focus', 'forumkeys', 'ext_portalmanager'))) {
					$setting['svalue'] = @unserialize($setting['svalue']);
					if($setting['skey'] == 'search') {
						foreach($setting['svalue'] as $key => $val) {
							foreach($val as $k => $v) {
								$setting['svalue'][$key][$k] = max(0, intval($v));
							}
						}
					}
					if($setting['skey'] == 'ftp') {
						$setting['svalue']['attachurl'] .= substr($setting['svalue']['attachurl'], -1, 1) != '/' ? '/' : '';
					}
				}
				$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
			}

			if($data['search']) {
				$searchstatus = 0;
				foreach($data['search'] as $item) {
					if($item['status']) {
						$searchstatus = 1;
					}
				}
				if(!$searchstatus) {
					$data['search'] = array();
				}
			}

			$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE action IN ('promotion_visit', 'promotion_register')");
			while($creditrule = DB::fetch($query)) {
				$ruleexist = false;
				for($i = 1; $i <= 8; $i++) {
					if($creditrule['extcredits'.$i]) {
						$ruleexist = true;
					}
				}
				$data['creditspolicy'][$creditrule['action']] = $ruleexist;
			}

			if($data['heatthread']['iconlevels']) {
				$data['heatthread']['iconlevels'] = explode(',', $data['heatthread']['iconlevels']);
				arsort($data['heatthread']['iconlevels']);
			} else {
				$data['heatthread']['iconlevels'] = array();
			}
			if($data['recommendthread']['status']) {
				if($data['recommendthread']['iconlevels']) {
					$data['recommendthread']['iconlevels'] = explode(',', $data['recommendthread']['iconlevels']);
					arsort($data['recommendthread']['iconlevels']);
				} else {
					$data['recommendthread']['iconlevels'] = array();
				}
			} else {
				$data['recommendthread'] = array('allow' => 0);
			}

			if($data['ftp']['allowedexts']) {
				$data['ftp']['allowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['allowedexts']);
				$data['ftp']['allowedexts'] = explode("\n", strtolower($data['ftp']['allowedexts']));
				array_walk($data['ftp']['allowedexts'], 'trim');
			}

			if($data['ftp']['disallowedexts']) {
				$data['ftp']['disallowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['disallowedexts']);
				$data['ftp']['disallowedexts'] = explode("\n", strtolower($data['ftp']['disallowedexts']));
				array_walk($data['ftp']['disallowedexts'], 'trim');
			}
			if(!empty($data['forumkeys'])) {
				$data['forumfids'] = array_flip($data['forumkeys']);
			} else {
				$data['forumfids'] = array();
			}

			$data['commentitem'] = explode("\t", $data['commentitem']);
			$data['allowviewuserthread'] = $data['allowviewuserthread']['allow'] && is_array($data['allowviewuserthread']['fids']) && $data['allowviewuserthread']['fids'] ? dimplode($data['allowviewuserthread']['fids']) : '';
			$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
			$data['sitemessage']['register'] = !empty($data['sitemessage']['register']) ? explode("\n", $data['sitemessage']['register']) : '';
			$data['sitemessage']['login'] = !empty($data['sitemessage']['login']) ? explode("\n", $data['sitemessage']['login']) : '';
			$data['sitemessage']['newthread'] = !empty($data['sitemessage']['newthread']) ? explode("\n", $data['sitemessage']['newthread']) : '';
			$data['sitemessage']['reply'] = !empty($data['sitemessage']['reply']) ? explode("\n", $data['sitemessage']['reply']) : '';
			$_G['setting']['version'] = $data['version'] = DISCUZ_VERSION;
			$data['cachethreadon'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE status='1' AND threadcaches>0") ? 1 : 0;
			$data['cronnextrun'] = DB::result_first("SELECT nextrun FROM ".DB::table('common_cron')." WHERE available>'0' AND nextrun>'0' ORDER BY nextrun LIMIT 1");
			$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

			$data['ftp']['connid'] = 0;
			if(!$data['imagelib']) {
				unset($data['imageimpath']);
			}

			if(is_array($data['relatedtag']['order'])) {
				asort($data['relatedtag']['order']);
				$relatedtag = array();
				foreach($data['relatedtag']['order'] AS $k => $v) {
					$relatedtag['status'][$k] = $data['relatedtag']['status'][$k];
					$relatedtag['name'][$k] = $data['relatedtag']['name'][$k];
					$relatedtag['limit'][$k] = $data['relatedtag']['limit'][$k];
					$relatedtag['template'][$k] = $data['relatedtag']['template'][$k];
				}
				$data['relatedtag'] = $relatedtag;

				foreach((array)$data['relatedtag']['status'] AS $appid => $status) {
					if(!$status) {
						unset($data['relatedtag']['limit'][$appid]);
					}
				}
				unset($data['relatedtag']['status'], $data['relatedtag']['order'], $relatedtag);
			}

			$data['seccodedata'] = $data['seccodedata'] ? unserialize($data['seccodedata']) : array();
			if($data['seccodedata']['type'] == 2) {
				if(extension_loaded('ming')) {
					unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
						$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
						$data['seccodedata']['color'], $data['seccodedata']['size'],
						$data['seccodedata']['animator']);
				} else {
					$data['seccodedata']['animator'] = 0;
				}
			} elseif($data['seccodedata']['type'] == 99) {
				$data['seccodedata']['width'] = 32;
				$data['seccodedata']['height'] = 24;
			}

			if($data['watermarktype'] == 'text' && $data['watermarktext']) {
				$data['watermarktext'] = unserialize($data['watermarktext']);
				if($data['watermarktext']['text'] && strtoupper(CHARSET) != 'UTF-8') {
					require_once libfile('class/chinese');
					$c = new Chinese(CHARSET, 'utf8');
					$data['watermarktext']['text'] = $c->Convert($data['watermarktext']['text']);
				}
				$data['watermarktext']['text'] = bin2hex($data['watermarktext']['text']);
				$data['watermarktext']['fontpath'] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'];
				$data['watermarktext']['color'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['color']);
				$data['watermarktext']['shadowcolor'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['shadowcolor']);
			} else {
				$data['watermarktext'] = array();
			}

			$data['styles'] = $data['styleicons'] = array();
			$query = DB::query("SELECT styleid, name FROM ".DB::table('common_style')." WHERE available='1'");
			while($style = DB::fetch($query)) {
				$data['styles'][$style['styleid']] = dhtmlspecialchars($style['name']);

				$querysv = DB::query("SELECT * FROM ".DB::table('common_stylevar')." WHERE styleid='$style[styleid]'");
				$stylevar = array();
				while($var = DB::fetch($querysv)) {
					$stylevar[$var['variable']] = $var['substitute'];
				}
				$stylevar['imgdir'] = $stylevar['imgdir'] ? $stylevar['imgdir'] : STATICURL.'image/common';
				$stylevar['styleimgdir'] = $stylevar['styleimgdir'] ? $stylevar['styleimgdir'] : $stylevar['imgdir'];
				$data['styleicons'][$style['styleid']] = setcssbackground($stylevar, 'iconbgcolor');
			}

			$data['inviteconfig'] = unserialize($data['inviteconfig']);

			$outextcreditsrcs = $outextcredits = array();
			foreach((array)$data['outextcredits'] as $value) {
				$outextcreditsrcs[$value['creditsrc']] = $value['creditsrc'];
				$key = $value['appiddesc'].'|'.$value['creditdesc'];
				if(!isset($outextcredits[$key])) {
					$outextcredits[$key] = array('title' => $value['title'], 'unit' => $value['unit']);
				}
				$outextcredits[$key]['ratiosrc'][$value['creditsrc']] = $value['ratiosrc'];
				$outextcredits[$key]['ratiodesc'][$value['creditsrc']] = $value['ratiodesc'];
				$outextcredits[$key]['creditsrc'][$value['creditsrc']] = $value['ratio'];
			}
			$data['outextcredits'] = $outextcredits;

			$exchcredits = array();
			$allowexchangein = $allowexchangeout = FALSE;
			foreach((array)$data['extcredits'] as $id => $credit) {
				$data['extcredits'][$id]['img'] = $credit['img'] ? '<img style="vertical-align:middle" src="'.$credit['img'].'" />' : '';
				if(!empty($credit['ratio'])) {
					$exchcredits[$id] = $credit;
					$credit['allowexchangein'] && $allowexchangein = TRUE;
					$credit['allowexchangeout'] && $allowexchangeout = TRUE;
				}
				$data['creditnotice'] && $data['creditnames'][] = str_replace("'", "\'", htmlspecialchars($id.'|'.$credit['title'].'|'.$credit['unit']));
			}
			$data['creditnames'] = $data['creditnotice'] ? @implode(',', $data['creditnames']) : '';

			$creditstranssi = explode(',', $data['creditstrans']);
			$data['creditstrans'] = $creditstranssi[0];
			unset($creditstranssi[0]);
			$data['creditstransextra'] = $creditstranssi;
			for($i = 1;$i < 6;$i++) {
				$data['creditstransextra'][$i] = !$data['creditstransextra'][$i] ? $data['creditstrans'] : $data['creditstransextra'][$i];
			}
			$data['exchangestatus'] = $allowexchangein && $allowexchangeout;
			$data['transferstatus'] = isset($data['extcredits'][$data['creditstrans']]);

			list($data['zoomstatus'], $data['imagemaxwidth']) = explode("\t", $data['zoomstatus']);
			$data['imagemaxwidth'] = substr(trim($data['imagemaxwidth']), -1, 1) != '%' && $data['imagemaxwidth'] <= 1920 ? $data['imagemaxwidth'] : '';

			require_once DISCUZ_ROOT.'./config/config_ucenter.php';
			$data['ucenterurl'] = UC_API;

			$query = DB::query("SELECT identifier, name FROM ".DB::table('common_magic')." WHERE available='1'");
			while($magic = DB::fetch($query)) {
				$data['magics'][$magic['identifier']] = $magic['name'];
			}

			$data['plugins'] = $data['pluginlinks'] = $data['hookscript'] = $data['threadplugins'] = $data['specialicon'] = $adminmenu = $scriptlang = array();
			$query = DB::query("SELECT pluginid, available, name, identifier, directory, datatables, modules FROM ".DB::table('common_plugin')."");
			$data['plugins']['available'] = array();
            $data['plugins']['available_plugins'] = array();
			while($plugin = DB::fetch($query)) {
				$addadminmenu = $plugin['available'] && DB::result_first("SELECT count(*) FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]'") ? TRUE : FALSE;
				$plugin['modules'] = unserialize($plugin['modules']);
				if($plugin['available']) {
					$data['plugins']['available'][] = $plugin['identifier'];
                    //add by join
                    $data['plugins']['available_plugins'][$plugin['identifier']] = $plugin;
					if(!empty($plugin['modules']['extra']['langexists'])) {
						@include DISCUZ_ROOT.'./data/plugindata/'.$plugin['identifier'].'.lang.php';
					}
				}
				$plugin['directory'] = $plugin['directory'].((!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '');
				if(is_array($plugin['modules'])) {
					unset($plugin['modules']['extra']);
					foreach($plugin['modules'] as $k => $module) {
						if($plugin['available'] && isset($module['name'])) {
							$k = '';
							switch($module['type']) {
								case 1:
								case 2:
									$k = 'links';
									$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
									list($module['menu'], $module['title']) = explode('/', $module['menu']);
									if(!DB::result_first("SELECT count(*) FROM ".DB::table('common_nav')." WHERE type='3' AND identifier='$plugin[identifier]' AND url='$module[url]'")) {
										DB::insert('common_nav', array(
											'name' => $module['menu'],
											'title' => $module['title'],
											'url' => $module['url'],
											'type' => '3',
											'identifier' => $plugin['identifier'],
										));
									}
									break;
 								case 5:
									$k = 'jsmenu';
									$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
									list($module['menu'], $module['title']) = explode('/', $module['menu']);
									$module['menu'] = $module['type'] == 1 ? ($module['menu'].($module['title'] ? '<span>'.$module['title'].'</span>' : '')) : $module['menu'];
									$data['plugins'][$k][] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'url' => "<a id=\"mn_plink_$module[name]\" href=\"$module[url]\">$module[menu]</a>");
									break;
								case 14:
									$k = 'faq';
								case 15:
									$k = !$k ? 'modcp_base' : $k;
								case 16:
									$k = !$k ? 'modcp_tools' : $k;
								case 7:
									$k = !$k ? 'spacecp' : $k;
								case 17:
									$k = !$k ? 'spacecp_profile' : $k;
								case 19:
									$k = !$k ? 'spacecp_credit' : $k;
									$data['plugins'][$k][$plugin['identifier'].':'.$module['name']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory']);
									break;
								case 3:
									$addadminmenu = TRUE;
									break;
								case 4:
									$data['plugins']['include'][$plugin['identifier']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'script' => $plugin['directory'].$module['name']);
									break;
								case 11:
									$script = $plugin['directory'].$module['name'];
									@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
									$classes = get_declared_classes();
									$classnames = array();
									$cnlen = strlen('plugin_'.$plugin['identifier']);
									foreach($classes as $classname) {
										if(substr($classname, 0, $cnlen) == 'plugin_'.$plugin['identifier']) {
											$hscript = substr($classname, $cnlen + 1);
											$classnames[$hscript ? $hscript : 'global'] = $classname;
										}
									}
									foreach($classnames as $hscript => $classname) {
										$hookmethods = get_class_methods($classname);
										foreach($hookmethods as $funcname) {
											$v = explode('_', $funcname);
											$curscript = $v[0];
											if(!$curscript || $classname == $funcname) {
												continue;
											}
											if(!@in_array($script, $data['hookscript'][$hscript][$curscript]['module'])) {
												$data['hookscript'][$hscript][$curscript]['module'][$plugin['identifier']] = $script;
												$data['hookscript'][$hscript][$curscript]['adminid'][$plugin['identifier']] = $module['adminid'];
											}
											if(preg_match('/\_output$/', $funcname)) {
												$varname = preg_replace('/\_output$/', '', $funcname);
												$data['hookscript'][$hscript][$curscript]['outputfuncs'][$varname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
											} else {
												$data['hookscript'][$hscript][$curscript]['funcs'][$funcname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
											}
										}
									}
									break;
								case 12:
									$script = $plugin['directory'].$module['name'];
									@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
									if(class_exists('threadplugin_'.$plugin['identifier'])) {
										$classname = 'threadplugin_'.$plugin['identifier'];
										$hookclass = new $classname;
										if($hookclass->name) {
											$data['threadplugins'][$plugin['identifier']]['name'] = $hookclass->name;
											$data['threadplugins'][$plugin['identifier']]['module'] = $script;
										}
									}
									break;
                                    //add by join
                                    default:
                                        $result = get_join_add_plugin_name($plugin, $module);
										if($result["name"]=='appapi'){
											$pluginnamearray=array_keys($result["data"]);
											$data['plugins'][$result["name"]][$pluginnamearray[0]]= $result["data"];
										}else{
                                        	$data['plugins'][$result["name"]]= $result["data"];
										}
							}
						}
					}
				}
				if($addadminmenu) {
					$adminmenu[] = array('url' => "plugins&operation=config&do=$plugin[pluginid]", 'action' => 'plugins_config_'.$plugin['pluginid'], 'name' => $plugin['name']);
				}
			}
			$_G['setting']['plugins']['available'] = $data['plugins']['available'];
            $_G['setting']['plugins']['available_plugins'] = $data['plugins']['available_plugins'];
            $_G['setting']['plugins']['appapi']= $data['plugins']['appapi'];
			$file = DISCUZ_ROOT.'./data/plugindata/lang_plugin.php';
			if($fp = @fopen($file, 'wb')) {
				fwrite($fp, "<?php\n".getcachevars(array('lang' => $scriptlang)).'?>');
				fclose($fp);
			}
			$data['pluginhooks'] = array();

			foreach($data['hookscript'] as $hscript => $hookscript) {
				foreach($hookscript as $curscript => $scriptdata) {
					if(is_array($scriptdata['funcs'])) {
						foreach($scriptdata['funcs'] as $funcname => $funcs) {
							usort($funcs, 'pluginmodulecmp');
							$tmp = array();
							foreach($funcs as $k => $v) {
								$tmp[$k] = $v['func'];
							}
							$data['hookscript'][$hscript][$curscript]['funcs'][$funcname] = $tmp;
						}
					}
					if(is_array($scriptdata['outputfuncs'])) {
						foreach($scriptdata['outputfuncs'] as $funcname => $funcs) {
							usort($funcs, 'pluginmodulecmp');
							$tmp = array();
							foreach($funcs as $k => $v) {
								$tmp[$k] = $v['func'];
							}
							$data['hookscript'][$hscript][$curscript]['outputfuncs'][$funcname] = $tmp;
						}
					}
				}
			}

			$data['tradeopen'] = DB::result_first("SELECT count(*) FROM ".DB::table('common_usergroup_field')." WHERE allowposttrade='1'") ? 1 : 0;

			$focus = array();
			if($data['focus']['data']) {
				foreach($data['focus']['data'] as $k => $v) {
					if($v['position']) {
						foreach($v['position'] as $position) {
							$focus[$position][$k] = $k;
						}
					}
				}
			}
			$data['focus'] = $focus;

			foreach(array('links', 'spacecp', 'include', 'jsmenu', 'space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_member', 'modcp_forum') as $pluginkey) {
				if(is_array($data['plugins'][$pluginkey])) {
					if(in_array($pluginkey, array('space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_tools'))) {
						uasort($data['plugins'][$pluginkey], 'pluginmodulecmp');
					} else {
						usort($data['plugins'][$pluginkey], 'pluginmodulecmp');
					}
					foreach($data['plugins'][$pluginkey] as $key => $module) {
						unset($data['plugins'][$pluginkey][$key]['displayorder']);
					}
				}
			}
			writetocache('adminmenu', '', getcachevars(array('adminmenu' => $adminmenu)));

			$data['navs'] = $data['snavs'] = $data['subnavs'] = $data['navmns'] = array();
			$mngsid = 1;
			$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE (available='1' OR type='0') AND parentid='0' ORDER BY displayorder");
			while($nav = DB::fetch($query)) {
				//added by fumz,2010-11-30 15:48:35
				//对专家用户屏蔽旧版网大链接
				//begin
				if($nav['name']=='旧版网大'&&$discuz->var['cookie']['expert_Is']){
					continue;
				}
				//end
				if($nav['id'] == 3 && !$data['groupstatus']) {
					continue;
				}
				if($nav['type'] == 3) {
					if(!in_array($nav['identifier'], $data['plugins']['available'])) {
						continue;
					}
				}
				if($nav['id'] == 5 && !$data['my_app_status']) {
					continue;
				}
				$nav['style'] = parsehighlight($nav['highlight']);
				$nav['url'] = $_G['config']['app']['domain'][$nav['identifier']] ? 'http://'.$_G['config']['app']['domain'][$nav['identifier']] : $nav['url'];
				$data['navs'][$nav['id']]['navname'] = $nav['name'];
				$data['navs'][$nav['id']]['filename'] = $nav['url'];
				$data['navs'][$nav['id']]['available'] = $nav['available'];
				$nav['name'] = $nav['name'].($nav['title'] ? '<span>'.$nav['title'].'</span>' : '');
				if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_nav')." WHERE parentid='$nav[id]' AND available='1' AND type IN ('0','1','3')")) {
					$id = random(6);
					$subquery = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE available='1' AND parentid='$nav[id]' ORDER BY displayorder");
					$subnavs = '';
					while($subnav = DB::fetch($subquery)) {
						$href = "<a href=\"$subnav[url]\" hidefocus=\"true\" ".($subnav['title'] ? "title=\"$subnav[title]\" " : '').($subnav['target'] == 1 ? "target=\"_blank\" " : '').parsehighlight($subnav['highlight']).">$subnav[name]</a>";
						$subnavs .= '<li>'.$href.'</li>';
					}
					if($subnavs) {
						$subnavs = '<ul class="p_pop h_pop" id="'.$id.'_menu" style="display: none">'.$subnavs.'</ul>';
						$data['subnavs'][] = $subnavs;
					}
					$data['navs'][$nav['id']]['nav'] = "<li class=\"menu_".$nav['id']."\" id=\"$id\" onmouseover=\"showMenu({'ctrlid':this.id, 'pos':'439'})\"><a href=\"$nav[url]\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')." class=\"dropmenu\"$nav[style]>$nav[name]</a></li>";
				} else {
					$subquery = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE available='1' AND parentid='$nav[id]' ORDER BY displayorder");
					$subnavs = '';
					while($subnav = DB::fetch($subquery)) {
						$href = "<a href=\"$subnav[url]\" hidefocus=\"true\" ".($subnav['title'] ? "title=\"$subnav[title]\" " : '').($subnav['target'] == 1 ? "target=\"_blank\" " : '').parsehighlight($subnav['highlight']).">$subnav[name]</a>";
						list($script) = explode('.', basename($nav['url']));
						$data['snavs'][$script][] = $href;
					}
					if($nav['id'] == '6') {
						$data['navs'][$nav['id']]['nav'] = !empty($data['plugins']['jsmenu']) ? "<li class=\"menu_3\" id=\"pluginnav\" onmouseover=\"showMenu({'ctrlid':this.id,'menuid':'plugin_menu'})\"><a href=\"javascript:;\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')."class=\"dropmenu\"$nav[style]>$nav[name]</a></li>" : '';
					} else {
						if($nav['id'] == '1') {
							$nav['url'] = 'portal.php';
						}
						list($mnid) = explode('.', basename($nav['url']));
						$purl = parse_url($nav['url']);
						$getvars = array();
						if($purl['query']) {
							parse_str($purl['query'], $getvars);
							$mnidnew = $mnid.'_'.$mngsid;
							$data['navmngs'][$mnid][] = array($getvars, $mnidnew);
							$mnid = $mnidnew;
							$mngsid++;
						}
						$data['navmns'][] = $mnid;
						$data['navs'][$nav['id']]['nav'] = "<li class=\"menu_".$nav['id']."\"><a href=\"$nav[url]\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')."id=\"mn_$mnid\"$nav[style]>$nav[name]</a></li>";
					}
				}
				$data['navs'][$nav['id']]['level'] = $nav['level'];
			}

			require_once DISCUZ_ROOT.'./uc_client/client.php';
			$ucapparray = uc_app_ls();
			$data['allowsynlogin'] = isset($ucapparray[UC_APPID]['synlogin']) ? $ucapparray[UC_APPID]['synlogin'] : 1;
			$appnamearray = array('UCHOME','XSPACE','DISCUZ','SUPESITE','SUPEV','ECSHOP','ECMALL');
			$data['ucapp'] = $data['ucappopen'] = array();
			$data['uchomeurl'] = '';
			$appsynlogins = 0;
			foreach($ucapparray as $apparray) {
				if($apparray['appid'] != UC_APPID) {
					if(!empty($apparray['synlogin'])) {
						$appsynlogins = 1;
					}
					if($data['uc']['navlist'][$apparray['appid']] && $data['uc']['navopen']) {
						$data['ucapp'][$apparray['appid']]['name'] = $apparray['name'];
						$data['ucapp'][$apparray['appid']]['url'] = $apparray['url'];
					}
				}
				if(!empty($apparray['viewprourl'])) {
					$data['ucapp'][$apparray['appid']]['viewprourl'] = $apparray['url'].$apparray['viewprourl'];
				}
				foreach($appnamearray as $name) {
					if($apparray['type'] == $name && $apparray['appid'] != UC_APPID) {
						$data['ucappopen'][$name] = 1;
						if($name == 'UCHOME') {
							$data['uchomeurl'] = $apparray['url'];
						} elseif($name == 'XSPACE') {
							$data['xspaceurl'] = $apparray['url'];
						}
					}
				}
			}
			$data['allowsynlogin'] = $data['allowsynlogin'] && $appsynlogins ? 1 : 0;
			$data['homeshow'] = $data['uchomeurl'] && $data['uchome']['homeshow'] ? $data['uchome']['homeshow'] : '0';
			$data['medalstatus'] = intval(DB::result_first("SELECT count(*) FROM ".DB::table('forum_medal')." WHERE available='1'"));

			unset($data['allowthreadplugin']);
			if($data['jspath'] == 'data/cache/') {
				writetojscache();
			} elseif(!$data['jspath']) {
				$data['jspath'] = 'static/js/';
			}
			break;
		case 'styles':
			$stylevars = $styledata = array();
			$defaultstyleid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey = 'styleid'");
			list(, $imagemaxwidth) = explode("\t", DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey = 'zoomstatus'"));
			$imagemaxwidth = $imagemaxwidth ? $imagemaxwidth : 600;
			$imagemaxwidthint = intval($imagemaxwidth);
			$query = DB::query("SELECT sv.* FROM ".DB::table('common_stylevar')." sv LEFT JOIN ".DB::table('common_style')." s ON s.styleid = sv.styleid AND (s.available=1 OR s.styleid='$defaultstyleid')");
			while($var = DB::fetch($query)) {
				$stylevars[$var['styleid']][$var['variable']] = $var['substitute'];
			}
			$query = DB::query("SELECT s.*, t.directory AS tpldir FROM ".DB::table('common_style')." s LEFT JOIN ".DB::table('common_template')." t ON s.templateid=t.templateid WHERE s.available=1 OR s.styleid='$defaultstyleid'");
			while($data = DB::fetch($query)) {
				$data = array_merge($data, $stylevars[$data['styleid']]);
				$datanew = array();
				$data['imgdir'] = $data['imgdir'] ? $data['imgdir'] : STATICURL.'image/common';
				$data['styleimgdir'] = $data['styleimgdir'] ? $data['styleimgdir'] : $data['imgdir'];
				foreach($data as $k => $v) {
					if(substr($k, -7, 7) == 'bgcolor') {
						$newkey = substr($k, 0, -7).'bgcode';
						$datanew[$newkey] = setcssbackground($data, $k);
					}
				}
				$data = array_merge($data, $datanew);
				if(strstr($data['boardimg'], ',')) {
					$flash = explode(",", $data['boardimg']);
					$flash[0] = trim($flash[0]);
					$flash[0] = preg_match('/^http:\/\//i', $flash[0]) ? $flash[0] : $data['styleimgdir'].'/'.$flash[0];
					$data['boardlogo'] = "<embed src=\"".$flash[0]."\" width=\"".trim($flash[1])."\" height=\"".trim($flash[2])."\" type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed>";
				} else {
					$data['boardimg'] = preg_match('/^http:\/\//i', $data['boardimg']) ? $data['boardimg'] : $data['styleimgdir'].'/'.$data['boardimg'];
					$data['boardlogo'] = "<img src=\"$data[boardimg]\" alt=\"".$_G['setting']['bbname']."\" border=\"0\" />";
				}
				$data['bold'] = $data['nobold'] ? 'normal' : 'bold';
				$contentwidthint = intval($data['contentwidth']);
				$contentwidthint = $contentwidthint ? $contentwidthint : 600;
				if(substr(trim($data['contentwidth']), -1, 1) != '%') {
					if(substr(trim($_G['setting']['imagemaxwidth']), -1, 1) != '%') {
						$data['imagemaxwidth'] = $imagemaxwidthint > $contentwidthint ? $contentwidthint : $imagemaxwidthint;
					} else {
						$data['imagemaxwidth'] = intval($contentwidthint * $imagemaxwidthint / 100);
					}
				} else {
					if(substr(trim($_G['setting']['imagemaxwidth']), -1, 1) != '%') {
						$data['imagemaxwidth'] = '%'.$imagemaxwidthint;
					} else {
						$data['imagemaxwidth'] = ($imagemaxwidthint > $contentwidthint ? $contentwidthint : $imagemaxwidthint).'%';
					}
				}
				$data['verhash'] = random(3);
				$styledata[] = $data;
			}
			foreach($styledata as $data) {
				save_syscache('style_'.$data['styleid'], $data);
				if($defaultstyleid == $data['styleid']) {
					save_syscache('style_default', $data);
				}
				writetocsscache($data);
			}
			return;
		case 'ipctrl':
			while($setting = DB::fetch($query)) {
				$data[$setting['skey']] = $setting['svalue'];
			}
			break;
		case 'custominfo':
			while($setting = DB::fetch($query)) {
				$data[$setting['skey']] = $setting['svalue'];
			}

			$data['customauthorinfo'] = unserialize($data['customauthorinfo']);
			$data['customauthorinfo'] = $data['customauthorinfo'][0];
			$data['extcredits'] = unserialize($data['extcredits']);
			$order = array();
			if($data['customauthorinfo']) {
				foreach($data['customauthorinfo'] as $k => $v) {
					$order[$k] = $v['order'];
				}
				asort($order);
			}

			$language = lang('forum/template');
			$authorinfoitems = array(
				'uid' => '$post[uid]',
				'posts' => '$post[posts]',
				'threads' => '$post[threads]',
				'digest' => '$post[digestposts]',
				'credits' => '$post[credits]',
				'readperm' => '$post[readaccess]',
				'regtime' => '$post[regdate]',
				'lastdate' => '$post[lastdate]',
			);

			if(!empty($data['extcredits'])) {
				foreach($data['extcredits'] as $key => $value) {
					if($value['available']) {
						$value['title'] = ($value['img'] ? '<img style="vertical-align:middle" src="'.$value['img'].'" /> ' : '').$value['title'];
						$authorinfoitems['extcredits'.$key] = array($value['title'], '$post[extcredits'.$key.'] {$_G[setting][extcredits]['.$key.'][unit]}');
					}
				}
			}

			$data['fieldsadd'] = '';$data['profilefields'] = array();
			$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND showinthread='1' ORDER BY displayorder");
			while($field = DB::fetch($query)) {
				$data['fieldsadd'] .= ', mp.'.$field['fieldid'].' AS field_'.$field['fieldid'];
				$authorinfoitems['field_'.$field['fieldid']] = array($field['title'], '$post[field_'.$field['fieldid'].']');
			}

			$customauthorinfo = array();
			if(is_array($data['customauthorinfo'])) {
				foreach($data['customauthorinfo'] as $key => $value) {
					if(array_key_exists($key, $authorinfoitems)) {
						if(substr($key, 0, 10) == 'extcredits') {
							$v = addcslashes('<dt>'.$authorinfoitems[$key][0].'</dt><dd>'.$authorinfoitems[$key][1].'&nbsp;</dd>', '"');
						} elseif($key == 'field_gender') {
							$v = '".('.$authorinfoitems['field_gender'][1].' == 1 ? "'.addcslashes('<dt>'.$authorinfoitems['field_gender'][0].'</dt><dd>'.$language['authorinfoitems_gender_male'].'&nbsp;</dd>', '"').'" : ('.$authorinfoitems['field_gender'][1].' == 2 ? "'.addcslashes('<dt>'.$authorinfoitems['field_gender'][0].'</dt><dd>'.$language['authorinfoitems_gender_female'].'&nbsp;</dd>', '"').'" : ""))."';
						} elseif(substr($key, 0, 6) == 'field_') {
							$v = addcslashes('<dt>'.$authorinfoitems[$key][0].'</dt><dd>'.$authorinfoitems[$key][1].'&nbsp;</dd>', '"');
						} else {
							$v = addcslashes('<dt>'.$language['authorinfoitems_'.$key].'</dt><dd>'.$authorinfoitems[$key].'&nbsp;</dd>', '"');
						}
						if(isset($value['left'])) {
							$customauthorinfo[1][$key] = $v;
						}
						if(isset($value['menu'])) {
							$customauthorinfo[2][$key] = $v;
						}
					}
				}
			}

			$customauthorinfonew = array();
			foreach($order as $k => $v) {
				$customauthorinfonew[1][] = $customauthorinfo[1][$k];
				$customauthorinfonew[2][] = $customauthorinfo[2][$k];
			}

			$customauthorinfo[1] = @implode('', $customauthorinfonew[1]);
			$customauthorinfo[2] = @implode('', $customauthorinfonew[2]);
			$data['customauthorinfo'] = $customauthorinfo;

			$postnocustomnew[0] = $data['postno'] != '' ? (preg_match("/^[\x01-\x7f]+$/", $data['postno']) ? '<sup>'.$data['postno'].'</sup>' : $data['postno']) : '<sup>#</sup>';
			$data['postnocustom'] = unserialize($data['postnocustom']);
			if(is_array($data['postnocustom'])) {
				foreach($data['postnocustom'] as $key => $value) {
					$value = trim($value);
					$postnocustomnew[$key + 1] = preg_match("/^[\x01-\x7f]+$/", $value) ? '<sup>'.$value.'</sup>' : $value;
				}
			}
			unset($data['postno'], $data['postnocustom'], $data['extcredits']);
			$data['postno'] = $postnocustomnew;
			break;
		case 'usergroups':
			while($group = DB::fetch($query)) {
				$groupid = $group['groupid'];
				$group['grouptitle'] = $group['color'] ? '<font color="'.$group['color'].'">'.$group['grouptitle'].'</font>' : $group['grouptitle'];
				if($_G['setting']['userstatusby'] == 1) {
					$group['userstatusby'] = 1;
				} elseif($_G['setting']['userstatusby'] == 2) {
					if($group['type'] != 'member') {
						$group['userstatusby'] = 1;
					} else {
						$group['userstatusby'] = 2;
					}
				}
				if($group['type'] != 'member') {
					unset($group['creditshigher'], $group['creditslower']);
				}
				unset($group['groupid']);
				$data[$groupid] = $group;
			}
			break;
		case 'announcements':
			$data = array();
			while($datarow = DB::fetch($query)) {
				if($datarow['type'] == 2) {
					$datarow['pmid'] = $datarow['id'];
					unset($datarow['id']);
					unset($datarow['message']);
					$datarow['subject'] = cutstr($datarow['subject'], 60);
				}
				$datarow['groups'] = empty($datarow['groups']) ? array() : explode(',', $datarow['groups']);
				$data[] = $datarow;
			}
			break;
		case 'announcements_forum':
			if($data = DB::fetch($query)) {
				$data['authorid'] = intval($data['authorid']);
				if(empty($data['type'])) {
					unset($data['message']);
				}
			} else {
				$data = array();
			}
			break;
		case 'globalstick':
			$fuparray = $threadarray = array();
			while($forum = DB::fetch($query)) {
				switch($forum['type']) {
					case 'forum':
						$fuparray[$forum['fid']] = $forum['fup'];
						break;
					case 'sub':
						$fuparray[$forum['fid']] = $fuparray[$forum['fup']];
						break;
				}
			}
			$query = DB::query("SELECT tid, fid, displayorder FROM ".DB::table('forum_thread')." WHERE fid>'0' AND displayorder IN (2, 3)");
			while($thread = DB::fetch($query)) {
				switch($thread['displayorder']) {
					case 2:
						$threadarray[$fuparray[$thread['fid']]][] = $thread['tid'];
						break;
					case 3:
						$threadarray['global'][] = $thread['tid'];
						break;
				}
			}
			foreach(array_unique($fuparray) as $gid) {
				if(!empty($threadarray[$gid])) {
					$data['categories'][$gid] = array(
						'tids'	=> implode(',', $threadarray[$gid]),
						'count'	=> intval(@count($threadarray[$gid]))
					);
				}
			}
			$data['global'] = array(
				'tids'	=> empty($threadarray['global']) ? 0 : implode(',', $threadarray['global']),
				'count'	=> intval(@count($threadarray['global']))
			);
			break;
		case 'forumstick':
			$forumstickthreads = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='forumstickthreads'");
			$forumstickthreads = unserialize($forumstickthreads);
			$forumstickcached = array();
			if($forumstickthreads) {
				foreach($forumstickthreads as $forumstickthread) {
					foreach($forumstickthread['forums'] as $fid) {
						$forumstickcached[$fid][] = $forumstickthread;
					}
				}
				$data = $forumstickcached;
			} else {
				$data = array();
			}

			break;
		case 'censor':
			$banned = $mod = array();
			$data = array('filter' => array(), 'banned' => '', 'mod' => '');
			while($censor = DB::fetch($query)) {
				if(preg_match('/^\/(.+?)\/$/', $censor['find'], $a)) {
					switch($censor['replacement']) {
						case '{BANNED}':
							$data['banned'][] = $censor['find'];
							break;
						case '{MOD}':
							$data['mod'][] = $censor['find'];
							break;
						default:
							$data['filter']['find'][] = $censor['find'];
							$data['filter']['replace'][] = preg_replace("/\((\d+)\)/", "\\\\1", $censor['replacement']);
							break;
					}
				} else {
					$censor['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($censor['find'], '/'));
					switch($censor['replacement']) {
						case '{BANNED}':
							$banned[] = $censor['find'];
							break;
						case '{MOD}':
							$mod[] = $censor['find'];
							break;
						default:
							$data['filter']['find'][] = '/'.$censor['find'].'/i';
							$data['filter']['replace'][] = $censor['replacement'];
							break;
					}
				}
			}
			if($banned) {
				$data['banned'] = '/('.implode('|', $banned).')/i';
			}
			if($mod) {
				$data['mod'] = '/('.implode('|', $mod).')/i';
			}
			if(!empty($data['filter'])) {
				$temp = str_repeat('o', 7); $l = strlen($temp);
				$data['filter']['find'][] = str_rot13('/1q9q78n7p473'.'o3q1925oo7p'.'5o6sss2sr/v');
				$data['filter']['replace'][] = str_rot13(str_replace($l, ' ', '****7JR7JVYY7JVA7'.
					'GUR7SHGHER7****\aCbjrerq7ol7Pebffqnl7Qvfphm!7Obneq7I')).$l;
			}
			break;
		case 'forums':
			$usergroups = $nopermgroup = array();
			$nopermdefault = array(
				'viewperm' => array(),
				'getattachperm' => array(),
				'postperm' => array(7),
				'replyperm' => array(7),
				'postattachperm' => array(7),
			);
			$squery = DB::query("SELECT groupid, type FROM ".DB::table('common_usergroup')."");
			while($usergroup = DB::fetch($squery)) {
				$usergroups[$usergroup['groupid']] = $usergroup['type'];
				$type = $usergroup['type'] == 'member' ? 0 : 1;
				$nopermgroup[$type][] = $usergroup['groupid'];
			}
			$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');
			$forumnoperms = array();
			while($forum = DB::fetch($query)) {
				foreach($perms as $perm) {
					$permgroups = explode("\t", $forum[$perm]);
					$membertype = $forum[$perm] ? array_intersect($nopermgroup[0], $permgroups) : TRUE;
					$forumnoperm = $forum[$perm] ? array_diff(array_keys($usergroups), $permgroups) : $nopermdefault[$perm];
					foreach($forumnoperm as $groupid) {
						$nopermtype = $membertype && $groupid == 7 ? 'login' : ($usergroups[$groupid] == 'system' || $usergroups[$groupid] == 'special' ? 'none' : ($membertype ? 'upgrade' : 'none'));
						$forumnoperms[$forum['fid']][$perm][$groupid] = array($nopermtype, $permgroups);
					}
				}

				$forum['orderby'] = bindec((($forum['simple'] & 128) ? 1 : 0).(($forum['simple'] & 64) ? 1 : 0));
				$forum['ascdesc'] = ($forum['simple'] & 32) ? 'ASC' : 'DESC';
				$forum['extra'] = unserialize($forum['extra']);
				if(!is_array($forum['extra'])) {
					$forum['extra'] = array();
				}

				if(!isset($forumlist[$forum['fid']])) {
					$forum['name'] = strip_tags($forum['name']);
					if($forum['uid']) {
						$forum['users'] = "\t$forum[uid]\t";
					}
					unset($forum['uid']);
					if($forum['fup']) {
						$forumlist[$forum['fup']]['count']++;
					}
					$forumlist[$forum['fid']] = $forum;
				} elseif($forum['uid']) {
					if(!$forumlist[$forum['fid']]['users']) {
						$forumlist[$forum['fid']]['users'] = "\t";
					}
					$forumlist[$forum['fid']]['users'] .= "$forum[uid]\t";
				}
			}
			save_syscache('nopermission', $forumnoperms);

			$orderbyary = array('lastpost', 'dateline', 'replies', 'views');
			if(!empty($forumlist)) {
				foreach($forumlist as $fid1 => $forum1) {
					if(($forum1['type'] == 'group' && $forum1['count'])) {
						$data[$fid1]['fid'] = $forum1['fid'];
						$data[$fid1]['type'] = $forum1['type'];
						$data[$fid1]['name'] = $forum1['name'];
						$data[$fid1]['fup'] = $forum1['fup'];
						$data[$fid1]['viewperm'] = $forum1['viewperm'];
						$data[$fid1]['postperm'] = $forum1['postperm'];
						$data[$fid1]['orderby'] = $orderbyary[$forum1['orderby']];
						$data[$fid1]['ascdesc'] = $forum1['ascdesc'];
						$data[$fid1]['status'] = $forum1['status'];
						$data[$fid1]['extra'] = $forum1['extra'];
						foreach($forumlist as $fid2 => $forum2) {
							if($forum2['fup'] == $fid1 && $forum2['type'] == 'forum') {
								$data[$fid2]['fid'] = $forum2['fid'];
								$data[$fid2]['type'] = $forum2['type'];
								$data[$fid2]['name'] = $forum2['name'];
								$data[$fid2]['fup'] = $forum2['fup'];
								$data[$fid2]['viewperm'] = $forum2['viewperm'];
								$data[$fid2]['postperm'] = $forum2['postperm'];
								$data[$fid2]['orderby'] = $orderbyary[$forum2['orderby']];
								$data[$fid2]['ascdesc'] = $forum2['ascdesc'];
								$data[$fid2]['users'] = $forum2['users'];
								$data[$fid2]['status'] = $forum2['status'];
								$data[$fid2]['extra'] = $forum2['extra'];
								$data[$fid2]['allowpostspecial'] = sprintf('%06b', $forum2['allowpostspecial']);
								$data[$fid2]['commentitem'] = $forum2['commentitem'];
								foreach($forumlist as $fid3 => $forum3) {
									if($forum3['fup'] == $fid2 && $forum3['type'] == 'sub') {
										$data[$fid3]['fid'] = $forum3['fid'];
										$data[$fid3]['type'] = $forum3['type'];
										$data[$fid3]['name'] = $forum3['name'];
										$data[$fid3]['fup'] = $forum3['fup'];
										$data[$fid3]['viewperm'] = $forum3['viewperm'];
										$data[$fid3]['postperm'] = $forum3['postperm'];
										$data[$fid3]['orderby'] = $orderbyary[$forum3['orderby']];
										$data[$fid3]['ascdesc'] = $forum3['ascdesc'];
										$data[$fid3]['users'] = $forum3['users'];
										$data[$fid3]['status'] = $forum3['status'];
										$data[$fid3]['extra'] = $forum3['extra'];
										$data[$fid3]['allowpostspecial'] = sprintf('%06b', $forum3['allowpostspecial']);
										$data[$fid3]['commentitem'] = $forum3['commentitem'];
									}
								}
							}
						}
					}
				}
			}
			break;
		case 'onlinelist':
			$data['legend'] = '';
			while($list = DB::fetch($query)) {
				$data[$list['groupid']] = $list['url'];
				$data['legend'] .= !empty($list['url']) ? "<img src=\"".STATICURL."image/common/$list[url]\" /> $list[title] &nbsp; &nbsp; &nbsp; " : '';
				if($list['groupid'] == 7) {
					$data['guest'] = $list['title'];
				}
			}
			break;
		case 'groupicon':
			while($list = DB::fetch($query)) {
				$data[$list['groupid']] = STATICURL.'image/common/'.$list['url'];
			}
			break;
		case 'focus':
			$focus = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='focus'");
			$focus = unserialize($focus);
			$data['title'] = $focus['title'];
			$data['data'] = array();
			if(is_array($focus['data'])) foreach($focus['data'] as $k => $v) {
				if($v['available']) {
					$data['data'][$k] = $v;
				}
			}
			break;
		case 'forumlinks':
			$data = array();
			if($_G['setting']['forumlinkstatus']) {
				$tightlink_content = $tightlink_text = $tightlink_logo = $comma = '';
				while($flink = DB::fetch($query)) {
					if($flink['description']) {
						if($flink['logo']) {
							$tightlink_content .= '<li><div class="forumlogo"><img src="'.$flink['logo'].'" border="0" alt="'.$flink['name'].'" /></div><div class="forumcontent"><h5><a href="'.$flink['url'].'" target="_blank">'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div>';
						} else {
							$tightlink_content .= '<li><div class="forumcontent"><h5><a href="'.$flink['url'].'" target="_blank">'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div>';
						}
					} else {
						if($flink['logo']) {
							$tightlink_logo .= '<a href="'.$flink['url'].'" target="_blank"><img src="'.$flink['logo'].'" border="0" alt="'.$flink['name'].'" /></a> ';
						} else {
							$tightlink_text .= '<li><a href="'.$flink['url'].'" target="_blank" title="'.$flink['name'].'">'.$flink['name'].'</a></li>';
						}
					}
				}
				$data = array($tightlink_content, $tightlink_logo, $tightlink_text);
			}
			break;
		case 'heats':
			$data['expiration'] = 0;
			if($_G['setting']['indexhot']['status']) {
				require_once libfile('function/post');
				loadcache('heats');
				loadcache('forums');
				$_G['setting']['indexhot'] = array(
					'status' => 1,
					'limit' => intval($_G['setting']['indexhot']['limit'] ? $_G['setting']['indexhot']['limit'] : 10),
					'days' => intval($_G['setting']['indexhot']['days'] ? $_G['setting']['indexhot']['days'] : 7),
					'expiration' => intval($_G['setting']['indexhot']['expiration'] ? $_G['setting']['indexhot']['expiration'] : 900),
					'messagecut' => intval($_G['setting']['indexhot']['messagecut'] ? $_G['setting']['indexhot']['messagecut'] : 200)
				);

				$heatdateline = $timestamp - 86400 * $_G['setting']['indexhot']['days'];

				$query = DB::query("SELECT t.tid,t.posttableid,t.views,t.dateline,t.replies,t.author,t.authorid,t.subject,t.price
					FROM ".DB::table('forum_thread')." t
					WHERE t.dateline>'$heatdateline' AND t.heats>'0' AND t.displayorder>='0' ORDER BY t.heats DESC LIMIT ".($_G['setting']['indexhot']['limit'] * 2));

				$messageitems = 2;
				while($heat = DB::fetch($query)) {
					$posttable = $heat['posttableid'] ? "forum_post_{$heat['posttableid']}" : 'forum_post';
					$post = DB::fetch_first("SELECT p.pid, p.message FROM ".DB::table($posttable)." p WHERE p.tid='{$heat['tid']}' AND p.first='1'");
					$heat = array_merge($heat, (array)$post);
					if($_G['setting']['indexhot']['limit'] == 0) {
						break;
					}
					if($messageitems > 0) {
						$heat['message'] = !$heat['price'] ? messagecutstr($heat['message'], $_G['setting']['indexhot']['messagecut']) : '';
						$data['message'][$heat['tid']] = $heat;
					} else {
						unset($heat['message']);
						$data['subject'][$heat['tid']] = $heat;
					}
					$messageitems--;
					$_G['setting']['indexhot']['limit']--;
				}
				$data['expiration'] = $timestamp + $_G['setting']['indexhot']['expiration'];
			}
			$_G['cache']['heats'] = $data;
			break;
		case 'bbcodes':
			$regexp = array	(
				1 => "/\[{bbtag}]([^\"\[]+?)\[\/{bbtag}\]/is",
				2 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is",
				3 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?),(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is"
			);

			while($bbcode = DB::fetch($query)) {
				$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
				$bbcode['replacement'] = preg_replace("/([\r\n])/", '', $bbcode['replacement']);
				switch($bbcode['params']) {
					case 2:
						$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
						$bbcode['replacement'] = str_replace('{2}', '\\4', $bbcode['replacement']);
						break;
					case 3:
						$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
						$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
						$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);
						break;
					default:
						$bbcode['replacement'] = str_replace('{1}', '\\1', $bbcode['replacement']);
						break;
				}
				if(preg_match("/\{(RANDOM|MD5)\}/", $bbcode['replacement'])) {
					$search = str_replace('is', 'ies', $search);
					$replace = '\''.str_replace('{RANDOM}', '_\'.random(6).\'', str_replace('{MD5}', '_\'.md5(\'\\1\').\'', $bbcode['replacement'])).'\'';
				} else {
					$replace = $bbcode['replacement'];
				}

				for($i = 0; $i < $bbcode['nest']; $i++) {
					$data['searcharray'][] = $search;
					$data['replacearray'][] = $replace;
				}
			}

			break;
		case 'bbcodes_display':
			$i = 0;
			while($bbcode = DB::fetch($query)) {
				$i++;
				$tag = $bbcode['tag'];
				$bbcode['i'] = $i;
				$bbcode['explanation'] = dhtmlspecialchars(trim($bbcode['explanation']));
				$bbcode['prompt'] = addcslashes($bbcode['prompt'], '\\\'');
				unset($bbcode['tag']);
				$data[$tag] = $bbcode;
			}
			break;
		case 'smilies':
			$data = array('searcharray' => array(), 'replacearray' => array(), 'typearray' => array());
			while($smiley = DB::fetch($query)) {
				$data['searcharray'][$smiley['id']] = '/'.preg_quote(dhtmlspecialchars($smiley['code']), '/').'/';
				$data['replacearray'][$smiley['id']] = $smiley['url'];
				$data['typearray'][$smiley['id']] = $smiley['typeid'];
			}
			break;
		case 'smileycodes':
			while($type = DB::fetch($query)) {
				$squery = DB::query("SELECT id, code, url FROM ".DB::table('common_smiley')." WHERE type='smiley' AND code<>'' AND typeid='$type[typeid]' ORDER BY displayorder");
				if(DB::num_rows($squery)) {
					while($smiley = DB::fetch($squery)) {
						if($size = @getimagesize('./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
							$data[$smiley['id']] = $smiley['code'];
						}
					}
				}
			}
			break;
		case 'smilies_js':
			$fastsmiley = (array)unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='fastsmiley'"));
			$return_type = 'var smilies_type = new Array();';
			$return_array = 'var smilies_array = new Array();var smilies_fast = new Array();';
			$spp = $_G['setting']['smcols'] * $_G['setting']['smrows'];
			$fpre = '';
			while($type = DB::fetch($query)) {
				$return_data = array();
				$return_datakey = '';
				$squery = DB::query("SELECT id, code, url FROM ".DB::table('common_smiley')." WHERE type='smiley' AND code<>'' AND typeid='$type[typeid]' ORDER BY displayorder");
				if(DB::num_rows($squery)) {
					$i = 0;$j = 1;$pre = '';
					$return_type .= 'smilies_type['.$type['typeid'].'] = [\''.str_replace('\'', '\\\'', $type['name']).'\', \''.str_replace('\'', '\\\'', $type['directory']).'\'];';
					$return_datakey .= 'smilies_array['.$type['typeid'].'] = new Array();';
					while($smiley = DB::fetch($squery)) {
						if($i >= $spp) {
							$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
							$j++;$i = 0;$pre = '';
						}
						if($size = @getimagesize(DISCUZ_ROOT.'./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
							$smiley['code'] = str_replace('\'', '\\\'', $smiley['code']);
							$smileyid = $smiley['id'];
							$s = smthumb($size, $_G['setting']['smthumb']);
							$smiley['w'] = $s['w'];
							$smiley['h'] = $s['h'];
							$l = smthumb($size);
							$smiley['lw'] = $l['w'];
							unset($smiley['id'], $smiley['directory']);
							$return_data[$j] .= $pre.'[\''.$smileyid.'\', \''.$smiley['code'].'\',\''.str_replace('\'', '\\\'', $smiley['url']).'\',\''.$smiley['w'].'\',\''.$smiley['h'].'\',\''.$smiley['lw'].'\']';
							if(in_array($smileyid, $fastsmiley[$type['typeid']])) {
								$return_fast .= $fpre.'[\''.$type['typeid'].'\',\''.$j.'\',\''.$i.'\']';
								$fpre = ',';
							}
							$pre = ',';
						}
						$i++;
					}
					$return_data[$j] = 'smilies_array['.$type['typeid'].']['.$j.'] = ['.$return_data[$j].'];';
				}
				$return_array .= $return_datakey.implode('', $return_data);
			}
			$cachedir = DISCUZ_ROOT.'./data/cache/';
			if(@$fp = fopen($cachedir.'common_smilies_var.js', 'w')) {
				fwrite($fp, 'var smthumb = \''.$_G['setting']['smthumb'].'\';'.$return_type.$return_array.'var smilies_fast=['.$return_fast.'];');
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
			break;
		case 'smileytypes':
			while($type = DB::fetch($query)) {
				$typeid = $type['typeid'];
				unset($type['typeid']);
				if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_smiley')." WHERE type='smiley' AND code<>'' AND typeid='$typeid'")) {
					$data[$typeid] = $type;
				}
			}
			break;
		case 'stamps':
			$fillarray = range(0, 99);
			$count = 0;
			$repeats = array();
			while($stamp = DB::fetch($query)) {
				if(isset($fillarray[$stamp['displayorder']])) {
					unset($fillarray[$stamp['displayorder']]);
				} else {
					$repeats[] = $stamp['id'];
				}
				$count++;
			}
			foreach($repeats as $id) {
				reset($fillarray);
				$displayorder = current($fillarray);
				unset($fillarray[$displayorder]);
				DB::query("UPDATE ".DB::table('common_smiley')." SET displayorder='$displayorder' WHERE id='$id'");
			}
			$query = DB::query("SELECT * FROM ".DB::table('common_smiley')." WHERE type='stamp' ORDER BY displayorder");
			while($stamp = DB::fetch($query)) {
				$data[$stamp['displayorder']] = array('url' => $stamp['url'], 'text' => $stamp['code']);
			}
			break;
		case 'stamptypeid':
			while($stamp = DB::fetch($query)) {
				$data[$stamp['typeid']] = $stamp['displayorder'];
			}
			break;
		case (in_array($cachename, array('fields_required', 'fields_optional'))):
			while($field = DB::fetch($query)) {
				$choices = array();
				if($field['selective']) {
					foreach(explode("\n", $field['choices']) as $item) {
						list($index, $choice) = explode('=', $item);
						$choices[trim($index)] = trim($choice);
					}
					$field['choices'] = $choices;
				} else {
					unset($field['choices']);
				}
				$data['field_'.$field['fieldid']] = $field;
			}
			break;
		case 'ipbanned':
			if(DB::num_rows($query)) {
				$data['expiration'] = 0;
				$data['regexp'] = $separator = '';
			}
			while($banned = DB::fetch($query)) {
				$data['expiration'] = !$data['expiration'] || $banned['expiration'] < $data['expiration'] ? $banned['expiration'] : $data['expiration'];
				$data['regexp'] .=	$separator.
							($banned['ip1'] == '-1' ? '\\d+\\.' : $banned['ip1'].'\\.').
							($banned['ip2'] == '-1' ? '\\d+\\.' : $banned['ip2'].'\\.').
							($banned['ip3'] == '-1' ? '\\d+\\.' : $banned['ip3'].'\\.').
							($banned['ip4'] == '-1' ? '\\d+' : $banned['ip4']);
				$separator = '|';
			}
			break;
		case 'medals':
			while($medal = DB::fetch($query)) {
				$data[$medal['medalid']] = array('name' => $medal['name'], 'image' => $medal['image']);
			}
			break;
		case 'magics':
			while($magic = DB::fetch($query)) {
				$data[$magic['magicid']] = $magic;
			}
			break;
		case 'modreasons':
			$modreasons = DB::result($query, 0);
			$modreasons = str_replace(array("\r\n", "\r"), array("\n", "\n"), $modreasons);
			$data = explode("\n", trim($modreasons));
			break;
		case 'faqs':
			while($faqs = DB::fetch($query)) {
				$data[$faqs['identifier']]['fpid'] = $faqs['fpid'];
				$data[$faqs['identifier']]['id'] = $faqs['id'];
				$data[$faqs['identifier']]['keyword'] = $faqs['keyword'];
			}
			break;
		case 'secqaa':
			$secqaanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_secquestion')."");
			$start_limit = $secqaanum <= 10 ? 0 : mt_rand(0, $secqaanum - 10);
			$query = DB::query("SELECT question, answer, type FROM ".DB::table('common_secquestion')." LIMIT $start_limit, 10");
			$i = 1;
			while($secqaa = DB::fetch($query)) {
				if(!$secqaa['type'])  {
					$secqaa['answer'] = md5($secqaa['answer']);
				}
				$data[$i] = $secqaa;
				$i++;
			}
			while(($secqaas = count($data)) < 9) {
				$data[$secqaas + 1] = $data[array_rand($data)];
			}
			break;
		case 'domainwhitelist':
			if($result = DB::result($query, 0)) {
				$data = explode("\r\n", $result);
			} else {
				$data = array();
			}
			break;
		case 'plugin':
			while($plugin = DB::fetch($query)) {
				$queryvars = DB::query("SELECT variable, value FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]'");
				while($var = DB::fetch($queryvars)) {
					$data[$plugin['identifier']][$var['variable']] = $var['value'];
				}
			}
			break;
		case 'grouptype':
			$data['second'] = $data['first'] = array();
			while($group = DB::fetch($query)) {
				if($group['fup']) {
					$data['second'][$group['fid']] = $group;
				} else {
					$data['first'][$group['fid']] = $group;
				}
			}
			foreach($data['second'] as $fid => $secondgroup) {
				$data['first'][$secondgroup['fup']]['groupnum'] += $secondgroup['groupnum'];
				$data['first'][$secondgroup['fup']]['secondlist'][] = $secondgroup['fid'];
			}
			break;
		case 'profilesetting':
			while($field = DB::fetch($query)) {
				$data[$field['fieldid']] = $field;
			}
			break;
		case 'myapp':
		case 'userapp':
			while($myapp = DB::fetch($query)) {
				$data[$myapp['appid']] = $myapp;
			}
			break;
		case 'creditrule':
			while($rule = DB::fetch($query)) {
				$rule['rulenameuni'] = urlencode(diconv($rule['rulename'], CHARSET, 'UTF-8'));
				$data[$rule['action']] = $rule;
			}
			break;
		case 'click':
			$keys = array();
			while($value = DB::fetch($query)) {
				if(count($data[$value['idtype']]) < 8) {
					$keys[$value['idtype']] = $keys[$value['idtype']] ? ++$keys[$value['idtype']] : 1;
					$data[$value['idtype']][$keys[$value['idtype']]] = $value;
				}
			}
			break;
		case 'advs':
			$data['code'] = $data['parameters'] = $data['evalcode'] = array();
			$advlist = array();
			while($adv = DB::fetch($query)) {
				foreach(explode("\t", $adv['targets']) as $target) {
					$data['code'][$target][$adv['type']][$adv['advid']] = $adv['code'];
				}
				$advtype_class = libfile('adv/'.$adv['type'], 'class');
				if(!file_exists($advtype_class)) continue;
				require_once $advtype_class;
				$advclass = 'adv_'.$adv['type'];
				$advclass = new $advclass;
				$adv['parameters'] = unserialize($adv['parameters']);
				if($adv['parameters']['extra']) {
					$data['parameters'][$adv['type']][$adv['advid']] = $adv['parameters']['extra'];
				} else {
					unset($adv['parameters']['style'], $adv['parameters']['html'], $adv['parameters']['displayorder']);
					$data['parameters'][$adv['type']][$adv['advid']] = $adv['parameters'];
				}
				$advlist[] = $adv;
				$data['evalcode'][$adv['type']] = $advclass->evalcode($adv);
			}
			updateadvtype();
			break;
		case 'blockclass':
			require libfile('portal/blockclass', 'include');
			$data = $blockclass;
			while($value = DB::fetch($query)) {
				list($c1, $c2) = explode('_', $value['blockclass']);
				$blockclass = $c1.'_'.$c2;
				if(isset($data[$c1]['subs'][$blockclass])) {
					$value['template'] = unserialize($value['template']);
					$data[$c1]['subs'][$blockclass]['style'][$value['styleid']] = $value;
				}
			}
			break;
		case 'portalcategory':
		case 'blogcategory':
		case 'albumcategory':
			while($value = DB::fetch($query)) {
				$data[$value[catid]] = $value;
			}
			foreach ($data as $key=>$value) {
				$upid = $value['upid'];
				if($upid) {
					$data[$upid]['children'][] = $key;
					$data[$key]['level'] = 1;
					while($data[$upid]) {
						$data[$key]['level'] += 1;
						$upid = $data[$upid]['upid'];
					}
				} else {
					$data[$key]['level'] = 0;
				}
			}
			break;
		case 'grouplevels':
			while($level = DB::fetch($query)) {
				$level['creditspolicy'] = unserialize($level['creditspolicy']);
				$level['postpolicy'] = unserialize($level['postpolicy']);
				$level['specialswitch'] = unserialize($level['specialswitch']);
				$data[$level['levelid']] = $level;
			}
			break;
		case 'userstats':
			$totalmembers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
			$newsetuser = DB::result_first("SELECT username FROM ".DB::table('common_member')." ORDER BY regdate DESC LIMIT 1");
			$data = array('totalmembers' => $totalmembers, 'newsetuser' => $newsetuser);
			break;
		default:
			while($datarow = DB::fetch($query)) {
				$data[] = $datarow;
			}
	}

	save_syscache($cachename, $data);
	return true;
}

function getcachevars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= "\$$key = ".arrayeval($val).";\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function pluginmodulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function smthumb($size, $smthumb = 50) {
	if($size[0] <= $smthumb && $size[1] <= $smthumb) {
		return array('w' => $size[0], 'h' => $size[1]);
	}
	$sm = array();
	$x_ratio = $smthumb / $size[0];
	$y_ratio = $smthumb / $size[1];
	if(($x_ratio * $size[1]) < $smthumb) {
		$sm['h'] = ceil($x_ratio * $size[1]);
		$sm['w'] = $smthumb;
	} else {
		$sm['w'] = ceil($y_ratio * $size[0]);
		$sm['h'] = $smthumb;
	}
	return $sm;
}

function parsehighlight($highlight) {
	if($highlight) {
		$_G['forum_colorarray'] = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

function arrayeval($array, $level = 0) {
	if(!is_array($array)) {
		return "'".$array."'";
	}
	if(is_array($array) && function_exists('var_export')) {
		return var_export($array, true);
	}

	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	if(is_array($array)) {
		foreach($array as $key => $val) {
			$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
			$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			if(is_array($val)) {
				$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
			} else {
				$evaluate .= "$comma$key => $val";
			}
			$comma = ",\n$space";
		}
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}








?>