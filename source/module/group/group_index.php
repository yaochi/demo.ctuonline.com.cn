<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_index.php 9843 2010-05-05 05:38:57Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$gid = intval(getgpc('gid'));
$sgid = intval(getgpc('sgid'));
$groupids = $groupnav = $typelist = '';
$selectorder = array('default' => '', 'thread' => '', 'membernum' => '', 'dateline' => '', 'activity' => '');
if(!empty($_G['gp_orderby'])) {
	$selectorder[$_G['gp_orderby']] = 'selected';
} else {
	$selectorder['default'] = 'selected';
}
$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
require_once libfile('function/group');
require_once libfile('function/friend');
$url = $_G['basescript'].'.php';
$navtitle = '';

if($gid) {
	if(!empty($first[$gid])) {
		$curtype = $first[$gid];
		foreach($curtype['secondlist'] as $fid) {
			$typelist[$fid] = $second[$fid];
		}
		$groupids = $first[$gid]['secondlist'];
		$url .= '?gid='.$gid;
	} else {
		$gid = 0;
	}
} elseif($sgid) {
	if(!empty($second[$sgid])) {
		$curtype = $second[$sgid];
		$fup = $curtype['fup'];
		$groupids = array($sgid);
		$url .= '?sgid='.$sgid;
	} else {
		$sgid = 0;
	}
}
if(empty($curtype)) {
	$curtype = array();
	$navtitle = lang('group/template', 'group_index');
} else {
	$_G['grouptypeid'] = $curtype['fid'];
	$navtitle = lang('group/template', 'group').' - '.$curtype['name'];
	$groupnav = get_groupnav($curtype);
	$perpage = 10;
	if($curtype['forumcolumns'] > 1) {
		$curtype['forumcolwidth'] = floor(99 / $curtype['forumcolumns']).'%';
		$perpage = $curtype['forumcolumns'] * 10;
	}
}

$data = $randgrouplist = $randgroupdata = $grouptop = $newgrouplist = array();

$randgroupdata = $_G['cache']['groupindex']['randgroupdata'];
$topgrouplist = $_G['cache']['groupindex']['topgrouplist'];
$newgrouparray = $_G['cache']['groupindex']['newgrouparray'];
$joinnum = intval($_G['cache']['groupindex']['joinnum']);
$todayposts = intval($_G['cache']['groupindex']['todayposts']);
$groupnum = intval($_G['cache']['groupindex']['groupnum']);
$cachetimeupdate = TIMESTAMP - intval($_G['cache']['groupindex']['updateline']);
if(empty($_G['cache']['groupindex']) || $cachetimeupdate > 3600 ) {
	//以下三个值未使用
	//$data['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
	$data['newgrouparray'] = $newgrouparray = grouplist('dateline', array('ff.icon, ff.membernum'), 10);
	//$data['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.fid', 'f.name', 'ff.membernum', 'ff.icon'), 8);
	$data['joinnum'] = $joinnum = DB::result_first("SELECT COUNT(DISTINCT uid) FROM ".DB::table('forum_groupuser')."");
	$data['updateline'] = TIMESTAMP;
	$groupdata = DB::fetch_first("SELECT SUM(todayposts) AS todayposts, COUNT(fid) AS groupnum FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub'");
	$topicdata=DB::fetch_first("SELECT COUNT(distinct tid) as topicnum FROM ".DB::table('forum_thread')." WHERE special='0' and dateline>".strtotime("-1 day")." and dateline<".strtotime("+1 day"));
	
	$data['todayposts'] = $todayposts = $topicdata['topicnum'];
	//ADD BY QIAOYZ,2011-2-28 修改“今日发帖”为涵盖当日专区新发帖数和回帖数。
	$date=date("Y-m-d",time());
	$date=strtotime($date);
	//$count_index=DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE dateline>".$date."");
	$todayposts=DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_post')." WHERE dateline>".$date."");
	$doingnum=DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_doing')." WHERE dateline>".$date);
	//$todayposts=$todayposts+$count_index;
	$data['todayposts'] = $todayposts+$doingnum;

	$data['groupnum'] = $groupnum = $groupdata['groupnum'];
	
	save_syscache('groupindex', $data);
}






