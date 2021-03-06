<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_group.php 11546 2010-06-08 02:20:35Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if($operation != 'setting' && empty($_G['setting']['groupstatus'])) {
	cpmsg('group_status_off', 'action=group&operation=setting', 'error');
}

if($operation == 'setting') {
	$setting = &$_G['setting'];
	$group_creditspolicy = unserialize($setting['group_creditspolicy']) ? unserialize($setting['group_creditspolicy']) : array();
	$group_admingroupids = unserialize($setting['group_admingroupids']) ? unserialize($setting['group_admingroupids']) : array();

	$group_postpolicy = unserialize($setting['group_postpolicy']) ? unserialize($setting['group_postpolicy']) : array();
	if($group_postpolicy['autoclose']) {
		$group_postpolicy['autoclosetime'] = abs($group_postpolicy['autoclose']);
		$group_postpolicy['autoclose'] = $group_postpolicy['autoclose'] / abs($group_postpolicy['autoclose']);
	}
	if(!submitcheck('updategroupsetting')) {
		shownav('group', 'nav_group_setting');
		showsubmenu('nav_group_setting');
		showformheader('group&operation=setting');
		showtableheader();
		showtitle('groups_setting_basic');
		showsetting('groups_setting_basic_status', 'settingnew[groupstatus]', $setting['groupstatus'], 'radio');
		showsetting('groups_setting_basic_iconsize', 'settingnew[group_imgsizelimit]', $setting['group_imgsizelimit'], 'text');
		showtitle('groups_setting_admingroup');
		$varname = array('newgroup_admingroupids', array(), 'isfloat');
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE radminid IN('1','2') ORDER BY groupid");
		while($ugroup = DB::fetch($query)) {
			$varname[1][] = array($ugroup['groupid'], $ugroup['grouptitle'], '1');
		}
		showsetting('', $varname, $group_admingroupids, 'omcheckbox');

		showtitle('groups_setting_seo');
		showsetting('groups_setting_seo_description', 'descriptionnew', $_G['setting']['group_description'], 'textarea');
		showsetting('groups_setting_seo_keyword', 'keywordsnew', $_G['setting']['group_keywords'], 'text');
		showsubmit('updategroupsetting');
		showtablefooter();
		showformfooter();
	} else {
		require_once libfile('function/discuzcode');
		$skey_array = array('groupstatus','group_imgsizelimit');
		foreach($_G['gp_settingnew'] as $skey => $svalue) {
			if(in_array($skey, $skey_array)){
				DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('$skey', '".intval($svalue)."')");
			}
		}

		DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('group_admingroupids', '".daddslashes(serialize($_G['gp_newgroup_admingroupids']))."')");
		$descriptionnew = addslashes(preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', dstripslashes($_G['gp_descriptionnew'])));
		$keywordsnew = $_G['gp_keywordsnew'];
		DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('group_description', '$descriptionnew')");
		DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('group_keywords', '".daddslashes($keywordsnew)."')");

		updatecache('setting');
		cpmsg('groups_setting_succeed', 'action=group&operation=setting', 'succeed');
	}
} elseif($operation=='import'){
		shownav('group', '专区人员导入');
	if(!submitcheck('importsubmit')) {
		showsubmenu('专区人员导入');
		showtips('group_import');
		showformheader('group&operation=import','enctype');
		showtableheader();
		showsubmenu('专区人员导入', array(
			array('导出模板文件', 'group&operation=export', 0),
		));
		showsetting('import_file', 'importfile', '', 'file');
		showsubmit('importsubmit');
		showtablefooter();
		showformfooter();

	} else {
		if($_FILES['importfile']['name']){
			$file=$_FILES['importfile'];
			$filepath='/data/attachment/xls';
			$filemaxsize=1024*1024*2;
			$error=upload_xls($file,$filepath,$filemaxsize);
			switch($error)
				   { 
					case 0:
						//$tomorrow=date("Y-m-d",strtotime("+1 day"));
						//sleep(strtotime($tomorrow)- $_G['timestamp']);
						require_once "./source/plugin/stationcourse/reader.php";
						$data = new Spreadsheet_Excel_Reader(); //实例化 
						$data->setOutputEncoding('gbk');      //编码 
						$data->read(DISCUZ_ROOT.$filepath.'/'.$file['name']);
						for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
							if($data->sheets[0]['cells'][$i][2]){
								$regname=$data->sheets[0]['cells'][$i][2];
							}else{
								$errorarr[0][sheeterrors][$i][]='员工编码不能为空。';
							}
							if($data->sheets[0]['cells'][$i][3]){
								$fid=$data->sheets[0]['cells'][$i][3];
							}else{
								$errorarr[0][sheeterrors][$i][]='专区id不能为空。';
							}
							if($data->sheets[0]['cells'][$i][1]){
								$uid=$data->sheets[0]['cells'][$i][1];
							}else{
								if($regname){
									$uid= DB::result_first("SELECT uid FROM ".DB::table("common_member")." WHERE username='$regname'");
									}
							}
							if($errorarr[0][sheeterrors][$i]){
								writelog('groupmembererror','第'.$i.'行  '.implode('',$errorarr[0][sheeterrors][$i]).'<br/>');
								continue;
							}
							writelog('groupmemberinsert','用户uid'.$uid.'用户名'.$regname.'专区id'.$fid.'<br/>');
							$count=DB::result_first("select count(*) from ".DB::TABLE("forum_groupuser")." where fid='$fid' and username='$regname' ");
							if(!$count){
								group_add_user_to_group($uid, $regname, $fid);
								writelog('groupmemberinsert','用户'.$regname.'添加成功<br/>');
							}else{
								writelog('groupmemberinsert','用户'.$regname.'本来就在专区中<br/>');
							}
						}
						unlink(DISCUZ_ROOT.$filepath.'/'.$file['name']);
						break;
					case 1: 
						cpmsg("上传的文件超过了系统配置中upload_max_filesize选项限制的值。", '', 'error');
					case 2: 
						cpmsg("上传文件的大小超过了 表单中 MAX_FILE_SIZE 选项指定的值。", '', 'error');
					case 3: 
						cpmsg("文件只有部分被上传！", '', 'error');
					case 4: 
						cpmsg("没有文件被上传！", '', 'error');
				   } 
			   }else{
			   		cpmsg("请选择文件！", '', 'error');
			   }
	}
	
}elseif($operation == 'export'){
	ob_end_clean();
	$file_dir='data/attachment/xls/';
	$file_name='model.xls';
	$file=fopen($file_dir.$file_name,"r");   //   打开文件  
	//   输入文件标签
	Header("Content-type:application/octet-stream");
	Header("Accept-Ranges:bytes");
	Header("Accept-Length:".filesize($file_dir.$file_name));
	Header("Content-Disposition:attachment;filename=".$file_name);
	//   输出文件内容
	echo fread($file,filesize($file_dir.$file_name));
	fclose($file);
	exit;
}elseif($operation == 'type') {
		shownav('group', 'nav_group_type');
		showsubmenu('nav_group_type');
	if(!submitcheck('editsubmit')) {
?>
<script type="text/JavaScript">
var rowtypedata = [
	[[1,'<input type="text" class="txt" name="newcatorder[]" value="0" />', 'td25'], [3, '<input name="newcat[]" value="<?=$lang[groups_type_level_1]?>" size="20" type="text" class="txt" />']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="board"><input name="newforum[{1}][]" value="<?=$lang[groups_type_sub_new]?>" size="20" type="text" class="txt" /></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="childboard"><input name="newforum[{1}][]" value="<?=$lang[groups_type_sub_new]?>" size="20" type="text" class="txt" /></div>']],
];
</script>
<?
		showformheader('group&operation=type');
		showtableheader('');
		showsubtitle(array('display_order', 'groups_type_name', 'groups_type_count', 'groups_type_operation'));

		$forums = $showedforums = array();
		$query = DB::query("SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.forumcolumns, f.inheritedmod, ff.moderators, ff.password, ff.redirect, ff.groupnum
			FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type IN('group', 'forum')
			ORDER BY f.type<>'group', f.displayorder");

		$groups = $forums = $subs = $fids = $showed = array();
		while($forum = DB::fetch($query)) {
			if($forum['type'] == 'group') {
				$groups[$forum['fid']] = $forum;
			} elseif($forum['type'] == 'sub') {
				$subs[$forum['fup']][] = $forum;
			} else {
				$forums[$forum['fup']][] = $forum;
			}
			$fids[] = $forum['fid'];
		}

		foreach ($groups as $id => $gforum) {
			$showed[] = showgroup($gforum, 'group');
			if(!empty($forums[$id])) {
				foreach ($forums[$id] as $forum) {
					$showed[] = showgroup($forum);
					$lastfid = 0;
					if(!empty($subs[$forum['fid']])) {
						foreach ($subs[$forum['fid']] as $sub) {
							$showed[] = showgroup($sub, 'sub');
							$lastfid = $sub['fid'];
						}
					}
					showgroup($forum, $lastfid, 'lastchildboard');
				}
			}
			showgroup($gforum, '', 'lastboard');
		}

		if(count($fids) != count($showed)) {
			foreach($fids as $fid) {
				if(!in_array($fid, $showed)) {
					DB::update('forum_forum', array(
						'fup' => '0',
						'type' => 'forum',
					), "fid='$fid'");
				}
			}
		}

		showgroup($gforum, '', 'last');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$order = $_G['gp_order'];
		$name = $_G['gp_name'];
		$newforum = $_G['gp_newforum'];
		$newcat = $_G['gp_newcat'];
		$neworder = $_G['gp_neworder'];
		$forumcolumnsnew = $_G['gp_forumcolumnsnew'];
		if(is_array($order)) {
			foreach($order as $fid => $value) {
				DB::query("UPDATE ".DB::table('forum_forum')." SET name='$name[$fid]', displayorder='$order[$fid]', forumcolumns='$forumcolumnsnew[$fid]' WHERE fid='$fid'");
			}
		}

		if(is_array($newcat)) {
			foreach($newcat as $key => $forumname) {
				if(empty($forumname)) {
					continue;
				}
				$fid = DB::insert('forum_forum', array('type' => 'group', 'name' => $forumname, 'status' => 3, 'displayorder' => $newcatorder[$key]), 1);
				DB::insert('forum_forumfield', array('fid' => $fid));
			}
		}

		$table_forum_columns = array('fup', 'type', 'name', 'status', 'displayorder', 'styleid', 'allowsmilies', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowpostspecial', 'alloweditrules', 'alloweditpost', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'autoclose', 'simple');
		$table_forumfield_columns = array('fid', 'attachextensions', 'threadtypes', 'creditspolicy', 'viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');
		$projectdata = array();

		if(is_array($newforum)) {
			foreach($newforum as $fup => $forums) {
				$forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum')." WHERE fid='$fup'");
				foreach($forums as $key => $forumname) {
					if(empty($forumname)) {
						continue;
					}
					$forumfields = array();

					$forumfields['allowsmilies'] = $forumfields['allowbbcode'] = $forumfields['allowimgcode'] = 1;
					$forumfields['allowpostspecial'] = 127;


					$forumfields['fup'] = $forum ? $fup : 0;
					$forumfields['type'] = 'forum';
					$forumfields['name'] = $forumname;
					$forumfields['status'] = 3;
					$forumfields['displayorder'] = $neworder[$fup][$key];

					$data = array();
					foreach($table_forum_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					$forumfields['fid'] = $fid = DB::insert('forum_forum', $data, 1);

					$data = array();
					foreach($table_forumfield_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					DB::insert('forum_forumfield', $data);
				}
			}
		}
		updatecache('grouptype');
		cpmsg('group_update_succeed', 'action=group&operation=type', 'succeed');
	}
} elseif($operation == 'manage') {
	if(!submitcheck('submit', 1)) {

		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
		searchgroups($_G['gp_submit']);

	} else {
		list($page, $start_limit, $groupnum, $conditions, $urladd) = countgroups(); 
		$multipage = multi($groupnum, 20, $page, ADMINSCRIPT."?action=group&operation=manage&submit=yes".$urladd);//change $_G['setting']['memberperpage'] to  20 by fumz,2010-11-24 15:56:02
		$query = DB::query("SELECT f.fid, f.fup, f.type, f.name, f.posts, f.threads, ff.membernum, ff.lastupdate, ff.dateline, ff.foundername, ff.founderuid FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid
			WHERE status='3' AND type='sub' AND $conditions LIMIT $start_limit, ".$_G['setting']['group_perpage']);

		while($group = DB::fetch($query)) {
			$groups .= showtablerow('', array('class="td25"', '', ''), array(
				"<input type=\"checkbox\" name=\"fidarray[]\" value=\"$group[fid]\" class=\"checkbox\">",
				"<a href=\"forum.php?mod=forumdisplay&fid=$group[fid]\" target=\"_blank\">$group[name]</a>",
				$group['posts'],
				$group['threads'],
				$group['membernum'],
				"<a href=\"home.php?mod=space&uid=$group[founderuid]\" target=\"_blank\">$group[foundername]</a>",
				"<a href=\"".ADMINSCRIPT."?action=group&operation=editgroup&fid=$group[fid]\" class=\"act\">".cplang('detail')."</a>"
			), TRUE);
		}

		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
		showformheader("group&operation=deletegroup");
		showtableheader(cplang('groups_search_result', array('groupnum' => $groupnum)).' <a href="javascript:history.go(-1);" class="act lightlink normal">'.cplang('research').'</a>');
		showsubtitle(array('', 'groups_manage_name', 'groups_manage_postcount', 'groups_manage_threadcount', 'groups_manage_membercount', 'groups_manage_founder', ''));
		echo $groups;
		showsubmit('submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'fidarray\')" class="checkbox">'.cplang('del'), '', $multipage);
		showtablefooter();
		showformfooter();

	}
} elseif($operation == 'deletetype') {
	$fid = $_G['gp_fid'];
	$ajax = $_G['gp_ajax'];
	$confirmed = $_G['gp_confirmed'];
	$finished = $_G['gp_finished'];
	$total = intval($_G['gp_total']);
	$pp = intval($_G['gp_pp']);
	$currow = intval($_G['gp_currow']);
	if($ajax) {
		ob_end_clean();
		require_once libfile('function/post');
		$tids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid='$fid' LIMIT $pp");
		while($thread = DB::fetch($query)) {
			$tids[] = $thread['tid'];
		}
		$tids = implode(',', $tids);

		if($tids) {
			$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE tid IN ($tids)");
			while($attach = DB::fetch($query)) {
				dunlink($attach);
			}

			require_once libfile('function/delete');
			foreach(array('forum_thread', 'forum_threadmod', 'forum_relatedthread', 'forum_post', 'forum_poll',
				'forum_polloption', 'forum_trade', 'forum_activity', 'forum_activityapply', 'forum_debate',
				'forum_debatepost', 'forum_attachment', 'forum_typeoptionvar', 'forum_forumrecommend', 'forum_postposition') as $value) {
				if($value == 'forum_post') {
					deletepost("tid IN ($tids)");
					continue;
				}
				DB::query("DELETE FROM ".DB::table($value)." WHERE tid IN ($tids)", 'UNBUFFERED');
				if($value == 'attachments') {
					DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE tid IN ($tids)", 'UNBUFFERED');
				}
			}
		}

		if($currow + $pp > $total) {
			DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_access')." WHERE fid='$fid'");
			echo 'TRUE';
			exit;
		}

		echo 'GO';
		exit;

	} else {
		if($finished) {
			updatecache('grouptype');
			cpmsg('grouptype_delete_succeed', 'action=group&operation=type', 'succeed');

		}

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE fup='$fid'")) {
			cpmsg('grouptype_delete_sub_notnull', '', 'error');
		}

		if(!$confirmed) {

			cpmsg('grouptype_delete_confirm', "action=group&operation=deletetype&fid=$fid", 'form');

		} else {

			$threads = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE fid='$fid'");

			cpmsg('grouptype_delete_alarm', "action=group&operation=deletetype&fid=$fid&confirmed=1", 'loadingform', array(), '<div id="percent">0%</div>', FALSE);
			echo "
			<div id=\"statusid\" style=\"display:none\"></div>
			<script type=\"text/JavaScript\">
				var xml_http_building_link = '".cplang('xml_http_building_link')."';
				var xml_http_sending = '".cplang('xml_http_sending')."';
				var xml_http_loading = '".cplang('xml_http_loading')."';
				var xml_http_load_failed = '".cplang('xml_http_load_failed')."';
				var xml_http_data_in_processed = '".cplang('xml_http_data_in_processed')."';
				var adminfilename = '".ADMINSCRIPT."';
				function forumsdelete(url, total, pp, currow) {

					var x = new Ajax('HTML', 'statusid');
					x.get(url+'&ajax=1&pp='+pp+'&total='+total+'&currow='+currow, function(s) {
						if(s != 'GO') {
							location.href = adminfilename + '?action=group&operation=deletetype&finished=1';
						}

						currow += pp;
						var percent = ((currow / total) * 100).toFixed(0);
						percent = percent > 100 ? 100 : percent;
						document.getElementById('percent').innerHTML = percent+'%';
						document.getElementById('percent').style.backgroundPosition = '-'+percent+'%';

						if(currow < total) {
							forumsdelete(url, total, pp, currow);
						}
					});
				}
				forumsdelete(adminfilename + '?action=group&operation=deletetype&fid=$fid&confirmed=1', $threads, 2000, 0);
			</script>
			";
		}
	}
} elseif($operation == 'editgroup') {
	
	require_once libfile('function/group');
	$fid = intval($_G['gp_fid']);
	if(empty($fid)) {
		cpmsg('group_nonexist', 'action=group&operation=manage', 'error');
	} else {
		$condition = "f.fid='$fid'";
	}
	$group = DB::fetch_first("SELECT f.fid, f.fup, f.name, f.status, f.digest, f.displayorder, f.org_id, ff.icon,ff.jointype,ff.gviewperm,ff.description,ff.rules,ff.banner,ff.group_type FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid WHERE status='3' AND type='sub' AND $condition");
	if(!$group) {
		cpmsg('group_nonexist', '', 'error');
	}

	require_once libfile('function/group');
	require_once libfile('function/discuzcode');
	$groupicon = get_groupimg($group['icon'], 'icon');
	$groupbanner = get_groupimg($group['banner']);
	$jointypeselect = array(array('-1', cplang('closed')), array('0', cplang('public')), array('1', cplang('invite')), array('2', cplang('moderate')));
	if(!submitcheck('editsubmit')) {
		$groupselect = get_groupselect(0, $group['fup'], 0);
		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
        echo '<script type="text/javascript">disallowfloat = ""</script>';
		showformheader("group&operation=editgroup&fid=$fid", 'enctype');
		showtableheader();
		showsetting('groups_editgroup_name', 'namenew', $group['name'], 'text');
		showsetting('groups_editgroup_category', '', '', '<select name="fupnew">'.$groupselect.'</select>');
		showsetting('groups_editgroup_jointype', array('jointypenew', $jointypeselect), $group['jointype'], 'select');
        showsetting('专区类型', array('group_type',  $_G["common"]["group_type"]), $group["group_type"], 'select', "", "", "",
                "onchange='orgtreechange(this)'");
        showsetting('', 'orgtree', '', '<input type="hidden" id="orgname_input_id" name="orgname_input_id" value="'.$group["org_id"].'" />');
		showsetting('', 'station', '', '<input type="hidden" id="station_input_id" name="station_input_id" value="'.$group["stattion"].'" />');
        
		require_once libfile('function/org');
		$org = getOrgById($group["org_id"]);
        $orgname = $org[0]["name"];
        $a = "<a onclick=\"showWindow('orgtree', this.href, 'get', 0);\" href=\"misc.php?mod=queryorg&namevalue=namevalue&orgid=$group[org_id]&orgname=".urlencode($orgname)."\">选择</a></span>";
        showsetting('', 'selectorgtree', '', '<tr class="noborder" id="selectorgtree"><td class="vtop rowform">选择机构:
            <span><input type="text" name="orgname_input" id="orgname_input" value="'.$orgname.'" readonly="true"/>'.$a.'</td></tr>');
        
        $a = "<a onclick=\"showWindow('qrystationwin', this.href, 'get', 0);\" href=\"misc.php?mod=querystation&sid=$group[org_id]&sname=".urlencode($orgname)."\">选择</a></span>";
        showsetting('', 'station_input_tr', '', '<tr class="noborder" id="station_input_tr"><td class="vtop rowform">选择岗位:
            <span><textarea class="tarea" cols="50" rows="6" name="station_input" id="station_input" readonly="true">'.$group[stattion_names].'</textarea>'.$a.'</td></tr>');
        
        if($group["group_type"]!=1){
            echo '<script type="text/javascript">$("selectorgtree").style.display="none";$("orgname_input_id").value = "";</script>';
        }
        if($group["group_type"]!=20){
            echo '<script type="text/javascript">$("station_input_tr").style.display="none";$("station_input_tr").value = "";</script>';
        }
		showsetting('groups_editgroup_recommend', 'ordernew', $group['displayorder'], 'radio');
		showsetting('groups_editgroup_digest', 'dignew', $group['digest'], 'radio');
		showsetting('groups_editgroup_visible_all', 'gviewpermnew', $group['gviewperm'], 'radio');
		showsetting('groups_editgroup_description', 'descriptionnew', $group['description'], 'textarea');
		showsetting('groups_editgroup_rules', 'rulesnew', $group['rules'], 'textarea');
		if($groupicon) {
			$groupicon = '<input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'<br /><img src="'.$groupicon.'?'.random(6).'" width="48" height="48" />';
		}
		if($groupbanner) {
			$groupbanner = '<input type="checkbox" class="checkbox" name="deletebanner" value="yes" /> '.$lang['delete'].'<br /><img src="'.$groupbanner.'?'.random(6).'" />';
		}
		showsetting('groups_editgroup_icon', 'iconnew', '', 'file', '', 0, $groupicon);
		showsetting('groups_editgroup_banner', 'bannernew', '', 'file', '', 0, $groupbanner);
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		if($_G["gp_group_type"]==1 && empty($_POST["orgname_input_id"])){
			cpmsg('请选择对应的机构', 'action=group&operation=editgroup&fid='.$fid, 'error');
        }
		$_G['gp_jointypenew'] = intval($_G['gp_jointypenew']);
		$_G['gp_fupnew'] = intval($_G['gp_fupnew']);
		$_G['gp_gviewpermnew'] = intval($_G['gp_gviewpermnew']);
		$_G['gp_ordernew'] = intval($_G['gp_ordernew']);
		$_G['gp_descriptionnew'] = dhtmlspecialchars(censor(trim($_G['gp_descriptionnew'])));
		$_G['gp_rulesnew'] = dhtmlspecialchars(censor(trim($_G['gp_rulesnew'])));
		$_G['gp_namenew'] = dhtmlspecialchars(censor(trim($_G['gp_namenew'])));
		$icondata = array();
		$iconnew = upload_icon_banner($group, $_FILES['iconnew'], 'icon');
		$bannernew = upload_icon_banner($group, $_FILES['bannernew'], 'banner');
		if($iconnew) {
			$icondata['icon'] = $iconnew;
		}
		if($bannernew) {
			$icondata['banner'] = $bannernew;
		};

		if($_G['gp_deleteicon']) {
			@unlink($_G['setting']['attachurl'].'group/'.$group['icon']);
			$icondata['icon'] = '';
		}
		if($_G['gp_deletebanner']) {
			@unlink($_G['setting']['attachurl'].'group/'.$group['banner']);
			$icondata['banner'] = '';
		}
        $station_input = $_POST["station_input"];
        $station_input_id = $_POST["station_input_id"];
        
        if($_G[gp_group_type]!=20){
            $station_input = '';
            $station_input_id = '';
        }
        
		$groupdata = array_merge($icondata, array(
			'description' => $_G['gp_descriptionnew'],
			'gviewperm' => $_G['gp_gviewpermnew'],
			'rules' => $_G['gp_rulesnew'],
			'jointype' => $_G['gp_jointypenew'],
            "group_type"=> $_G["gp_group_type"],
            "stattion"=>$station_input_id,
            "stattion_names"=>$station_input
		));
		
		DB::update('forum_forumfield', $groupdata, "fid='$fid'");
        if($_G[gp_group_type]==20 && !empty($station_input)){
            //拉人
            misc_enstation($fid, $station_input_id);
            
            require_once libfile("function/org");
            $station_input_id = explode(",", $station_input_id);
            foreach($station_input_id as $value){
                $result = org_station_by_user($value);
                foreach($result as $item){
                    group_add_user_to_group($item["id"], $item["regName"], $fid);
                }
            }
        }
        
		if($_G['gp_fupnew']) {
			$fupsql = ", fup = '$_G[gp_fupnew]'";
		}
		if($_G['gp_namenew'] && $_G['gp_namenew'] != $group['name'] && DB::result(DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='$_G[gp_namenew]'"), 0)) {
			cpmsg('group_name_exist', 'action=group&operation=editgroup&fid='.$fid, 'error');
		}
        $orgid = $_POST["orgname_input_id"];
        if($_G["gp_group_type"]!=1){
            $orgid = 0;
        }
        if($_G["gp_group_type"]==1 && $orgid!=0){
             //拉人操作
			require_once libfile("function/org");
			require_once libfile("function/group");
			//删除原专区除群主外的用户
			$query = DB::query("SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$fid);
            $forum = DB::fetch($query);
            
			 if(empty($forum["org_id"]) || $forum["org_id"]==0 || $forum["org_id"]!=$orgid){
			 	//只有当执行了机构修改后，才可以删除机构专区下面的人，王聪
			 	delete_groupuser_fid($fid);
			 }
			
			
			/*
			 * 取消龙泰写的拉用户的操作的操作
			 * 王聪
			 */
//			echo ("<script>");
//			echo ("var x = new Ajax();");
//			echo ("var url = '/forum/api/sso/putgroupuser_link.php?fid=".$fid."&orgId=".$orgid."';");
//			echo ("x.get(url, function(s, x) {});");
//			echo ("</script>");

            //$result = getUserByOrg($orgid);
            //if($result[0]!="没有查找到数据"){
			//	foreach($result as $item){
			//		group_add_user_to_group($item["id"], $item["regName"], $fid);
			//	}
			//}
            
            if(empty($forum["org_id"]) || $forum["org_id"]==0 || $forum["org_id"]!=$orgid){
                require_once libfile("function/misc");
                misc_esngrouporg($fid, $orgid);
            }
            require_once libfile("function/label");
            $data = listlabelsbygroupid($fid);
            $tags = array("机构");
            foreach($data as $d){
                $tags[] = $d;
            }
            //更新tag
            group_add_tag($fid, $tags);
            group_remov_tag($fid, array("业务"));
            DB::query("UPDATE ".DB::table("forum_forumfield")." SET jointype=3 WHERE fid=".$fid);
        }
        if($_G["gp_group_type"]==20){
            require_once libfile("function/label");
            $data = listlabelsbygroupid($fid);
            $tags = array("业务");
            foreach($data as $d){
                $tags[] = $d;
            }
            group_add_tag($fid, $tags);
            group_remov_tag($fid, array("机构"));
        }
        if($_G["gp_group_type"]!=20 && $_G["gp_group_type"]!=1){
            group_remov_tag($fid, array("业务", "机构"));
        }
        //加精积分digestgroup
        if ($_G[gp_dignew]) {
        	require_once libfile('function/credit');
        	credit_create_credit_log($group['founderuid'], 'digestgroup', $fid);
        }
		DB::query("UPDATE ".DB::table('forum_forum')." SET name = '$_G[gp_namenew]', org_id=$orgid, digest = '$_G[gp_dignew]', displayorder = '$_G[gp_ordernew]'$fupsql WHERE fid='$fid'");
		cpmsg('group_edit_succeed', 'action=group&operation=editgroup&fid='.$fid, 'succeed');
	}
} elseif($operation == 'deletegroup') {
	$fidarray = $_G['gp_fidarray'];
	if(submitcheck('confirmed', 1)){
		$fidarray = explode(',', $fidarray);
		foreach ($fidarray as $delfid) {
			$fuid[$delfid]=get_uid_by_fid($delfid);
		}
		delete_groupimg($fidarray);
		require_once libfile('function/post');
		$tids = $nums = array();
		$pp = 100;
		$start = intval($_G['gp_start']);
		$query = DB::query("SELECT fup FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
		while($fup = DB::fetch($query)) {
			$nums[$fup['fup']] ++;
		}
		foreach($nums as $fup => $num) {
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum = groupnum+(-$num) WHERE fid='$fup'");
		}
		$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid IN(".dimplode($fidarray).") ORDER BY tid LIMIT $start, $pp");
		while($thread = DB::fetch($query)) {
			$tids[] = $thread['tid'];
		}
		if($tids) {
			$tids = implode(',', $tids);
			require_once libfile('function/delete');
			deletepost("tid IN($tids)");
			cpmsg('group_thread_removing', 'action=group&operation=deletegroup&submit=yes&confirmed=yes&fidarray='.$_G['gp_fidarray'].'&start='.($start + $pp));
		}
		foreach(array('forum_thread', 'forum_post', 'forum_forumrecommend', 'forum_postposition') as $value) {
			DB::query("DELETE FROM ".DB::table($value)." WHERE fid IN(".dimplode($fidarray).")", 'UNBUFFERED');
		}
		//修改专区标签start
		if(!empty($fidarray)){
			require_once libfile("function/label");
			foreach($fidarray as $key1=>$value1){
				$labels=listlabelsbygroupoffu($value1);
				if(!empty($labels)){
					foreach($labels as $key2=>$value2){
						deletelabelgroup($value1,$value2);   
					}
				}			
			}
		}
		//end
		DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
		DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid IN(".dimplode($fidarray).")");
		DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE fid IN(".dimplode($fidarray).")");
        DB::query("DELETE FROM ".DB::table('common_group_plugin')." WHERE fid IN(".dimplode($fidarray).")");
        DB::query("DELETE FROM ".DB::table('group_empirical_values')." WHERE fid IN(".dimplode($fidarray).")");
		DB::query("DELETE FROM ".DB::table('common_plugin_category')." WHERE fid IN(".dimplode($fidarray).")");
		DB::query("DELETE FROM ".DB::table('common_category')." WHERE fid IN(".dimplode($fidarray).")");
		updatecache('grouptype');
		
		//积分
		require_once libfile('function/credit'); 
		foreach ($fidarray as $delfid) {
			credit_create_credit_log($fuid[$delfid]['founderuid'], 'deletegroup', $delfid); 
		}
		
		cpmsg('group_delete_succeed', 'action=group&operation=manage', 'succeed');
	}
	if($fidarray) {
		if(is_array($fidarray)) {
			$fidarray = implode(',', $fidarray);
		}
		cpmsg('group_delete_confirm', 'action=group&operation=deletegroup&submit=yes', 'form', array(), '<input type="hidden" name="fidarray" value="'.$fidarray.'">');
	} else {
		cpmsg('group_delete_no_choice', '', 'error');
	}
} elseif($operation == 'userperm') {
	$group_userperm = unserialize($_G['setting']['group_userperm']) ? unserialize($_G['setting']['group_userperm']) : array();
	if(!submitcheck('permsubmit')) {
		shownav('group', 'nav_group_userperm');
		$varname = array('newgroup_userperm', array(), 'isfloat');
		showsubmenu(cplang('nav_group_userperm').' - '.cplang('group_userperm_moderator'));
		showformheader("group&operation=userperm&id=$id");
		showtableheader();
		$varname[1] = array(
		 	array('allowstickthread', cplang('admingroup_edit_stick_thread'), '1'),
		 	array('allowbumpthread', cplang('admingroup_edit_bump_thread'), '1'),
		 	array('allowhighlightthread', cplang('admingroup_edit_highlight_thread'), '1'),
		 	array('allowstampthread', cplang('admingroup_edit_stamp_thread'), '1'),
		 	array('allowclosethread', cplang('admingroup_edit_close_thread'), '1'),
		 	array('allowmergethread', cplang('admingroup_edit_merge_thread'), '1'),
		 	array('allowsplitthread', cplang('admingroup_edit_split_thread'), '1'),
		 	array('allowrepairthread', cplang('admingroup_edit_repair_thread'), '1'),
		 	array('allowrefund', cplang('admingroup_edit_refund'), '1'),
		 	array('alloweditpoll', cplang('admingroup_edit_edit_poll'), '1'),
		 	array('allowremovereward', cplang('admingroup_edit_remove_reward'), '1'),
		 	array('alloweditactivity', cplang('admingroup_edit_edit_activity'), '1'),
		 	array('allowedittrade', cplang('admingroup_edit_edit_trade'), '1'),
		 );
		showtitle('admingroup_edit_threadperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		showsetting('admingroup_edit_digest_thread', array('newgroup_userperm[allowdigestthread]', array(
			array(0, cplang('admingroup_edit_digest_thread_none')),
			array(1, cplang('admingroup_edit_digest_thread_1')),
			array(2, cplang('admingroup_edit_digest_thread_2')),
			array(3, cplang('admingroup_edit_digest_thread_3')),
		)), $group_userperm['allowdigestthread'], 'mradio');

		$varname[1] = array(
		 	array('alloweditpost', cplang('admingroup_edit_edit_post'), '1'),
		 	array('allowwarnpost', cplang('admingroup_edit_warn_post'), '1'),
		 	array('allowbanpost', cplang('admingroup_edit_ban_post'), '1'),
		 	array('allowdelpost', cplang('admingroup_edit_del_post'), '1'),
		 );
		showtitle('admingroup_edit_postperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		$varname[1] = array(
		 	array('allowupbanner', cplang('group_userperm_upload_banner'), '1'),
		 );
		showtitle('admingroup_edit_modcpperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		$varname[1] = array(
		 	array('disablepostctrl', cplang('admingroup_edit_disable_postctrl'), '1'),
		 	array('allowviewip', cplang('admingroup_edit_view_ip'), '1')
		 );
		showtitle('group_userperm_others');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		showtablefooter();
		echo '</td></tr>';
		showtagfooter('tbody');
		showsubmit('permsubmit', 'submit');
		showtablefooter();
		showformfooter();
	} else {
		$default_perm = array('allowstickthread' => 0, 'allowbumpthread' => 0, 'allowhighlightthread' => 0, 'allowstampthread' => 0, 'allowclosethread' => 0, 'allowmergethread' => 0, 'allowsplitthread' => 0, 'allowrepairthread' => 0, 'allowrefund' => 0, 'alloweditpoll' => 0, 'allowremovereward' => 0, 'alloweditactivity' => 0, 'allowedittrade' => 0, 'allowdigestthread' => 0, 'alloweditpost' => 0, 'allowwarnpost' => 0, 'allowbanpost' => 0, 'allowdelpost' => 0, 'allowupbanner' => 0, 'disablepostctrl' => 0, 'allowviewip' => 0);
		$_G['gp_newgroup_userperm'] = array_merge($default_perm, $_G['gp_newgroup_userperm']);
		if(serialize($_G['gp_newgroup_userperm']) != serialize($group_userperm)) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('group_userperm', '".daddslashes(serialize($_G['gp_newgroup_userperm']))."')");
			updatecache('setting');
		}
		cpmsg('group_userperm_succeed', 'action=group&operation=userperm', 'succeed');
	}
} elseif($operation == 'label'){
	shownav('group','nav_group_label');
	showsubmenu('nav_group_label');
?>
<script type="text/JavaScript">
var rowtypedata = [
	[ [3, '<input name="newcat[]" value="<?=$lang[groups_label_name]?>" size="20" type="text" class="txt" onclick="javascript:hiddernvalue(this);" />']],
	[ [3, '<div class="board"><input name="newforum[{1}][]" value="<?=$lang[groups_label_sub_add]?>" size="20" type="text" class="txt" onclick="javascript:hiddernvalue(this);" /></div>']],
	
];

function hiddernvalue(obj){
	obj.value = "" ;
}
</script>	
<?
	if(!submitcheck('editsubmit'))
	{
		showformheader('group&operation=label');
		showtableheader('');
		
		$forums = $showedforums = array();
		
		$op = $_G['op'];
		if(empty($op)){
			$op = 'search';
		}
		
		if($op=='search')
		{
			$sql = "SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.forumcolumns, f.inheritedmod, ff.moderators, ff.password, ff.redirect, ff.groupnum
			FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type = 'label' ";
			
			$name = $_G['name'];
			if(!empty($name)){
				$sql .= " and f.name like '%"+$name+"%'";
			}
			$sql .= "ORDER BY  f.displayorder";
			$query = DB::query($sql);
			$labels = $toplabels = array();
			while($forum=DB::fetch($query)){
				if($forum['type']=='label')
				{
					if($forum[fup]=='0'){
						$toplabels[$forum[fid]] = $forum;
					}else{
						$labels[$forum[fup]][] = $forum;
					}
				}
			}

			foreach($toplabels as $id => $tlabel){
				
				showlabel($tlabel,'');
				$index = 1;
				foreach($labels as $lid => $label){					
					if($lid==$id){
						foreach($label as $subid => $sublabel){			
							if($index%4==1)echo '<tr>';
							showlabel($sublabel,'label');						
							if($index%4==0)echo '</tr>' ;
							$index ++ ;
						}
					}
				}
				
				showlabel($tlabel, '', 'lastbroad');
			}
		
		showlabel($label, '', 'lastlabel');
		
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();		
		}
				
	}else{
		$newcat = $_G['gp_newcat'];
		$newforum = $_G['gp_newforum'];
		$errormsg = array();
		
		if(is_array($newcat)){
			foreach($newcat as $id => $forumname){
				if(empty($forumname)) {
					continue;
				}
				include_once libfile('function/label'); 
				$labelid=getlabelbyname($forumname);
				if($labelid){
					//showmessage("'".$forumname."'标签分类已经存在,不能提交重复的分类","admin.php?frames=yes&action=group&operation=label");
					//cpmsg("'".$forumname."'标签分类已经存在,不能提交重复的分类", 'action=group&operation=label', 'succeed');
					$errormsg[]=$forumname;
					continue;
				}
				$fid = DB::insert('forum_forum', array('type' => 'label', 'name' => $forumname, 'status' => 3), 1);
				DB::insert('forum_forumfield', array('fid' => $fid));
			}	
		}
		if(is_array($newforum)){
			foreach($newforum as $parentid => $sublabels){
				if(empty($sublabels)) {
					continue;
				}
				
				if(is_array($sublabels)){
					foreach($sublabels as $k => $forumname){
						if(empty($forumname)) {
							continue;
						}
						include_once libfile('function/label'); 
						$labelid=getlabelbyname($forumname);
						if($labelid){
							//showmessage("'".$forumname."'标签已经存在，不能提交重复的标签","admin.php?frames=yes&action=group&operation=label");
							//cpmsg("'".$forumname."'标签已经存在，不能提交重复的标签", 'action=group&operation=label', 'succeed');
							$errormsg[]=$forumname;
							continue;
						}
						$fid = DB::insert('forum_forum', array('type' => 'label', 'name' => $forumname, 'status' => 3,'fup' => $parentid), 1);
						DB::insert('forum_forumfield', array('fid' => $fid));
					}
				}
			}
		}
		
		updatecache('grouptype');
		if($errormsg){
			cpmsg("'".implode("','",$errormsg)."'已经存在，不能提交重复的标签或分类。", 'action=group&operation=label', 'succeed');
		}else{
			cpmsg('grouplabel_update_succeed', 'action=group&operation=label', 'succeed');
		}
		
	}

}elseif($operation == 'deletelabel'){
	$fid = $_G['gp_fid'];
	$ajax = $_G['gp_ajax'];
	$confirmed = $_G['gp_confirmed'];
	$finished = $_G['gp_finished'];
	$total = intval($_G['gp_total']);
	$pp = intval($_G['gp_pp']);
	$currow = intval($_G['gp_currow']);
	if($ajax) {
		ob_end_clean();
		require_once libfile('function/post');
		

		if($currow + $pp > $total) {
			DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$fid' OR fup ='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid not in (select fid from".DB::table('forum_forum').")");
			DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_access')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
			echo 'TRUE';
			exit;
		}

		echo 'GO';
		exit;

	} else {
		if($finished) {
			updatecache('grouptype');
			cpmsg('grouplabel_delete_succeed', 'action=group&operation=label', 'succeed');

		}

		if(!$confirmed) {

			cpmsg('grouplabel_delete_confirm', "action=group&operation=deletelabel&fid=$fid", 'form');

		} else {

			$threads = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE fid='$fid'");

			cpmsg('grouplabel_delete_alarm', "action=group&operation=deletelabel&fid=$fid&confirmed=1", 'loadingform', array(), '<div id="percent">0%</div>', FALSE);
			echo "
			<div id=\"statusid\" style=\"display:none\"></div>
			<script type=\"text/JavaScript\">
				var xml_http_building_link = '".cplang('xml_http_building_link')."';
				var xml_http_sending = '".cplang('xml_http_sending')."';
				var xml_http_loading = '".cplang('xml_http_loading')."';
				var xml_http_load_failed = '".cplang('xml_http_load_failed')."';
				var xml_http_data_in_processed = '".cplang('xml_http_data_in_processed')."';
				var adminfilename = '".ADMINSCRIPT."';
				function forumsdelete(url, total, pp, currow) {

					var x = new Ajax('HTML', 'statusid');
					x.get(url+'&ajax=1&pp='+pp+'&total='+total+'&currow='+currow, function(s) {
						if(s != 'GO') {
							location.href = adminfilename + '?action=group&operation=deletelabel&finished=1';
						}

						currow += pp;
						var percent = ((currow / total) * 100).toFixed(0);
						percent = percent > 100 ? 100 : percent;
						document.getElementById('percent').innerHTML = percent+'%';
						document.getElementById('percent').style.backgroundPosition = '-'+percent+'%';

						if(currow < total) {
							forumsdelete(url, total, pp, currow);
						}
					});
				}
				forumsdelete(adminfilename + '?action=group&operation=deletelabel&fid=$fid&confirmed=1', $threads, 2000, 0);
			</script>
			";
		}
	}
}elseif($operation == 'level') {
	$levelid = !empty($_G['gp_levelid']) ? intval($_G['gp_levelid']) : 0;
	if(empty($levelid)) {
		$grouplevels = '';
		if(!submitcheck('grouplevelsubmit')) {
			$query = DB::query("SELECT * FROM ".DB::table('forum_grouplevel')." WHERE 1 ORDER BY creditslower");
			while($level = DB::fetch($query)) {
				$grouplevels .= showtablerow('', array('class="td25"', '', 'class="td28"', 'class=td28'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$level[levelid]]\" value=\"$level[levelid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"levelnew[$level[levelid]][leveltitle]\" value=\"$level[leveltitle]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"levelnew[$level[levelid]][creditshigher]\" value=\"$level[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"levelnew[$level[levelid]][creditslower]\" value=\"$level[creditslower]\" disabled />",
					"<a href=\"".ADMINSCRIPT."?action=group&operation=level&levelid=$level[levelid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="levelnewadd[leveltitle][]">'],
		[1,'<input type="text" class="txt" size="6" name="levelnewadd[creditshigher][]">', 'td28'],
		[4,'']
	],
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="leveltitlenewadd[]">'],
		[1,'<input type="text" class="txt" size="2" name="creditshighernewadd[]">', 'td28'],
		[4, '']
	]
];
</script>
EOT;
			shownav('group', 'nav_group_level');
			showtips('group_level_tips');

			showformheader('group&operation=level');
			showtableheader('group_level', 'fixpadding', 'id="grouplevel"');
			showsubtitle(array('', 'group_level_title', 'group_level_creditsrange', ''));
			echo $grouplevels;
			echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['group_level_add'].'</a></div></td></tr>';
			showsubmit('grouplevelsubmit');
			showtablefooter();
			showformfooter();
		} else {
			$levelnewadd = $levelnewkeys = $orderarray = array();
			$maxlevelid = 0;
			if(!empty($_G['gp_levelnewadd'])) {
				$levelnewadd = array_flip_keys($_G['gp_levelnewadd']);
				foreach($levelnewadd as $k => $v) {
					if(!$v['leveltitle'] || !$v['creditshigher']) {
						unset($levelnewadd[$k]);
					}
				}
			}
			if(!empty($_G['gp_levelnew'])) {
				$levelnewkeys = array_keys($_G['gp_levelnew']);
				$maxlevelid = max($levelnewkeys);
			}

			foreach($levelnewadd as $k=>$v) {
				$_G['gp_levelnew'][$k+$maxlevelid+1] = $v;
			}
			if(is_array($_G['gp_levelnew'])) {
				foreach($_G['gp_levelnew'] as $id => $level) {
					if((is_array($_G['gp_delete']) && in_array($id, $_G['gp_delete'])) || ($id == 0 && (!$level['grouptitle'] || $level['creditshigher'] == ''))) {
						unset($_G['gp_levelnew'][$id]);
					} else {
						$orderarray[$level['creditshigher']] = $id;
					}
				}
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
			foreach($_G['gp_levelnew'] as $id => $level) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('group_level_update_credits_duplicate', '', 'error');
				}
				if(in_array($id, $levelnewkeys)) {
					DB::query("UPDATE ".DB::table('forum_grouplevel')." SET leveltitle='$level[leveltitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew' WHERE levelid='$id'");

				} elseif($level['leveltitle'] && $level['creditshigher'] != '') {
					$data = array(
						'leveltitle' => $level['leveltitle'],
						'type' => 'default',
						'creditshigher' => $creditshighernew,
						'creditslower' => $creditslowernew,
					);

					$newlevelid = DB::insert('forum_grouplevel', $data, 1);
				}
			}
			if($ids = dimplode($_G['gp_delete'])) {
				$levelcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_grouplevel'));
				if(count($_G['gp_delete']) == $levelcount) {
					updatecache('grouplevels');
					cpmsg('group_level_succeed_except_all_levels', 'action=group&operation=level', 'succeed');

				}
				DB::query("DELETE FROM ".DB::table('forum_grouplevel')." WHERE levelid IN ($ids)");
			}
			updatecache('grouplevels');
			cpmsg('group_level_update_succeed', 'action=group&operation=level', 'succeed');
		}
	} else {
		$grouplevel = DB::fetch_first("SELECT * FROM ".DB::table('forum_grouplevel')." WHERE levelid='$levelid'");
		if(empty($grouplevel)) {
			cpmsg('group_level_noexist', 'action=group&operation=level', 'error');
		}
		$group_creditspolicy = unserialize($grouplevel['creditspolicy']) ? unserialize($grouplevel['creditspolicy']) : array();
		$group_postpolicy = unserialize($grouplevel['postpolicy']) ? unserialize($grouplevel['postpolicy']) : array();
		$specialswitch = unserialize($grouplevel['specialswitch']) ? unserialize($grouplevel['specialswitch']) : array();
		if(!submitcheck('editgrouplevel')) {
			shownav('group', 'nav_group_level');
			showtips('group_level_tips');
			showsubmenu('nav_group_level_editor');
			showformheader('group&operation=level&levelid='.$levelid, 'enctype');
			showtableheader();
			showtitle('groups_setting_basic');
			showsetting('group_level_title', 'levelnew[leveltitle]', $grouplevel['leveltitle'], 'text');
			if($grouplevel['icon']) {
				$valueparse = parse_url($grouplevel['icon']);
				if(isset($valueparse['host'])) {
					$grouplevelicon = $grouplevel['icon'];
				} else {
					$grouplevelicon = $_G['setting']['attachurl'].'common/'.$grouplevel['icon'].'?'.random(6);
				}
				$groupleveliconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon[{$grouplevel[levelid]}]" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$grouplevelicon.'" />';
			}
			showsetting('group_level_icon', 'iconnew', $grouplevel['icon'], 'filetext', '', 0, $groupleveliconhtml);

			showtitle('group_level_credits');
			$varname = array('levelnew[creditspolicy]', array(), 'isfloat');
			$varname[1] = array(
			 	array('post', cplang('group_level_credits_post'), '1'),
			 	array('reply', cplang('group_level_credits_reply'), '1'),
			 	array('digest', cplang('group_level_credits_digest'), '1'),
			 	array('postattach', cplang('group_level_credits_upload'), '1'),
			 	array('getattach', cplang('group_level_credits_download'), '1'),
			 	array('tradefinished', cplang('group_level_credits_trade'), '1'),
			 	array('joinpoll', cplang('group_level_credits_poll'), '1'),
			 );
			showsetting('', $varname, $group_creditspolicy, 'omcheckbox');
			showtitle('group_level_posts');
			$varname = array('levelnew[postpolicy]', array(), 'isfloat');
			$varname[1] = array(
			 	array('alloweditpost', cplang('forums_edit_posts_alloweditpost'), '1'),
			 	array('recyclebin', cplang('forums_edit_posts_recyclebin'), '1'),
			 	array('allowsmilies', cplang('forums_edit_posts_smilies'), '1'),
			 	array('allowhtml', cplang('forums_edit_posts_html'), '1'),
			 	array('allowbbcode', cplang('forums_edit_posts_bbcode'), '1'),
			 	array('allowanonymous', cplang('forums_edit_posts_anonymous'), '1'),
			 	array('jammer', cplang('forums_edit_posts_jammer'), '1'),
			 	array('allowimgcode', cplang('forums_edit_posts_imgcode'), '1'),
			 	array('allowmediacode', cplang('forums_edit_posts_mediacode'), '1'),
			 );
			showsetting('', $varname, $group_postpolicy, 'omcheckbox');

			showsetting('forums_edit_posts_allowpostspecial', array('levelnew[postpolicy][allowpostspecial]', array(
				cplang('thread_poll'),
				cplang('thread_trade'),
				cplang('thread_reward'),
				cplang('thread_activity'),
				cplang('thread_debate')
			)), $group_postpolicy['allowpostspecial'], 'binmcheckbox');
			showsetting('forums_edit_posts_attach_ext', 'levelnew[postpolicy][attachextensions]', $group_postpolicy['attachextensions'], 'text');

			showtitle('group_level_special');
			showsetting('group_level_special_allowchangename', 'specialswitchnew[allowchangename]', $specialswitch['allowchangename'], 'radio');
			showsetting('group_level_special_allowchangetype', 'specialswitchnew[allowchangetype]', $specialswitch['allowchangetype'], 'radio');
			showsetting('group_level_special_allowclose', 'specialswitchnew[allowclosegroup]', $specialswitch['allowclosegroup'], 'radio');
			showsetting('group_level_special_allowthreadtype', 'specialswitchnew[allowthreadtype]', $specialswitch['allowthreadtype'], 'radio');
			showsetting('group_level_special_membermax', 'specialswitchnew[membermaximum]', $specialswitch['membermaximum'], 'text');

			showsubmit('editgrouplevel');
			showtablefooter();
			showformfooter();
		} else {
			$dataarr = array();
			$levelnew = $_G['gp_levelnew'];
			$dataarr['leveltitle'] = daddslashes($levelnew['leveltitle']);
			$dataarr['creditspolicy'] = daddslashes(serialize($levelnew['creditspolicy']));

			$default_postpolicy = array('alloweditpost' => 0, 'recyclebin' => 0, 'allowsmilies' => 0, 'allowhtml' => 0, 'allowbbcode' => 0, 'allowanonymous' => 0, 'jammer' => 0, 'allowimgcode' => 0, 'allowmediacode' => 0);
			$levelnew['postpolicy'] = array_merge($default_postpolicy, $levelnew['postpolicy']);

			$levelnew['postpolicy']['allowpostspecial'] = bindec(intval($levelnew['postpolicy']['allowpostspecial'][6]).intval($levelnew['postpolicy']['allowpostspecial'][5]).intval($levelnew['postpolicy']['allowpostspecial'][4]).intval($levelnew['postpolicy']['allowpostspecial'][3]).intval($levelnew['postpolicy']['allowpostspecial'][2]).intval($levelnew['postpolicy']['allowpostspecial'][1]));

			$dataarr['postpolicy'] = daddslashes(serialize($levelnew['postpolicy']));
			$dataarr['specialswitch']['membermaximum'] = intval($dataarr['specialswitch']['membermaximum']);
			$dataarr['specialswitch'] = daddslashes(serialize($_G['gp_specialswitchnew']));
			if($_G['gp_deleteicon']) {
				@unlink($_G['setting']['attachurl'].'common/'.$grouplevel['icon']);
				$dataarr['icon'] = '';
			} else {
				if($_FILES['iconnew']) {
					$data = array('extid' => "$levelid");
					$dataarr['icon'] = upload_icon_banner($data, $_FILES['iconnew'], 'grouplevel_icon');
				} else {
					$dataarr['icon'] = $_G['gp_iconnew'];
				}
			}
			DB::update('forum_grouplevel', $dataarr, array('levelid' => $levelid));
			updatecache('grouplevels');
			cpmsg('groups_setting_succeed', 'action=group&operation=level&levelid='.$levelid, 'succeed');
		}
	}
} elseif($operation == 'mergetype') {
	require_once libfile('function/group');
	$fid = $_G['gp_fid'];
	$sourcetype = DB::fetch_first("SELECT f.name, ff.groupnum FROM ".DB::table('forum_forum')." as f LEFT JOIN ".DB::table('forum_forumfield')." as ff ON ff.fid=f.fid WHERE f.fid='$fid'");
	shownav('group', 'nav_group_type');
	showsubmenu(cplang('nav_group_type').' - '.cplang('group_mergetype').' - '.$sourcetype['name']);
	if(!submitcheck('mergesubmit', 1)) {
		$groupselect = get_groupselect(0, 0, 0);
		showformheader("group&operation=mergetype&fid=$fid", 'enctype');
		showtableheader();
		showsetting('group_mergetype_selecttype', '', '', '<select name="mergefid">'.$groupselect.'</select>');
		showsubmit('mergesubmit');
		showtablefooter();
		showformfooter();
	} else {
		$mergefid = $_G['gp_mergefid'];
		if(empty($_G['gp_confirm'])) {
			cpmsg('group_mergetype_confirm', 'action=group&operation=mergetype&fid='.$fid.'&mergesubmit=yes&confirm=1', 'form', array(), '<input type="hidden" name="mergefid" value="'.$mergefid.'">');
		}
		if($mergefid == $fid) {
			cpmsg('group_mergetype_target_error', 'action=group&operation=mergetype&fid='.$fid, 'error');
		}
		DB::query("UPDATE ".DB::table('forum_forum')." SET fup=$mergefid WHERE fup='$fid'");
		DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
		DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum=groupnum+$sourcetype[groupnum] WHERE fid='$mergefid'");
		updatecache('grouptype');
		cpmsg('group_mergetype_succeed', 'action=group&operation=type');
	}
} else if($operation=="weight"){
    showsubmenu('专区活跃度权重设置');
    if(!submitcheck('savesubmit', 1)) {
        require_once libfile("function/misc");
        $config = misc_get_forum_hot_config();
        showformheader("group&operation=weight", 'enctype');
		showtableheader();
        showsetting('主题数比例(小数)', 'threads', $config["threads"], 'text', 0, 0, '请输入小数');
        showsetting('访问数比例(小数)', 'viewnum', $config["viewnum"], 'text', 0, 0, '请输入小数');
        showsetting('回贴数比例(小数)', 'replys', $config["replys"], 'text', 0, 0, '请输入小数');
        showsetting('成员数比例(小数)', 'members', $config["members"], 'text', 0, 0, '请输入小数');
		showsubmit('savesubmit');
		showtablefooter();
		showformfooter();
    }else{
        $config[threads] = $_POST["threads"];
        $config[viewnum] = $_POST["viewnum"];
        $config[replys] = $_POST["replys"];
        $config[members] = $_POST["members"];
        $oldconfig = misc_get_forum_hot_config();
        if($config[threads]!=$oldconfig[threads]
                || $config[viewnum]!=$oldconfig[viewnum]
                || $config[replys]!=$oldconfig[replys]
                || $config[members]!=$oldconfig[members]){
            DB::query("DELETE FROM ".DB::table("commmon_config")." WHERE `key`='threadhotprice'");
            DB::insert("commmon_config", array(key=>"threadhotprice", value=>  serialize($config)));
            require_once libfile("function/group");
            group_update_forum_hot($config);
        }
        cpmsg('保存成功', 'action=group&operation=weight');
    }
}

function showgroup(&$forum, $type = '', $last = '') {
	global $_G;

	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = $fcolumns = '';
		$fcolumns = ' '.cplang('groups_type_show_rows').'<input type="text" name="forumcolumnsnew['.$forum['fid'].']" value="'.$forum['forumcolumns'].'" class="txt" style="width: 30px;" />';

		if(!$forum['status']  || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
			$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
			$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.htmlspecialchars($forum['name']).'" class="txt" />&nbsp;'.$fcolumns.'</div>'.$boardattr.
			'</td>
			<td>'.($type != 'group' ? $forum['groupnum'] : '').'</td>
			<td><a href="'.ADMINSCRIPT.'?action=group&operation=deletetype&fid='.$forum['fid'].'" title="'.cplang('groups_type_delete').'" class="act">'.cplang('delete').'</a>';
		$return .= $type != 'group' ? ' &nbsp;|&nbsp; <a href="'.ADMINSCRIPT.'?action=group&operation=manage&submit=yes&selectgroupid[]='.$forum['fid'].'" class="act">'.cplang('groups_type_search').'</a> &nbsp;|&nbsp; <a href="'.ADMINSCRIPT.'?action=group&operation=mergetype&fid='.$forum['fid'].'" class="act">'.cplang('group_mergetype').'</a>' : '';
		$return .= '</td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('groups_type_sub_new').'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('groups_type_level_1_add').'</a></div></td></tr>';
		}
	}
	echo $return;
	return $forum['fid'];
}

function searchgroups($submit) {
	global $_G;
	require_once libfile('function/group');
	empty($_G['gp_selectgroupid']) && $_G['gp_selectgroupid'] = array();
	$groupselect = get_groupselect(0, $_G['gp_selectgroupid'], 0);
	$monthselect = $dayselect = $birthmonth = $birthday = '';
	for($m=1; $m<=12; $m++) {
		$m = sprintf("%02d", $m);
		$monthselect .= "<option value=\"$m\" ".($birthmonth == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d=1; $d<=31; $d++) {
		$d = sprintf("%02d", $d);
		$dayselect .= "<option value=\"$d\" ".($birthday == $d ? 'selected' : '').">$d</option>\n";
	}

	showtagheader('div', 'searchgroups', !$submit);
	echo '<script src="static/js/forum_calendar.js" type="text/javascript"></script>';
	showformheader("group&operation=manage");
	showtableheader();
	showsetting('groups_manage_name', 'srchname', $srchname, 'text');
	showsetting('groups_manage_id', 'srchfid', $srchfid, 'text');
	showsetting('groups_editgroup_category', '', '', '<select name="selectgroupid[]" multiple="multiple" size="10"><option value="all"'.(in_array('all', $_G['gp_selectgroupid']) ? ' selected' : '').'>'.cplang('unlimited').'</option>'.$groupselect.'</select>');
	showsetting('groups_manage_membercount', array('memberhigher', 'memberlower'), array($_G['gp_memberhigher'], $_G['gp_memberlower']), 'range');
	showsetting('groups_manage_threadcount', array('threadshigher', 'threadslower'), array($threadshigher, $threadslower), 'range');
	showsetting('groups_manage_replycount', array('postshigher', 'postslower'), array($postshigher, $postslower), 'range');
	showsetting('groups_manage_createtime', array('datelineafter', 'datelinebefore'), array($datelineafter, $datelinebefore), 'daterange');
	showsetting('groups_manage_updatetime', array('lastupdateafter', 'lastupdatebefore'), array($lastupdateafter, $lastupdatebefore), 'daterange');
	showsetting('groups_manage_founder', 'srchfounder', $srchfounder, 'text');
	showsetting('groups_manage_founder_uid', 'srchfounderid', $srchfounderid, 'text');

	showtagfooter('tbody');
	showsubmit('submit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
}

function countgroups() {
	global $_G;
	$_G['setting']['group_perpage'] = 20;//change 100 to 20 by fumz，2010-11-24 15:56:47
	$page = $_G['gp_page'] ? $_G['gp_page'] : 1;
	$start_limit = ($page - 1) * $_G['setting']['group_perpage'];
	$dateoffset = date('Z') - ($_G['setting']['timeoffset'] * 3600);
	$username = trim($username);

	$conditions = 'f.type=\'sub\' AND f.status=\'3\'';
	if($_G['gp_srchname'] != '') {
		$srchname = explode(',', $_G['gp_srchname']);
		foreach($srchname as $u) {
			$srchnameary[] = " f.name LIKE '".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $u)."'";
		}
		$conditions .= " AND (".implode(' OR ', $srchnameary).")";
	}

	$conditions .= intval($_G['gp_srchfid']) ? " AND f.fid='".intval($_G['gp_srchfid'])."'" : '';
	$conditions .= !empty($_G['gp_selectgroupid']) && !in_array('all', $_G['gp_selectgroupid']) != '' ? " AND f.fup IN ('".implode('\',\'', $_G['gp_selectgroupid'])."')" : '';

	$conditions .= $_G['gp_postshigher'] != '' ? " AND f.posts>'$_G[gp_postshigher]'" : '';
	$conditions .= $_G['gp_postslower'] != '' ? " AND f.posts<'$_G[gp_postslower]'" : '';

	$conditions .= $_G['gp_threadshigher'] != '' ? " AND f.threads>'$_G[gp_threadshigher]'" : '';
	$conditions .= $_G['gp_threadslower'] != '' ? " AND f.threads<'$_G[gp_threadslower]'" : '';

	$conditions .= $_G['gp_memberhigher'] != '' ? " AND ff.membernum<'".strtotime($_G['gp_memberhigher'])."'" : '';
	$conditions .= $_G['gp_memberlower'] != '' ? " AND ff.membernum>'".strtotime($_G['gp_memberlower'])."'" : '';

	$conditions .= $_G['gp_datelinebefore'] != '' ? " AND ff.dateline<'".strtotime($_G['gp_datelinebefore'])."'" : '';
	$conditions .= $_G['gp_datelineafter'] != '' ? " AND ff.dateline>'".strtotime($_G['gp_datelineafter'])."'" : '';

	$conditions .= $_G['gp_lastupbefore'] != '' ? " AND ff.lastupdate<'".strtotime($_G['gp_lastupbefore'])."'" : '';
	$conditions .= $_G['gp_lastupafter'] != '' ? " AND ff.lastupdate>'".strtotime($_G['gp_lastupafter'])."'" : '';

	if($_G['gp_srchfounder'] != '') {
		$srchfounder = explode(',', $_G['gp_srchfounder']);
		foreach($srchfounder as $fu) {
			$srchfnameary[] = " ff.foundername LIKE '".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $fu)."'";
		}
		$conditions .= " AND (".implode(' OR ', $srchfnameary).")";
	}

	$conditions .= intval($_G['gp_srchfounderid']) ? " AND ff.founderuid='".intval($_G['gp_srchfounderid'])."'" : '';


	if(!$conditions && !$uidarray && $operation == 'clean') {
		cpmsg('groups_search_invalid', '', 'error');
	}

	$urladd = "&srchname=".rawurlencode($_G['gp_srchname'])."&srchfid=".intval($_G['gp_srchfid'])."&postshigher=".rawurlencode($_G['gp_postshigher'])."&postslower=".rawurlencode($_G['gp_postslower'])."&threadshigher=".rawurlencode($_G['gp_threadshigher'])."&threadslower=".rawurlencode($_G['gp_threadslower'])."&memberhigher=".rawurlencode($_G['gp_memberhigher'])."&memberlower=".rawurlencode($_G['gp_memberlower'])."&datelinebefore=".rawurlencode($_G['gp_datelinebefore'])."&datelineafter=".rawurlencode($_G['gp_datelineafter'])."&lastupbefore=".rawurlencode($_G['gp_lastupbefore'])."&lastupafter=".rawurlencode($_G['gp_lastupafter'])."&srchfounderid=".rawurlencode($_G['gp_srchfounderid']);
	if(is_array($srchname)) {
		foreach($srchname as $gid => $value) {
			if($value != '') {
				$urladd .= '&srchname[]='.rawurlencode($value);
			}
		}
	}

	if(is_array($srchfounder)) {
		foreach($srchfounder as $gid => $value) {
			if($value != '') {
				$urladd .= '&srchfounder[]='.rawurlencode($value);
			}
		}
	}

	$groupnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid
		WHERE $conditions");
	return array($page, $start_limit, $groupnum, $conditions, $urladd);
}

function delete_groupimg($fidarray) {
	global $_G;
	if(!empty($fidarray)) {
		$query = DB::query("SELECT icon, banner FROM ".DB::table('forum_forumfield')." WHERE fid IN(".dimplode($fidarray).")");
		$imgdir = $_G['setting']['attachdir'].'/group/';
		while($group = DB::fetch($query)) {
			@unlink($imgdir.$group['icon']);
			@unlink($imgdir.$group['banner']);
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

function showlabel(&$label,$type,$last){
	if($last=='')
	{
		if($type== 'label'){
			$return = '<td><div class="lastboard">&nbsp;'.$label[name] .'&nbsp;('.$label[groupnum].')&nbsp;<a href="'.ADMINSCRIPT.'?action=group&operation=deletelabel&fid='.$label['fid'].'" title="'.cplang('groups_label_delete').'" class="act">'.cplang('delete').'</a></div></td>';
		}else if($type== ''){
			$return = '<tr><th><b>'.$label[name].'</b>&nbsp;<a href="'.ADMINSCRIPT.'?action=group&operation=deletelabel&fid='.$label['fid'].'" title="'.cplang('groups_label_delete').'" class="act">'.cplang('delete').'</a></th></tr>';
		}
	}else{
		if($last == 'lastlabel') {
			$return = '<tr><td colspan="3" align="left"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('groups_label_add').'</a></div></td></tr>';
		}if($last == 'lastbroad') {
			$return = '<tr><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$label[fid].')" class="addtr">'.cplang('groups_label_sub_add').'</a></div></td></tr>';
		}
	}
	
	
	echo $return;
}

?>