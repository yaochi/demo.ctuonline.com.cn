<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_group.php 10857 2010-05-17 09:13:31Z liulanbo $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if (!$_G['setting']['groupstatus']) {
    showmessage('group_status_off');
}
require_once libfile('function/home');
require_once libfile('function/group');
$_G['action']['action'] = 3;
$_G['action']['fid'] = $_G['fid'];
$_G['basescript'] = 'group';

$actionarray = array('join', 'out', 'create', 'viewmember', 'manage', 'index', 'memberlist', 'plugin','invitejoin');
$action = getgpc('action') && in_array($_G['gp_action'], $actionarray) ? $_G['gp_action'] : 'index';
if (in_array($action, array('join', 'out', 'create', 'manage','invitejoin'))) {
    if (empty($_G['uid'])) {
        showmessage('not_loggedin', '', '', array('login' => 1));
    }
}
if (empty($_G['fid']) && $action != 'create') {
    showmessage('group_rediret_now', 'group.php');
}
if($_G["forum"]["fid"]){

	//modify  by songsp 2011-3-18 12:50:49
	if( !$_G["forum"]["group_type"]){
		$result = DB::fetch(DB::query("SELECT group_type FROM " . DB::table("forum_forumfield") . " WHERE fid=" . $_G["forum"]["fid"]));
   		$_G["forum"]["group_type"] = $result;
	}

    if ($_G["forum"]["type"] == "activity") {
        $url = str_replace("mod=group", "mod=activity", $_SERVER['QUERY_STRING']);
        $url = "forum.php?" . $url;
        header("Location:" . $url);
    }
}

//print_r('=================================================================');

$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="' . $_G['setting']['bbname'] . ' - ' . $navtitle . '" href="' . $_G['siteurl'] . 'forum.php?mod=rss&fid=' . $_G['fid'] . '&amp;auth=' . $rssauth . "\" />") : '';
$navtitle = lang('group/template', 'group');
if ($_G['fid']) {
    $navtitle .= ' - ' . $_G['forum']['name'];
    $metakeywords = $_G['forum']['metakeywords'];
    $metadescription = $_G['forum']['metadescription'];
    if ($_G['forum']['status'] != 3) {
        showmessage('forum_not_group', 'group.php');
    } elseif ($_G['forum']['jointype'] < 0 && !$_G['forum']['ismoderator']) {
        showmessage('forum_group_status_off', 'group.php');
    }

    //$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'), 604800);

    $_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
    $_G['forum']['banner'] = get_groupimg($_G['forum']['banner']);
    $_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');
    $_G['forum']['posts'] = intval($_G['forum']['posts']);
    $_G['grouptypeid'] = $_G['forum']['fup'];
    $groupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
    $_G["forum"]["my_empirical"] = $groupuser["empirical_value"];


    //放置到G中  性能优化  2011-3-23 13:48:45     by songsp
    $_G['myself']['forum_groupuser'][$_G[fid].'_'.$_G[uid]] = ($groupuser)?$groupuser:'null';

	//wang 用户经验值所属的用户等级 例如自定义一个经验等级为普通用户一级 1-100，普通用户等级为普通用户二级：101-200，如果用户的经验值为150，则显示为普通用户二级。

	//echo "SELECT * FROM   forum_userlevel WHERE fid='$_G[fid]' and start_emp<= $groupuser[empirical_value]  ";
	if($groupuser[empirical_value]){
		$query = DB::fetch_first(" SELECT * FROM ".DB::table('forum_userlevel')." WHERE fid='$_G[fid]' and start_emp<= $groupuser[empirical_value] and end_emp>= $groupuser[empirical_value] ORDER BY end_emp LIMIT 1 ");
	    $userlevel=$query;

	    $_G['myself']['$userlevel'][$_G[fid].'_'.$_G[uid]] = $userlevel?$userlevel:'null' ; //存放用户在某一专区所属等级名称


	}

	//print_r($userlevel[level_name]);

	//当前访问专区的在线用户
    //$onlinemember = grouponline($_G['fid'], 1);

    $groupmanagers = $_G['forum']['moderators'];
    if(!$_COOKIE["forum_group_".$_G[uid]."_".$_G[fid]]){
        DB::query("UPDATE " . DB::table("forum_forumfield") . " SET viewnum=viewnum+1 WHERE fid=" . $_G[fid]);
        setcookie("forum_group_".$_G[uid]."_".$_G[fid], "true", time()+86400);
    }
}

if ($_G["gp_groupalbum2_action"]!="swfupload" && in_array($action, array('out', 'viewmember', 'manage', 'index', 'memberlist', 'plugin'))) {
    $status = groupperm($_G['forum'], $_G['uid'], $action, $groupuser);
    if ($status == -1) {
        showmessage('forum_not_group', 'group.php');
    } elseif ($status == 1) {
        showmessage('forum_group_status_off');
    }
    if ($action != 'index') {
        if ($status == 2) {
            showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
        } elseif ($status == 3) {
            showmessage('forum_group_moderated', "forum.php?mod=group&fid=$_G[fid]");
        }
    }
}

if (in_array($action, array('index')) && $status != 2) {

	//注释 by songsp 2011-3-21 13:20:23
	/*
    $newuserlist = $activityuserlist = array();
    foreach ($groupcache['newuserlist']['data'] as $user) {
        $newuserlist[$user['uid']] = $user;
        $newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
    }

    $activityuser = array_slice($groupcache['activityuser']['data'], 0, 8);
    foreach ($activityuser as $user) {
        $activityuserlist[$user['uid']] = $user;
        $activityuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
    }
	*/

    // 注释 by songsp  2011-3-21 13:19:38
    //$groupviewed_list = get_viewedgroup();
}
//print_r('=================================================================');
$groupnav = get_groupnav($_G['forum']);

$showpoll = $showtrade = $showreward = $showactivity = $showdebate = 0;
if ($_G['forum']['allowpostspecial']) {
    $showpoll = $_G['forum']['allowpostspecial'] & 1;
    $showtrade = $_G['forum']['allowpostspecial'] & 2;
    $showreward = isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($_G['forum']['allowpostspecial'] & 4);
    $showactivity = $_G['forum']['allowpostspecial'] & 8;
    $showdebate = $_G['forum']['allowpostspecial'] & 16;
}

if ($_G['group']['allowpost']) {
    $_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && $showpoll;
    $_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && $showtrade;
    $_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && $showreward;
    $_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && $showactivity;
    $_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && $showdebate;
}