//新建专区
if($newgrouparray) {
	foreach($newgrouparray as $groupid => $group) {
		$newgrouplist[$groupid]['icon'] = $group['icon'];
    	$newgrouplist[$groupid]['name'] = cutstr($group['name'], 26);
		$newgrouplist[$groupid]['membernum'] = $group['membernum'];
	}
}


/*  页面上未用到
$randgroup = $list = array();
if($groupids) {
	$orderby = in_array(getgpc('orderby'), array('membernum', 'dateline', 'thread', 'activity')) ? getgpc('orderby') : 'displayorder';
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
	$start = ($page - 1) * $perpage;
	$getcount = grouplist('', '', '', $groupids, 1, 1);
	if($getcount) {
		$list = grouplist($orderby, '', array($start, $perpage), $groupids, 1);
		$multipage = multi($getcount, $perpage, $page, $url."&orderby=$orderby");
	}

} else {
	if($randgroupdata) {
		foreach($randgroupdata as $groupid => $rgroup) {
			if($rgroup['iconstatus']) {
				$randgrouplist[$groupid] = $rgroup;
			}
		}
	}

	if(count($randgrouplist) > 8) {
		foreach(array_rand($randgrouplist, min(8, count($randgrouplist))) as $fid) {
			$randgroup[] = $randgrouplist[$fid];
		}
	} elseif (count($randgrouplist)) {
		$randgroup = $randgrouplist;
	}
}


*/



//使用cache机制
$allowmem = memory('check');




	
	


//好友加入的专区  
	/*$frienduid = friend_list($_G['uid'], 50);
	$frienduidarray = $friendgrouplist = array();
	if($frienduid && is_array($frienduid)) {
		foreach($frienduid as $friend) {
			$frienduidarray[] = $friend['fuid'];
		}
	
		$query = DB::query("SELECT f.fid, f.name, ff.icon, ff.group_type , f.displayorder 
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
			LEFT JOIN ".DB::table('forum_groupuser')." g ON g.fid=f.fid
			WHERE f.type='sub' AND g.uid IN (".dimplode($frienduidarray).") LIMIT 0, 6");
		while($group = DB::fetch($query)) {
			$group['icon'] = get_groupimg($group['icon'], 'icon');
			if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
		        $group['type_icn_id'] = 'brzone_s';
		    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
		        $group['type_icn_id'] = 'bizone_s';
		    }
			$friendgrouplist[$group['fid']] = $group;
		}
	}
	*/
//好友加入的专区  
$cache_key = 'group_index_friendgrouplist_'.$_G['uid'] ;// key 和用户有关
$expire = 43200; // 过期时间 12小时
$is_from_db = true;
if($allowmem){
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$friendgrouplist=unserialize($cache);	
		$is_from_db = false;
	}
}

if($is_from_db){  //从DB中查询
	$frienduid = friend_list($_G['uid'], 50);
	$frienduidarray = $friendgrouplist = array();
	if($frienduid && is_array($frienduid)) {
		foreach($frienduid as $friend) {
			$frienduidarray[] = $friend['fuid'];
		}
	
		$query = DB::query("SELECT f.fid, f.name, ff.icon, ff.group_type ,ff.jointype, f.displayorder 
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
			LEFT JOIN ".DB::table('forum_groupuser')." g ON g.fid=f.fid
			WHERE f.type='sub' AND g.uid IN (".dimplode($frienduidarray).") LIMIT 0, 6");
		while($group = DB::fetch($query)) {
			$group['icon'] = get_groupimg($group['icon'], 'icon');
			if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
		        $group['type_icn_id'] = 'brzone_s';
		    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
		        $group['type_icn_id'] = 'bizone_s';
		    }
			$friendgrouplist[$group['fid']] = $group;
		}
	}
	
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($friendgrouplist), $expire);
		
	}	
}

	
	
	
	


// 浏览过的专区
//$groupviewed_list = get_viewedgroup();

$cache_key = 'group_index_groupviewed_list_'.$_G['uid'] ;  // key 和用户有关
$expire = 3600; // 过期时间 1小时
$is_from_db = true;
if($allowmem){
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$groupviewed_list=unserialize($cache);	
		$is_from_db = false;
	}
}

if($is_from_db){
	$groupviewed_list = get_viewedgroup();
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($groupviewed_list), $expire);
	}	
}
	






