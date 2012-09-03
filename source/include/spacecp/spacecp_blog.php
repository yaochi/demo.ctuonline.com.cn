<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_blog.php 9821 2010-05-05 04:03:14Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$blog = array();
if($blogid) {
	$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b
		LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
		WHERE b.blogid='$blogid'");
	$blog = DB::fetch($query);
}

if(empty($blog)) {
	if(!checkperm('allowblog')) {
		showmessage('no_authority_to_add_log');
	}

	ckrealname('blog');

	ckvideophoto('blog');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	$blog['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);
	$blog['message'] = empty($_GET['message'])?'':getstr($_GET['message'], 5000, 1, 0);

} else {

	if($_G['uid'] != $blog['uid'] && !checkperm('manageblog')) {
		showmessage('no_authority_operation_of_the_log');
	}
}

if(submitcheck('blogsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(empty($blog['blogid'])) {
		$blog = array();
	} else {
		if(!checkperm('allowblog')) {
			showmessage('no_authority_to_add_log');
		}
	}

	if($_G['setting']['blogcategorystat'] && $_G['setting']['blogcategoryrequired'] && !$_POST['catid']) {
		showmessage('blog_choose_system_category');
	}
	require_once libfile('function/blog');
	if($newblog = blog_post($_POST, $blog)) {
		if(empty($blog) && $newblog['topicid']) {
			$url = 'home.php?mod=space&do=topic&topicid='.$newblog['topicid'].'&view=blog&quickforward=1';
		} else {
			$url = 'home.php?mod=space&uid='.$newblog['uid'].'&do=blog&diymode=1&quickforward=1&id='.$newblog['blogid'];
		}
		//积分
		require_once libfile('function/credit');
		if($_POST['operate'] != 'edit'){
			credit_create_credit_log($_G['uid'], 'publishblog', $newblog['blogid']); 
			hook_create_resource($newblog['blogid'], "blog");//added by fumz,2010-9-21 11:13:59<br />
		}
		showmessage('do_success', $url);
	} else {
		showmessage('that_should_at_least_write_things', NULL, array(), array('return'=>1));
	}
}
if($_GET['op'] == 'delete') {
	
	if(submitcheck('deletesubmit')) {
		require_once libfile('function/delete');
		if(deleteblogs(array($blogid))) {
			showmessage('do_success', "home.php?mod=space&uid=$blog[uid]&do=blog&view=me");
		} else {
			showmessage('failed_to_delete_operation');
		}
	}

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('manageblog')) {
		showmessage('no_privilege');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_blog', array('hot'=>$_POST['hot']), array('blogid'=>$blog['blogid']));
		if($_POST['hot']>0) {
			require_once libfile('function/feed');
			feed_publish($blog['blogid'], 'blogid');
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$blog['blogid'], 'idtype'=>'blogid'));
		}

		showmessage('do_success', "home.php?mod=space&uid=$blog[uid]&do=blog&quickforward=1&id=$blog[blogid]");
	}

} else {
	$classarr = $blog['uid']?getclassarr($blog['uid']):getclassarr($_G['uid']);
	$albums = getalbums($_G['uid']);

	$friendarr = array($blog['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($blog['friend'] == 4) {
		$passwordstyle = '';
	} elseif($blog['friend'] == 2) {
		$selectgroupstyle = '';
		if($blog['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($blog[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
				$realnames[]=user_get_user_name_by_username($value['username']);
			}
			$blog['target_names'] = implode(' ', $names);
			$blog['target_realnames'] = implode(' ', $realnames);
		}
	}


	$blog['message'] = dhtmlspecialchars($blog['message']);

	$allowhtml = checkperm('allowhtml');

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if($_G['setting']['blogcategorystat']) {
		loadcache('blogcategory');
		$category = $_G['cache']['blogcategory'];

		$categoryselect = '';
		if($category) {
			$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
			foreach ($category as $value) {
				if($value['level'] == 0) {
					$selected = $blog['catid'] == $value['catid']?' selected':'';
					$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
					if(!$value['children']) {
						continue;
					}
					foreach ($value['children'] as $catid) {
						$selected = $blog['catid'] == $catid?' selected':'';
						$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
						if($category[$catid]['children']) {
							foreach ($category[$catid]['children'] as $catid2) {
								$selected = $blog['catid'] == $catid2?' selected':'';
								$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
							}
						}
					}
				}
			}
			$categoryselect .= "</select>";
		}
	}
	$menuactives = array('space'=>' class="active"');
}

include_once template("home/spacecp_blog");

?>