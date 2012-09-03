<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_group.php 10851 2010-05-17 08:17:09Z liulanbo $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function delgroupcache($fid = 0, $cachearray) {
	$addfid = $fid ? "AND fid='$fid'" : '';
	DB::query("DELETE FROM " . DB::table('forum_groupfield') . " WHERE type IN (" . dimplode($cachearray) . ") $addfid");
}

function groupperm(&$forum, $uid, $action = '', $isgroupuser = '') {
	if ($forum['status'] != 3 || ($forum['type'] != 'sub' && $forum['type'] != 'activity')) {
		return -1;
	}
	if (!empty($forum['founderuid']) && $forum['founderuid'] == $uid) {  //创建者
		return 'isgroupuser';
	}
	$isgroupuser = empty($isgroupuser) && $isgroupuser !== false ? DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid='$forum[fid]' AND uid='$uid'") : $isgroupuser;
	

	if ($forum['ismoderator'] && !$isgroupuser) {
		return '';
	}
	if ($forum['jointype'] < 0 && !$forum['ismoderator']) {//关闭
		return 1; 
	}
	
	
	if($forum['type']=='activity' && $forum['gviewperm']==3){
		//活动，浏览权限继承专区
		//查询专区的浏览权限
		$s_forum = DB::fetch_first("SELECT * FROM " . DB::table('forum_forumfield') . " WHERE fid='$forum[fup]' ") ;
		
		$forum['gviewperm'] = empty($s_forum)?1:$s_forum['gviewperm'];
	}
	
	
	if (!$forum['gviewperm'] && !$isgroupuser) { //即成员可见，并且当前用户不属于该专区
		return 2;
	}
	
	if($forum['type']=='activity'){ //针对活动。
		if( $forum['gviewperm']==2){ //浏览权限为专区（专区+已经参加活动的人员）
			
			if(!$isgroupuser){				
				$isgroupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid='$forum[fup]' AND uid='$uid'") ;
			}
			if(!$isgroupuser){
				return 2;
			}
		}	
		
	}
	
	
	if ($forum['jointype'] == 2 && !empty($isgroupuser['uid']) && $isgroupuser['level'] == 0) {
		return 3;
	}
	if ($action == 'post' && !$isgroupuser) {
		return 4;
	}
	return $isgroupuser ? 'isgroupuser' : '';
}


/**
 * 这里查询的是所有的非专家用户
 * 专家用户+管理员
 */
function groupuserlist($fid, $orderby = '', $num = 0, $start = 0, $addwhere = '', $fieldarray = array(), $onlinemember = array()) {

	$fid = intval($fid);
	if ($fieldarray && is_array($fieldarray)) {
		$fieldadd = 'uid';
		foreach ($fieldarray as $field) {
			$fieldadd .= ' ,' . $field;
		}
	} else {
		$fieldadd = '*';
	}

	$sqladd = $levelwhere = '';
	if ($addwhere) {
		if (is_array($addwhere)) {
			foreach ($addwhere as $field => $value) {
				if (is_array($value)) {
					$levelwhere = "AND level>'0' ";
					$sqladd .= "AND $field IN (" . dimplode($value) . ") ";
				} else {
					$sqladd .= is_numeric($field) ? "AND $value " : "AND $field='$value' ";
				}
			}
			if (!empty($addwhere['level']))
			$levelwhere = '';
		} else {
			$sqladd = $addwhere;
		}
	}

	$orderbyarray = array('level_join' => 'level ASC, joindateline ASC','level_update'=>'level ASC, lastupdate ASC', 'joindateline' => 'joindateline DESC', 'lastupdate' => 'lastupdate DESC', 'threads' => 'threads DESC', 'replies' => 'replies DESC');
	$orderby = !empty($orderbyarray[$orderby]) ? "ORDER BY $orderbyarray[$orderby]" : '';
	$limitsql = $num ? "LIMIT " . ($start ? intval($start) : 0) . ", $num" : '';

	$groupuserlist = array();
	//所有非专家用户
	$sql1="SELECT $fieldadd FROM " . DB::table('forum_groupuser') . " WHERE level!= '4' and fid='$fid' $levelwhere $sqladd $orderby $limitsql";
	
	
	
	$query = DB::query($sql1);
	while ($groupuser = DB::fetch($query)) {
		$groupuserlist[$groupuser['uid']] = $groupuser;
		$groupuserlist[$groupuser['uid']]['online'] = !empty($onlinemember) && is_array($onlinemember) && !empty($onlinemember[$groupuser['uid']]) ? 1 : 0;
	}
	//专家用户+管理员
    $sql2="SELECT $fieldadd FROM " . DB::table('forum_groupuser') . " WHERE level= '4' and ( pre_forum_groupuser.uid not in ( SELECT pre_expertuser.uid FROM  pre_expertuser) ) and fid='$fid' $levelwhere $sqladd $orderby $limitsql";
    $query = DB::query($sql2);
   
	while ($groupuser = DB::fetch($query)) {
		$groupuserlist[$groupuser['uid']] = $groupuser;
		$groupuserlist[$groupuser['uid']]['online'] = !empty($onlinemember) && is_array($onlinemember) && !empty($onlinemember[$groupuser['uid']]) ? 1 : 0;
	}
	
	
	
	return $groupuserlist;
}


function groupexpertuserlist($fid, $orderby = '', $num = 0, $start = 0, $addwhere = '', $fieldarray = array(), $onlinemember = array()) {

	$fid = intval($fid);
	$query=DB::query("select * from ".DB::table("expertuser")." where fid=".$fid);
	$groupuserlist = array();
	while($value=DB::fetch($query)){
		$groupuser = DB::fetch_first("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE uid='".$value[uid]."' AND level='4' and fid=".$fid);
		$groupuser['expertinfo']['activelink'] = get_expertuser_activelink_by_uid($groupuser['uid']);
		$groupuserlist[$groupuser['uid']] = $groupuser;
		$groupuserlist[$groupuser['uid']]['online'] = !empty($onlinemember) && is_array($onlinemember) && !empty($onlinemember[$groupuser['uid']]) ? 1 : 0;
	}
	
	/*if ($fieldarray && is_array($fieldarray)) {
		$fieldadd = 'uid';
		foreach ($fieldarray as $field) {
			$fieldadd .= ' ,' . $field;
		}
	} else {
		$fieldadd = '*';
	}

	$sqladd = $levelwhere = '';
	if ($addwhere) {
		if (is_array($addwhere)) {
			foreach ($addwhere as $field => $value) {
				if (is_array($value)) {
					$levelwhere = "AND level>'0' ";
					$sqladd .= "AND $field IN (" . dimplode($value) . ") ";
				} else {
					$sqladd .= is_numeric($field) ? "AND $value " : "AND $field='$value' ";
				}
			}
			if (!empty($addwhere['level']))
			$levelwhere = '';
		} else {
			$sqladd = $addwhere;
		}
	}

	$orderbyarray = array('level_join' => 'level ASC, joindateline ASC', 'joindateline' => 'joindateline DESC', 'lastupdate' => 'lastupdate DESC', 'threads' => 'threads DESC', 'replies' => 'replies DESC');
	$orderby = !empty($orderbyarray[$orderby]) ? "ORDER BY $orderbyarray[$orderby]" : '';
	$limitsql = $num ? "LIMIT " . ($start ? intval($start) : 0) . ", $num" : '';

	$groupuserlist = array();
	$query = DB::query("SELECT $fieldadd FROM " . DB::table('forum_groupuser') . " WHERE fid='$fid' $levelwhere $sqladd $orderby $limitsql");
	while ($groupuser = DB::fetch($query)) {
		//过滤非专家用户
		if (!check_is_expertuser($groupuser['uid'], $fid)) {
			continue;
		}
		$groupuser['expertinfo']['activelink'] = get_expertuser_activelink_by_uid($groupuser['uid']);
		$groupuserlist[$groupuser['uid']] = $groupuser;
		$groupuserlist[$groupuser['uid']]['online'] = !empty($onlinemember) && is_array($onlinemember) && !empty($onlinemember[$groupuser['uid']]) ? 1 : 0;
	}*/

	return $groupuserlist;

}

