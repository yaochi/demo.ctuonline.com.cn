<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_moderation.php 11096 2010-05-24 00:33:26Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_G['tid'])) {
	$_G['gp_moderate'] = array($_G['tid']);
}

if(!defined('IN_DISCUZ') || CURMODULE != 'topicadmin') {
	exit('Access Denied');
}

$allow_operation = array('delete', 'category', 'release', 'highlight', 'open', 'close', 'stick', 'digest', 'bump', 'down', 'recommend', 'type', 'move', 'recommend_group');

$operations = empty($_G['gp_operations']) ? array() : $_G['gp_operations'];
if($operations && $operations != array_intersect($operations, $allow_operation) || (!$_G['group']['allowdelpost'] && in_array('delete', $operations)) || (!$_G['group']['allowstickthread'] && in_array('stick', $operations))) {
	showmessage('admin_moderate_invalid');
}

$threadlist = $loglist = array();
$operation = getgpc('operation');
$extratype=$_G['gp_extratype'];
loadcache('threadtableids');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}
if($tids = dimplode($_G['gp_moderate'])) {
	if($extratype=='class'){
		$query = DB::query("SELECT * FROM ".DB::table("extra_class")." WHERE id IN ($tids) LIMIT $_G[tpp]");
	}else if($extratype=='org'){
		$query = DB::query("SELECT * FROM ".DB::table("extra_org")." WHERE id IN ($tids) LIMIT $_G[tpp]");
	}else if($extratype=='lec'){
		$query = DB::query("SELECT * FROM ".DB::table("extra_lecture")." WHERE id IN ($tids) LIMIT $_G[tpp]");
	}
	while($thread = DB::fetch($query)) {
//		if($thread['closed'] > 1 && $operation && !in_array($operation, array('delete', 'highlight', 'stick', 'digest', 'bump', 'down'))) {
//			continue;
//		}
//		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
//		$thread['dblastpost'] = $thread['lastpost'];
//		$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
		$threadlist[$thread['id']] = $thread;
		$_G['tid'] = empty($_G['tid']) ? $thread['id'] : $_G['tid'];
	}
}

if(empty($threadlist)) {
	showmessage('admin_moderate_invalid11');
}

$modpostsnum = count($threadlist);
$single = $modpostsnum == 1 ? TRUE : FALSE;
$frommodcp = getgpc('frommodcp');
switch($frommodcp) {
	case '1':
		$_G['referer'] = "forum.php?mod=modcp&action=thread&fid=$_G[fid]&op=thread&do=".($frommodcp == 1 ? '' : 'list');
		break;
	case '2':
		$_G['referer'] = "forum.php?mod=modcp&action=forum&op=recommend".(getgpc('show') ? "&show=getgpc('show')" : '')."&fid=$_G[fid]";
		break;
	default:
		if($extratype=='class'){
			$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=extraresource&plugin_op=groupmenu";
		}elseif($extratype=='org'){
			$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=indexorg";
		}elseif($extratype=='lec'){
			$_G['referer'] = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=indexlec";
		}
		break;
}

$optgroup = $_G['gp_optgroup'] = isset($_G['gp_optgroup']) ? intval($_G['gp_optgroup']) : 0;
$listextra = $_G['gp_listextra'] = getgpc('listextra');
$expirationstick = getgpc('expirationstick');

$defaultcheck = array();
foreach ($allow_operation as $v) {
	$defaultcheck[$v] = '';
}
$defaultcheck[$operation] = 'checked="checked"';