if ($_G["fid"]) {
    group_load_plugin($_G["fid"]);
    if ($result["group_type"] >= 1 && $result["group_type"] < 20) {
        $_G['forum']['type_icn_id'] = 'brzone_l';
    } elseif ($result["group_type"] >= 20 && $result["group_type"] < 60) {
        $_G['forum']['type_icn_id'] = 'bizone_l';
    }
}
if ($action == 'index') {

	/*
	$newthreadlist = array();
    if ($status != 2) {
        require_once libfile('function/feed');
        $newthreadlist = getgroupcache($_G['fid'], array('dateline'), 0, 10, 0, 1);
        foreach ($newthreadlist['dateline']['data'] as $tid => $thread) {
            if ($thread['closed']) {
                $newthreadlist['dateline']['data'][$tid]['folder'] = 'lock';
            } elseif (empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D' . $tid . 'D') === FALSE) {
                $newthreadlist['dateline']['data'][$tid]['folder'] = 'new';
            } else {
                $newthreadlist['dateline']['data'][$tid]['folder'] = 'common';
            }
        }
        $groupfeedlist = feed_activity_user(array_keys($groupcache['activityuser']['data']));
    } else {
		$jointype=DB::result_first("SELECT jointype FROM ".DB::TABLE('forum_forumfield')." WHERE fid=$_G[fid]");
        $newuserlist = $activityuserlist = array();
        $newuserlist = array_slice($groupcache['newuserlist']['data'], 0, 4);
        foreach ($newuserlist as $user) {
            $newuserlist[$user['uid']] = $user;
            $newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
        }
    } */
	$jointype=$_G[forum][jointype];
    write_groupviewed($_G['fid']);


      //begin update by qiaoyongzhi,2011-2-25 EKSN 72 专区可分享
    	//分享

    //$query = DB::query("SELECT f.fid as fid,f.name as name,ff.description as message,ff.founderuid as authorid,ff.foundername as authorname,ff.icon as icon FROM " . DB::table('forum_forum') . " as f LEFT JOIN " . DB::table('forum_forumfield') . " as ff ON ff.fid=f.fid WHERE f.type='sub' AND f.fid=".$_G[fid]);
	//$group = DB::fetch($query);
	$group['fid'] = $_G['forum']['fid'];
	$group['name'] = $_G['forum']['name'];
	$group['authorid']= $_G['forum']['founderuid'];
	$group['authorname']= $_G['forum']['foundername'];
	$group['message'] = $_G['forum']['description'];
	$group['icon']= $_G['forum']['icon'];


	//print_r($_G['forum']);
	//print_r($group);
	$titlelink = 'forum.php?mod=group&fid='.$_G['fid'];
	$params['id'] = $_G['fid'];
	$params['subject'] = base64_encode($group['name']);
	$params['subjectlink'] = base64_encode($titlelink);
	$params['authorid'] = $group['authorid'];
	$params['author'] = base64_encode(user_get_user_name($group['authorid']));
	$params['message'] = base64_encode($group['message']);
	if($group['icon']){
		//$params['image'] = base64_encode("data/attachment/group/".$group['icon']);
		$params['image'] = base64_encode($group['icon']);
	}
	else{$params['image'] = base64_encode($group['icon']);}
	$params['type'] = "group";
	$params['fid'] = $_G['fid'];
	foreach($params as $key => $value){
		$url .= $key ."=".$value."&";
	}

	$shareurl = "home.php?mod=spacecp&ac=share&handlekey=sharehk_1&".$url;
    //end

	//为组件菜单diy模块
	$_G['myself']['group']['status'] = $status;
	$_G['myself']['group']['action'] = $action;

	//print_r("----------------222-----------");
    include template('diy:group/group:' . $_G['fid']);

} elseif ($action == "plugin") {
    if ($_G['gp_plugin_op'] == "createmenu") {
        include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
        include template('group/plugincreate');
    } else if($_G['gp_plugin_op'] == "viewmenu"){
        include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
        include template('group/plugin_view');
    } else {
			//文档是否需要分类
		require_once libfile("function/category");
		$allowedittype = common_category_is_enable($_G['fid'], $pluginid);
		$allowrequired = common_category_is_required($_G['fid'], 'groupdoc');
		//showmessage($allowrequired);

        include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
        include template('diy:group/group:' . $_G['fid']);

    }
} elseif ($action == 'memberlist') {

    $oparray = array('card', 'address', 'alluser');
    $op = getgpc('op') && in_array($_G['gp_op'], $oparray) ? $_G['gp_op'] : 'alluser';
    $page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;

    $alluserlist = $adminuserlist = array();
    $expertuserlist = $page < 2 ? groupexpertuserlist($_G['fid'], '', 0, 0, "AND level='4'") : '';
    $adminlist = $groupmanagers && $page < 2 ? $groupmanagers : array();
    $expertuids = array();
    foreach($expertuserlist as $expertuser){
        $expertuids[] = $expertuser[uid];
    }
    $expertuserrealname = user_get_user_realname($expertuids);

    if ($op == 'alluser') {
		$membernumber=DB::result_first("SELECT count(*) FROM ".DB::TABLE("forum_groupuser")." WHERE level= '4' and ( pre_forum_groupuser.uid not in ( SELECT pre_expertuser.uid FROM pre_expertuser) ) and fid=".$_G['fid']." AND level='4'");
        $alluserlist = groupuserlist($_G['fid'], 'lastupdate', $perpage, $start, "AND level='4'", '', $onlinemember['list']);
        $multipage = multi($membernumber, $perpage, $page, 'forum.php?mod=group&action=memberlist&op=alluser&fid=' . $_G['fid']);

        $alluseruids = array();
        foreach($alluserlist as $startuser){
            $alluseruids[] = $startuser[uid];
        }
        $alluserrealname = user_get_user_realname($alluseruids);

        if ($adminlist) {
            $adminuids = array();

            foreach ($adminlist as $user) {
                $adminuserlist[$user['uid']] = $user;
                $adminuids[] = $user[uid];
                $adminuserlist[$user['uid']]['online'] = $onlinemember['list'] && is_array($onlinemember['list']) && $onlinemember['list'][$user['uid']] ? 1 : 0;
            }

            $adminuserrealname = user_get_user_realname($adminuids);
        }
    }
    $groupnav.=" &rsaquo; 专区成员";
    include template('diy:group/group:' . $_G['fid']);
}elseif($action =='invitejoin'){
	$scode=$_GET['scode'];
	if($scode==md5($_G['forum']['fid'] .'esn')){
		$inviteuid = 0;
		$membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
		if (!empty($membermaximum)) {
			$curnum = DB::result_first("SELECT count(*) FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]'");
			if ($curnum >= $membermaximum) {
				showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
			}
		}
		if ($groupuser['uid']) {
			showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
		} else {
			$modmember = 4;
			$showmessage = 'group_join_succeed';
			$confirmjoin = TRUE;
			$inviteuid = DB::result_first("SELECT uid FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");

			if ($_G['forum']['jointype'] == 3) {
				if (!$inviteuid) {
					$confirmjoin = FALSE;
					$showmessage = 'group_join_forbid_anybody';
				}
			}
		  //  if ($_G['forum']['jointype'] == 1) {
	//            if (!$inviteuid) {
	//                $confirmjoin = FALSE;
	//                $showmessage = 'group_join_need_invite';
	//            }
	//        }
			if ($_G['forum']['jointype'] == 2) {
				$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
				!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
				$showmessage = 'group_join_apply_succeed';//2011-2-18 12：40 update by qiaoyongzhi,修改审核加入类型，点加入专区中showmessage的显示内容
			}

			if ($confirmjoin) {
				DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
				if ($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
					foreach ($groupmanagers as $manage) {
						notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'] . 'forum.php?mod=group&action=manage&op=checkuser&fid=' . $_G['fid']), 1);
					}
				} else {
					update_usergroups($_G['uid']);
				}
				if ($inviteuid) {
					DB::query("DELETE FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
				}
				if ($modmember == 4) {
					DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$_G[fid]'");
				}
				updateactivity($_G['fid'], 0);

				//动态
				require_once libfile('function/feed');
				$tite_data = array('groupname' => '<a href="forum.php?mod=group&fid=' . $_G['fid'] . '">' . $_G['forum']['name'] . '</a>');
				feed_add('group', 'feed_group_join', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $username,$_G['fid']);
			}

			include_once libfile('function/stat');
			updatestat('groupjoin');
			include_once libfile('function/group');
			group_update_forum_hot($_G["fid"]);
			delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
			require_once libfile("function/credit");
			credit_create_credit_log($_G[uid], "joingroup", $_G[fid]);
			group_add_empirical_by_setting($_G[uid], $_G[fid], "group_join_group", $_G["fid"]);
			showmessage($showmessage, "forum.php?mod=group&fid=$_G[fid]");
		}
	}else{
		showmessage('您没有被邀请加入专区',"forum.php?mod=group&fid=$_G[fid]");
	}
} elseif ($action == 'join') {
	if($_G['forum']['jointype'] != 1){
		$inviteuid = 0;
		$membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
		if (!empty($membermaximum)) {
			$curnum = DB::result_first("SELECT count(*) FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]'");
			if ($curnum >= $membermaximum) {
				showmessage('group_member_maximum', '', array('membermaximum' => $membermaximum));
			}
		}
		if ($groupuser['uid']) {
			showmessage('group_has_joined', "forum.php?mod=group&fid=$_G[fid]");
		} else {
			$modmember = 4;
			$showmessage = 'group_join_succeed';
			$confirmjoin = TRUE;
			$inviteuid = DB::result_first("SELECT uid FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");

			if ($_G['forum']['jointype'] == 3) {
				if (!$inviteuid) {
					$confirmjoin = FALSE;
					$showmessage = 'group_join_forbid_anybody';
				}
			}
		  //  if ($_G['forum']['jointype'] == 1) {
	//            if (!$inviteuid) {
	//                $confirmjoin = FALSE;
	//                $showmessage = 'group_join_need_invite';
	//            }
	//        }
			if ($_G['forum']['jointype'] == 2) {
				$modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
				!empty($groupmanagers[$inviteuid]) && $showmessage = 'group_join_apply_succeed';
				$showmessage = 'group_join_apply_succeed';//2011-2-18 12：40 update by qiaoyongzhi,修改审核加入类型，点加入专区中showmessage的显示内容
			}

			if ($confirmjoin) {
				DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
				if ($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
					foreach ($groupmanagers as $manage) {
						notification_add($manage['uid'], 'group', 'group_member_join', array('url' => $_G['siteurl'] . 'forum.php?mod=group&action=manage&op=checkuser&fid=' . $_G['fid']), 1);
					}
				} else {
					update_usergroups($_G['uid']);
				}
				if ($inviteuid) {
					DB::query("DELETE FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
				}
				if ($modmember == 4) {
					DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$_G[fid]'");
				}
				updateactivity($_G['fid'], 0);

				//动态
				require_once libfile('function/feed');
				$tite_data = array('groupname' => '<a href="forum.php?mod=group&fid=' . $_G['fid'] . '">' . $_G['forum']['name'] . '</a>');
				feed_add('group', 'feed_group_join', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $username,$_G['fid']);
			}

			include_once libfile('function/stat');
			updatestat('groupjoin');
			include_once libfile('function/group');
			group_update_forum_hot($_G["fid"]);
			delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
			require_once libfile("function/credit");
			credit_create_credit_log($_G[uid], "joingroup", $_G[fid]);
			group_add_empirical_by_setting($_G[uid], $_G[fid], "group_join_group", $_G["fid"]);
			showmessage($showmessage, "forum.php?mod=group&fid=$_G[fid]");
		}
	}else{
		showmessage('该专区是邀请加入专区，只能通过邀请加入',"forum.php?mod=group&fid=$_G[fid]");
	}
} elseif ($action == 'out') {

    if ($_G['uid'] == $_G['forum']['founderuid']) {
        showmessage('group_exit_founder');
    }
    $showmessage = 'group_exit_succeed';
    DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
    $cache_key = "group_member_".$_G[fid]."_".$_G[uid];
    memory("rm", $cache_key);

    DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
    update_usergroups($_G['uid']);
    delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
    update_groupmoderators($_G['fid']);
    include_once libfile('function/group');
    group_update_forum_hot($_G["fid"]);
	include_once libfile('function/repeats','plugin/repeats');
	delazarepeatsbyuid(array($_G[uid]),$_G["fid"]);
    //showmessage($showmessage, "forum.php?mod=forumdisplay&fid=$_G[fid]");
    showmessage($showmessage, "forum.php?mod=group&fid=$_G[fid]");
} elseif ($action == 'create') { //创建专区

    if (!$_G['group']['allowbuildgroup']) {
        showmessage('group_create_usergroup_failed', "group.php");
    }/*
      $groupnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_groupuser')." WHERE uid='$_G[uid]' AND level='1'");
      $allowbuildgroup = $_G['group']['allowbuildgroup'] - $groupnum;
      if($allowbuildgroup < 1) {
      showmessage('group_create_max_failed');
      } */

    if (!submitcheck('createsubmit')) {
        $groupselect = get_groupselect(getgpc('fupid'), getgpc('groupid'));

        //专区组件
    	$query = DB::query("SELECT identifier, name FROM " . DB::table("common_plugin"));
        $cur_group_plugins = array();
        while ($row = DB::fetch($query)) {
            $cur_group_plugins[$row["identifier"]] = $row;
        }
        //用户等级
        //$query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
        $levels = array(array(level_name => "仅专区成员"), array(level_name => "全部"), array(level_name => "群主"), array(level_name => "副群主"));

    } else { //提交新增
        $_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 20, ''))));
        if (empty($_G['gp_name'])) {
            showmessage('group_name_empty');
        }
        if (DB::result(DB::query("SELECT fid FROM " . DB::table('forum_forum') . " WHERE name='$_G[gp_name]' AND type='sub' "), 0)) {
            showmessage('group_name_exist');
        }

        $levelid = DB::result_first("SELECT levelid FROM " . DB::table('forum_grouplevel') . " WHERE creditshigher<='0' AND '0'<creditslower LIMIT 1");

        DB::query("INSERT INTO " . DB::table('forum_forum') . "(fup, type, name, status, level) VALUES ('$_G[gp_fup]', 'sub', '$_G[gp_name]', '3', '$levelid')");
        $newfid = DB::insert_id();
        if ($newfid) {
            $jointype = intval($_G['gp_jointype']);
            $gviewperm = intval($_G['gp_gviewperm']);
            $descriptionnew = dhtmlspecialchars(censor(trim($_G['gp_descriptionnew'])));
            $tagnew = dhtmlspecialchars(censor(trim($_G['gp_tagnew'])));
            $group_type = 60;
            DB::query("INSERT INTO " . DB::table('forum_forumfield') . "(fid, description, jointype, gviewperm, dateline, founderuid, foundername, membernum, tag, group_type) VALUES ('$newfid', '$descriptionnew', '$jointype', '$gviewperm', '" . TIMESTAMP . "', '$_G[uid]', '$_G[username]', '1', '$tagnew', '$group_type')");
            DB::query("UPDATE " . DB::table('forum_forumfield') . " SET groupnum=groupnum+1 WHERE fid='$_G[gp_fup]'");
            DB::query("INSERT INTO " . DB::table('forum_groupuser') . "(fid, uid, username, level, joindateline) VALUES ('$newfid', '$_G[uid]', '$_G[username]', '1', '" . TIMESTAMP . "')");
            group_create_empirical($newfid);
            group_create_plugin($newfid);
            require_once libfile('function/label');
            insertlabelgroup($newfid, $_POST["tagnews"]);
            require_once libfile('function/cache');
            updatecache('grouptype');


            //修改插件状态
        	foreach ($_POST["plugin_ids"] as $plugin_id) {
                $one = $_POST["m"][$plugin_id];
                $query = DB::query("SELECT * FROM " . DB::table("common_group_plugin") . " WHERE fid=" . $newfid . " AND plugin_id='" . $plugin_id . "'");
                $result = DB::fetch($query);

                $data = array(fid => $newfid, plugin_id => $plugin_id );
                if ($one == "disable") {
                 	$data["status"] = 'N';
                }elseif ($one == "enable") {
                    $data["status"] = 'Y';
                }
                if (!$result["id"]) {

                    DB::insert("common_group_plugin", $data);
                }else{
                	DB::update("common_group_plugin", $data, array(fid => $newfid, plugin_id => $plugin_id));
                }

                $oper = $_POST["oper"][$plugin_id];
                //更新权限
                $data["auth_group"] = $oper;
                DB::update("common_group_plugin", $data, array(fid => $newfid, plugin_id => $plugin_id));
            }

            //设置模板
        	if($_POST['temp']){


        		init_group_template($newfid,$_POST['temp']);


            }



        }
        include_once libfile('function/stat');
        updatestat('group');
        require_once libfile("function/credit");
        credit_create_credit_log($_G[uid], "creategroup", $newfid);
        hook_create_resource($newfid, "group");//added by fumz,2010-9-24 17:31:29
		//写入redis
		if($_G['config']['redis']['used']&&$newfid){
			$redis = new Redis();
			$redis->connect($_G['config']['redis']['hostname'],$_G['config']['redis']['server_port']);
			$redis->select(1);
			
			$reidsforum=DB::fetch_first("select * from ".DB::TABLE("forum_forum")." ff left join ".DB::TABLE("forum_forumfield")." fff on ff.fid=fff.fid where ff.type='sub' and ff.fid=".$newfid);
			if($reidsforum){
				$redis->hset($reidsforum['fid'],'fid',$reidsforum['fid']);
				$redis->hset($reidsforum['fid'],'name',$reidsforum['name']);
				$redis->hset($reidsforum['fid'],'description',$reidsforum['description']);
				$redis->hset($reidsforum['fid'],'icon',$reidsforum['icon']);
				$redis->hset($reidsforum['fid'],'moderators',$reidsforum['moderators']);
				$redis->hset($reidsforum['fid'],'jointype',$reidsforum['jointype']);
				$redis->hset($reidsforum['fid'],'gviewperm',$reidsforum['gviewperm']);
				$redis->hset($reidsforum['fid'],'membernum',$reidsforum['membernum']);
				$redis->hset($reidsforum['fid'],'dateline',$reidsforum['dateline']);
				$redis->hset($reidsforum['fid'],'founderuid',$reidsforum['founderuid']);
				$redis->hset($reidsforum['fid'],'foundername',$reidsforum['foundername']);
				$redis->hset($reidsforum['fid'],'banner',$reidsforum['banner']);
				$redis->hset($reidsforum['fid'],'group_type',$reidsforum['group_type']);
				$redis->hset($reidsforum['fid'],'viewnum',$reidsforum['viewnum']);
				$redis->hset($reidsforum['fid'],'hot',$reidsforum['hot']);
			}
			
		}
		
		
		
		
        showmessage('group_create_succeed', "forum.php?mod=group&action=manage&fid=$newfid", array(), array('msgtype' => 3));
    }


    include template('diy:group/group:' . $_G['fid']);
} elseif ($action == 'manage') {
    if (!$_G['forum']['ismoderator']) {
        showmessage('group_admin_noallowed');
    }
    //判断组件在专区中是否启用，add by qiaoyz
    $pluginuse[stationcourse]=check_plugin_is_in_use($_G['fid'],'stationcourse');
    $pluginuse[shresourcelist]=check_plugin_is_in_use($_G['fid'],'shresourcelist');
	$pluginuse[shlecturer]=check_plugin_is_in_use($_G['fid'],'shlecturer');
	$pluginuse[extraresource]=check_plugin_is_in_use($_G['fid'],'extraresource');
	 $pluginuse[repeats]=check_plugin_is_in_use($_G['fid'],'repeats');

    $pluginStatus=check_plugin_is_in_use($_G['fid'],'groupad');
    $specialswitch = $_G['current_grouplevel']['specialswitch'];

    $oparray = array('group','managecontent' ,'checkuser', 'manageuser', 'threadtype', 'demise', 'delete', 'manageexpert', 'manageplugin', 'manageempirical', 'manage_level','manage_stationcourse','manage_shresourcelist','manage_extraresource','manage_extraorg','manage_extralec','manage_extraclass','manage_adv', 'managecategory', 'settingempirical','manageinvite','manage_shlecturer');
    $_G['gp_op'] = getgpc('op') && in_array($_G['gp_op'], $oparray) ? $_G['gp_op'] : 'group';
    if (empty($groupmanagers[$_G[uid]])&& $_G[group][groupid]!='1' && $_G['gp_op'] != 'group') {
        showmessage('group_admin_noallowed');
    }
    $page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;
    $url = 'forum.php?mod=group&action=manage&op=' . $_G['gp_op'] . '&fid=' . $_G['fid'];
    /*$keytime=$_GET['keytime'] ? $_GET['keytime'] : 1;
	if($keytime!=-1){
		$key=time();
		$key=$key+($keytime*24*60*60);
		$key=base64_encode($key);
		$key=urlencode($key);
		$keyurl="&key={$key}";
	}else{
		$keyurl="";
	}*/
	//获取权限级别 caimm
	$levelnum=DB::result(DB::query("select level from pre_forum_groupuser where fid= ".$_GET[fid]." and uid = ".$_G[uid]));

    if ($_G['gp_op'] == 'group') {
	$key=time();
	$key=$key+(7*24*60*60);
	$key=base64_encode($key);
	$key=urlencode($key);
	$keyurl="&key={$key}";
	$groupkey=md5($_G['fid'].'group');
	$inviteurl=getsiteurl()."group.php?mod=invite&u={$_G['uid']}&fid={$_G['fid']}&groupkey={$groupkey}".$keyurl;
        if (submitcheck('groupmanage')) {
            if (($_G['gp_name'] && !empty($specialswitch['allowchangename'])) || ($_G['gp_fup'] && !empty($specialswitch['allowchangetype']))) {
                if ($levelnum!=1) {//群主可修改名字
                    showmessage('group_edit_only_founder');
                }

                if (isset($_G['gp_name'])) {
                    $_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 20, ''))));
                    if (empty($_G['gp_name']))
                        showmessage('group_name_empty');
                } elseif (isset($_G['gp_fup']) && empty($_G['gp_fup'])) {
                    showmessage('group_category_empty');
                }

                $addsql = '';
                if (!empty($_G['gp_name']) && $_G['gp_name'] != $_G['forum']['name']) {
                    if (DB::result(DB::query("SELECT fid FROM " . DB::table('forum_forum') . " WHERE name='$_G[gp_name]' AND type='sub' "), 0)) {
                        showmessage('group_name_exist', $url);
                    }
                    $addsql = "name = '$_G[gp_name]'";
                }
                /* 不许更新类别
                  if(intval($_G['gp_fup']) != $_G['forum']['fup']) {
                  $addsql .= $addsql ? ', ' : '';
                  $addsql .= "fup = '".intval($_G['gp_fup'])."'";
                  } */

                if ($addsql) {
                    DB::query("UPDATE " . DB::table('forum_forum') . " SET $addsql WHERE fid='$_G[fid]'");
                }
            }
            if (in_array($_G['adminid'], array(1, 2)) && $_G['gp_recommend'] != $_G['forum']['recommend']) {
                DB::query("UPDATE " . DB::table('forum_forum') . " SET recommend='" . intval($_G['gp_recommend']) . "' WHERE fid='$_G[fid]'");
                require_once libfile('function/cache');
                updatecache('forumrecommend');
            }

            $iconsql = '';
            $deletebanner = $_G['gp_deletebanner'];
            $iconnew = upload_icon_banner($_G['forum'], $_FILES['iconnew'], 'icon');
            $bannernew = upload_icon_banner($_G['forum'], $_FILES['bannernew'], 'banner');
            if ($iconnew) {
                $iconsql .= ", icon='$iconnew'";
            }
            if ($bannernew && empty($deletebanner)) {
                $iconsql .= ", banner='$bannernew'";
            } elseif ($deletebanner) {
                $iconsql .= ", banner=''";
                @unlink($_G['forum']['banner']);
            }
            $_G['gp_descriptionnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_descriptionnew']))));
            $_G['gp_tagnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_tagnew']))));
            $_G['gp_jointypenew'] = intval($_G['gp_jointypenew']);
            if ($_G['gp_jointypenew'] == '-1' && $_G['uid'] != $_G['forum']['founderuid']) {
                showmessage('group_close_only_founder');
            }
            $_G['gp_gviewpermnew'] = intval($_G['gp_gviewpermnew']);
            DB::query("UPDATE " . DB::table('forum_forumfield') . " SET description='$_G[gp_descriptionnew]', tag='$_G[gp_tagnew]', jointype='$_G[gp_jointypenew]', gviewperm='$_G[gp_gviewpermnew]'$iconsql WHERE fid='$_G[fid]'");
            require_once libfile('function/label');
            insertlabelgroup($_G[fid], $_POST["tagnews"]);
            showmessage('group_setup_succeed', $url);
        } else {
            require_once libfile('function/label');
            $firstgid = $_G['cache']['grouptype']['second'][$_G['forum']['fup']]['fup'];
            $groupselect = get_groupselect($firstgid, $_G['forum']['fup']);
            $gviewpermselect = $jointypeselect = array('', '', '');
            $_G['forum']['descriptionnew'] = str_replace("<br />", '', $_G['forum']['description']);
            $_G['forum']['tag'] = implode(",", listlabelsbygroupid($_G[forum][fid]));
            $_G['forum']['tagnew'] = str_replace("<br />", '', $_G['forum']['tag']);
            $_G[forum][tagids] = getlabelbyname(str_replace(" ", ",", $_G['forum']['tagnew']));
            $jointypeselect[$_G['forum']['jointype']] = 'checked="checked"';
            $gviewpermselect[$_G['forum']['gviewperm']] = 'checked="checked"';
            require_once libfile('function/forumlist');
            $forumselect = forumselect(FALSE, 0, $_G['forum']['recommend']);
        }
    } elseif($_G['gp_op'] == 'manageinvite'){
			$inviteurl=getsiteurl()."group.php?mod=invite&u={$_G['uid']}&fid={$_G['fid']}&key={$key}";//增加时间水印
	}

	//管理专区首页内容
	elseif ($_G['gp_op'] == 'managecontent') {
    	global $_G;
		//获得专区首页组件内容
		//查询出专区首页显示的组件模块

		$fid = $_G['fid'];//专区id

		$targettplname = "group/group_".$fid;

		$blocknames = array(
			'groupad'=>'广告',
			'notice'=>'通知公告',
			'resourcelist'=>'资源列表',
			'lecturer'=>'讲师',
			'ranking'=>'排行榜',
			'topic_list'=>'话题',
			'groupalbum2'=>'相册',
			'grouppic'=>'相册图片',
			'poll'=>'投票',
			'qbar'=>'提问吧',
			'activity_list'=>'活动',
			'groupmember'=>'专区成员',
			'groupdoc'=>'文档',
			'grouplive'=>'直播',
			'questionary'=>'问卷',
			'html_html'=>'DIY工具',

		);
		//插件目录
		$blockpaths = array(
			'groupad'=>'groupad',
			'notice'=>'notice',
			'resourcelist'=>'resourcelist',
			'lecturer'=>'lecturer',
			'ranking'=>'ranking',
			'topic_list'=>'topic',
			'groupalbum2'=>'groupalbum2',
			'grouppic'=>'grouppic',
			'poll'=>'poll',
			'qbar'=>'qbar',
			'activity_list'=>'activity',
			'groupmember'=>'groupmember',
			'groupdoc'=>'groupdoc',
			'grouplive'=>'grouplive',
			'questionary'=>'questionary',
			//'html_html'=>'DIY工具',

		);


		$query = DB::query("SELECT b.*  FROM ".DB::table('common_template_block')." tb LEFT JOIN ".DB::table('common_block')." b ON b.bid = tb.bid  WHERE tb.targettplname='$targettplname'");
		$bclist = array();
		include_once libfile('function/block');
		while ($row = DB::fetch($query)) {

			   //获取各个block的数据
				//print_r($row['bid']);
				$_G['block'][$row['bid']] = $row ;
				block_updatecache($row['bid'], true);
				$cache_key = "block_".$row['bid'];
    			memory("rm", $cache_key);
				//print_r($_G['block'][$row['bid']]['data']['listdata']);
				//print_r($_G['block'][$row['bid']]["blockclass"]);

				//组件数据
				$listadata = $_G['block'][$row['bid']]['data']['listdata'];
				//组件类型
				$blockclass = $_G['block'][$row['bid']]["blockclass"];


				$bclist[$row['bid']]['listadata'] = $listadata;
				$bclist[$row['bid']]['blockclass'] = $blockclass;

				//焦点模式  top 即为一条数据
				if($_G['block'][$row['bid']]['data']['top']){

					$bclist[$row['bid']]['top'] = $_G['block'][$row['bid']]['data']['top'];
				}


				//print_r($_G['block'][$row['bid']]['data']['top']);

				//print_r($blocklist);
				//print_r($_G['block'][$row['bid']]);
        }


		if($_POST['delsubmit'] == '1'){

			$isdeletes = $_POST['is_delete']; //需要删除数据 [blockclass][contenttype][id]

			//print_r($isdeletes);

			$issuccess = true;

			if($isdeletes){
				foreach ($isdeletes as $bc=> $content) {
					//print_r($bc);
					//print_r($content);

					if($content){ //该组件有需要删除的内容

						$plugin= $blockpaths[$bc]; //插件目录
						$plugin = $_G['group_plugins']['group_available'][$plugin];

						$obj = "block_delete_".$blockpaths[$bc];
						$filename = "block/delete.inc.php";
						require_once(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . $filename ));
						$o = new $obj();

						//遍历
						foreach ($content as $contenttype=>$rows ){
							$ids = array();
							foreach ($rows as $cid=>$row ){
								$ids[]= $cid;
							}
							//print_r($ids);
							$issuccess = $o->deletedata($ids,$contenttype,$fid);
						}

					}

				}
			}
			$msg = "删除成功";
			if(!$issuccess){
				$msg = "删除失败";
			}
			//print_r($url);
			showmessage($msg, $url);
			//print_r($_POST['is_delete']);

			/*$script = 'notice';
			$obj = "block_delete_".$script;
            $blockclass = "block/block_".$script;
            require_once(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . $blockclass . '.php'));
            return new $obj();*/
		/*
			$script = 'notice_delete';
   			$plugin='notice';

			$plugin = $_G['group_plugins']['group_available'][$plugin];

            $obj = "block_delete_notice";
            $blockclass = "block/delete.inc";
            require_once(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . $blockclass . '.php'));

			print_r(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . $blockclass . '.php'));

			//$c = new $obj();
			//$c->deletedata('111','222','333');

			*/
			//print_r($_G['group_plugins']['group_available']);


		}





    }

	elseif ($_G['gp_op'] == 'checkuser') {
        $checktype = 0;
        $checkusers = array();
        if (!empty($_G['gp_uid'])) {
            $checkusers = array($_G['gp_uid']);
            $checktype = intval($_G['gp_checktype']);
        } elseif (getgpc('checkall') == 1 || getgpc('checkall') == 2) {
            $checktype = $_G['gp_checkall'];
            $query = DB::query("SELECT uid FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND level='0'");
            while ($row = DB::fetch($query)) {
                $checkusers[] = $row['uid'];
            }
        }
        if ($checkusers) {
            foreach ($checkusers as $uid) {
                if ($checktype === 1) {
                    notification_add($uid, 'group', 'group_member_check', array('groupname' => $_G['forum']['name'], 'url' => $_G['siteurl'] . 'forum.php?mod=group&fid=' . $_G['fid']), 1);
                }
            }
            if ($checktype == 1) {
                DB::query("UPDATE " . DB::table('forum_groupuser') . " SET level='4' WHERE uid IN(" . dimplode($checkusers) . ") AND fid='$_G[fid]'");
                foreach($checkusers as $uid){
                    $cache_key = "group_get_gorup_level_".$_G[fid]."_".$uid;
                    memory("rm", $cache_key);
                }
                update_usergroups($checkusers);
                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+" . count($checkusers) . " WHERE fid='$_G[fid]'");
            } elseif ($checktype == 2) {
                DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE uid IN(" . dimplode($checkusers) . ") AND fid='$_G[fid]'");
				notification_add($uid, 'group', 'group_member_check_false', array('groupname' => $_G['forum']['name'], 'url' => $_G['siteurl'] . 'forum.php?mod=group&fid=' . $_G['fid']), 1);
            }
            if ($checktype == 1) {
                showmessage('group_moderate_succeed', $url);
            } else {
                showmessage('group_moderate_failed', $url);
            }
        } else {
            $checkusers = array();
            $userlist = groupuserlist($_G['fid'], 'joindateline', $perpage, $start, array('level' => 0));
            $checknum = DB::result(DB::query("SELECT count(*) FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND level='0'"), 0);
            $multipage = multi($checknum, $perpage, $page, $url);
            foreach ($userlist as $user) {
                $user['joindateline'] = date('Y-m-d H:i', $user['joindateline']);
                $checkusers[$user['uid']] = $user;
            }
        }
    } elseif ($_G['gp_op'] == 'manageplugin') {


     	if ($_POST["plugin_ids"]) {
            foreach ($_POST["plugin_ids"] as $plugin_id) {
                $one = $_POST["m"][$plugin_id];
               // $fname=!empty($_POST["fname"][$plugin_id])?$_POST["fname"][$plugin_id]:$_G["group_plugins"]["group_all"][$plugin_id]["name"];   //update by qiaoyongzhi
                $query = DB::query("SELECT * FROM " . DB::table("common_group_plugin") . " WHERE fid=" . $_G["gp_fid"] . " AND plugin_id='" . $plugin_id . "'");
                $result = DB::fetch($query);
                if (!$result["id"]) {
                    $data = array(fid => $_G["gp_fid"], plugin_id => $plugin_id, status => 'N');
                    DB::insert("common_group_plugin", $data);
                }
                if ($one == "disable") {
                    $data["status"] = 'N';
                    DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $plugin_id));
					if($plugin_id=='repeats'){
						include_once libfile('function/repeats','plugin/repeats');
						closerepeatsbyfid($_G[fid]);
					}
                } elseif ($one == "enable") {
                    $data["status"] = 'Y';
                    DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $plugin_id));
					if($plugin_id=='repeats'){
						include_once libfile('function/repeats','plugin/repeats');
						createrepeats($_G[forum][name],$_G[fid],$_G[uid],'1');
					}
                }

                $oper = $_POST["oper"][$plugin_id];
                //更新权限
                $data["auth_group"] = $oper;
               // $data['fname']=$fname;   //update by qiaoyongzhi
                DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $plugin_id));
            }

        }

        $query = DB::query("SELECT plugin_id,auth_group,status,fname FROM " . DB::table('common_group_plugin') . " WHERE fid=" . $_G["fid"]);   //update by qiaoyongzhi
        $cur_group_plugins = array();


        $cache_group_get_plugin_available = array(); // add by songsp 2011-3-23 15:03:23  性能优化

        while ($row = DB::fetch($query)) {
            $cur_group_plugins[$row["plugin_id"]] = $row;

        	if ($row["status"] == "Y") {
				$cache_group_get_plugin_available[$row["plugin_id"]] = $row;
			}

        }

        //add  by songsp  2011-3-23 15:05:24  性能优化
        $allowmem = memory('check');
		$cache_key = 'group_get_plugin_available_'.$_G["fid"] ; //UezBhH_
        if($allowmem){
			memory("set", $cache_key, serialize($cache_group_get_plugin_available), 86400); //60*60*24  24小时

		}



		if ($_POST["plugin_ids"]) {  // 如果是提交操作 ，跳转到提示页面
			 showmessage("保存成功", $url);
		}


        //查询用户等级
        //$query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
        $levels = array(array(level_name => "仅专区成员"), array(level_name => "全部"), array(level_name => "群主"), array(level_name => "副群主"));