function grouplist($orderby = 'displayorder', $fieldarray = array(), $num = 1, $fids = array(), $sort = 0, $getcount = 0, $grouplevel = array(),$fromfid=0,$jointype) {
	if ($fieldarray && is_array($fieldarray)) {
		$fieldadd = '';
		foreach ($fieldarray as $field) {
			$fieldadd .= ' ,' . $field;
		}
	} else {
		$fieldadd = ' ,ff.*';
	}
	$start = 0;
	if (is_array($num)) {
		list($start, $snum) = $num;
	} else {
		$snum = $num;
	}
	if($fromfid && $orderby=='fup'){
		$wheresql=" and f.fid<".$fromfid;
	}elseif($fromfid && $orderby=='fdown'){
		$wheresql=" and f.fid>".$fromfid;
	}
	if($jointype){
		$jointypesql=" and ff.jointype!=".$jointype;
	}
	$orderbyarray = array('displayorder' => 'f.displayorder DESC', 'dateline' => 'ff.dateline DESC', 'lastupdate' => 'ff.lastupdate DESC', 'membernum' => 'ff.membernum DESC', 'thread' => 'f.threads DESC', 'activity' => 'f.commoncredits DESC','fup'=>'f.fid desc','fdown'=>'f.fid asc');
	$useindex = $orderby == 'displayorder' ? 'USE INDEX(fup_type)' : '';
	$orderby = !empty($orderby) && $orderbyarray[$orderby] ? "ORDER BY " . $orderbyarray[$orderby] : '';
	$limitsql = $num ? "LIMIT $start, $snum " : '';
	$field = $sort ? 'fup' : 'fid';
	$fids = $fids && is_array($fids) ? 'f.' . $field . ' IN (' . dimplode($fids) . ')' : '';

	$grouplist = array();
	if (empty($getcount)) {
		$fieldsql = 'f.fid, f.name, f.threads, f.posts, f.todayposts,f.digest,ff.group_type,ff.gviewperm,ff.description,ff.membernum ' . $fieldadd;
	} else {
		$fieldsql = 'count(*)';
		$orderby = $limitsql = '';
	}
	
	$query = DB::query("SELECT $fieldsql FROM " . DB::table('forum_forum') . " f $useindex " . (empty($getcount) ? " LEFT JOIN " . DB::table("forum_forumfield") . " ff ON ff.fid=f.fid" : '' ) . " WHERE" . ($fids ? " $fids AND " : '') . " f.type='sub' ".$wheresql.$jointypesql." AND f.status=3 $orderby $limitsql");
	$orderid = 0;
	if ($getcount) {
		return DB::result($query, 0);
	}
	while ($group = DB::fetch($query)) {
		$group['iconstatus'] = $group['icon'] ? 1 : 0;
		isset($group['icon']) && $group['icon'] = get_groupimg($group['icon'], 'icon');
		isset($group['banner']) && $group['banner'] = get_groupimg($group['banner']);
		$group['orderid'] = $orderid ? intval($orderid) : '';
		isset($group['dateline']) && $group['dateline'] = $group['dateline'] ? dgmdate($group['dateline'], 'd') : '';
		isset($group['lastupdate']) && $group['lastupdate'] = $group['lastupdate'] ? dgmdate($group['lastupdate'], 'd') : '';
		$group['level'] = !empty($grouplevel) ? intval($grouplevel[$group['fid']]) : 0;
		isset($group['description']) && $group['description'] = cutstr($group['description'], 130);
		$group['titlename'] = $group["name"];
		if(strlen($group["name"])>30){
    		$group['name'] = cutstr($group["name"], 18);
		}
		if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
	        $group['type_icn_id'] = 'brzone_s';
	    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
	        $group['type_icn_id'] = 'bizone_s';
	    }
		$grouplist[$group['fid']] = $group;
		$orderid++;
	}
	return $grouplist;
}

function mygrouplist($uid, $orderby = '', $fieldarray = array(), $num = 0, $start = 0, $ismanager = 0, $count = 0,$fromfid=0,$jointype) {
	$uid = intval($uid);
	if (empty($uid)) {
		return array();
	}
	if (empty($ismanager)) {
		$levelsql = '';
	} elseif ($ismanager == 1) {
		$levelsql = ' AND fg.level IN(1,2)';
	} elseif ($ismanager == 2) {
		$levelsql = ' AND fg.level IN(3,4)';
	}
	if ($count == 1) {
		return DB::result_first("SELECT count(*) FROM " . DB::table('forum_groupuser') . " fg, " . DB::table('forum_forum') . " ff WHERE fg.fid=ff.fid AND ff.type='sub' AND fg.uid='$uid' $levelsql");
	}
	empty($start) && $start = 0;
	if (!empty($num)) {
		$limitsql = "LIMIT $start, $num";
	} else {
		$limitsql = "LIMIT $start, 100";
	}
	$groupfids = $grouplevel = array();
	if($fromfid && $orderby=='fup'){
		$wheresql=" and fg.fid<".$fromfid;
	}elseif($fromfid && $orderby=='fdown'){
		$wheresql=" and fg.fid>".$fromfid;
	}
	
	if($orderby=='fup'){
		$query = DB::query("SELECT fg.fid, fg.level FROM " . DB::table('forum_groupuser') . " fg, " . DB::table('forum_forum') . " ff  WHERE fg.fid=ff.fid AND ff.type='sub' AND fg.uid='$uid' ".$wheresql." $levelsql ORDER BY fg.fid DESC $limitsql");
	}elseif($orderby=='fdown'){
		$query = DB::query("SELECT fg.fid, fg.level FROM " . DB::table('forum_groupuser') . " fg, " . DB::table('forum_forum') . " ff  WHERE fg.fid=ff.fid AND ff.type='sub' AND fg.uid='$uid' ".$wheresql." $levelsql ORDER BY fg.fid asc $limitsql");
	}else{
		$query = DB::query("SELECT fg.fid, fg.level FROM " . DB::table('forum_groupuser') . " fg, " . DB::table('forum_forum') . " ff  WHERE fg.fid=ff.fid AND ff.type='sub' AND fg.uid='$uid' $levelsql ORDER BY lastupdate DESC $limitsql");
	}
	while ($group = DB::fetch($query)) {
		$groupfids[] = $group['fid'];
		$grouplevel[$group['fid']] = $group['level'];
	}
	if (empty($groupfids)) {
		return false;
	}
	$mygrouplist = grouplist($orderby, $fieldarray, $num, $groupfids, 0, 0, $grouplevel,$fromfid,$jointype);

	return $mygrouplist;
}

function get_groupimg($imgname, $imgtype = '') {
	global $_G;
	$imgpath = $_G['setting']['attachurl'] . 'group/' . $imgname;
	if ($imgname) {
		return $imgpath;
	} else {
		if ($imgtype == 'icon') {
			return 'static/image/images/def_group.png';
		} else {
			return '';
		}
	}
}

function get_groupselect($fup = 0, $groupid = 0, $ajax = 1) {
	global $_G;
	loadcache('grouptype');
	$firstgroup = $_G['cache']['grouptype']['first'];
	$secondgroup = $_G['cache']['grouptype']['second'];
	$grouptypeselect = array('first' => '', 'second' => '');
	if ($ajax) {
		$fup = intval($fup);
		$groupid = intval($groupid);
		foreach ($firstgroup as $gid => $group) {
			$extra = unserialize($group["extra"]);
			$second = unserialize($secondgroup[$group['secondlist'][0]]["extra"]);
			if ($extra && $second["type"] == 60) {
				$selected = $fup == $gid ? 'selected="selected"' : '';
				$grouptypeselect['first'] .= $group['secondlist'] ? '<option value="' . $gid . '" ' . $selected . '>' . $group['name'] . '</option>' : '';
			}
		}

		if ($fup) {
			foreach ($firstgroup[$fup]['secondlist'] as $sgid) {
				$extra = unserialize($secondgroup[$sgid]["extra"]);
				if ($extra && $extra["type"] == 60) {
					$selected = $sgid == $groupid ? 'selected="selected"' : '';
					$grouptypeselect['second'] .= '<option value="' . $sgid . '" ' . $selected . '>' . $secondgroup[$sgid]['name'] . '</option>';
				}
			}
		}
	} else {
		foreach ($firstgroup as $gid => $group) {
			$grouptypeselect .= '<optgroup label="' . $group['name'] . '">';
			if (is_array($group['secondlist'])) {
				foreach ($group['secondlist'] as $secondid) {
					$selected = $groupid == $secondid ? 'selected="selected"' : '';
					$grouptypeselect .= '<option value="' . $secondid . '" ' . $selected . '>' . $secondgroup[$secondid]['name'] . '</option>';
				}
			}
			$grouptypeselect .= '</optgroup>';
		}
	}

	return $grouptypeselect;
}
function get_activitynav($activity){
	global $_G;
	//echo("执行方法get_activitynav");
	if(empty($activity)){
		return;
	}
	loadcache('grouptype');
	$activitynav = '';
	$gp_plugin_name=$_G[gp_plugin_name];
	$re_plugin_name=$_REQUEST['plugin_name'];
	$plugin_name=$gp_plugin_name?$gp_plugin_name:$re_plugin_name;
	$fid=$activity[fid];
		
	$fup=$activity[fup];
	if(!$fid||!$fup)return $activitynav;
	require_once libfile("function/category");
	$is_enable_category=false;
	$categorys=array();
	if(common_category_is_enable($fup, 'activity')){				
		$is_enable_category=true;
		$categorys = common_category_get_category($fup, 'activity');			
		if(!empty($categorys)){
			foreach($categorys as $category){
				if($category[id]==$activity['category_id']){
					$activitynav.="<a href='forum.php?mod=group&action=plugin&fid=$fup&plugin_name=activity&plugin_op=groupmenu&type=$_G[gp_type]&category_id=$category[id]'>[$category[name]]</a>";				
				}
			}
		}
	}
		
	if($activitynav){
		$activitynav.=" &rsaquo; ";
	}
	$activitynav.="<a href='forum.php?mod=activity&fup=$fup&fid=$fid'>活动首页</a>";
	if($plugin_name){
		$query=DB::query("SELECT pluginid,name,identifier FROM pre_common_plugin");
		while($pluginEntity=DB::fetch($query)){	
			if($pluginEntity['identifier']==$plugin_name){
				$name=$pluginEntity['name'];
				$activitynav.=" &rsaquo; <a href='forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$plugin_name&plugin_op=groupmenu'>$name</a>";
			}
		}
	}	
	return $activitynav;
}