if(!submitcheck('modsubmit')) {

	$stickcheck  = $closecheck = $digestcheck = array('', '', '', '', '');
	$expirationdigest = $expirationhighlight = $expirationclose = '';

	if($_G['gp_optgroup'] == 1 && $single) {
		empty($threadlist[$_G['tid']]['displayorder']) ? $stickcheck[0] ='selected="selected"' : $stickcheck[$threadlist[$_G['tid']]['displayorder']] = 'selected="selected"';
		empty($threadlist[$_G['tid']]['digest']) ? $digestcheck[0] = 'selected="selected"' : $digestcheck[$threadlist[$_G['tid']]['digest']] = 'selected="selected"';
		$string = sprintf('%02d', $threadlist[$_G['tid']]['highlight']);
		$stylestr = sprintf('%03b', $string[0]);
		for($i = 1; $i <= 3; $i++) {
			$stylecheck[$i] = $stylestr[$i - 1] ? 1 : 0;
		}
		$colorcheck = $string[1];
		$_G['forum']['modrecommend'] = is_array($_G['forum']['modrecommend']) ? $_G['forum']['modrecommend'] : array();
	} elseif($_G['gp_optgroup'] == 2) {
		require_once libfile('function/forumlist');
		$forumselect = forumselect(FALSE, 0, $single ? $threadlist[$_G['tid']]['fid'] : 0);
		$typeselect = typeselect($single ? $threadlist[$_G['tid']]['typeid'] : 0);
	} elseif($_G['gp_optgroup'] == 4 && $single) {
		empty($threadlist[$_G['tid']]['closed']) ? $closecheck[0] = 'checked="checked"' : $closecheck[1] = 'checked="checked"';
	}

	$imgattach = array();
	if(count($threadlist) == 1 && $operation == 'recommend') {
		$query = DB::query("SELECT a.*, af.description FROM ".DB::table('forum_attachment')." a LEFT JOIN ".DB::table('forum_attachmentfield')." af ON a.aid=af.aid WHERE a.tid='$_G[tid]' AND a.isimage IN ('1', '-1')");
		while($row = DB::fetch($query)) {
			$imgattach[] = $row;
		}
		$query = DB::query("SELECT * FROM ".DB::table('forum_forumrecommend')." WHERE tid='$_G[tid]'");
		if($oldthread = DB::fetch($query)) {
			$threadlist[$_G['tid']]['subject'] = $oldthread['subject'];
			$selectposition[$oldthread['position']] = ' selected="selected"';
			$selectattach = $oldthread['aid'];
		} else {
			$selectattach = $imgattach[0]['aid'];
			$selectposition[0] = ' selected="selected"';
		}
	}
	
//	分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = "notice";
    if(common_category_is_enable($_G['fid'],$pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G['fid'],$pluginid);
    }
	
	include template('forum/extraresourcetopicadmin');

} else {

	$moderatetids = dimplode(array_keys($threadlist));
	$reason = checkreasonpm();
	$stampstatus = 0;
	$stampaction = 'SPA';
	if(empty($operations)) {
		showmessage('admin_nonexistence');
	} else {
		$posts = $images = array();
		foreach($operations as $operation) {

			if(in_array($operation, array('stick', 'highlight', 'digest', 'recommend'))) {
				if(empty($posts)) {
					$postlist = getfieldsofposts('*', "tid IN ($moderatetids) AND first='1'");
					foreach($postlist as $post) {
						$post['message'] = messagecutstr($post['message'], 200);
						$posts[$post['tid']] = $post;
					}
				}
			}

			$updatemodlog = TRUE;
			if($operation == 'stick') {
				/*$displayorder = intval($_G['gp_sticklevel']);
				if($displayorder < 0 || $displayorder > 3 || $displayorder > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				
				DB::query("UPDATE ".DB::table('notice')." SET displayorder='$displayorder', moderated='1' WHERE id IN ($moderatetids)");

			//	积分
				$resultids = explode(',',$moderatetids);
				require_once libfile('function/credit');
				foreach ($resultids as $idss) {
					credit_create_credit_log($_G['uid'], "digest", $idss);
				}
				*/
			} elseif($operation == 'highlight') {
				/*if(!$_G['group']['allowhighlightthread']) {
					showmessage('undefined_action');
				}
				$highlight_style = $_G['gp_highlight_style'];
				$highlight_color = $_G['gp_highlight_color'];
				$expiration = checkexpiration($_G['gp_expirationhighlight'], $operation);
				$stylebin = '';
				for($i = 1; $i <= 3; $i++) {
					$stylebin .= empty($highlight_style[$i]) ? '0' : '1';
				}

				$highlight_style = bindec($stylebin);
				if($highlight_style < 0 || $highlight_style > 7 || $highlight_color < 0 || $highlight_color > 8) {
					showmessage('undefined_action', NULL);
				}

				DB::query("UPDATE ".DB::table('notice')." SET highlight='$highlight_style$highlight_color', moderated='1' WHERE id IN ($moderatetids)", 'UNBUFFERED');
				
				$modaction = ($highlight_style + $highlight_color) ? ($expiration ? 'EHL' : 'HLT') : 'UHL';
				$expiration = $modaction == 'UHL' ? 0 : $expiration;
				*/
			} elseif($operation == 'digest') {
				/*$digestlevel = intval($_G['gp_digestlevel']);
				if($digestlevel < 0 || $digestlevel > 3 || $digestlevel > $_G['group']['allowdigestthread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationdigest'], $operation);
				$expirationdigest = $digestlevel ? $expirationdigest : 0;

				DB::query("UPDATE ".DB::table('notice')." SET digest='$digestlevel', moderated='1' WHERE id IN ($moderatetids)");
				
				//积分
				$resultids = explode(',',$moderatetids);
				require_once libfile('function/credit');
				foreach ($resultids as $idss) {
					credit_create_credit_log($_G['uid'], "digest", $idss);
				}

				foreach($threadlist as $thread) {
					if($thread['digest'] != $digestlevel) {
						if($digestlevel == $thread['digest']) continue;
						$extsql = array();
						if($digestlevel > 0 && $thread['digest'] == 0) {
							$extsql = array('digestposts' => 1);
						}
						if($digestlevel == 0 && $thread['digest'] > 0) {
							$extsql = array('digestposts' => -1);
						}
						if($digestlevel == 0) {
							$stampaction = 'SPD';
						}
						updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $digestlevel - $thread['digest']);
					}
				}
				

				$modaction = $digestlevel ? ($expiration ? 'EDI' : 'DIG') : 'UDG';
				$stampstatus = 2;
				//fix bug
				require_once libfile('function/group');
				delgroupcache($_G["fid"], "digest");*/
			} elseif($operation == 'delete') {
				if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				
				if($extratype=='class'){
					$query=DB::query("select * from ".DB::TABLE('extra_class')." where id in ($moderatetids)");
					while($value=DB::fetch($query)){
				notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的课程，被管理员删除了！', array( 'groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $value[name]), 1);
					}
					
					DB::query("delete from ".DB::table('extra_class')." WHERE id IN ($moderatetids)");
					DB::query("delete from ".DB::table('extra_resource')." WHERE type='class' and resourceid IN ($moderatetids)");
					DB::query("delete from ".DB::table('extra_relationship')." WHERE classid IN ($moderatetids)");
				}else if($extratype=='org'){
					$query=DB::query("select * from ".DB::TABLE('extra_org')." where id in ($moderatetids)");
					while($value=DB::fetch($query)){
				notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的机构，被管理员删除了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $value[name]), 1);
					}
					DB::query("delete from ".DB::table('extra_org')." WHERE id IN ($moderatetids)");
					DB::query("delete from ".DB::table('extra_resource')." WHERE type='org' and resourceid IN ($moderatetids)");
					deleteorgrelation($moderatetids);
				}else if($extratype=='lec'){
					$query=DB::query("select * from ".DB::TABLE('extra_lecture')." where id in ($moderatetids)");
					while($value=DB::fetch($query)){
				notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的讲师，被管理员删除了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $value[name]), 1);
					}
					DB::query("delete from ".DB::table('extra_lecture')." WHERE id IN ($moderatetids)");
					DB::query("delete from ".DB::table('extra_resource')." WHERE type='lec' and resourceid IN ($moderatetids)");
					DB::query("delete from ".DB::table('extra_relationship')." WHERE lecid IN ($moderatetids)");
				}
				
				/*foreach($threadlist as $rid){
					hook_delete_resource($rid['id'],'notice');
				}*/
			} elseif($operation == 'category') {
				/*if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				DB::query("UPDATE ".DB::table('notice')." SET category_id=".$_POST["category"]." WHERE id IN ($moderatetids)");*/

			} elseif($operation == 'release') {
				if(!$_G['group']['allowdelpost']) {
					showmessage('undefined_action');
				}
				
				if($extratype=='class'){
					DB::query("update ".DB::table("extra_class")." set released=".$_POST["pstatus"].", releaseddateline=".time()."  WHERE id IN ($moderatetids)");
					DB::query("update ".DB::table("extra_resource")." set released=".$_POST["pstatus"]." WHERE type='class' and resourceid IN ($moderatetids)");
					if($_POST["pstatus"]=='1'){
						$query=DB::query("select * from ".DB::TABLE('extra_class')." where id in ($moderatetids)");
						while($value=DB::fetch($query)){
							$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id=".$value[id] ;
							notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的课程进行了发布，赶快去查看一下了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$value[name].'</a>'), 1);
							require_once libfile('function/feed');
							$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'class' => '<a href="'.$url.'">'.$value[name].'</a>');
							feed_add('extraresource', 'feed_extra_class', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);
						}
					}
				}else if($extratype=='org'){
					DB::query("update ".DB::table("extra_org")." set released=".$_POST["pstatus"].", releaseddateline=".time()."  WHERE id IN ($moderatetids)");
					DB::query("update ".DB::table("extra_resource")." set released=".$_POST["pstatus"]." WHERE type='org' and resourceid IN ($moderatetids)");
					if($_POST["pstatus"]=='1'){
						$query=DB::query("select * from ".DB::TABLE('extra_org')." where id in ($moderatetids)");
						while($value=DB::fetch($query)){
							$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=vieworg&id=".$value[id] ;
							notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的机构进行了发布，赶快去查看一下了！', array( 'groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$value[name].'</a>'), 1);
							
							require_once libfile('function/feed');
							$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'org' => '<a href="'.$url.'">'.$value[name].'</a>');
							feed_add('extraresource', 'feed_extra_org', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);
						}
					}
				}else if($extratype=='lec'){
					
					if($_POST["pstatus"]=='1'){
						$query=DB::query("select * from ".DB::TABLE('extra_lecture')." where id in ($moderatetids)");
						while($value=DB::fetch($query)){
							if($value[oldlecid]){
							}else{
								if($value[isinnerlec]=='1'){
									DB::insert("lecturer", array("lecid"=>$value[innerlecid],
									"name"=>$value[name],
									"gender"=>intval($value[gender]+1),
									"rank"=>'5',
									"orgname"=>$value["relationorgname"],
									"tel"=>$value["telephone"],
									"email"=>$value["email"],
									"isinnerlec"=>$value["isinnerlec"],
									"teachdirection"=>$value["teachdirection"],
									"imgurl"=>$value["uploadfile"],
									"fid"=>'197',
									"fname"=>'培训师家园',
									"uid"=>$value["sugestuid"],
									"dateline"=>$value["sugestdateline"],
									"bginfo"=>$value["descr"],
									"trainingexperience"=>$value["trainingexperince"],
									"trainingtrait"=>$value["trainingtrait"]));
								}
								$oldlecid=DB::insert_id();
							}
							if($oldlecid){
								$lecsql=",oldlecid='".$oldlecid."'";
							}
						
							$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewlec&id=".$value[id] ;
							notification_add($value[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的讲师进行了发布，赶快去查看一下了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$value[name].'</a>'), 1);
							
							require_once libfile('function/feed');
							$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'lecture' => '<a href="'.$url.'">'.$value[name].'</a>');
							feed_add('extraresource', 'feed_extra_lec', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);
							DB::query("update ".DB::table("extra_lecture")." set released=".$_POST["pstatus"].", releaseddateline=".time().$lecsql."  WHERE id=".$value[id]);
							DB::query("update ".DB::table("extra_resource")." set released=".$_POST["pstatus"]." WHERE type='lec' and resourceid=".$value[id]);
						}
					}
					else{
							DB::query("update ".DB::table("extra_lecture")." set released=".$_POST["pstatus"].", releaseddateline=".time()."  WHERE id IN ($moderatetids)");
							DB::query("update ".DB::table("extra_resource")." set released=".$_POST["pstatus"]." WHERE type='lec' and resourceid IN ($moderatetids)");
					}
				}

			} elseif($operation == 'close') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationclose'], $operation);
				$modaction = $expiration ? 'ECL' : 'CLS';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'open') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('undefined_action');
				}
				$expiration = checkexpiration($_G['gp_expirationopen'], $operation);
				$modaction = $expiration ? 'EOP' : 'OPN';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='0', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'move') {
				if(!$_G['group']['allowmovethread']) {
					showmessage('undefined_action');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				} else {
					$moveto = $toforum['fid'];
					$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
					$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
					if($modnewthreads || $modnewreplies) {
						showmessage('admin_move_have_mod');
					}
				}

				if($_G['adminid'] == 3) {
					if($_G['member']['accessmasks']) {
						$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowpostattach';
						$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid='$moveto'";
					}
					$priv = DB::fetch_first("SELECT ff.postperm, m.uid AS istargetmod $accessadd1
							FROM ".DB::table('forum_forumfield')." ff
							$accessadd2
							LEFT JOIN ".DB::table('forum_moderator')." m ON m.fid='$moveto' AND m.uid='$_G[uid]'
							WHERE ff.fid='$moveto'");
					if((($priv['postperm'] && !in_array($_G['groupid'], explode("\t", $priv['postperm']))) || ($_G['member']['accessmasks'] && ($priv['allowview'] || $priv['allowreply'] || $priv['allowgetattach'] || $priv['allowpostattach']) && !$priv['allowpost'])) && !$priv['istargetmod']) {
						showmessage('admin_move_nopermission');
					}
				}

				$moderate = array();
				$stickmodify = 0;
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						if(in_array($thread['displayorder'], array(2, 3))) {
							$stickmodify = 1;
						}
						if($_G['gp_type'] == 'redirect') {
							$thread = daddslashes($thread, 1);
							DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment)
								VALUES ('$thread[fid]', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '$thread[dblastpost]', '".addslashes($thread['lastposter'])."', '0', '0', '0', '0', '$thread[tid]', '0', '0')");
						}
					}
				}

				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_moderate_invalid');
				}

				$displayorderadd = $_G['adminid'] == 3 ? ', displayorder=\'0\'' : '';
				DB::query("UPDATE ".DB::table('forum_thread')." SET fid='$moveto', moderated='1', isgroup='0' $displayorderadd WHERE tid IN ($moderatetids)");
				updatepost(array('fid' => $moveto), "tid IN ($moderatetids)");

				if($_G['setting']['globalstick'] && $stickmodify) {
					require_once libfile('function/cache');
					updatecache('globalstick');
				}
				$modaction = 'MOV';

				updateforumcount($moveto);
				updateforumcount($_G['fid']);
			} elseif($operation == 'type') {
				if(!$_G['group']['allowedittypethread']) {
					showmessage('undefined_action');
				}
				if(!isset($_G['forum']['threadtypes']['types'][$_G['gp_typeid']]) && ($_G['gp_typeid'] != 0 || $_G['forum']['threadtypes']['required'])) {
					showmessage('admin_type_invalid');
				}

				DB::query("UPDATE ".DB::table('forum_thread')." SET typeid='$_G[gp_typeid]', moderated='1' WHERE tid IN ($moderatetids)");
				$modaction = 'TYP';
			} elseif($operation == 'recommend_group') {
				if($_G['forum']['status'] != 3 || !in_array($_G['adminid'], array(1, 2))) {
					showmessage('undefined_action');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				}
				$moderate = array();
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if($thread['closed']) {
						continue;
					}
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						$thread = daddslashes($thread, 1);
						DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment, isgroup)
							VALUES ('$moveto', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '".TIMESTAMP."', '".addslashes($thread['lastposter'])."', '$thread[views]', '$thread[replies]', '0', '$thread[digest]', '$thread[tid]', '$thread[special]', '$thread[attachment]', '$thread[isgroup]')");
						$newtid = DB::insert_id();
						DB::query("UPDATE ".DB::table('forum_thread')." SET closed='$newtid' WHERE tid='$thread[tid]'");
					}
				}
				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_succeed', $_G['referer']);
				}
				$modaction = 'REG';
			}

			

		}

		showmessage('admin_succeed', $_G['referer']);
	}

}

