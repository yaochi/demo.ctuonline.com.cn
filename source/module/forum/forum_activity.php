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

require_once libfile('function/group');
$_G['action']['action'] = 3;
$_G['action']['fid'] = $_G['fid'];
$_G['basescript'] = 'group';
if (!$_G[forum][founderuid]) {
	showmessage('活动不存在', 'group.php');
}
$_G[forum][realname] = user_get_user_name($_G[forum][founderuid]);
//马甲引入
$anonymity=DB::result_first("select anonymity from ".DB::TABLE("home_feed")." where icon='activitys' and id=".$_G['fid']);
if($anonymity>0){
	include_once libfile('function/repeats','plugin/repeats');
	$repeats=getforuminfo($anonymity);
}

$actionarray = array('join', 'out', 'create', 'viewmember', 'manage', 'index', 'memberlist', 'plugin');
$action = getgpc('action') && in_array($_G['gp_action'], $actionarray) ? $_G['gp_action'] : 'index';
if (in_array($action, array('join', 'out', 'create', 'manage'))) {
    if (empty($_G['uid'])) {
        showmessage('not_loggedin', '', '', array('login' => 1));
    }
}
if (empty($_G['fid']) && $action != 'create') {
    showmessage('活动重定向', 'group.php');
}
$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="' . $_G['setting']['bbname'] . ' - ' . $navtitle . '" href="' . $_G['siteurl'] . 'forum.php?mod=rss&fid=' . $_G['fid'] . '&amp;auth=' . $rssauth . "\" />\n") : '';
$navtitle = "活动";
if ($_G['fid']) {
    $navtitle .= ' - ' . $_G['forum']['name'];
    $metakeywords = $_G['forum']['metakeywords'];
    $metadescription = $_G['forum']['metadescription'];
    if ($_G['forum']['status'] != 3) {
        showmessage('活动不存在', 'group.php');
    } elseif ($_G['forum']['jointype'] < 0 && !$_G['forum']['ismoderator']) {
        showmessage('活动禁止进入', 'group.php');
    }
    $groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'), 604800);

    $_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
    $_G['forum']['banner'] = get_groupimg($_G['forum']['banner']);
    $_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');
    $_G['forum']['posts'] = intval($_G['forum']['posts']);
    $_G['grouptypeid'] = $_G['forum']['fup'];
    if($_G['forum']['fup']){
        $sql = "SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$_G['forum']['fup'];
        $query = DB::query($sql);
        $_G['parent'] = DB::fetch($query);
        $_G['parent']["icon"] = get_groupimg($_G['parenet']["icon"], 'icon');
        
        require_once libfile("function/category");
        $is_enable_category = false;
        $other_plugin_info = common_category_is_other($_G['parent'][fid], "activity");
        if($other_plugin_info["state"]=='Y' && $other_plugin_info['prefix']=='Y'){
            $is_enable_category = true;
            $categorys = common_category_get_category($_G['parent'][fid], "activity");
        }
    }
    require_once libfile("function/misc");
    $is_in_cur_group = check_user_group($_G[username],$_G[fid]);
    $is_in_parent_group = check_user_group($_G[username],$_G['forum']['fup']);
    $groupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
    $onlinemember = grouponline($_G['fid'], 1);
    $groupmanagers = $_G['forum']['moderators'];
    //将专区管理员加入活动管理组
    $query = DB::query("SELECT fff.founderuid uid, fff.foundername username, ff.fup fup FROM ".DB::table("forum_forumfield")." fff, ".DB::table("forum_forum")." ff WHERE ff.fup=fff.fid AND ff.fid=".$_G['fid']);
    while ($row=DB::fetch($query)) {
    	$groupfmanager = $row;
    }
    require_once libfile("function/credit");
    credit_create_credit_log($_G[uid], "viewevent", $_G[fid]);
    
    if(empty($_COOKIE["view_activity_".$_G[uid]."_".$_G[fid]])){
        //增加浏览次数
        DB::query("UPDATE " . DB::table("forum_forum_activity") . " SET viewnum=viewnum+1 WHERE fid=" . $_G["fid"]);
        //查看活动经验值activity_view
    	group_add_empirical_by_setting($_G['uid'], $_G['parent'][fid], 'activity_view', $_G[fid]);
        setcookie("view_activity_".$_G[uid]."_".$_G[fid], "true", time()+86400);
    }
    
    $query = DB::query("SELECT * FROM " . DB::table("forum_forum_activity") . " WHERE fid=" . $_G["fid"]);
    $_G["activity"] = DB::fetch($query);
    if ($_G["activity"]) {
        $_G["activity"][live_id] = unserialize($_G["activity"][live_id]);
        $extra = unserialize($_G[forum][extra]);
        if($extra["startime"]!=0 && $extra["endtime"]!=0){
            $_G["activity"]["str_start_time"] = getdate($extra["startime"]);
            $_G["activity"]["str_end_time"] = getdate($extra["endtime"]);
            /*if($extra["endtime"]>time() && $extra["signuptime"]>time()){
                $_G[activity][joinflag] = true;
            }*/
        }
        
        // modifiy by  sonsp 2010-12-4 
        $_G['activity']['joinflag'] = true;  
        if($extra["endtime"]!=0 &&  $extra["endtime"]<time() ) $_G['activity']['joinflag'] = false;
        if($extra["signuptime"]!=0 && $extra["signuptime"]<time()) $_G['activity']['joinflag'] = false;

        
        if($_G["activity"]["teacher_id"]){
            $teacher_ids = unserialize($_G["activity"]["teacher_id"]);
			foreach($teacher_ids as $i=>$teacherid){
				if($teacherid){
					$teacher_ids[$i]=$teacherid;
				}else{
					$teacher_ids[$i]='0';
				}
			}
			
            $t = implode(",", $teacher_ids);
            if(count($teacher_ids)!=0 && $t){
                $query = DB::query("SELECT * FROM " . DB::table("lecturer") . " WHERE id IN(" . $t .")" );
                $img_activity = $_G['forum']['banner'];
                while($result = DB::fetch($query)){
                	$rtnn = check_is_group_teacher($_G['forum']['fup'], $result[id]);
                	if ($rtnn) {
                		$result[imgurl] = $rtnn[imgurl];
                		$result[about] = $rtnn[about];
                		$result[url] = "forum.php?mod=group&action=plugin&fid=".$rtnn["fid"]."&plugin_name=lecturer&plugin_op=viewmenu&lecid=".$rtnn["id"]."&lecturer_action=index";
                		if (!$img_activity && $result[imgurl]) {
                			$img_activity = $result[imgurl];
                		}
                	} else {
                		$result[url] = "forum.php?mod=group&action=plugin&fid=".$result["fid"]."&plugin_name=lecturermanage&plugin_op=viewmenu&lecturermanage_action=index&lecid=".$result["id"];
                	}
                    $_G["activity"]["teacher"][] = $result;
                }
            }
        }

        if ($_G["activity"][live_id]) {
			foreach($_G["activity"][live_id] as $i=>$activityid){
				if($activityid){
					$_G["activity"][live_id][$i]=$activityid;
				}else{
					$_G["activity"][live_id][$i]='0';
				}
			}
		    $t = implode(",", $_G["activity"][live_id]);
            if($t){
                $query = DB::query("SELECT * FROM " . DB::table("group_live") . " WHERE liveid IN(" . $t . ")");
                while ($live = DB::fetch($query)) {
					if($live["starttime"]>time()){
						$live[whatstatus]='0';
					}elseif($live["endtime"]<time()){
						$live[whatstatus]='2';
					}else{
						$live[whatstatus]='1';
					}
                    $live[starttime] = dgmdate($live[starttime],'Y-m-d H:i');
                    $live[endtime] = dgmdate($live[endtime],'Y-m-d H:i');
                    $_G[activity][live][] = $live;
                }
            }
        }
		
        $query = DB::query("SELECT * FROM ".DB::table("forum_groupuser")." WHERE fid=$_G[fid] AND level>0 ORDER BY joindateline DESC LIMIT 8");
        while($user=DB::fetch($query)){
            $new_join_user[] = $user;
        }
    }
}