function get_groupnav($forum) {
	global $_G;
	if (empty($forum) || empty($forum['fid']) || empty($forum['name'])) {
		return '';
	}
	loadcache('grouptype');
	$groupnav = '';
	$groupsecond = $_G['cache']['grouptype']['second'];
	if ($forum['type'] == 'sub') {
		$secondtype = !empty($groupsecond[$forum['fup']]) ? $groupsecond[$forum['fup']] : array();
	} else {
		$secondtype = !empty($groupsecond[$forum['fid']]) ? $groupsecond[$forum['fid']] : array();
	}
	$firstid = !empty($secondtype) ? $secondtype['fup'] : (!empty($forum['fup']) ? $forum['fup'] : $forum['fid']);
	$firsttype = $_G['cache']['grouptype']['first'][$firstid];
	/* if($firsttype) {
	 $groupnav = ' &rsaquo; <a href="group.php?gid='.$firsttype['fid'].'">'.$firsttype['name'].'</a>';
	 }
	 if($secondtype) {
	 $groupnav .= ' &rsaquo; <a href="group.php?sgid='.$secondtype['fid'].'">'.$secondtype['name'].'</a>';
	 } */
	if ($forum['type'] == 'sub') {
		//$mod_action = $_G['gp_mod'] == 'forumdisplay' || $_G['gp_mod'] == 'viewthread' ? 'mod=forumdisplay&action=list' : 'mod=group';
		$groupnav .= ( $groupnav ? ' &rsaquo; ' : '') . '&rsaquo; <a href="forum.php?mod=group&fid=' . $forum['fid'] . '">'. $forum['name'] . '</a>';
		//$groupnav .= '&special=' . $_G[gp_special] . '&plugin_name=' . $_G[gp_plugin_name] . '&plugin_op=' . $_G[gp_plugin_op] . '">' . $forum['name'] . '</a>';
		$gp_plugin_name=$_G[gp_plugin_name];
		$re_plugin_name=$_REQUEST['plugin_name'];
		$plugin_name=$gp_plugin_name?$gp_plugin_name:$re_plugin_name;
		$fid=$forum[fid];
		if($plugin_name){
			$query=DB::query("SELECT pluginid,name,identifier FROM pre_common_plugin");
			while($pluginEntity=DB::fetch($query)){	
				if($pluginEntity['identifier']==$plugin_name){
					$name=$pluginEntity['name'];
					$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&fid=$fid&plugin_name=$plugin_name&plugin_op=groupmenu'> $name</a>";
				}
			}
		}	
	}elseif($forum['type'] == 'activity'){
		//$groupnav .= ( $groupnav ? ' &rsaquo; ' : '') . '&rsaquo; <a href="forum.php?mod=group&fid=' . $forum['fid'] . '">'. $forum['name'] . '</a>';
		if($_G['forum']['fup']){
        	$sql = "SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$_G['forum']['fup'];
        	$query = DB::query($sql);
        	$_G['parent'] = DB::fetch($query);
		}
		$groupnav.=" &rsaquo; <a href='forum.php?mod=group&fid=".$_G['parent']['fid']."'>".$_G['parent']['name'].'</a>';
		$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&plugin_name=activity&plugin_op=groupmenu&fid=".$_G['forum']['fup']."'>"."活动"."</a>";
		
		//$groupnav.=" &rsaquo; <a href='forum.php?mod=activity&fup=".$_G['forum']['fup']."&fid=".$_G['forum']['fid']."'>".活动.'</a>';
		$gp_plugin_name=$_G[gp_plugin_name];
		$re_plugin_name=$_REQUEST['plugin_name'];
		$plugin_name=$gp_plugin_name?$gp_plugin_name:$re_plugin_name;
		$plugin_op=$_REQUEST['plugin_op'];
		$fid=$forum[fid];
/*		if($plugin_name){
			$query=DB::query("SELECT pluginid,name,identifier FROM pre_common_plugin");
			while($pluginEntity=DB::fetch($query)){	
				if($pluginEntity['identifier']==$plugin_name){
					$name=$pluginEntity['name'];
					$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&fid=$fid&plugin_name=$plugin_name&plugin_op=groupmenu'> $name</a>";
				}
			}
		}	*/
	}
	return $groupnav;
}
function get_groupcreatenav($forum) {
	global $_G;
	if (empty($forum) || empty($forum['fid']) || empty($forum['name'])) {
		return '';
	}
	loadcache('grouptype');
	$groupnav = '';
	$groupsecond = $_G['cache']['grouptype']['second'];
	if ($forum['type'] == 'sub') {
		$secondtype = !empty($groupsecond[$forum['fup']]) ? $groupsecond[$forum['fup']] : array();
	} else {
		$secondtype = !empty($groupsecond[$forum['fid']]) ? $groupsecond[$forum['fid']] : array();
	}
	$firstid = !empty($secondtype) ? $secondtype['fup'] : (!empty($forum['fup']) ? $forum['fup'] : $forum['fid']);
	$firsttype = $_G['cache']['grouptype']['first'][$firstid];
	/* if($firsttype) {
	 $groupnav = ' &rsaquo; <a href="group.php?gid='.$firsttype['fid'].'">'.$firsttype['name'].'</a>';
	 }
	 if($secondtype) {
	 $groupnav .= ' &rsaquo; <a href="group.php?sgid='.$secondtype['fid'].'">'.$secondtype['name'].'</a>';
	 } */
	if ($forum['type'] == 'sub') {
		//$mod_action = $_G['gp_mod'] == 'forumdisplay' || $_G['gp_mod'] == 'viewthread' ? 'mod=forumdisplay&action=list' : 'mod=group';
		$groupnav .= ( $groupnav ? ' &rsaquo; ' : '') . '&rsaquo; <a href="forum.php?mod=group&fid=' . $forum['fid'] . '">'. $forum['name'] . '</a>';
		//$groupnav .= '&special=' . $_G[gp_special] . '&plugin_name=' . $_G[gp_plugin_name] . '&plugin_op=' . $_G[gp_plugin_op] . '">' . $forum['name'] . '</a>';
		$gp_plugin_name=$_G[gp_plugin_name];
		$re_plugin_name=$_REQUEST['plugin_name'];
		$plugin_name=$gp_plugin_name?$gp_plugin_name:$re_plugin_name;
		$fid=$forum[fid];
		if($plugin_name){
			$query=DB::query("SELECT pluginid,name,identifier FROM pre_common_plugin");
			while($pluginEntity=DB::fetch($query)){	
				if($pluginEntity['identifier']==$plugin_name){
					$name=$pluginEntity['name'];
					$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&fid=$fid&plugin_name=$plugin_name&plugin_op=groupmenu'> $name</a>";
				}
			}
		}	
	}elseif($forum['type'] == 'activity'){
		//$groupnav .= ( $groupnav ? ' &rsaquo; ' : '') . '&rsaquo; <a href="forum.php?mod=group&fid=' . $forum['fid'] . '">'. $forum['name'] . '</a>';
		if($_G['forum']['fup']){
        	$sql = "SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$_G['forum']['fup'];
        	$query = DB::query($sql);
        	$_G['parent'] = DB::fetch($query);
		}
		$groupnav.=" &rsaquo; <a href='forum.php?mod=group&fid=".$_G['parent']['fid']."'>".$_G['parent']['name'].'</a>';
		$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&plugin_name=activity&plugin_op=groupmenu&fid=".$_G['forum']['fup']."'>"."活动"."</a>";
		
		//$groupnav.=" &rsaquo; <a href='forum.php?mod=activity&fup=".$_G['forum']['fup']."&fid=".$_G['forum']['fid']."'>".活动.'</a>';
		$gp_plugin_name=$_G[gp_plugin_name];
		$re_plugin_name=$_REQUEST['plugin_name'];
		$plugin_name=$gp_plugin_name?$gp_plugin_name:$re_plugin_name;
		$plugin_op=$_REQUEST['plugin_op'];
		$fid=$forum[fid];
		if($plugin_name){
			$query=DB::query("SELECT pluginid,name,identifier FROM pre_common_plugin");
			while($pluginEntity=DB::fetch($query)){	
				if($pluginEntity['identifier']==$plugin_name){
					$name=$pluginEntity['name'];
					$groupnav.=" &rsaquo; <a href='forum.php?mod=group&action=plugin&fid=$fid&plugin_name=$plugin_name&plugin_op=groupmenu'> $name</a>";
				}
			}
		}	
	}
	return $groupnav;
}

function get_viewedgroup() {
	$groupviewed_list = $list = array();
	$groupviewed = getcookie('groupviewed');
	$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
	if ($groupviewed) {
		//$query = DB::query("SELECT f.fid, f.fup, f.name, ff.icon, ff.membernum, ff.group_type , f.displayorder FROM " . DB::table('forum_forum') . " as f LEFT JOIN " . DB::table('forum_forumfield') . " as ff ON ff.fid=f.fid WHERE f.type='sub' AND f.fid IN(" . dimplode($groupviewed) . ")");
		//修改 by songsp  2011-3-16 15:12:40   合并下面的check_group_is_activity操作
		$query = DB::query("SELECT f.fid, f.fup, f.name, ff.icon, ff.membernum, ff.group_type ,ff.jointype, f.displayorder FROM " . DB::table('forum_forum') . " as f LEFT JOIN " . DB::table('forum_forumfield') . " as ff ON ff.fid=f.fid WHERE f.type='sub' AND f.fid IN(" . dimplode($groupviewed) . ") AND f.type!='activity'");
		
		while ($row = DB::fetch($query)) {
			$row['icon'] = get_groupimg($row['icon'], 'icon');
			if ($row["group_type"] >= 1 && $row["group_type"] < 20) {
				$row['type_icn_id'] = 'brzone_s';
			} elseif ($row["group_type"] >= 20 && $row["group_type"] < 60) {
				$row['type_icn_id'] = 'bizone_s';
			}
			//$list[$row['fid']] = $row;
			$groupviewed_list[$row['fid']] = $row;
		}
	}
	
	
	/*
	foreach ($groupviewed as $fid) {
		if (check_group_is_activity($fid)) {
			continue;
		}
		$groupviewed_list[$fid] = $list[$fid];
	}
	
	*/
	

	
	
	return $groupviewed_list;
}

function check_group_is_activity($fid) {
	$query = DB::query("SELECT * FROM ".DB::table("forum_forum")." WHERE type='activity' AND fid=".$fid);
	$row =DB::fetch($query);
	if ($row) {
		return true;
	} else {
		return false;
	}
}