//推荐专区
/*$recommendgroup = array();
$query = DB::query("SELECT f.fid, f.name, f.digest, ff.icon, ff.membernum, ff.group_type 
		FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
		WHERE f.displayorder=1 ORDER BY ff.dateline DESC LIMIT 16");
while($group=DB::fetch($query)){
    $group['icon'] = get_groupimg($group['icon'], 'icon');
	$group['titlename'] = $group["name"];
   	if(strlen($group["name"])>30){
    	$group['name'] = cutstr($group["name"], 18);
	}
	
    if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
        $group['type_icn_id'] = 'brzone_s';
    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
        $group['type_icn_id'] = 'bizone_s';
    }
    $recommendgroup[] = $group;
}
*/
//推荐专区
$cache_key = 'group_index_recommendgroup';
$expire = 3600; // 推荐专区过期时间 1小时
$is_from_db = true;
if($allowmem){
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$recommendgroup=unserialize($cache);	
		$is_from_db = false;
	}
}
if($is_from_db){
	
	$recommendgroup = array();
	$query = DB::query("SELECT f.fid, f.name, f.digest, ff.icon, ff.membernum, ff.group_type,ff.jointype 
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
			WHERE f.displayorder=1 and f.type='sub' and ff.jointype!='-1' ORDER BY ff.dateline DESC LIMIT 16");
	while($group=DB::fetch($query)){
	    $group['icon'] = get_groupimg($group['icon'], 'icon');
		$group['titlename'] = $group["name"];
	   	if(strlen($group["name"])>30){
	    	$group['name'] = cutstr($group["name"], 18);
		}
		
	    if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
	        $group['type_icn_id'] = 'brzone_s';
	    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
	        $group['type_icn_id'] = 'bizone_s';
	    }
	    $recommendgroup[] = $group;
	}	
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($recommendgroup), $expire);
	}	
}





//活跃专区
/*
$newactivity = array();
$query = DB::query("SELECT f.fid, f.name,f.digest, ff.icon, ff.membernum, ff.group_type , f.displayorder 
		FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
		WHERE f.type='sub' ORDER BY ff.hot DESC LIMIT 16");
while($group=DB::fetch($query)){
    $group['icon'] = get_groupimg($group['icon'], 'icon');
	$group['titlename'] = $group["name"];
	if(strlen($group["name"])>30){
    	$group['name'] = cutstr($group["name"], 18);
	}
	if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
        $group['type_icn_id'] = 'brzone_s';
    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
        $group['type_icn_id'] = 'bizone_s';
    }
    $newactivity[] = $group;
}
*/

//活跃专区
$cache_key = 'group_index_newactivity';
$expire = 86400; // 活跃专区过期时间 24小时
$is_from_db = true;
if($allowmem){
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$newactivity=unserialize($cache);	
		$is_from_db = false;
	}
}
if($is_from_db){
	$newactivity = array();
	$query = DB::query("SELECT f.fid, f.name,f.digest, ff.icon, ff.membernum, ff.group_type ,ff.jointype, f.displayorder 
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
			WHERE f.type='sub'  and ff.jointype!='-1' ORDER BY ff.hot DESC LIMIT 16");
	while($group=DB::fetch($query)){
	    $group['icon'] = get_groupimg($group['icon'], 'icon');
		$group['titlename'] = $group["name"];
		if(strlen($group["name"])>30){
	    	$group['name'] = cutstr($group["name"], 18);
		}
		if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
	        $group['type_icn_id'] = 'brzone_s';
	    } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
	        $group['type_icn_id'] = 'bizone_s';
	    }
	    $newactivity[] = $group;
	}
	
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($newactivity), $expire);
	}	
	
}



//专区分类
/*
$categorys = array();
require_once libfile("function/label");
foreach($_G["common"]["group_type"] as $item){
    $query = DB::query("SELECT fl.labelid FROM ".DB::table("forum_forumfield")." fff,".DB::table("forum_labelgroup")." fl WHERE fff.fid=fl.groupid AND fff.group_type=".$item[0]." GROUP BY fl.labelid");
	$count=DB::result_first("SELECT count(*) FROM ".DB::table("forum_forumfield")." fff,".DB::table("forum_forum")." ff WHERE ff.fid=fff.fid AND ff.type='sub' AND group_type=".$item[0]);
    $labelids = array();
    while($row=DB::fetch($query)){
        $labelids[]  = $row["labelid"];
    }
    array_unique($labelids);
    $labels = array();
    foreach($labelids as $labelid){
        if($labelid){
            $labels[] = getlabelgroupnum($labelid);
        }
    }
    $categorys[$item[1]]["groupnum"] = $count;
    $categorys[$item[1]]["subs"] = $labels;
	$categorys[$item[1]]["gid"]=$item[0];
}
*/