function checkexpiration($expiration, $operation) {
	global $_G;
	if(!empty($expiration) && in_array($operation, array('recommend', 'stick', 'digest', 'highlight', 'close'))) {
		$expiration = strtotime($expiration) - $_G['setting']['timeoffset'] * 3600 + date('Z');
		if(dgmdate($expiration, 'Ymd') <= dgmdate(TIMESTAMP, 'Ymd') || ($expiration > TIMESTAMP + 86400 * 180)) {
			showmessage('admin_expiration_invalid');
		}
	} else {
		$expiration = 0;
	}
	return $expiration;
}

function set_stamp($typeid, $stampaction, &$threadlist, $expiration) {
	global $_G;
	$moderatetids = dimplode(array_keys($threadlist));
	if(empty($threadlist)) {
		return false;
	}
	if(array_key_exists($typeid, $_G['cache']['stamptypeid'])) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET ".buildbitsql('status', 5, TRUE)." WHERE tid IN ($moderatetids)");
		if($stampaction == 'SPD') {
			$stamptids = array();
			foreach($threadlist as $stamptid => $thread) {
				$currentstamp = DB::fetch_first("SELECT * FROM ".DB::table('forum_threadmod')." WHERE tid='$stamptid' AND status='1' ORDER BY dateline DESC LIMIT 1");
				if(!empty($currentstamp) && $currentstamp['stamp'] == $_G['cache']['stamptypeid'][$typeid]) {
					$stamptids[] = $stamptid;
				}
			}
			if(!empty($stamptids)) {
				$moderatetids = dimplode($stamptids);
			} else {
				$moderatetids = '';
			}
		}
		!empty($moderatetids) && updatemodlog($moderatetids, $stampaction, $expiration, 0, $_G['cache']['stamptypeid'][$typeid]);
	}
}

function deleteorgrelation($ids){
	$query=DB::query("select * from ".DB::table("extra_relationship")." where orgid IN ($ids)");
	while($value=DB::fetch($query)){
		if($value[lecid]){
			$orgname='';
			$newname=array();
			$orgname=explode(',',$value[lecorg]);
			foreach($orgname as $name){
				if($name==$value[orgname]){
				}else{
					$newname[]=$name;
				}
			}
			$lecorg=implode(',',$newname);
			DB::query("update ".DB::table("extra_lecture")." set relationorgname='".$lecorg."' where id=".$value[lecid]);
			DB::query("update ".DB::table("extra_relationship")." set lecorg='".$lecorg."' where lecid=".$value[lecid]);
		}
		if($value[classid]){
			$orgname='';
			$newname=array();
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			$orgname=explode(',',$class[relationorgname]);
			foreach($orgname as $name){
				if($name==$value[orgname]){
				}else{
					$newname[]=$name;
				}
			}
			$relationorgname=implode(',',$newname);
			DB::query("update ".DB::table("extra_class")." set relationorgname='".$relationorgname."' where id=".$value[classid]);
		}
	}
	DB::query("delete from ".DB::table('extra_relationship')." WHERE orgid IN ($ids)");
	
}


?>