function getgroupthread($fid, $type, $timestamp = 0, $num = 10, $privacy = 0) {
	$typearray = array('replies', 'views', 'dateline', 'lastpost', 'digest');
	$type = in_array($type, $typearray) ? $type : '';

	$groupthreadlist = array();
	if ($type) {
		$sqltimeadd = $timestamp ? "AND $type>='" . (TIMESTAMP - $timestamp) . "'" : '';
		$sqladd = $type == 'digest' ? "AND digest>'0' ORDER BY dateline DESC" : "ORDER BY $type DESC";
		$query = DB::query("SELECT * FROM " . DB::table('forum_thread') . " WHERE fid='$fid' AND displayorder>='0' $sqltimeadd $sqladd LIMIT 0, $num");
		while ($thread = DB::fetch($query)) {
			$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
			$groupthreadlist[$thread['tid']]['special'] = $thread['special'];
			$groupthreadlist[$thread['tid']]['closed'] = $thread['closed'];
			$groupthreadlist[$thread['tid']]['dateline'] = dgmdate($thread['dateline'], 'd');
			$groupthreadlist[$thread['tid']]['author'] = $thread['author'];
			$groupthreadlist[$thread['tid']]['authorid'] = $thread['authorid'];
			$groupthreadlist[$thread['tid']]['views'] = $thread['views'];
			$groupthreadlist[$thread['tid']]['replies'] = $thread['replies'];
			$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
			$groupthreadlist[$thread['tid']]['lastposter'] = $thread['lastposter'];
			$groupthreadlist[$thread['tid']]['lastposterenc'] = rawurlencode($thread['lastposter']);
		}
	}

	return $groupthreadlist;
}

function getgroupcache($fid, $typearray = array(), $timestamp = 0, $num = 10, $privacy = 0, $force = 0) {
	$groupcache = array();
	$typeadd = $typearray && is_array($typearray) ? "AND type IN(" . dimplode($typearray) . ")" : '';

	if (!$force) {
		$query = DB::query("SELECT fid, dateline, type, data FROM " . DB::table('forum_groupfield') . " WHERE fid='$fid' AND privacy='$privacy' $typeadd");
		while ($group = DB::fetch($query)) {
			$groupcache[$group['type']] = unserialize($group['data']);
			$groupcache[$group['type']]['dateline'] = $group['dateline'];
		}
	}

	$cachetimearray = array('replies' => 3600, 'views' => 3600, 'dateline' => 0, 'lastpost' => 3600, 'digest' => 86400, 'ranking' => 86400, 'activityuser' => 3600);
	$userdataarray = array('activityuser' => 'lastupdate', 'newuserlist' => 'joindateline');
	foreach ($typearray as $type) {
		if (empty($groupcache[$type]) || (!empty($cachetimearray[$type]) && TIMESTAMP - $groupcache[$type]['dateline'] > $cachetimearray[$type])) {
			if ($type == 'ranking') {
				$groupcache[$type]['data'] = getgroupranking($fid, $groupcache[$type]['data']['today']);
			} elseif (in_array($type, array('activityuser', 'newuserlist'))) {
				$num = $type == 'activityuser' ? 50 : 8;
				$groupcache[$type]['data'] = groupuserlist($fid, $userdataarray[$type], $num, '', "AND level>'0'");
			} else {
				$groupcache[$type]['data'] = getgroupthread($fid, $type, $timestamp, $num, $privacy);
			}
			if (!$force && $fid) {
				DB::query("REPLACE INTO " . DB::table('forum_groupfield') . " (fid, dateline, type, data) VALUES ('$fid', '" . TIMESTAMP . "', '$type', '" . addslashes(serialize($groupcache[$type])) . "')", 'UNBUFFERED');
			}
		}
	}

	return $groupcache;
}

function getgroupranking($fid = '', $nowranking = '', $num = 100) {
	$topgroup = $rankingdata = $topyesterday = array();
	if ($fid) {
		updateactivity($fid);
	}

	$ranking = 1;
	$query = DB::query("SELECT f.fid FROM " . DB::table('forum_forum') . " as f LEFT JOIN " . DB::table('forum_forumfield') . " as ff ON ff.fid=f.fid WHERE f.type='sub' AND f.status='3' ORDER BY ff.activity DESC LIMIT 0, 1000");
	while ($group = DB::fetch($query)) {
		$topgroup[$group['fid']] = $ranking++;
	}

	if ($fid && $topgroup) {
		$rankingdata['yesterday'] = intval($nowranking);
		$rankingdata['today'] = intval($topgroup[$fid]);
		$rankingdata['trend'] = $rankingdata['yesterday'] ? grouptrend($rankingdata['yesterday'], $rankingdata['today']) : 0;
		$topgroup = $rankingdata;
	} else {
		$query = DB::query("SELECT * FROM " . DB::table('forum_groupranking') . " ORDER BY today LIMIT 0, $num");
		while ($top = DB::fetch($query)) {
			$topyesterday[$top['fid']] = $top;
		}

		foreach ($topgroup as $forumid => $today) {
			$yesterday = intval($topyesterday[$forumid]);
			$trend = $yesterday ? grouptrend($yesterday, $today) : 0;
			DB::query("REPLACE INTO " . DB::table('forum_groupranking') . " (fid, yesterday, today, trend) VALUES ('$forumid', '$yesterday', '$today', '$trend')", 'UNBUFFERED');
		}
		$topgroup = $topyesterday;
	}

	return $topgroup;
}

function grouponline($fid, $getlist = '') {
	$fid = intval($fid);
	if (empty($getlist)) {
		$onlinemember = DB::result_first("SELECT COUNT(*) FROM " . DB::table('common_session') . " WHERE fid='$fid'");
		$onlinemember['count'] = $onlinemember ? intval($onlinemember) : 0;
	} else {
		$onlinemember = array('count' => 0, 'list' => array());
		$query = DB::query("SELECT uid FROM " . DB::table('common_session') . " WHERE fid='$fid'");
		while ($member = DB::fetch($query)) {
			if ($member['uid']) {
				$onlinemember['list'][$member['uid']] = $member['uid'];
			}
			$onlinemember['count']++;
		}
	}
	return $onlinemember;
}

function grouptrend($yesterday, $today) {
	$trend = $yesterday - $today;
	return $trend;
}

function write_groupviewed($fid) {
	$fid = intval($fid);
	if ($fid) {
		$groupviewed_limit = 8;
		$groupviewed = getcookie('groupviewed');
		if (!strexists(",$groupviewed,", ",$fid,")) {
			$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
			$groupviewed[] = $fid;
			if (count($groupviewed) > $groupviewed_limit) {
				array_shift($groupviewed);
			}
			dsetcookie('groupviewed', implode(',', $groupviewed), 86400);
		}
	}
}

function updateactivity($fid, $activity = 1) {
	$fid = $fid ? intval($fid) : intval($_G['fid']);
	if ($activity) {
		$forumdata = DB::fetch_first("SELECT f.threads, f.posts, ff.dateline, ff.membernum, ff.activity FROM " . DB::table('forum_forum') . " as f LEFT JOIN " . DB::table('forum_forumfield') . " as ff ON ff.fid=f.fid WHERE f.fid='$fid'");
		if (!$forumdata['activity']) {
			$perpost = intval(($forumdata['threads'] + $forumdata['posts']) / ((TIMESTAMP - $forumdata['dateline']) / 86400));
			$activity = intval($forumdata['threads'] / 2 + $forumdata['posts'] / 5 + $forumdata['membernum'] / 10 + $perpost * 2);
			DB::query("UPDATE " . DB::table('forum_forumfield') . " SET activity='$activity' WHERE fid='$fid'");
		}
	}
	DB::query("UPDATE " . DB::table('forum_forumfield') . " SET lastupdate='" . TIMESTAMP . "' WHERE fid='$fid'");
}

function update_groupmoderators($fid) {
	global $_G;
	if (empty($fid))
	return false;
	$moderators = groupuserlist($fid, 'level_update', 0, 0, array('level' => array('1', '2')), array('username', 'level'));
	if (!empty($moderators)) {
		DB::query("UPDATE " . DB::table('forum_forumfield') . " SET moderators='" . addslashes(serialize($moderators)) . "' WHERE fid='$fid'");
		return $moderators;
	} else {
		return array();
	}
}

function update_usergroups($uids) {
	if (empty($uids))
	return '';
	if (!is_array($uids))
	$uids = array($uids);
	foreach ($uids as $uid) {
		$groups = $grouptype = $usergroups = array();
		$query = DB::query("SELECT f.fid, f.fup, f.name FROM " . DB::table('forum_groupuser') . " g LEFT JOIN " . DB::table('forum_forum') . " f ON f.fid=g.fid WHERE g.uid='$uid' AND g.level>0 ORDER BY g.lastupdate DESC");
		while ($group = DB::fetch($query)) {
			$groups[$group['fid']] = $group['name'];
			$typegroup[$group['fup']][] = $group['fid'];
		}
		if (!empty($typegroup)) {
			$fups = array_keys($typegroup);
			$query = DB::query("SELECT fid, fup, name FROM " . DB::table('forum_forum') . " WHERE fid IN(" . dimplode($fups) . ")");
			while ($fup = DB::fetch($query)) {
				$grouptype[$fup['fid']] = $fup;
				$grouptype[$fup['fid']]['groups'] = implode(',', $typegroup[$fup['fid']]);
			}
			$usergroups = array('groups' => $groups, 'grouptype' => $grouptype);
			if (!empty($usergroups)) {
				DB::query("UPDATE " . DB::table('common_member_field_forum') . " SET groups='" . addslashes(serialize($usergroups)) . "' WHERE uid='$uid'");
			}
		} else {
			DB::query("UPDATE " . DB::table('common_member_field_forum') . " SET groups='' WHERE uid='$uid'");
		}
	}
	return $usergroups;
}