//专区分类
$cache_key = 'group_index_categorys';
$expire = 7200; // 活跃专区过期时间 2小时
$is_from_db = true;
if($allowmem){
	$cache = memory("get", $cache_key);
	if(!empty($cache)){
		$categorys=unserialize($cache);	
		$is_from_db = false;
	}
}
if($is_from_db){
	$categorys = array();
	require_once libfile("function/label");
	foreach($_G["common"]["group_type"] as $item){
	    $query = DB::query("SELECT fl.labelid FROM ".DB::table("forum_forumfield")." fff,".DB::table("forum_labelgroup")." fl WHERE fff.fid=fl.groupid AND fff.group_type=".$item[0]." GROUP BY fl.labelid");
		$count=DB::result_first("SELECT count(*) FROM ".DB::table("forum_forumfield")." fff,".DB::table("forum_forum")." ff WHERE ff.fid=fff.fid AND ff.type='sub' AND group_type=".$item[0]);
	    $labelids = array();
	    while($row=DB::fetch($query)){
	        $labelids[]  = $row["labelid"];
	    }
	    array_unique($labelids);
	    $labels = array();
	    foreach($labelids as $labelid){
	        if($labelid){
	            $labels[] = getlabelgroupnum($labelid);
	        }
	    }
	    $categorys[$item[1]]["groupnum"] = $count;
	    $categorys[$item[1]]["subs"] = $labels;
		$categorys[$item[1]]["gid"]=$item[0];
	}
	
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($categorys), $expire);
	}	
	
}






if($_G["gp_gid"]){
	 $gid = $_G["gp_gid"];
    $query = DB::query("SELECT ff.*,fff.icon,fff.membernum,fff.dateline,fff.group_type FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff WHERE ff.fid=fff.fid AND ff.type='sub' AND fff.group_type=$gid");
    while($row=DB::fetch($query)){
        $row[dateline] = dgmdate($row[dateline]);
        $result = DB::fetch(DB::query("SELECT COUNT(1) as c FROM ".DB::table("forum_post")." fp WHERE fp.fid=".$row["fid"]));
        $row[topics] = $result["c"];
        $row[icon] = get_groupimg($row['icon'], 'icon');
    	if ($row["group_type"] >= 1 && $row["group_type"] < 20) {
	        $row['type_icn_id'] = 'brzone_s';
	    } elseif ($row["group_type"] >= 20 && $row["group_type"] < 60) {
	        $row['type_icn_id'] = 'bizone_s';
	    }
        $grouplistbytag[] = $row;
    }
    $grouplistbytag_count = count($grouplistbytag);
}
if($_G["gp_sgid"]){
    $sgid = $_G["gp_sgid"];
    $query = DB::query("SELECT ff.*,fff.icon,fff.membernum,fff.dateline,fff.jointype,fff.group_type FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff, "
            .DB::table("forum_labelgroup")." fl WHERE ff.fid=fff.fid AND ff.fid=fl.groupid AND fl.labelid=".$_G["gp_sgid"]);
    while($row=DB::fetch($query)){
        $row[dateline] = dgmdate($row[dateline]);
        $result = DB::fetch(DB::query("SELECT COUNT(1) as c FROM ".DB::table("forum_post")." fp WHERE fp.fid=".$row["fid"]));
        $row[topics] = $result["c"];
        $row[icon] = get_groupimg($row['icon'], 'icon');
    	if ($row["group_type"] >= 1 && $row["group_type"] < 20) {
	        $row['type_icn_id'] = 'brzone_s';
	    } elseif ($row["group_type"] >= 20 && $row["group_type"] < 60) {
	        $row['type_icn_id'] = 'bizone_s';
	    }
        $grouplistbytag[] = $row;
    }
    $grouplistbytag_count = count($grouplistbytag);
}


include template('group/index');
?>