/*        while ($row = DB::fetch($query)) {
            $levels[] = $row;
        }*/

        memory("rm", "group_load_plugin");
    } elseif ($_G['gp_op'] == 'manageempirical') {
        $t_ac = $_G["gp_m"] ? $_G["gp_m"] : "index";
        if ($t_ac == "index") {
            group_create_empirical($_G["gp_fid"]);
            $query = DB::query("SELECT gev.id,gev.value,ge.name,ge.ename,gev.status FROM " . DB::table("group_empirical") . "  ge," . DB::table("group_empirical_values") . " gev WHERE ge.id=gev.eid AND gev.fid=" . $_G["gp_fid"]);
            $group_empirical_op_settings = array();
            while ($row = DB::fetch($query)) {
                $group_empirical_op_settings[$row["id"]] = $row;
            }
        } elseif ($t_ac == "save") {
            $status = $_POST["status"];
            foreach ($_G["gp_values"] as $key => $values) {
                $stat = $status[$key] ? 'Y' : 'N';
				if(!$values)  $values=0;//add by qiaoyz,2011-4-1
                DB::query("UPDATE " . DB::table("group_empirical_values") . " SET value=" . $values . ", status='" . $stat . "' WHERE id=" . $key . " AND fid=" . $_G["gp_fid"]);
            }
            showmessage("更新经验值列表成功", $url);
        }
    } elseif ($_G[gp_op]=="settingempirical"){
        $t_ac = $_G["gp_m"] ? $_G["gp_m"] : "index";
        if($t_ac=="index"){
            $query = DB::query("SELECT * FROM ".DB::table("group_empirical_values")." WHERE id=".$_G[gp_id]);
            $result = DB::fetch($query);
            // 存数据库时存秒数，显示时需要转换为分钟
            if($result['cycle'] != 0 ){
                $result['cycle'] = $result['cycle']/60;
            }
        }else if($t_ac=="save"){
            $cycle = $_G[gp_cycle]?$_G[gp_cycle]:0;
            $total = $_G[gp_total]?$_G[gp_total]:0;
            $cycle = $cycle*60;
            DB::query("UPDATE ".DB::table("group_empirical_values")." SET cycletype=".$_G[gp_cycletype].", cycle=".$cycle.",total=".$total." WHERE id=".$_G[gp_id]);
            showmessage("更新数据成功", "forum.php?mod=group&action=manage&op=manageempirical&fid=".$_G[fid]);
        }
    } elseif ($_G["gp_op"] == "managecategory") {
        $typenumlimit = 20;
        $plugin_id = $_G["gp_id"];
        require_once libfile("function/category");
        if ($_POST["m"] == "save") {
            common_category_create_plugin_category($plugin_id, array(state => $_POST["state"], required => $_POST["required"], prefix => $_POST["prefix"], fid => $_G["gp_fid"]));
            $categorys = $_POST["categorys"];
            //更新
            foreach ($categorys["displayorder"] as $key => $value) {
                $data["displayorder"] = $value[0];
                $data["name"] = $categorys["name"][$key][0];
                DB::update("common_category", $data, array("id" => $key));
            }
            //删除
            if ($categorys["delete"]) {
                DB::query("DELETE FROM " . DB::table("common_category") . " WHERE id IN (" . implode(",", $categorys["delete"]) . ")");
            }
           //新建
            $new_categorys = $_POST["newdisplayorder"];
            foreach ($new_categorys as $key => $value) {
                $data["displayorder"] = $value;
                $data["name"] = $_POST["newname"][$key];
                if(!$data["name"]){
                	showmessage("新增的分类名称不能为空,请重新输入", $url . "&id=" . $plugin_id);
                }
                //select * from pre_common_category where  fid='194' and pid='activity' and name='第九季';
                $judge=DB::query("SELECT * FROM " . DB::table("common_category") . " WHERE name = '" . $data["name"] . "' and fid='".$_G["gp_fid"]."' and pid='".$plugin_id."'");
                if(mysql_num_rows($judge)>=1) {showmessage("类别已存在", $url . "&id=" . $plugin_id);}
                else
                      common_category_create_category($_G["gp_fid"], $plugin_id, $data["name"], $data["displayorder"]);
            }
            showmessage("更新类别成功", $url . "&id=" . $plugin_id);
        }
        $other_info = common_category_is_other($_G["gp_fid"], $plugin_id);
        $display = "none";
        $status_no = 'checked="true"';
        $required_no = 'checked="true"';
        $prefix_no = 'checked="true"';

        if ($other_info["state"] == "Y") {
            $status_yes = 'checked="true"';
            $status_no = "";
            $display = "";
        }
        if ($other_info["required"] == "Y") {
            $required_yes = 'checked="true"';
            $required_no = "";
        }
        if ($other_info["prefix"] == "Y") {
            $prefix_yes = 'checked="true"';
            $prefix_no = "";
        }
        $categorys = common_category_get_category($_G["gp_fid"], $plugin_id);
    } elseif ($_G["gp_op"] == "manage_level") {
        if (!is_array($_POST["level"]) && $_G["gp_m"] != "save" && !is_array($_POST["ids"])) {
            $query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
            $levels = array();
            while ($row = DB::fetch($query)) {
                $levels[] = $row;
            }
        }
        if (is_array($_POST["ids"])) {
            DB::query("DELETE FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"] . " AND id IN (" . implode(",", $_POST["ids"]) . ")");
            showmessage("删除成功", $url);
        }
        if ($_G["gp_m"] == "save") {
            $data["level_name"] = $_POST["level_name"];
            $data["start_emp"] = $_POST["start_emp"];
            $data["end_emp"] = $_POST["end_emp"];
            $data["fid"] = $_G["fid"];
            DB::insert("forum_userlevel", $data);
            showmessage("保存成功", $url);
        }
        if (is_array($_POST["level"])) {
            foreach ($_POST["level"] as $key => $item) {
                $data["level_name"] = $_POST["level"][$key]["level_name"];
                $data["start_emp"] = $_POST["level"][$key]["start_emp"];
                $data["end_emp"] = $_POST["level"][$key]["end_emp"];
                DB::update("forum_userlevel", $data, array(id => $key, fid => $_G["fid"]));
            }
            showmessage("更新成功", $url);
        }
    } elseif ($_G['gp_op'] == 'manage_adv') {

    } elseif ($_G['gp_op'] == 'manageuser') {
        $mtype = array(1 => lang('group/template', 'group_moderator'),
            2 => lang('group/template', 'group_moderator_vice'),
            4 => lang('group/template', 'group_normal_member'),
            5 => lang('group/template', 'group_goaway'));
        $query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
        while ($row = DB::fetch($query)) {
            $mtype["level_".$row["id"]] = $row["level_name"];
        }
        if (!submitcheck('manageuser') && !submitcheck("updateempirical") && !submitcheck("createexpert")) {
            $userlist = array();
            $staruserlist = $page < 2 ? groupuserlist($_G['fid'], '', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';

            $staruseruids = array();
            foreach($staruserlist as $startuser){
                $staruseruids[] = $startuser[uid];
            }
            $staruserrealname = user_get_user_realname($staruseruids);

            $adminuserlist = $groupmanagers && $page < 2 ? $groupmanagers : array();

            $adminuseruids = array();
            foreach($adminuserlist as $adminuser){
                $adminuseruids[] = $adminuser[uid];
            }
            $adminuserrealname = user_get_user_realname($adminuseruids);

			$usercount=DB::result_first("SELECT count(*) FROM ".DB::TABLE("forum_groupuser")." WHERE level= '4' and ( pre_forum_groupuser.uid not in ( SELECT pre_expertuser.uid FROM pre_expertuser) ) and fid=".$_G['fid']." AND level='4'");
            $userlist = groupuserlist($_G['fid'], '', $perpage, $start, "AND level='4'");

            $usersuids = array();
            foreach($userlist as $user){
                $usersuids[] = $user[uid];
            }
            $userlistrealname = user_get_user_realname($usersuids);

            $expertuserlist = groupexpertuserlist($_G['fid'], '', 0, 0, "AND level='4'");
            $expertusersuids = array();
            foreach($expertuserlist as $user){
                $expertusersuids[] = $user[uid];
            }
            $expertuserlistrealname = user_get_user_realname($expertusersuids);

            $multipage = multi($usercount, $perpage, $page, $url);
        } else if (submitcheck("updateempirical")) {
            $empiricals = $_POST["empiricals"];
            foreach ($empiricals as $emp => $value) {
                DB::query("UPDATE " . DB::table("forum_groupuser") . " SET empirical_value=$value[0] WHERE fid=" . $_G[fid] . " AND uid=" . $emp);
            }
            showmessage('修改经验值成功', $url);
        } else if (submitcheck("createexpert")) {
        	if (!$_POST['expertname']) {
        		showmessage('请输入专家用户真实姓名', $url);
        	} else {
        		require_once libfile("function/org");
        		if ($_G['forum']['founderuid'] != $_G['uid']) {
        			showmessage('您没有权限创建外部专家', $url);
        		}
        		$userarr = array();
        		$userarr['name'] = base64_encode($_POST['expertname']);
        		$userarr['fids'] = $_G['fid'];
        		$userarr['fname'] = base64_encode(get_groupname_by_fid($_G['fid']));
        		$userarr['furl'] = $_G['siteurl']."forum.php?mod=group&fid=".$_G['fid'];
        		$creatorRegName = $_G['username'];
	        	$result = create_expert_user($userarr, $creatorRegName);
	        	if ($result['result']['success']!=1) {
	        		showmessage('新建专家用户失败', $url);
	        	} else {
	        		group_add_user_to_group($result['expertuid'], $result['username'], $_G['fid']);
	        		$expertinfo['uid'] = $result['expertuid'];
	        		$expertinfo['username'] = $result['username'] ;
	        		$expertinfo['fid'] = $_G['fid'];
	        		$expertinfo['activelink'] = $result['activelink'];
	        		insert_expertuser($expertinfo);
	        		showmessage('新建专家用户成功', $url);
	        	}
        	}
        } else {
            if (count($groupuser) == 1) {
                showmessage('group_admin_only_one_member', $url);
            }
            $muser = getgpc('muid');
            $targetlevel = $_G['gp_targetlevel'];
            if ($muser && is_array($muser)) {
                foreach ($muser as $muid => $mlevel) {
                    if ($_G['forum']['founderuid'] != $_G['uid'] && $groupmanagers[$muid] && $groupmanagers[$muid]['level'] <= $groupuser['level']) {
                        showmessage('group_member_level_admin_noallowed.', $url);
                    }
                    if ($muid != $_G['uid'] && ($_G['forum']['founderuid'] == $_G['uid'] || !$groupmanagers[$muid] || $groupmanagers[$muid]['level'] > $groupuser['level'])) {
                        if ($targetlevel == 2 || $targetlevel == 4 || $targetlevel == 1) {
                            DB::query("UPDATE " . DB::table('forum_groupuser') . " SET level='$targetlevel',lastupdate='".time()."' WHERE uid='$muid' AND fid='$_G[fid]'");
                            $cache_key = "group_get_gorup_level_".$_G[fid]."_".$muid;
                            memory("rm", $cache_key);
                        } else if($targetlevel==5){
                            if (!$groupmanagers[$muid] || count($groupmanagers) > 1) {
                                DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE uid='$muid' AND fid='$_G[fid]'");
                                $cache_key = "group_member_".$_G[fid]."_".$muid;
                                memory("rm", $cache_key);
                                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
                                update_usergroups($muid);
                            } else {
                                showmessage('group_only_one_moderator', $url);
                            }
                        }else {
                            //经验值不同的等级
							$reult = explode("_", $targetlevel);
//                            $reult[1] = $targetlevel;
                            $queri=DB::query("SELECT empirical_value FROM ".DB::table("forum_groupuser")." WHERE uid=".$muid);
                            $resulti = DB::fetch($queri);

                            $query = DB::query("SELECT start_emp FROM ".DB::table("forum_userlevel")." WHERE id=".$reult[1]);
                            $result = DB::fetch($query);
                            $gap=$result['start_emp']-$resulti['empirical_value'];
                            DB::query("UPDATE " . DB::table("forum_groupuser") . " SET empirical_value=$result[start_emp] WHERE fid=" . $_G[fid] . " AND uid=" . $muid);
                        }
                    }
                }
                update_groupmoderators($_G['fid']);
                if($gap>0) showmessage('专区设置成功更新。'.'<br/>由于用户经验值不在新等级的经验值范围，在提交时用户经验值将被更改。', $url . '&page=' . $page);
                      else showmessage('group_setup_succeed' , $url . '&page=' . $page);
            } else {
                showmessage('group_choose_member', $url);
            }
        }
    } elseif ($_G['gp_op'] == 'threadtype') {
        if (empty($specialswitch['allowthreadtype'])) {
            showmessage('group_level_cannot_do');
        }
        if ($_G['forum']['founderuid'] != $_G['uid']) {
            showmessage('group_threadtype_only_founder');
        }
        $typenumlimit = 20;
        if (!submitcheck('groupthreadtype')) {
            $threadtypes = $checkeds = array();
            if (empty($_G['forum']['threadtypes'])) {
                $checkeds['status'][0] = 'checked';
                $display = 'none';
            } else {
                $display = '';
                $_G['forum']['threadtypes']['status'] = 1;
                foreach ($_G['forum']['threadtypes'] as $key => $val) {
                    $val = intval($val);
                    $checkeds[$key][$val] = 'checked';
                }
            }

            $query = DB::query("SELECT * FROM " . DB::table('forum_threadclass') . " WHERE fid='{$_G['fid']}' ORDER BY displayorder");
            while ($type = DB::fetch($query)) {
                $type['enablechecked'] = isset($_G['forum']['threadtypes']['types'][$type['typeid']]) ? ' checked="checked"' : '';
                $type['name'] = dhtmlspecialchars($type['name']);
                $threadtypes[] = $type;
            }
        } else {
            $threadtypesnew = $_G['gp_threadtypesnew'];
            $threadtypesnew['types'] = $threadtypes['special'] = $threadtypes['show'] = array();
            if (is_array($_G['gp_newname']) && $_G['gp_newname']) {
                $newname = array_unique($_G['gp_newname']);
                if ($newname) {
                    foreach ($newname as $key => $val) {
                        $val = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($val)), 16, ''))));
                        if ($_G['gp_newenable'][$key] && $val) {
                            $newtypeid = DB::result_first("SELECT typeid FROM " . DB::table('forum_threadclass') . " WHERE fid='{$_G['fid']}' AND name='$val'");
                            if (!$newtypeid) {
                                $typenum = DB::result_first("SELECT count(*) FROM " . DB::table('forum_threadclass') . " WHERE fid='{$_G['fid']}'");
                                if ($typenum < $typenumlimit) {
                                    $threadtypes_newdisplayorder = intval($_G['gp_newdisplayorder'][$key]);
                                    $newtypeid = DB::insert('forum_threadclass', array('fid' => $_G['fid'], 'name' => $val, 'displayorder' => $threadtypes_newdisplayorder), 1);
                                }
                            }
                            if ($newtypeid) {
                                $threadtypesnew['types'][$newtypeid] = $val;
                            }
                        }
                    }
                }
                $threadtypesnew['status'] = 1;
            } else {
                $newname = array();
            }
            if ($threadtypesnew['status']) {
                if (is_array($threadtypesnew['options']) && $threadtypesnew['options']) {

                    if (!empty($threadtypesnew['options']['enable'])) {
                        $typeids = implodeids(array_keys($threadtypesnew['options']['enable']));
                    } else {
                        $typeids = '0';
                    }
                    if (!empty($threadtypesnew['options']['delete'])) {
                        $threadtypes_deleteids = implodeids($threadtypesnew['options']['delete']);
                        DB::query("DELETE FROM " . DB::table('forum_threadclass') . " WHERE `typeid` IN ($threadtypes_deleteids) AND fid='{$_G['fid']}'");
                    }
                    $query = DB::query("SELECT * FROM " . DB::table('forum_threadclass') . " WHERE typeid IN ($typeids) AND fid='{$_G['fid']}' ORDER BY displayorder");
                    while ($type = DB::fetch($query)) {
                        if ($threadtypesnew['options']['enable'][$type['typeid']]) {
                            $threadtypesnew['types'][$type['typeid']] = $threadtypesnew['options']['name'][$type['typeid']];
                        }
                        if ($threadtypesnew['options']['name'][$type['typeid']] != $type['name'] || $threadtypesnew['options']['displayorder'][$type['typeid']] != $type['displayorder']) {
                            $threadtypesnew['options']['name'][$type['typeid']] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($threadtypesnew['options']['name'][$type['typeid']])), 16, ''))));
                            $threadtypesnew['options']['displayorder'][$type['typeid']] = intval($threadtypesnew['options']['displayorder'][$type['typeid']]);
                            DB::update('forum_threadclass', array(
                                        'name' => $threadtypesnew['options']['name'][$type['typeid']],
                                        'displayorder' => $threadtypesnew['options']['displayorder'][$type['typeid']],
                                            ), array('typeid' => "{$type['typeid']}", 'fid' => "{$_G['fid']}"));
                        }
                        $threadtypesnew['icons'][$type['typeid']] = trim($threadtypesnew['options']['icon'][$type['typeid']]);
                    }
                }
                $threadtypesnew = $threadtypesnew['types'] ? addslashes(serialize(array
                                    (
                                    'required' => (bool) $threadtypesnew['required'],
                                    'listable' => (bool) $threadtypesnew['listable'],
                                    'prefix' => $threadtypesnew['prefix'],
                                    'types' => $threadtypesnew['types']
                                ))) : '';
            } else {
                $threadtypesnew = '';
            }
            DB::update('forum_forumfield', array('threadtypes' => $threadtypesnew), "fid='{$_G['fid']}'");
            showmessage('group_threadtype_edit_succeed', $url);
        }
    } elseif ($_G['gp_op'] == 'demise') {
        if (!empty($_G['forum']['founderuid']) && $_G['forum']['founderuid'] == $_G['uid']) {
            $ucresult = $allowbuildgroup = $groupnum = 0;
            unset($groupmanagers[$_G['forum']['founderuid']]);
            if (empty($groupmanagers)) {
                showmessage('group_cannot_demise');
            }

            if (submitcheck('groupdemise')) {
                $suid = intval($_G['gp_suid']);
                if (empty($suid)) {
                    showmessage('group_demise_choose_receiver');
                }
                /*if (empty($_G['gp_grouppwd'])) {
                    showmessage('group_demise_password');
                }
                loaducenter();
                $ucresult = uc_user_login($_G['uid'], $_G['gp_grouppwd'], 1);
                if (!is_array($ucresult) || $ucresult[0] < 1) {
                    showmessage('group_demise_password_error');
                }*/
                $user = getuserbyuid($suid);
                loadcache('usergroup_' . $user['groupid']);
                $allowbuildgroup = $_G['cache']['usergroup_' . $user['groupid']]['allowbuildgroup'];
                if ($allowbuildgroup > 0) {
                    $groupnum = DB::result_first("SELECT count(*) FROM " . DB::table('forum_forumfield') . " WHERE founderuid='$suid'");
                }
                if (empty($allowbuildgroup) || $allowbuildgroup - $groupnum < 1) {
                    showmessage('group_demise_receiver_cannot_do');
                }
                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET founderuid='$suid', foundername='{$user['username']}' WHERE fid='$_G[fid]'");
                DB::query("UPDATE " . DB::table("forum_groupuser") . " SET level=1 WHERE fid=" . $_G['fid'] . " AND uid=" . $suid);
                $cache_key = "group_founder_".$_G[fid]."_".$user[uid];
                memory("rm", $cache_key);
                update_usergroups($_G['uid']);
                group_update_forum_hot($_G["fid"]);
                delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
                update_groupmoderators($_G['fid']);
                include_once libfile('function/group');
                group_update_forum_hot($_G["fid"]);

                sendpm($suid, lang('group/template', lang('group/template', 'group_demise_message_title', array('forum' => $_G['forum']['name'])), lang('group/template', 'group_demise_message_body', array('forum' => $_G['forum']['name'], 'siteurl' => $_G['siteurl'], 'fid' => $_G['fid'])), $_G['uid']));
                showmessage('group_demise_succeed', 'forum.php?mod=group&action=manage&fid=' . $_G['fid']);
            }
        } else {
            showmessage('group_demise_founder_only');
        }
    } elseif ($_G['gp_op'] == 'delete') {
        if (!empty($_G['forum']['founderuid']) && ($_G['forum']['founderuid'] == $_G['uid']||$_G['group']['groupid']=='1')) {
        	if (submitcheck('groupdemise')) {
        		include_once libfile('function/group');
        		$fidarray = array($_G['fid']);
				if($_G['group']['groupid']=='1'){
        			$rtn = group_delete_by_useradmin($fidarray, true);
				}else{
					$rtn = group_delete_by_useradmin($fidarray, false);
				}
        		if ($rtn) {
        			showmessage('group_delete_succeed', 'group.php');
        		} else {
        			showmessage('group_delete_failure');
        		}
        	}
        } else {
            showmessage('group_delete_founder_only');
        }
    } elseif ($_G['gp_op'] == 'manage_stationcourse') {
    	require_once (dirname(dirname(dirname(__FILE__)))."/plugin/stationcourse/function/function_stationcourse.php");
    	$total_station=statistics();
    }elseif ($_G['gp_op'] == 'manage_shlecturer') {
    } elseif ($_G['gp_op'] == 'manage_extraresource') {
    } elseif ($_G['gp_op'] == 'manage_extraorg') {
    } elseif ($_G['gp_op'] == 'manage_extralec') {
    } elseif ($_G['gp_op'] == 'manage_extraclass') {
    } elseif ($_G['gp_op'] == 'manage_shresourcelist') {
    	require_once (dirname(dirname(dirname(__FILE__)))."/plugin/stationcourse/function/function_stationcourse.php");
    	$total_station=statistics();
    } else {
        showmessage('undefined_action');
    }
    include template('diy:group/group:' . $_G['fid']);
}
?>