function group_get_plugin_available($fid) {
	
	// modify by songsp 2011-3-16 14:46:08   性能优化
	global  $_G;
	

	//memcache  by songsp  2011-3-23 14:19:10
	$allowmem = memory('check');
	$cache_key = 'group_get_plugin_available_'.$fid ; //UezBhH_
	
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			return unserialize($cache);
		}
	}
	
	
	//将$result 值放到当前页面全局变量中，第一次没有值是查询，之后直接使用
	$result = $_G['myself']['cache']['group_get_plugin_available']['result'][$fid] ;
	if(empty($result)){
		
		$query = DB::query("SELECT plugin_id, status, auth_group FROM " . DB::table('common_group_plugin') . " WHERE fid=" . $fid);
		$result = array();
		while ($row = DB::fetch($query)) {
			if ($row["status"] == "Y") {
				$result[$row["plugin_id"]] = $row;
			}
		}
		
		$_G['myself']['cache']['group_get_plugin_available']['result'][$fid] = $result;
		
		if($allowmem){
			memory("set", $cache_key, serialize($result), 86400); //60*60*24  24小时
		}
		
	}
	
	
	
	
	
	/*
	$query = DB::query("SELECT plugin_id, status, auth_group FROM " . DB::table('common_group_plugin') . " WHERE fid=" . $fid);
	$result = array();
	while ($row = DB::fetch($query)) {
		if ($row["status"] == "Y") {
			$result[$row["plugin_id"]] = $row;
		}
	}
	*/
	
	return $result;
}

/**
 * 获取经验值
 * $user_id 用户编号
 * $fid 专区编号
 */
function group_get_empirical($user_id, $fid) {
	$query = DB::query("SELECT empirical_value FROM " . DB::table("forum_groupuser") . " WHERE uid=" . $user_id . " AND fid=" . $fid);
	$result = DB::fetch($query);
	if ($result) {
		return $result["empirical_value"];
	}
}

function group_add_empirical($user_id, $fid, $value) {
	global  $_G;
	if (!$value) {
		$value = 0;
	}
	$query = DB::query("SELECT type,fup FROM " . DB::table("forum_forum") . " WHERE fid=" . $fid);
	$type = DB::fetch($query);
	if ($type && $type["type"] == "activity") {
		$fid = $type["fup"];
	}
	$oldSoruce=group_get_empirical($user_id, $fid);
	DB::query("UPDATE " . DB::table("forum_groupuser") . " SET empirical_value=empirical_value+(" . $value . ") WHERE uid=" . $user_id . " AND fid=" . $fid);
	if($user_id==$_G[uid]){
		$source = group_get_empirical($user_id, $fid);
		$notics = array(0, $value, 0, 0, 0, 0, 0, 0, 0);
		$bases  = array($oldSoruce, 0, 0, 0, 0, 0);
		$rules = array("");

/**/	
/*		dsetcookie('creditnotice', implode('D', $notics).'D'.$user_id, 43200);
		dsetcookie('creditbase', '0D'.implode('D', $bases), 43200);
		dsetcookie('creditrule', strip_tags(implode("\t", $rules)), 43200);*/
  		dsetcookie('empiricalValueNotice', implode('D', $notics).'D'.$user_id, 43200);
		dsetcookie('empiricalValueBase', '0D'.implode('D', $bases), 43200);
		dsetcookie('empiricalValueRule', strip_tags(implode("\t", $rules)), 43200);
	}
}

function group_add_empirical_by_setting($user_id, $group_id, $op_setting, $resourceid=0) {
	if(!$group_id||!$user_id){
		return;
	}
	$query = DB::query("SELECT type,fup FROM " . DB::table("forum_forum") . " WHERE fid=" . $group_id);
	$type = DB::fetch($query);
	if ($type && $type["type"] == "activity") {
		$group_id = $type["fup"];
	}
	if(group_add_empirical_rule($user_id, $group_id, $op_setting, $resourceid)){
		$resourceid = $op_setting."_".$resourceid;
		$time = TIMESTAMP;
		DB::query("INSERT INTO ".DB::table("group_empirical_log")."(fid, resourceid, uid, dateline) VALUES($group_id, '$resourceid', $user_id, $time)");
		$query = DB::query("SELECT gev.value as gevval,gev.status FROM " . DB::table("group_empirical_values") . " gev," . DB::table("group_empirical") . " ge WHERE gev.eid=ge.id AND ge.ename='" . $op_setting . "' and gev.fid=".$group_id);
		$value = DB::fetch($query);
		if ($value && $value["status"] == "Y") {
			group_add_empirical($user_id, $group_id, $value["gevval"]);
		}
	}
}

function group_add_empirical_rule($user_id, $group_id, $op_setting, $resourceid){
	/*
	 * 如果是专家用户返回false
 	 * added by fumz，2010-10-14 13:55:04
	 */
	if(check_is_expertuser($user_id)){
		return false;
	}
	/*
	 * 单个资源评论，表态只能获取一次经验值(加入专区除外)
	 * 如果用户已经获取了经验值，不再重新获取经验值
	 */
	if($resourceid&&$op_setting!='group_join_group'&&$op_setting!="at_group"){ 
		
		$resourceid = $op_setting."_".$resourceid;
		$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_empirical_log")." WHERE fid=".$group_id." AND uid=".$user_id." AND resourceid='".$resourceid."'");
		$tmp = DB::fetch($query);
		if($tmp[c]>0){//用户已经就某个资源做过操作并获取了积分
			return false;
		}
	}	
	
	/**
	 * 查询出经验值规则
	 */
	$query = DB::query("SELECT gev.* FROM " . DB::table("group_empirical_values")." gev," . DB::table("group_empirical") . " ge WHERE gev.eid=ge.id AND ge.ename='" . $op_setting . "' and gev.fid=".$group_id);
	$result = DB::fetch($query);
	if(!$result){
		return false;
	}
	
	$cycletype = $result["cycletype"];

	switch($cycletype){
		case 1:
			//仅仅次数
			//$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_empirical_log")." WHERE fid=".$group_id." AND uid=".$user_id." AND resourceid='".$resourceid."'");
			if($result["total"]==0){
				return true;
			}
			$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_empirical_log")." WHERE fid=".$group_id." AND uid=".$user_id." AND resourceid LIKE '%$op_setting%'");
			$tmp = DB::fetch($query);
			return $tmp[c]>=$result["total"]?false:true;
		case 2:
			//时间
			//$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_empirical_log")." WHERE fid=".$group_id." AND uid=".$user_id." AND resourceid='".$resourceid."' AND dateline>=".(TIMESTAMP-$result[cycle])." AND dateline<=".TIMESTAMP);
			if($result['cycle']==0||$result["total"]==0){
				return true;
			}
			$query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("group_empirical_log")." WHERE fid=".$group_id." AND uid=".$user_id." AND dateline>=".(TIMESTAMP-$result[cycle])." AND dateline<=".TIMESTAMP." AND resourceid LIKE '%$op_setting%'");
			$tmp = DB::fetch($query);
			if($tmp[c]<$result["total"]){
				return true;
			}
			return false;
	}
}
/**
 * modified by fumz，2010-10-6 17:09:51
 * 修改经专区默认积分周期为给定值
 */
function group_create_empirical($fid) {
	$query = DB::query("SELECT COUNT(1) AS c FROM " . DB::table("group_empirical_values") . " WHERE fid=" . $fid);
	$values = DB::fetch($query);
	if (!$values || ($values["c"] != 0)) {
		return false;
	}
	$query = DB::query("SELECT * FROM " . DB::table("group_empirical"));
	$empirical_list = array();
	while ($row = DB::fetch($query)) {
		switch($row['ename']){
			case 'comment_someting':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>15));
				break;
			case 'express_someting':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>10));
				break;
			case 'share_someting':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>10));
				break;
			case 'topc_reply':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>15));
				break;
			case 'article_view':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'click_ad':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'topic_publish':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'question_answer':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>10));
				break;
			case 'vote_join':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>10));
				break;
			case 'photo_upload':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>10));
				break;
			case 'doc_upload':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>15));
				break;
			case 'doc_read':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'direct_join':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>3));
				break;
			case 'unicast_join':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>3));
				break;				
			case 'resource_read':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'resource_class_unicast':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			
			case 'questionnaire_join':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'activity_apply':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'teacher_commend':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			case 'teacher_comment':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;				
			case 'case_read':
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y",cycletype=>2,cycle=>86400,total=>5));
				break;
			default:
				DB::insert("group_empirical_values", array(eid => $row["id"], fid => $fid, value => $row["default_value"], status => "Y"));
				break;
		}
		
	}
}