if (in_array($action, array('out', 'viewmember', 'manage', 'index', 'memberlist','plugin'))) {  //添加 by songsp  'plugin'
    $status = groupperm($_G['forum'], $_G['uid'], $action, $groupuser);
    if ($status == -1) {
        showmessage('活动不存在', 'group.php');
    } elseif ($status == 1) {
        showmessage('活动状态已经关闭');
    }
    if ($action != 'index') {
        if ($status == 2) {
            showmessage('活动不允许', "forum.php?mod=activity&fid=$_G[fid]");
        } elseif ($status == 3) {
            showmessage('forum_group_moderated', "forum.php?mod=activity&fid=$_G[fid]");
        }
    }
}

if (in_array($action, array('index')) && $status != 2) {

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

    $groupviewed_list = get_viewedgroup();
}

$groupnav = get_groupnav($_G['forum']);
$activitynav=get_activitynav($_G['forum']);
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
}
$groupquery=DB::query("SELECT fa.fid,fa.fup,fa.name as aname,ff.name as gname FROM ".DB::table('forum_forum')." fa ,".DB::table('forum_forum')." ff WHERE fa.fup=ff.fid AND fa.fid='$_G[fid]'");
$groupinf=DB::fetch($groupquery);
if ($action == 'index') {

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
        $newuserlist = $activityuserlist = array();
        $newuserlist = array_slice($groupcache['newuserlist']['data'], 0, 4);
        foreach ($newuserlist as $user) {
            $newuserlist[$user['uid']] = $user;
            $newuserlist[$user['uid']]['online'] = !empty($onlinemember['list']) && is_array($onlinemember['list']) && !empty($onlinemember['list'][$user['uid']]) ? 1 : 0;
        }
    }

    write_groupviewed($_G['fid']);
    $extra = unserialize($_G["forum"]["extra"]);
    $_G[forum][startime] = date("Y-m-d H:i", $extra["startime"]);
    $_G[forum][endtime] = date("Y-m-d H:i", $extra["endtime"]);
    $isold = time() > $extra["endtime"];
    $_G[forum][realname] = user_get_user_name($_G[forum][founderuid]);
    include template('diy:group/activity:' . $_G['fid']);
} elseif ($action == "plugin") {
    if ($_G['gp_plugin_op'] == "createmenu") {
        include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
        include template('group/activity_plugincreate');
    } else {
        include join_group_pluginmodule($_G['gp_plugin_name'], $_G['gp_plugin_op']);
        include template('diy:group/activity:' . $_G['fid']);
    }
} elseif ($action == 'memberlist') {

    $oparray = array('card', 'address', 'alluser');
    $op = getgpc('op') && in_array($_G['gp_op'], $oparray) ? $_G['gp_op'] : 'alluser';
    $page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;

    $alluserlist = $adminuserlist = array();
    $staruserlist = $page < 2 ? groupuserlist($_G['fid'], 'lastupdate', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
    $adminlist = $groupmanagers && $page < 2 ? $groupmanagers : array();
    $startuids = array();
    foreach($staruserlist as $startuser){
        $startuids[] = $startuser[uid];
    }
    $startuserrealname = user_get_user_realname($startuids);
    
    if ($op == 'alluser') {
        $alluserlist = groupuserlist($_G['fid'], 'lastupdate', $perpage, $start, "AND level='4'", '', $onlinemember['list']);
        $multipage = multi($_G['forum']['membernum'], $perpage, $page, 'forum.php?mod=activity&action=memberlist&op=alluser&fid=' . $_G['fid']);

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
	$activitynav.=" &rsaquo; 活动成员";
    include template('diy:group/activity:' . $_G['fid']);
} elseif ($action == 'join') {
    $inviteuid = 0;
    /*$membermaximum = $_G['current_grouplevel']['specialswitch']['membermaximum'];
    if (!empty($membermaximum)) {
        $curnum = DB::result_first("SELECT count(*) FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]'");
        if ($curnum >= $membermaximum) {
            showmessage('活动人员已满', '', array('membermaximum' => $membermaximum));
        }
    }*/
    //BEGIN CHANGE BY QIAOYONGZHI 2011-2-22 13:00
    $invitekey=$_GET['key'];
    $invitekey=urldecode($invitekey);
    $invitekey=base64_decode($invitekey);
    $now=time();
    //add by qiaoyz,2011-3-24, EKSN-231 无法加入活动，提示时间过期
    $activity_jointype=DB::fetch_first("SELECT jointype FROM ".DB::TABLE('forum_forumfield')." where fid=".$_G['fid']);
    if($now>$invitekey&&$activity_jointype['jointype']=='1'){
    	showmessage('该邀请链接已过期','home.php?mod=space&do=home');
    }
	$favalue=DB::fetch_first("SELECT ff.fid,ff.name fname,fff.name name,field.jointype FROM ".DB::TABLE('forum_forum')." ff,".DB::TABLE('forum_forum')." fff,".DB::TABLE('forum_forumfield')." field WHERE field.fid=ff.fid AND ff.fid=fff.fup AND fff.fid=".$_G['fid']);
	$groupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid=$favalue[fid] AND uid='$_G[uid]'");
	//END
	if($favalue[jointype]=='2'&&($groupuser[level]=='0'||!$groupuser)){
		showmessage('“'.$favalue[name].'”活动属于“'.$favalue[fname].'”专区，您将首先加入专区','forum.php?mod=group&fid='.$favalue[fid]);
	}elseif($favalue[jointype]=='-1'&&($groupuser[level]=='0'||!$groupuser)){
		showmessage('“'.$favalue[fname].'”专区已关闭，您无法加入该活动','group.php');
	}elseif($favalue[jointype]=='3'&&($groupuser[level]=='0'||!$groupuser)){
		showmessage('“'.$favalue[fname].'”专区禁止任何人加入，您无法加入该活动，请联系管理员','group.php');
	}else{
		if(!$groupuser){
			DB::query("INSERT INTO ".DB::table('forum_groupuser')." (fid,uid,username,level,joindateline) values ('$favalue[fid]', '$_G[uid]', '$_G[username]', '4', '" . TIMESTAMP . "')");
			DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$favalue[fid]'");
		}
		if ($is_in_cur_group) {
			showmessage('您已加入该活动', "forum.php?mod=activity&fid=$_G[fid]");
		} else {
			$modmember = 4;
			$showmessage = '活动加入成功';
			$confirmjoin = TRUE;
			$inviteuid = DB::result_first("SELECT uid FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
			if ($_G['forum']['jointype'] == 3) {
				if (!$inviteuid) {
					$confirmjoin = FALSE;
					$showmessage = '活动不允许任何人加入';
				}
			}
			if ($_G['forum']['jointype'] == 1) {
				/*if (!$inviteuid) {
					$confirmjoin = FALSE;
					$showmessage = '活动需要邀请';
				}*/
			} elseif ($_G['forum']['jointype'] == 2) {
			   $modmember = !empty($groupmanagers[$inviteuid]) ? 4 : 0;
			   /* !empty($groupmanagers[$inviteuid]) &&
				echo "ok";*/
				$showmessage = '申请加入活动成功';
			};
			if ($confirmjoin) {
				DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('$_G[fid]', '$_G[uid]', '$_G[username]', '$modmember', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
				if ($_G['forum']['jointype'] == 2 && (empty($inviteuid) || empty($groupmanagers[$inviteuid]))) {
					foreach ($groupmanagers as $manage) {
						notification_add($manage['uid'], 'group', 'group_activity_member_join', array('url' => $_G['siteurl'] . 'forum.php?mod=activity&action=manage&op=checkuser&fid=' . $_G['fid'],'dateline'=>dgmdate(TIMESTAMP),'fup'=>$groupinf[fup],'groupname'=>$groupinf[gname],'activityname'=>$groupinf[aname]), 1);
					}
					group_add_empirical_by_setting($_G[uid], $_G[fid], "activity_apply", $_G[fid]);
				} else {
					update_usergroups($_G['uid']);
					group_add_empirical_by_setting($_G[uid], $_G[fid], "activity_join", $_G[fid]);
				}
				if ($inviteuid) {
					DB::query("DELETE FROM " . DB::table('forum_groupinvite') . " WHERE fid='$_G[fid]' AND inviteuid='$_G[uid]'");
				}
				if ($modmember == 4) {
					DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$_G[fid]'");
				}
				updateactivity($_G['fid'], 0);
				
			}
			include_once libfile('function/stat');
			updatestat('groupjoin');
			delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
			require_once libfile("function/credit");
			credit_create_credit_log($_G[uid], "joinevent", $_G[fid]);
			$cache_key = "group_member_".$_G[fid]."_".$_G[uid];
			memory("rm", $cache_key);
			showmessage($showmessage, "forum.php?mod=activity&fid=$_G[fid]");
		}
	}
} elseif ($action == 'out') {

    if ($_G['uid'] == $_G['forum']['founderuid']) {
        showmessage('活动创建者不能退出活动');
    }
    $showmessage = '活动退出成功';
    DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
    $cache_key = "group_member_".$_G[fid]."_".$_G[uid];
    memory("rm", $cache_key);
    DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
    update_usergroups($_G['uid']);
    delgroupcache($_G['fid'], array('activityuser', 'newuserlist'));
    showmessage($showmessage, "forum.php?mod=forumdisplay&fid=$_G[fid]");
} elseif ($action == 'create') {

    if (!$_G['group']['allowbuildgroup']) {
        showmessage('新加用户出错', "group.php");
    }
    $groupnum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('forum_groupuser') . " WHERE uid='$_G[uid]' AND level='1'");
    $allowbuildgroup = $_G['group']['allowbuildgroup'] - $groupnum;
    if ($allowbuildgroup < 1) {
        showmessage('group_create_max_failed');
    }

    if (!submitcheck('createsubmit')) {
        $groupselect = get_groupselect(getgpc('fupid'), getgpc('groupid'));
    } else {
        $_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 200, ''))));
        if (empty($_G['gp_name'])) {
            showmessage('活动名不能为空');
        } elseif (empty($_G['gp_fup'])) {
            showmessage('活动列表不能为空');
        }
        if (empty($_G['cache']['grouptype']['second'][$_G['gp_fup']])) {
            showmessage('group_category_error');
        }
        if (DB::result(DB::query("SELECT fid FROM " . DB::table('forum_forum') . " WHERE name='$_G[gp_name]'"), 0)) {
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
            $query = DB::query("SELECT group_type FROM " . DB::table("forum_forumfield") . " WHERE fid=" . $_G["fid"]);
            $group = DB::fetch($query);
            if ($group["group_type"]) {
                $group_type = $group["group_type"];
                DB::query("INSERT INTO " . DB::table('forum_forumfield') . "(fid, description, jointype, gviewperm, dateline, founderuid, foundername, membernum, tag, group_type) VALUES ('$newfid', '$descriptionnew', '$jointype', '$gviewperm', '" . TIMESTAMP . "', '$_G[uid]', '$_G[username]', '1', '$tagnew', '$group_type')");
                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET groupnum=groupnum+1 WHERE fid='$_G[gp_fup]'");
                DB::query("INSERT INTO " . DB::table('forum_groupuser') . "(fid, uid, username, level, joindateline) VALUES ('$newfid', '$_G[uid]', '$_G[username]', '1', '" . TIMESTAMP . "')");
                //group_create_empirical($newfid);
                group_create_plugin($newfid);
                require_once libfile('function/cache');
                updatecache('grouptype');
            }
        }
        include_once libfile('function/stat');
        updatestat('group');
        require_once libfile("function/credit");
        credit_create_credit_log($_G[uid], "createevent", $_G[fid]);
        showmessage('group_create_succeed', "forum.php?mod=activity&action=manage&fid=$newfid", array(), array('msgtype' => 3));
    }

    include template('diy:group/activity:' . $_G['fid']);
} elseif ($action == 'manage') {
//    if (!$_G['forum']['ismoderator']) {
//        showmessage('group_admin_noallowed');
//    }
  if($_G["activity"]["teacher_id"]){
            $teacher_ids = unserialize($_G["activity"]["teacher_id"]);
			foreach($teacher_ids as $i=>$teacherid){
				if($teacherid){
					$teacher_ids[$i]=$teacherid;
				}else{
					$teacher_ids[$i]='0';
				}
			}
		}
	if($_G[activity][live_id]){
			foreach($_G[activity][live_id] as $i=>$activityid){
				if($activityid){
					$_G[activity][live_id][$i]=$activityid;
				}else{
					$_G[activity][live_id][$i]='0';
				}
			}
		}
    $_G[activity][teacher][ids] = implode(",", $teacher_ids);
    $_G[activity][live][ids] = implode(",", $_G[activity][live_id]);
    if($_G[activity][teacher][ids] ){
        $query = DB::query("SELECT * FROM " . DB::table("lecturer")." WHERE id IN(".$_G[activity][teacher][ids] .")");
        while($row = DB::fetch($query)){
            $_G[activity][teacher][names][] = $row["name"];
        }
        $_G[activity][teacher][names] = implode(",", $_G[activity][teacher][names]);
    }
    
    if($_G[activity][live][ids]){
        $query = DB::query("SELECT * FROM " . DB::table("group_live")." WHERE liveid IN(".$_G[activity][live][ids] .")");
        while($row = DB::fetch($query)){
            $_G[activity][live][names][] = $row["subject"];
        }
        $_G[activity][live][names] = implode(",", $_G[activity][live][names]);
    }
	if (!$_G['forum']['ismoderator'] && $groupfmanager[uid] != $_G['uid']) {
		showmessage('group_admin_noallowed');
	}
    $specialswitch = $_G['current_grouplevel']['specialswitch'];

    $oparray = array('group', 'checkuser', 'deleteactivity', 'manageuser', 'threadtype', 'demise', 'manageplugin', 'manageempirical', 'managecategory');
    $_G['gp_op'] = getgpc('op') && in_array($_G['gp_op'], $oparray) ? $_G['gp_op'] : 'group';
    if (empty($groupmanagers[$_G[uid]]) && $groupfmanager[uid] != $_G['uid'] && $_G['gp_op'] != 'group') {
        showmessage('group_admin_noallowed');
    }
    $page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
    $perpage = 48;
    $start = ($page - 1) * $perpage;
    $url = 'forum.php?mod=activity&action=manage&op=' . $_G['gp_op'] . '&fid=' . $_G['fid'];
    if ($_G['gp_op'] == 'group') {
        if (submitcheck('groupmanage')) {
            $teacher_ids = $_POST["teacherids"];
            $live_ids = $_POST["livesids"];

            $teacher_ids = explode(",", $teacher_ids);
            $live_ids = explode(",", $live_ids);
            array_pop($teacher_ids);
            array_pop($live_ids);

            $live_ids = serialize(array_unique($live_ids));
            $teacher_ids = serialize(array_unique($teacher_ids));

            if(!$teacher_ids || !$live_ids){
                showmessage('请选择讲师', join_plugin_action("index"));
            }
            DB::query("UPDATE ".DB::table("forum_forum_activity")." SET teacher_id='".$teacher_ids."',live_id='".$live_ids."' WHERE fid=".$_G[fid]);
            
    
            if (($_G['gp_name'] && !empty($specialswitch['allowchangename'])) || ($_G['gp_fup'] && !empty($specialswitch['allowchangetype']))) {
                if ($_G['uid'] != $_G['forum']['founderuid']) {
                    showmessage('group_edit_only_founder');
                }

                if (isset($_G['gp_name'])) {
                    $_G['gp_name'] = dhtmlspecialchars(censor(addslashes(cutstr(stripslashes(trim($_G['gp_name'])), 200, ''))));
                    if (empty($_G['gp_name']))
                        showmessage('活动名不能为空');
                }/* elseif(isset($_G['gp_fup']) && empty($_G['gp_fup'])) {
                  showmessage('group_category_empty');
                  } */

                $addsql = '';
                if (!empty($_G['gp_name']) && $_G['gp_name'] != $_G['forum']['name']) {
                    if (DB::result(DB::query("SELECT fid FROM " . DB::table('forum_forum') . " WHERE name='$_G[gp_name]'"), 0)) {
                        showmessage('活动名已经存在', $url);
                    }
                    $addsql = "name = '$_G[gp_name]'";
                }

                /* if(intval($_G['gp_fup']) != $_G['forum']['fup']) {
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
            $bannernew = upload_icon_banner($_G['forum'], $_FILES['bannernew'], 'banner', true);
            if ($iconnew) {
                $iconsql .= ", icon='$iconnew'";
            }
            if ($bannernew && empty($deletebanner)) {
                $iconsql .= ", banner='$bannernew'";
            } elseif ($deletebanner) {
                $iconsql .= ", banner=''";
                @unlink($_G['forum']['banner']);
            }
			$_G['gp_category_id']=intval($_G['gp_category_id']);
            $_G['gp_descriptionnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_descriptionnew']))));
            $_G['gp_tagnew'] = nl2br(dhtmlspecialchars(censor(trim($_G['gp_tagnew']))));
            $_G['gp_jointypenew'] = intval($_G['gp_jointypenew']);
            if ($_G['gp_jointypenew'] == '-1' && $_G['uid'] != $_G['forum']['founderuid']) {
                showmessage('活动只能有创建者关闭');
            }
            $_G['gp_gviewpermnew'] = intval($_G['gp_gviewpermnew']);
            $query = DB::query("SELECT * FROM ".DB::table("forum_forum_activity")." WHERE fid=".$_G[fid]);
            $rs = DB::fetch($query);
            if($rs){
                $startime = strtotime($_POST["starttime"]);
                $endtime = strtotime($_POST["endtime"]);
                $signuptime = strtotime($_POST["signuptime"]);
                $rs[extra] = unserialize($rs[extra]);
                $rs[extra] = serialize(array(startime=>$startime, endtime=>$endtime, signuptime=>$signuptime));
            }
            DB::query("UPDATE " . DB::table('forum_forumfield') . " SET description='$_G[gp_descriptionnew]',category_id='$_G[gp_category_id]', tag='$_G[gp_tagnew]',extra='$rs[extra]', jointype='$_G[gp_jointypenew]', gviewperm='$_G[gp_gviewpermnew]'$iconsql WHERE fid='$_G[fid]'");
            showmessage('活动更新成功', $url);
        } else {
            $firstgid = $_G['cache']['grouptype']['second'][$_G['forum']['fup']]['fup'];
            $groupselect = get_groupselect($firstgid, $_G['forum']['fup']);
            $gviewpermselect = $jointypeselect = array('', '', '');
            $_G['forum']['descriptionnew'] = str_replace("<br />", '', $_G['forum']['description']);
            $_G['forum']['tagnew'] = str_replace("<br />", '', $_G['forum']['tag']);
            $query = DB::query("SELECT * FROM ".DB::table("forum_forumfield")." WHERE fid=".$_G[fid]);
            $rs = DB::fetch($query);
            if($rs){
                $_G[activity][extra] = unserialize($rs[extra]);
				if($_G[activity][extra][startime]){
                	$_G[activity][extra][startime] = date("Y-n-j H:i", $_G[activity][extra][startime]);
				}
				if($_G[activity][extra][signuptime]){
               		$_G[activity][extra][signuptime] = date("Y-n-j H:i", $_G[activity][extra][signuptime]);
				}
				if($_G[activity][extra][endtime]){
                	$_G[activity][extra][endtime] = date("Y-n-j H:i", $_G[activity][extra][endtime]);
				}
            }
            $jointypeselect[$_G['forum']['jointype']] = 'checked="checked"';
            $gviewpermselect[$_G['forum']['gviewperm']] = 'checked="checked"';
            require_once libfile('function/forumlist');
            $forumselect = forumselect(FALSE, 0, $_G['forum']['recommend']);
        }
    } elseif ($_G['gp_op'] == 'checkuser') {
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
                    notification_add($uid, 'group', 'group_activity_member_check', array('fup'=>$groupinf[fup],'groupname'=>$groupinf[gname],'activityname'=>$groupinf[aname], 'url' => $_G['siteurl'] . 'forum.php?mod=activity&fid=' . $_G['fid']), 1);
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
				  notification_add($uid, 'group', 'group_activity_member_check_false', array('fup'=>$groupinf[fup],'groupname'=>$groupinf[gname],'activityname'=>$groupinf[aname], 'url' => $_G['siteurl'] . 'forum.php?mod=activity&fid=' . $_G['fid']), 1);	
            }
            if ($checktype == 1) {
                showmessage('活动设置成功', $url);
            } else {
                showmessage('设置成功 ', $url);
            }
        } else {
            $checkusers = array();
            $userlist = groupuserlist($_G['fid'], 'joindateline', $perpage, $start, array('level' => 0));
            $checknum = DB::result(DB::query("SELECT count(*) FROM " . DB::table('forum_groupuser') . " WHERE fid='$_G[fid]' AND level='0'"), 0);
            $multipage = multi($checknum, $perpage, $page, $url);
            require_once libfile('function/group');
            foreach ($userlist as $user) {
                $user['joindateline'] = date('Y-m-d H:i', $user['joindateline']);
                $user['realname'] = user_get_user_name($user['uid']);
                $checkusers[$user['uid']] = $user;
            }
        }
    } elseif ($_G['gp_op'] == 'manageplugin') {
        $query = DB::query("SELECT plugin_id,auth_group,status FROM " . DB::table('common_group_plugin') . " WHERE fid=" . $_G["fid"]);
        $cur_group_plugins = array();
        while ($row = DB::fetch($query)) {
            $cur_group_plugins[$row["plugin_id"]] = $row;
        }
        //查询用户等级
        $query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
        //$levels = array(array(level_name => "仅专区成员"), array(level_name => "全部"), array(level_name => "群主"), array(level_name => "副群主"));
        $levels = array( array(level_name => "仅活动管理员"),array(level_name => "仅活动成员"), array(level_name => "所有用户"));
        while ($row = DB::fetch($query)) {
            $levels[] = $row;
        }
        if ($_POST["plugin_ids"]) {
            foreach ($_POST["plugin_ids"] as $id) {
                $one = $_POST["m"][$id];
                $query = DB::query("SELECT * FROM " . DB::table("common_group_plugin") . " WHERE fid=" . $_G["gp_fid"] . " AND plugin_id='" . $id . "'");
                $result = DB::fetch($query);
                if (!$result["id"]) {
                    $data = array(fid => $_G["gp_fid"], plugin_id => $id, status => 'N');
                    DB::insert("common_group_plugin", $data);
                }
                if ($one == "disable") {
                    $data["status"] = 'N';
                    DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $id));
                } elseif ($one == "enable") {
                    $data["status"] = 'Y';
                    DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $id));
                }

                $oper = $_POST["oper"][$id];
                //更新权限
                $data["auth_group"] = $oper;
                DB::update("common_group_plugin", $data, array(fid => $_G["gp_fid"], plugin_id => $id));
            }

            showmessage("保存成功", $url);
        }
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
    } elseif($_G["gp_op"] == "managecategory"){
        $typenumlimit = 20;
        $plugin_id = $_G["gp_id"];
        require_once libfile("function/category");
        if($_POST["m"]=="save"){
            common_category_create_plugin_category($plugin_id, array(state=>$_POST["state"], required=>$_POST["required"], prefix=>$_POST["prefix"], fid=>$_G["gp_fid"]));
            $categorys = $_POST["categorys"];
            //更新
            foreach($categorys["displayorder"] as $key=>$value){
                $data["displayorder"] = $value[0];
                $data["name"] = $categorys["name"][$key][0];
                DB::update("common_category", $data, array("id"=>$key));
            }
            //删除
            if($categorys["delete"]){
                DB::query("DELETE FROM ".DB::table("common_category")." WHERE id IN (".  implode(",", $categorys["delete"]).")");
            }
            //新建
            $new_categorys = $_POST["newdisplayorder"];
            foreach($new_categorys as $key=>$value){
                $data["displayorder"] = $value;
                $data["name"] = $_POST["newname"][$key];
                common_category_create_category($_G["gp_fid"], $plugin_id, $data["name"], $data["displayorder"]);
            }
            showmessage("更新类别成功", $url."&id=".$plugin_id);
        }
        $other_info = common_category_is_other($_G["gp_fid"], $plugin_id);
        $display = "none";
        $status_no = 'checked="true"';
        $required_no = 'checked="true"';
        $prefix_no = 'checked="true"';

        if($other_info["state"]=="Y"){
            $status_yes = 'checked="true"';
            $status_no = "";
            $display = "";
        }
        if($other_info["required"]=="Y"){
            $required_yes = 'checked="true"';
            $required_no = "";
        }
        if($other_info["prefix"]=="Y"){
            $prefix_yes = 'checked="true"';
            $prefix_no = "";
        }
       $categorys = common_category_get_category($_G["gp_fid"], $plugin_id);
    } elseif ($_G['gp_op'] == 'deleteactivity') {
    	if (!empty($_G['forum']['founderuid']) || $groupmanagers[uid] == $_G['uid']) {
        	if (submitcheck('activitydelete')) {
        		include_once libfile('function/group');
        		$fidarray = array($_G['fid']);
        		$rtn = activity_delete_by_useradmin($fidarray, true);
        		if ($rtn) {
        			showmessage('删除活动成功', 'forum.php?mod=group&action=plugin&plugin_name=activity&plugin_op=groupmenu&fid='.$groupfmanager[fup]);
        		} else {
        			showmessage('活动删除失败');
        		}
        	}
        } else {
            showmessage('只有管理员才能删除活动');
        }
    } elseif ($_G['gp_op'] == 'manageuser') {
        $mtype = array(1 => lang('group/template', '活动管理员'), 4 => lang('group/template', 'group_normal_member'), 5 => lang('group/template', 'group_goaway'));
        if (!submitcheck('manageuser')) {
            $userlist = array();
            $staruserlist = $page < 2 ? groupuserlist($_G['fid'], '', 0, 0, array('level' => '3'), array('uid', 'username', 'level', 'joindateline', 'lastupdate')) : '';
            $adminuserlist = $groupmanagers && $page < 2 ? $groupmanagers : array();
            $userlist = groupuserlist($_G['fid'], '', $perpage, $start, "AND level='4'");
            $multipage = multi($_G['forum']['membernum'], $perpage, $page, $url);
            
            $startuids = array();
            foreach($staruserlist as $startuser){
                $startuids[] = $startuser[uid];
            }
            $startuserrealname = user_get_user_realname($startuids);

            $alluseruids = array();
            foreach($userlist as $user){
                $alluseruids[] = $user[uid];
            }
            $alluserrealname = user_get_user_realname($alluseruids);
            
            $adminuids = array();
            foreach ($adminuserlist as $user) {
                $adminuserlist[$user['uid']] = $user;
                $adminuids[] = $user[uid];
                $adminuserlist[$user['uid']]['online'] = $onlinemember['list'] && is_array($onlinemember['list']) && $onlinemember['list'][$user['uid']] ? 1 : 0;
            }

            $adminuserrealname = user_get_user_realname($adminuids);
            
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
                        if ($targetlevel != 5) {
                            DB::query("UPDATE " . DB::table('forum_groupuser') . " SET level='$targetlevel' WHERE uid='$muid' AND fid='$_G[fid]'");
                            $cache_key = "group_get_gorup_level_".$_G[fid]."_".$muid;
                            memory("rm", $cache_key);
                        } else {
                            if (!$groupmanagers[$muid] || count($groupmanagers) > 1) {
                                DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE uid='$muid' AND fid='$_G[fid]'");
                                $cache_key = "group_member_".$_G[fid]."_".$muid;
                                memory("rm", $cache_key);
                                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='$_G[fid]'");
                                update_usergroups($muid);
                            } else {
                                showmessage('group_only_one_moderator', $url);
                            }
                        }
                    }
                }
                update_groupmoderators($_G['fid']);
                showmessage('活动设置成功', $url . '&page=' . $page);
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
                if (empty($_G['gp_grouppwd'])) {
                    showmessage('group_demise_password');
                }
                loaducenter();
                $ucresult = uc_user_login($_G['uid'], $_G['gp_grouppwd'], 1);
                if (!is_array($ucresult) || $ucresult[0] < 1) {
                    showmessage('group_demise_password_error');
                }
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
                $cache_key = "group_founder_".$_G[fid]."_".$user[uid];
                memory("rm", $cache_key);
                sendpm($suid, lang('group/template', lang('group/template', 'group_demise_message_title', array('forum' => $_G['forum']['name'])), lang('group/template', 'group_demise_message_body', array('forum' => $_G['forum']['name'], 'siteurl' => $_G['siteurl'], 'fid' => $_G['fid'])), $_G['uid']));
                showmessage('group_demise_succeed', 'forum.php?mod=activity&action=manage&fid=' . $_G['fid']);
            }
        } else {
            showmessage('group_demise_founder_only');
        }
    } else {
        showmessage('undefined_action');
    }
    include template('diy:group/activity:' . $_G['fid']);
}
?>