function group_load_plugin($fid) {
	global $_G;
	/*
	$cache_key = "group_load_plugin";
	$sacname = $fid."_".$_G[uid];
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
	$plugins = unserialize($cache);
	if($plugins[$secname]){
	$_G["group_plugins"] = $plugins[$fid];
	return true;
	}
	}*/
	
	
	
	//修改 by songsp 2011-3-16 14:18:47   性能优化
	
	//将$group 值放到当前页面全局变量中，第一次没有值是查询，之后直接使用
	$group = $_G['myself']['cache']['group_load_plugin']['group'][$fid] ;
	if(empty($group)){
		
		//modify by songsp  2011-3-21 15:05:16
		if($_G['forum']['fid']){
			$group['fid'] = $_G['forum']['fid'];
			$group['fup'] = $_G['forum']['fup'];
			$group['type']= $_G['forum']['type'];
			$group['name']= $_G['forum']['name'];
			$group['displayorder'] = $_G['forum']['displayorder'];
		}else{
			$query = DB::query("SELECT fid, fup, type,name,displayorder FROM " . DB::table("forum_forum") . " WHERE fid=" . $fid);
			$group = DB::fetch($query);
		}

		$_G['myself']['cache']['group_load_plugin']['group'][$fid] = $group;
		
	}

	
	//$query = DB::query("SELECT fid, fup, type,name,displayorder FROM " . DB::table("forum_forum") . " WHERE fid=" . $fid);
	//$group = DB::fetch($query);
	
	if ($group) {
		
		//$query = DB::query("SELECT group_type FROM " . DB::table('forum_forumfield') . " WHERE fid=" . $group["fid"]);
		//$extra = DB::fetch($query);
		
		//修改 by songsp 2011-3-16 14:38:40   性能优化
		//$extra值也从当前页变量中取
		$extra = $_G['myself']['cache']['group_load_plugin']['extra'][$fid] ;
		if(empty($extra)){
			
			if($_G['forum']['fid']){
				$extra['group_type'] = $_G['forum']['group_type'];
				
			}else{
				$query = DB::query("SELECT group_type FROM " . DB::table('forum_forumfield') . " WHERE fid=" . $group["fid"]);
				$extra = DB::fetch($query);
			}
				
			
			
			$_G['myself']['cache']['group_load_plugin']['extra'][$fid] = $extra;
			
		}
		
		
		$group_available_plugin = $_G['myself']['group']['group_available_plugin'][$group["fid"]];
		if(!$group_available_plugin){
			$group_available_plugin = group_get_plugin_available($group["fid"]);
			$_G['myself']['group']['group_available_plugin'][$group["fid"]] = $group_available_plugin;
		}
		
		
		
		
		
		
		$group_is_group_moderator_vice = $_G['myself']['group']['group_is_group_moderator_vice'][$_G["fid"].'_'.$_G["uid"]];
		if(!$group_is_group_moderator_vice){
			$group_is_group_moderator_vice = group_is_group_moderator_vice($_G["fid"], $_G["uid"]);
			$_G['myself']['group']['group_is_group_moderator_vice'][$_G["fid"].'_'.$_G["uid"]] = $group_is_group_moderator_vice;
		}
		
		$group_is_group_moderator = $_G['myself']['group']['group_is_group_moderator'][$_G["fid"].'_'.$_G["uid"]];
		if(!$group_is_group_moderator){
			$group_is_group_moderator = group_is_group_moderator($_G["fid"], $_G["uid"]);
			$_G['myself']['group']['group_is_group_moderator'][$_G["fid"].'_'.$_G["uid"]] = $group_is_group_moderator;
		}
		
		
		$group_is_group_member = $_G['myself']['group']['group_is_group_member'][$_G["fid"].'_'.$_G["uid"]];
		if(!$group_is_group_member){
			$group_is_group_member = group_is_group_member($_G["fid"], $_G["uid"]);
			$_G['myself']['group']['group_is_group_member'][$_G["fid"].'_'.$_G["uid"]] = $group_is_group_member;
		}
		
		$group_is_group_moderator = $_G['myself']['group']['group_is_group_moderator'][$_G["fid"].'_'.$_G["uid"]];
		if(!$group_is_group_moderator){
			$group_is_group_moderator = group_is_group_moderator($_G["fid"], $_G["uid"]);
			$_G['myself']['group']['group_is_group_moderator'][$_G["fid"].'_'.$_G["uid"]] = $group_is_group_moderator;
		}
		
		
		$group_get_user_title_by_empirical = $_G['myself']['group']['group_get_user_title_by_empirical'][$_G["fid"].'_'.$_G["uid"]];
		if(!$group_get_user_title_by_empirical){
			$group_get_user_title_by_empirical = group_get_user_title_by_empirical($_G["uid"], $_G["fid"]);;
			$_G['myself']['group']['group_get_user_title_by_empirical'][$_G["fid"].'_'.$_G["uid"]] = $group_get_user_title_by_empirical;
		}
		
		
		
		
		foreach ($_G['setting']['plugins']['available_plugins'] as $key => $value) {
			$adminid = $value["modules"]['extra']["adminid"];
			if ($adminid[$extra["group_type"]]) {
				if ($group_available_plugin[$key]) {
					if (($key == 'activity' && $group['type'] == 'activity')) {
						continue;
					}
					if ($key == 'lecturermanage' && strpos($group['fid'], '197') === false) {
						continue;
					}
					if ($key == 'lecturerecord' && strpos($group['fid'], '197') === false) {
						continue;
					}
					if ($key == 'certificate' && strpos($group['fid'], '197') === false) {
						continue;
					}
				    if ($key == 'stationcourse' && strpos($group['fid'], '536') === false) {
						continue;
					}
					if ($key == 'shlecturer' && strpos($group['fid'], '536') === false) {
						continue;
					}
					if ($key == 'shresourcelist' && strpos($group['fid'], '536') === false) {
						continue;
					}
					$data["group_available"][$key] = $value;
				}
				$data["group_all"][$key] = $value;
				foreach ($value["modules"] as $k => $v) {
					if ($v["type"]) {
						if ($v["type"] == "createmenu"
						&& $group_available_plugin[$key]["auth_group"] == "群主"
						&& !$group_is_group_moderator) {
							continue;
						} else if ($v["type"] == "createmenu"
						&& $group_available_plugin[$key]["auth_group"] == "仅活动管理员"
						&& !$group_is_group_moderator) {
							continue;
						}  else if ($v["type"] == "createmenu"
						&& $group_available_plugin[$key]["auth_group"] == "副群主"
						&& !$group_is_group_moderator_vice) {
							continue;
						} else if ($v["type"] == "createmenu"
						&& $group_available_plugin[$key]["auth_group"] == "仅专区成员"
						&& !$group_is_group_member
						&& !$_G['forum']['ismoderator']) {
							continue;
						}else if ($v["type"] == "createmenu"
						&& $group_available_plugin[$key]["auth_group"] == "仅活动成员"
						&& !$group_is_group_member
						&& !$_G['forum']['ismoderator']) {
							continue;
						} else if ($v["type"] == "createmenu"
						&& !$group_is_group_moderator 
						&& !in_array($group_available_plugin[$key]["auth_group"], array("全部", "群主", "副群主", "仅专区成员","所有用户","仅活动成员","仅活动管理员"))
						&& $group_available_plugin[$key]["auth_group"] != $group_get_user_title_by_empirical
						&& !$_G['forum']['ismoderator']) {
							continue;
						}
						if ($group_available_plugin[$key]) {
							if (($key == 'activity' && $group['type'] == 'activity')) {
								continue;
							}
							$data["group_available"][$v["type"]][$key] = $v;
						}
					}
				}
			}
		}
	}

	/*$plugins[$sacname] = $data;
	 memory("set", $cache_key, serialize($plugins), 86400);*/

	$_G["group_plugins"] = $data;
}

function group_get_user_title_by_empirical($uid, $fid) {
	global $_G;
	
	//性能优化  by songsp 2011-3-23 12:45:32
	
	$userlevel = $_G['myself']['$userlevel'][$fid.'_'.$uid] ; //存放用户在某一专区所属等级名称
	
	if($userlevel){
		return 'null'==$userlevel? '':$userlevel['level_name'];
	}
	
	
	$groupuser = $_G['myself']['forum_groupuser'][$fid.'_'.$uid];
	if($groupuser){
		
		if($groupuser=='null'){
			$_G['myself']['$userlevel'][$fid.'_'.$uid] = 'null';
			return '';
		}
		$user_value =  $groupuser;
	}
	
	
	if(!$user_value){
		$query = DB::query("SELECT empirical_value FROM " . DB::table("forum_groupuser") . " WHERE fid=" . $fid . " AND uid=" . $uid);
		$user_value = DB::fetch($query);
	}
	
	if ($user_value) {
		$query = DB::query("SELECT level_name FROM " . DB::table("forum_userlevel") . " WHERE fid=".$fid." AND start_emp<=" . $user_value["empirical_value"]
		. " AND end_emp>=" . $user_value["empirical_value"] . " ORDER BY end_emp LIMIT 1");
		$level_name = DB::fetch($query);

		$_G['myself']['$userlevel'][$fid.'_'.$uid] = $level_name?$level_name:'null';
		
		return $level_name["level_name"];
	}
	
	
}

function group_get_by_user($uid) {
	if (!isset($uid)) {
		return array();
	}
	$query = DB::query("SELECT fid FROM " . DB::table("forum_groupuser") . " WHERE uid=" . $uid);
	$fids = array();
	while ($row = DB::fetch($query)) {
		$fids[] = $row["fid"];
	}
	if ($fids && count($fids) != 0) {
		$query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE fid IN(" . implode(",", $fids) . ") and type='sub'");
		$groups = array();
		while ($group = DB::fetch($query)) {
			$groups[] = $group;
		}
	}
	return $groups;
}

function group_create_plugin($fid) {
	$query = DB::query("SELECT identifier, modules FROM " . DB::table("common_plugin"));
	while ($row = DB::fetch($query)) {
		$plugin_id = $row["identifier"];
		$module = $row["adminid"];
		$sql = sprintf("INSERT INTO " . DB::table("common_group_plugin") . "(fid, plugin_id, auth_group, status) VALUES({$fid}, '{$plugin_id}', '仅专区成员', 'Y')");
		DB::query($sql);
	}
}

function group_getid_by_userid($uid) {
	if (!isset($uid)) {
		return array();
	}
	$query = DB::query("SELECT fid FROM " . DB::table("forum_groupuser") . " WHERE uid=" . $uid);
	$fids = array();
	while ($row = DB::fetch($query)) {
		$fids[] = $row["fid"];
	}
	return $fids;
}

function group_add_user_to_group($uid, $username, $fid) {
	$query = DB::query("SELECT COUNT(1) AS c FROM " . DB::table("forum_groupuser") . " WHERE fid=" . $fid . " AND uid=" . $uid);
	$count = DB::fetch($query);
	if ($count && $count["c"] != 0) {
		return false;
	}

	$modmember = 4;
	DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate)
           VALUES ('$fid', '$uid', '$username', '$modmember', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
	update_usergroups($uid);
	DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$fid'");
	updateactivity($fid, 0);
	include_once libfile('function/stat');
	updatestat('groupjoin');
	delgroupcache($fid, array('activityuser', 'newuserlist'));
	return true;
}

function group_is_group_admin($fid, $uid) {
	if (!$fid || !$uid) {
		return false;
	}
	$level = group_get_gorup_level($fid, $uid);
	if ($level) {
		return ($level == 1 || $level == 2) ? true : false;
	}
}

function group_get_gorup_level($fid, $uid) {
	global $_G;
	if (!$fid || !$uid) {
		return false;
	}
	$cache_key = "group_get_gorup_level_".$fid."_".$uid;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
    
	
	//性能优化 by songsp 2011-3-23 13:07:31
	$groupuser = $_G['myself']['forum_groupuser'][$fid.'_'.$uid];
	if($groupuser){
		
		return $groupuser!='null'?$groupuser['level']:'';
		
	}
	
	
	
	$return = $_G['myself']['group_level'][$fid.'_'.$uid];
	if(!$return){
		$query = DB::query("SELECT * FROM " . DB::table('forum_groupuser') . " WHERE fid=" . $fid . " AND uid=" . $uid);
		$result = DB::fetch($query);
		$return = $result["level"];
		if(!$return) $return = 'null'; //如果结果是空 设置为null字符串
		$_G['myself']['group_level'][$fid.'_'.$uid] = $return;
		
		
		$_G['myself']['forum_groupuser'][$fid.'_'.$uid] = $result? $result :'null';
	}
	
	
	if('null'==$return) $return = '';
	
	memory("set", $cache_key, serialize($return), 86400);
	return $return;
}

function group_is_group_moderator($fid, $uid) {
	if (!$fid || !$uid) {
		return false;
	}
	$level = group_get_gorup_level($fid, $uid);
	return $level == 1 ? true : false;
}

function group_is_group_moderator_vice($fid, $uid) {
	if (!$fid || !$uid) {
		return false;
	}
	$level = group_get_gorup_level($fid, $uid);
	return ($level ==2||$level==1) ? true : false;//权限
}

function group_is_group_founder($fid, $uid) {
	if (!$fid || !$uid) {
		return false;
	}
	$cache_key = "group_founder_".$fid."_".$uid;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}

	$query = DB::query("SELECT count(1) as c FROM " . DB::table('forum_forumfield') . " WHERE fid=" . $fid . " AND founderuid=" . $uid);
	$result = DB::fetch($query);
	$return = $result["c"] ? true : false;
	memory("set", $cache_key, serialize($return), 86400);
	return $return;
}

function group_is_group_member($fid, $uid) {
	global $_G;
	$cache_key = "group_member_".$fid."_".$uid;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}

	
	//性能优化  by songsp 2011-3-23 12:45:32
	$groupuser = $_G['myself']['forum_groupuser'][$fid.'_'.$uid];
	if($groupuser){
		$return = $groupuser=='null'?false:true;
		return $return ;
		
	}
	
	
	$query = DB::query("SELECT count(1) as c FROM " . DB::table("forum_groupuser") . " fg WHERE fg.fid=" . $fid . " AND uid=" . $uid);
	$result = DB::fetch($query);
	if($result){
		$return = ($result["c"] !=0? true : false);
		memory("set", $cache_key, serialize($return), 86400);
		return $return;
	}
}

function group_update_forum_hot($config, $fid=false) {
	if(!is_array($config)){
		$fid = $config;
		require_once libfile("function/misc");
		$config = misc_get_forum_hot_config();
	}
	if (!$fid) {
		DB::query("UPDATE " . DB::table("forum_forumfield") .
                " SET hot=threads*$config[threads]+viewnum*$config[viewnum]+replys*$config[replys]+membernum*$config[members]");
	} else {
		DB::query("UPDATE " . DB::table("forum_forumfield") .
                " SET hot=threads*$config[threads]+viewnum*$config[viewnum]+replys*$config[replys]+membernum*$config[members] WHERE fid=$fid");
	}
}

function group_add_tag($fid, $tags){
	$sql = "select f.fid,f.name,f.fup,ff.groupnum from ".DB::table("forum_forum")." f  LEFT JOIN "
	.DB::table('forum_forumfield')." ff USING(fid) where f.type='label' and f.name IN(".dimplode($tags).") and f.fup!=0";
	$query = DB::query($sql);
	$fids = array();
	while($row=DB::fetch($query)){
		$fids[] = $row["fid"];
	}
	require_once libfile("function/label");
	insertlabelgroup($fid, implode(",", $fids));
}

function group_remov_tag($fid, $tags){
	require_once libfile("function/label");
	$sql = "select f.fid,f.name,f.fup,ff.groupnum from ".DB::table("forum_forum")." f  LEFT JOIN "
	.DB::table('forum_forumfield')." ff USING(fid) where f.type='label' and f.name IN(".dimplode($tags).") and f.fup!=0";
	$query = DB::query($sql);
	$fids = array();
	while($row=DB::fetch($query)){
		deletelabelgroup($fid, $row[fid]);
	}
}

/**根据用户id，查找用户名，一个也没只查询一次，引入$G对象
 * 王聪
 * 
 */
function user_get_user_realname($uids){
	global $_G;
	if(count($uids)!=0){		
		$sql="SELECT uid, realname FROM ".DB::table("common_member_profile")." cmp WHERE cmp.uid IN(".  dimplode($uids).")";
		if($_G['user_get_user_realname'][$sql]){
			return $_G['user_get_user_realname'][$sql];
		}
		$query = DB::query($sql);
		while($row=DB::fetch($query)){
			$return[$row[uid]] = $row;
		}
		$_G['user_get_user_realname'][$sql]=$return;
		return $return;
	}
}

function user_get_user_name($uid){
	global $_G;
	
	if(!$uid)return "未知";
	
	$uname = $_G['myself']['user_realname'][$uid];
	if($uname){
		return $uname;
	}
	
	
	
	$cache_key = "user_realname_".$uid;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	$query = DB::query("SELECT uid, realname ,cmp.* FROM ".DB::table("common_member_profile")." cmp WHERE cmp.uid=".$uid);
	$row = DB::fetch($query);
	if($row){
		$return = $row["realname"];
		memory("set", $cache_key, serialize($return), 86400);
		
		$_G['myself']['common_member_profile'][$uid] = $row;
		
		//return $return;
	}else{ // add by songsp 2011-3-16 16:50:05  如果数据库中没有该用户信息，设置为未知
		$return = "未知";
		memory("set", $cache_key, serialize($return), 86400);
		//return $return;
	}
	$_G['myself']['user_realname'][$uid] = $return;
	return $return;
}


/**
 * 根据用户id，查找用户名，一个也没只查询一次，引入$G对象
 * 王聪注释
 *  
 */
function user_get_user_name_by_username($username){
	$cache_key = "user_realname_username_".$username;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	$query = DB::query("SELECT cmp.uid, cmp.realname FROM ".DB::table("common_member_profile")." cmp, ".DB::table("common_member")." cm WHERE cm.uid=cmp.uid AND cm.username='".$username."'");
	$row = DB::fetch($query);
	if($row){
		$return = $row["realname"];
		memory("set", $cache_key, serialize($return), 86400);
		return $return;
	}
}

function user_get_uid_by_username($username){
	$cache_key = "user_uid_username_".$username;
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		return unserialize($cache);
	}
	$query = DB::query("SELECT cmp.uid, cmp.realname FROM ".DB::table("common_member_profile")." cmp, ".DB::table("common_member")." cm WHERE cm.uid=cmp.uid AND cm.username='".$username."'");
	$row = DB::fetch($query);
	if($row){
		$return = $row["uid"];
		memory("set", $cache_key, serialize($return), 86400);
		return $return;
	}
}

function get_groupname_by_fid($fid) {
	if($fid){
		$cache_key = "group_name_".$fid;
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			return unserialize($cache);
		}
		$query = DB::query("SELECT fid, name FROM ".DB::table("forum_forum")." WHERE fid=".$fid);
		$row = DB::fetch($query);
		if($row){
			$return = $row["name"];
			memory("set", $cache_key, serialize($return), 86400);
			return $return;
		}
	}
}

//根据fid返回创建者uid, 若为活动，可获得活动所在专区fid
function get_uid_by_fid($fid) {
	if (!$fid) {
		return false;
	}
	$query = DB::query("SELECT ff.fid fid, ff.fup fup, ff.name name, fff.founderuid founderuid FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff WHERE ff.fid=fff.fid AND ff.fid=".$fid);
	$row = DB::fetch($query);
	if($row){
		$return = $row;
		return $return;
	}
}

/**
 *
 * 判断某用户是否是某个专区的成员
 * @param username $username
 * @param fid $fid
 */
function check_user_group($username, $fid) {
	if (!$username || !$fid) {
		return false;
	}
	$query = DB::query("SELECT * FROM ".DB::table("forum_groupuser")." WHERE username='".$username."' AND fid=".$fid);
	$row =DB::fetch($query);
	if ($row) {
		return true;
	} else {
		return false;
	}
}

/**
 *
 * 判断用户是否为专区创建者
 * @param $uid
 * @param $fid
 */
function check_user_group_founder($uid, $fidarray) {
	if (!$uid || !$fidarray) {
		return false;
	}
	$query = DB::query("SELECT * FROM ".DB::table("forum_forumfield")." WHERE founderuid=".$uid." AND fid in(".dimplode($fidarray).")");
	$row =DB::fetch($query);
	if ($row) {
		return true;
	} else {
		return false;
	}
}

/**
 *
 * 删除活动
 * @param $fid 活动fid
 * @param $isadmin=true 高级管理员
 */
function activity_delete_by_useradmin($fidarray, $isadmin = false) {
	if (!$fidarray) {
		return false;
	}
	global $_G;
	if (!$isadmin) {
		if (!check_user_group_founder($_G['uid'], $fidarray)) {
			return false;
		}
	}
	$fidarray_old = $fidarray;
	foreach ($fidarray as $delfid) {
		$fuid[$delfid]=get_uid_by_fid($delfid);
	}
	$tids = $nums = array();
	$query = DB::query("SELECT fup FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
	while($fup = DB::fetch($query)) {
		$nums[$fup['fup']] ++;
	}
	foreach($nums as $fup => $num) {
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET activity = activity+(-$num) WHERE fid='$fup'");
	}
	$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid IN(".dimplode($fidarray).") ORDER BY tid ");
	while($thread = DB::fetch($query)) {
		$tids[] = $thread['tid'];
	}
	if($tids) {
		$tids = implode(',', $tids);
		require_once libfile('function/delete');
		deletepost("tid IN($tids)");
		//		cpmsg('group_thread_removing', 'action=group&operation=deletegroup&submit=yes&confirmed=yes&fidarray='.$fidarray_old);
	}
	foreach(array('forum_thread', 'forum_post', 'forum_forumrecommend', 'forum_postposition') as $value) {
		DB::query("DELETE FROM ".DB::table($value)." WHERE fid IN(".dimplode($fidarray).")", 'UNBUFFERED');
	}

	DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('forum_forum_activity')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_group_plugin')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('group_empirical_values')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_plugin_category')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_category')." WHERE fid IN(".dimplode($fidarray).")");
	
	DB::query("DELETE FROM ".DB::Table("common_resource")." WHERE fid IN(".dimplode($fidarray).")");

	//积分
	require_once libfile('function/credit');
	foreach ($fidarray as $delfid) {
		credit_create_credit_log($fuid[$delfid]['founderuid'], 'deletegroup', $delfid);
	}

	return true;
	//	cpmsg('group_delete_succeed', 'action=group&operation=manage', 'succeed');
}

/**
 *
 * 删除专区
 * @param $fid 专区fid
 * @param $isadmin=true 高级管理员
 */
function group_delete_by_useradmin($fidarray, $isadmin = false) {
	if (!$fidarray) {
		return false;
	}
	global $_G;
	if (!$isadmin) {
		if (!check_user_group_founder($_G['uid'], $fidarray)) {
			return false;
		}
	}
	/*
	 * 专区tag下专区数修改
	 */
	//begin
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
	$fidarray_old = $fidarray;
	foreach ($fidarray as $delfid) {
		$fuid[$delfid]=get_uid_by_fid($delfid);
	}
	$tids = $nums = array();
	$query = DB::query("SELECT fup FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
	while($fup = DB::fetch($query)) {
		$nums[$fup['fup']] ++;
	}
	foreach($nums as $fup => $num) {
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum = groupnum+(-$num) WHERE fid='$fup'");
	}
	$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid IN(".dimplode($fidarray).") ORDER BY tid ");
	while($thread = DB::fetch($query)) {
		$tids[] = $thread['tid'];
	}
	if($tids) {
		$tids = implode(',', $tids);
		require_once libfile('function/delete');
		deletepost("tid IN($tids)");
		//		cpmsg('group_thread_removing', 'action=group&operation=deletegroup&submit=yes&confirmed=yes&fidarray='.$fidarray_old);
	}
	foreach(array('forum_thread', 'forum_post', 'forum_forumrecommend', 'forum_postposition') as $value) {
		DB::query("DELETE FROM ".DB::table($value)." WHERE fid IN(".dimplode($fidarray).")", 'UNBUFFERED');
	}

	DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_group_plugin')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('group_empirical_values')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_plugin_category')." WHERE fid IN(".dimplode($fidarray).")");
	DB::query("DELETE FROM ".DB::table('common_category')." WHERE fid IN(".dimplode($fidarray).")");
	
	DB::query("DELETE FROM ".DB::Table("common_resource")." WHERE fid IN(".dimplode($fidarray).")");

	//积分
	require_once libfile('function/credit');
	foreach ($fidarray as $delfid) {
		credit_create_credit_log($fuid[$delfid]['founderuid'], 'deletegroup', $delfid);
	}

	return true;
	//	cpmsg('group_delete_succeed', 'action=group&operation=manage', 'succeed');
}

/**
 *
 * 调用基础管理平台接口，判断用户是否是该专区的专家用户
 * @param 用户username $username
 * @param 专区fid $fid
 */
function check_is_groupexpert($username,$fid) {
	//调用基础管理平台接口
	return false;
}

function delete_groupuser_fid($fid) {
	/*
	 * 王聪，原来的接口为什么叫是uid？
	 */
	if (!$fid) {
		return false;
	}
	$num = DB::result_first("SELECT count(*) FROM ".DB::table("forum_groupuser")." WHERE level!=1 AND fid=".$fid);
	
	if ($num>0) {
		DB::query("DELETE FROM ".DB::table('forum_groupuser')." WHERE level!=1 AND fid=".$fid);
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum = membernum+(-$num) WHERE fid='$fid'");
	}
}

/**
 *
 * 将专家用户信息入社区库
 * @param $expertinfo
 */
function insert_expertuser($expertinfo) {
	if (!$expertinfo['uid']) {
		return false;
	}
	DB::query("INSERT INTO ".DB::table("expertuser")."(uid, username, fid, activelink) VALUES($expertinfo[uid], '$expertinfo[username]', $expertinfo[fid], '$expertinfo[activelink]')");
}

function firstlogin_expertuser($uid) {
	if (!$uid) {
		return false;
	}
	$query = DB::query("SELECT * FROM ".DB::table("expertuser")." WHERE status=0 AND uid=".$uid);
	$row =DB::fetch($query);
	if ($row) {
		DB::query("UPDATE " . DB::table('expertuser') . " SET status=1 WHERE uid='$uid'");
		return true;
	} else {
		return false;
	}
}

function check_is_expertuser($uid) {
	if (!$uid) {
		return false;
	}
	$query = DB::query("SELECT * FROM ".DB::table("expertuser")." WHERE uid=".$uid);
	$row =DB::fetch($query);
	if ($row) {
		return true;
	} else {
		return false;
	}
}

function get_expertuser_activelink_by_uid($uid) {
	if (!$uid) {
		return false;
	}
	$query = DB::query("SELECT * FROM ".DB::table("expertuser")." WHERE uid=".$uid);
	$row =DB::fetch($query);
	if ($row) {
		return $row['activelink'];
	} else {
		return false;
	}
}
function check_is_group_teacher($fid, $lecid) {
	global $_G;
	if (!$fid || !lecid) {
		return false;
	}
	if($_G['check_is_group_teacher'][$fid][$lecid]){
		return $_G['check_is_group_teacher'][$fid][$lecid];
	}
	$query = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE fid=".$fid." AND lecid=".$lecid);
	$row =DB::fetch($query);
	if ($row) {
		$_G['check_is_group_teacher'][$fid][$lecid]=$row;
		return $row;
	} else {
		return false;
	}
}
/*
 * 判断专区中某个组件是否在用
 * 组件未开启返回false,否则返回true
 * 专区中没有该组件返回false
 */
function check_plugin_is_in_use($fid,$plugin_id){
	if(!$fid||!$plugin_id){
		return false;
	}
	$query=DB::query("SELECT * FROM pre_common_group_plugin WHERE fid=$fid AND plugin_id='$plugin_id'");
	$row=DB::fetch($query);	
	if($row){
		if($row['status']=='Y'){
			return true;
		}
	}
	return false;
}

/*
 * add by  songsp 2010-11-29 19:00
 * 创建专区是，指定模板
 * $fid 专区id
 * $template_code 模板编码
 * $is_group 是否专区
 *
 * 成功 true  失败 false
 */
function init_group_template($fid , $template_code , $is_group=true ){
	global $_G;
	
	if(!$fid) return false;
	
	
	$template_code = empty($template_code)? '1':$template_code;

	
	require_once libfile("function/portalcp");
	//require './source/function/function_portalcp.php';
	//require './source/function/function_home.php';


	//模板页面
	$template_file = DISCUZ_ROOT.'./template/default/diy/set_'.$template_code.'.xml';
	




	//解析模板
	$xml_content = file_get_contents($template_file);
	require_once libfile('class/xml');
	if (empty($xml_content)) return false;

	$diycontent = xml2array($xml_content); //模板内容转为数组


	if (!$diycontent)  return false;
		


	foreach ($diycontent['layoutdata'] as $key => $value) {
		if (!empty($value)) getframeblock($value);
	}
	$newframe = array();
	foreach ($_G['curtplframe'] as $value) {
		$newframe[] = $value['type'].random(6);
	}

	$mapping = array();
	if (!empty($diycontent['blockdata'])) {
		$mapping = block_import($diycontent['blockdata']);
		unset($diycontent['bockdata']);
	}

	$oldbids = $newbids = array();
	if (!empty($mapping)) {
		foreach($mapping as $obid=>$nbid) {
				$oldbids[] = '#portal_block_'.$obid.' ';
				$newbids[] = '#portal_block_'.$nbid.' ';
				$oldbids[] = '[portal_block_'.$obid.']';
				$newbids[] = '[portal_block_'.$nbid.']';
				$oldbids[] = '~portal_block_'.$obid.'"';
				$newbids[] = '~portal_block_'.$nbid.'"';
				$oldbids[] = 'portal_block_'.$obid;
				$newbids[] = 'portal_block_'.$nbid;
		
		}
	}


	$xml = array2xml($diycontent['layoutdata'],true);
		

	$xml = str_replace($oldbids, $newbids, $xml);
	$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
	$diycontent['layoutdata'] = xml2array($xml);


	$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
	$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);

	$templatedata = array();
		
	$templatedata['spacecss'] = $css;
		
	$templatedata['layoutdata'] = $diycontent['layoutdata'];
	if (empty($templatedata['layoutdata'])) showmessage('diy_data_format_invalid');
	

	$newbids = array();

	foreach($mapping as $obid=>$nbid) {
			$newbids[$nbid] = $nbid;

	}
	$_G['curtplbid'] = $newbids;


	$template = 'group/group';
	if(!$is_group){
		//不是专区
	}
	

	$clonefile =$fid;

	$targettplname = $template.'_'.$clonefile;



	$r = save_diy_data($template, $targettplname, $templatedata, true, '');

	return true;	
	
}




?>