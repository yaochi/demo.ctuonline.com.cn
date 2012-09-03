<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_home.php 10066 2010-05-06 09:30:28Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/feed');
require_once libfile('function/credit');

if(empty($_G['setting']['feedhotday'])) {
	$_G['setting']['feedhotday'] = 2;
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$_GET['view'] = empty($_GET['view'])?"we":$_GET['view'];
if(empty($_GET['view'])) {
	if($space['self']) {
		space_merge($space, 'count');
		if($_G['setting']['showallfriendnum'] && $space['friends'] < $_G['setting']['showallfriendnum']) {
			$_GET['view'] = 'all';
		} else {
			$_GET['view'] = 'we';
		}
	} else {
		$_GET['view'] = 'all';
	}
}
if(empty($_GET['order'])) {
	$_GET['order'] = 'dateline';
}

$perpage = $_G['setting']['feedmaxnum']<20?20:$_G['setting']['feedmaxnum'];
$perpage = mob_perpage($perpage);

if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
	$perpage = 50;
}

$page = intval($_GET['page']);
if($page < 1) $page = 1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$_G['home_today'] = $_G['timestamp'] - $_G['timestamp']%(3600*24);

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'home',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'appid' => $_GET['appid'],
	'icon' => $_GET['icon']
);
$theurl = 'home.php?'.url_implode($gets);
if($_GET["view"]=="notice"){ //专区动态

	require_once libfile('space/'.'notice_home', 'include');
	
		
}else{

$need_count = true;
$wheresql = array('1');

if($_GET['view'] == 'all') {//\全站的
	
	
	/*
	//过滤条件，显示公开专区和自己参加专区的
	// 我参与的专区活动ids
	$join_fids = join_groups($space['uid']);
	//开放的专区或活动（包括浏览权限为继承专区的）
	$open_fids = open_groups();
	//合并
	$fids = array_unique(array_merge($join_fids, $open_fids)); 

	
	

	if($fids){
		$wheresql['fid'] = "( fid=0  OR fid IN (".dimplode($fids).") )";
	}else{
		$wheresql['fid'] = "fid=0";
	}
	*/
	
	// 目前只找开放专区或者活动的，不查找（继承专区的活动）
	$wheresql['fid'] = "( fid=0  OR fid IN (select  f1.fid FROM ".DB::table("forum_forumfield")." as  f1 LEFT JOIN " . DB::table('forum_forum') . " as f ON f.fid=f1.fid , ".DB::table("forum_forumfield")." as f2 WHERE f.status=3 AND f1.gviewperm=1 ) )";
	
	if($_GET['order'] == 'dateline') {
		$ordersql = "dateline DESC";
		$f_index = '';
		$orderactives = array('dateline' => ' class="a"');
	} else {
		$wheresql['hot'] = "hot>='$minhot'";
		$ordersql = "dateline DESC";
		$f_index = '';
		$orderactives = array('hot' => ' class="a"');
	}

} elseif($_GET['view'] == 'me') {

	$wheresql['uid'] = "uid='$space[uid]'";
	$ordersql = "dateline DESC";
	$f_index = '';

	$diymode = 1;
	if($space['self'] && $_GET['from'] != 'space') $diymode = 0;
} elseif($_GET["view"]=="group"){ //专区动态
	
	/*
    $uids = feed_group($space['uid']);
    if($uids){
        $wheresql["uid"] = $uids;
    }*/
	
	// 我参与的专区活动ids
	$fids = join_groups($space['uid']);
	if($fids){
		$wheresql["fid"] = "fid IN (".dimplode($fids).")";
	}else{
		$wheresql["fid"] = "0";
	}
	
	
	
    $ordersql = "dateline DESC";
} else {
	
	//print_r('%%%%%%%%%%%%%%%%%%%%');

	space_merge($space, 'field_home');
	
	if($space['uid']!= getOfficialBlogUid()){ //添加官方博客
		if(empty($space['feedfriend'])){
			$space['feedfriend'] = getOfficialBlogUid();
		}else{
			
			$space['feedfriend'] = $space['feedfriend'].','.getOfficialBlogUid() ; 
		}	
	}
	
	if(empty($space['feedfriend'])) {
		$need_count = false;
	} else {
		
		//print_r($space['feedfriend']);
		$wheresql['uid'] = "uid IN ('0',$space[feedfriend])";
		$ordersql = "dateline DESC";
		$f_index = 'USE INDEX(dateline)';
	}
}

$appid = empty($_GET['appid'])?0:intval($_GET['appid']);
if($appid) {
	$wheresql['appid'] = "appid='$appid'";
}
$icon = empty($_GET['icon'])?'':trim($_GET['icon']);
if($icon) {
	$wheresql['icon'] = "icon='$icon'";
}
$gid = !isset($_GET['gid'])?'-1':intval($_GET['gid']);
if($gid>=0) {
	$fuids = array();
	$query = db::query("SELECT * FROM ".db::table('home_friend')." WHERE uid='$_G[uid]' AND gid='$gid' ORDER BY num DESC LIMIT 0,100");
	while ($value = db::fetch($query)) {
		$fuids[] = $value['fuid'];
	}
	if(empty($fuids)) {
		$need_count = false;
	} else {
		$wheresql['uid'] = "uid IN (".dimplode($fuids).")";
	}
}
$gidactives[$gid] = ' class="a"';

$feed_users = $feed_list = $user_list = $filter_list  = $list = $mlist = array();
$count = $filtercount = 0;
$multi = '';

if($need_count) {
	if($_GET['view'] != 'notice')
	$query = DB::query("SELECT * FROM ".DB::table('home_feed')." $f_index
		WHERE ".implode(' AND ', $wheresql)." 
		ORDER BY $ordersql
		LIMIT $start,$perpage");
	
	
	/*print_r("SELECT * FROM ".DB::table('home_feed')." $f_index
		WHERE ".implode(' AND ', $wheresql)." 
		ORDER BY $ordersql
		LIMIT $start,$perpage");*/
	
	$feed_ids_doing = $doing_ids = array();
	
	if($_GET['view'] == 'me') {
		while ($value = DB::fetch($query)) {
			if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
				$value = mkfeed($value);
				//add by qiaoyz,2011-3-24, EKSN-38 动态显示内容改造
				/*if($value[icon]=='doing'){
				$qu=DB::query("select replynum from ".DB::table(home_doing)." where doid =".$value['id']);
				$replynum = DB::fetch($qu);
				$value['replynum']=$replynum['replynum'];
				}*/
				if($value[icon]=='doing'){
					$doing_ids[] = $value['id'];
					$feed_ids_doing[$value['id']] =  $value['feedid'];
				}
				
				if($value['dateline']>=$_G['home_today']) {
					$list['today'][] = $value;
				} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
					$list['yesterday'][] = $value;
				} else {
					$theday = dgmdate($value['dateline'], 'Y-m-d');
					$list[$theday][] = $value;
				}
			}
			$count++;
		}
	} else {
		$hash_datas = array();
		$more_list = array();
		$uid_feedcount = array();

		while ($value = DB::fetch($query)) {
			if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
				$value = mkfeed($value);
				//add by qiaoyz,2011-3-24, EKSN-38 动态显示内容改造
				/*if($value[icon]=='doing'){
				$qu=DB::query("select replynum from ".DB::table(home_doing)." where doid =".$value['id']);
				$replynum = DB::fetch($qu);
				$value['replynum']=$replynum['replynum'];
				}
				*/
				if($value[icon]=='doing'){
					$doing_ids[] = $value['id'];
					$feed_ids_doing[$value['id']] =  $value['feedid'];
				}
				
				if(ckicon_uid($value)) {

					if($value['dateline']>=$_G['home_today']) {
						$dkey = 'today';
					} elseif ($value['dateline']>=$_G['home_today']-3600*24) {
						$dkey = 'yesterday';
					} else {
						$dkey = dgmdate($value['dateline'], 'Y-m-d');
					}

					$maxshownum = 3;
					if(empty($value['uid'])) $maxshownum = 10;

					if(empty($value['hash_data'])) {
						if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
						if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;

						$uid_feedcount[$dkey][$value['uid']]++;

						if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
							$more_list[$dkey][$value['uid']][] = $value;
						} else {
							$feed_list[$dkey][$value['uid']][] = $value;
						}

					} elseif(empty($hash_datas[$value['hash_data']])) {
						$hash_datas[$value['hash_data']] = 1;
						if(empty($feed_users[$dkey][$value['uid']])) $feed_users[$dkey][$value['uid']] = $value;
						if(empty($uid_feedcount[$dkey][$value['uid']])) $uid_feedcount[$dkey][$value['uid']] = 0;


						$uid_feedcount[$dkey][$value['uid']] ++;

						if($uid_feedcount[$dkey][$value['uid']]>$maxshownum) {
							$more_list[$dkey][$value['uid']][] = $value;
						} else {
							$feed_list[$dkey][$value['uid']][$value['hash_data']] = $value;
						}

					} else {
						$user_list[$value['hash_data']][] = "<a href=\"home.php?mod=space&uid=$value[uid]\">$value[username]</a>";
					}


				} else {
					$filtercount++;
					$filter_list[] = $value;
				}
			}
			$count++;
		}
	}
	
	$doing_replynum = array();
	//查询记录的评论数
	if($doing_ids && count($doing_ids)>0){
			$query=DB::query("select doid ,replynum from ".DB::table(home_doing)." where doid  IN (".dimplode($doing_ids).")");
			
			while ($value = DB::fetch($query)){

				$doing_replynum[$feed_ids_doing[$value['doid']]] = $value['replynum'];
			}
	}

	$multi = simplepage($count, $perpage, $page, $theurl);
}

$olfriendlist = $visitorlist = $task = $ols = $birthlist = $hotlist = $guidelist = array();



require_once libfile("function/home");
$value = home_user_add_blog();
if($value!=false){
    $olfriendlist[] = $value;
}
$oluids = array();
$groups = array();
$defaultusers = array();

//修改  by songsp 2011-3-25 16:37:36
//未使用
$commongroup2 = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 8);


//查询我加入的专区和 我管理的专区。不使用原有的
$mygrouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 50);


/*
//我加入的专区和我管理的专区

$sql = "SELECT fg.fid, fg.level  ,ff.name  ,ff.digest , fff.icon  , fff.group_type  FROM ".DB::table('forum_groupuser')." fg, ".DB::table('forum_forum')." ff  LEFT JOIN ".DB::table('forum_forumfield')." fff ON ff.fid=fff.fid   WHERE fg.fid=ff.fid AND ff.type='sub' AND fg.uid='1'  ORDER BY fff.lastupdate DESC LIMIT 0, 50";

$query = DB::query($sql);
while ($tmp_group = DB::fetch($query)) {
	isset($tmp_group['icon']) && $tmp_group['icon'] = get_groupimg($tmp_group['icon'], 'icon');
	if(strlen($tmp_group["name"])>30){
    	$tmp_group['name'] = cutstr($tmp_group["name"], 18);
	}
	if ($tmp_group["group_type"] >= 1 && $tmp_group["group_type"] < 20) {
        $tmp_group['type_icn_id'] = 'brzone_s';
    } elseif ($tmp_group["group_type"] >= 20 && $tmp_group["group_type"] < 60) {
        $tmp_group['type_icn_id'] = 'bizone_s';
    }
	$mygrouplist[$tmp_group['fid']] = $tmp_group;
}


*/

	if($mygrouplist) {
		$managegroup = $commongroup  = array();  //管理的专区   和 我加入的
		foreach($mygrouplist as $fid => $group) {
			if($group['level'] == 1 || $group['level'] == 2) {
				if(count($managegroup) == 8) {
					continue;
				}
				$managegroup[$fid]['name'] = $group['name'];
				$managegroup[$fid]['type_icn_id'] = $group['type_icn_id'];
				$managegroup[$fid]['digest'] = $group['digest'];
				$managegroup[$fid]['icon'] = $group['icon'];
			} else {
				if(count($commongroup) == 8) {
					continue;
				}
				$commongroup[$fid]['name'] = $group['name'];
				$commongroup[$fid]['icon'] = $group['icon'];
				$commongroup[$fid]['digest'] = $group['digest'];
				$commongroup[$fid]['type_icn_id'] = $group['type_icn_id'];
			}
		}				
	}	

	//print_r('========');
	
$feedid=$_G["gp_feedid"];
if($space['self'] && empty($start)) {
	space_merge($space, 'field_home');

	if($_GET['view'] == 'we') {
		require_once libfile('function/friend');
		//$groups = friend_group_list();
	}

	$isnewer = ($_G['timestamp']-$space['regdate'] > 3600*24*7) ?0:1;
	if($isnewer) {

		$friendlist = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]'");
		while ($value = DB::fetch($query)) {
			$friendlist[$value['fuid']] = 1;
		}
        require_once libfile("function/blog");
        $t = blog_offical_user($_G[uid]);
        $olfriendlist[] = $t;
        
		$query = DB::query("SELECT * FROM ".DB::table('home_specialuser')." WHERE status='1' ORDER BY displayorder");
		while ($value = DB::fetch($query)) {
			if(empty($friendlist[$value['uid']])) {
				$defaultusers[] = $value;
				$oluids[] = $value['uid'];
			}
		}
	}

	if($space['newprompt']) {
		space_merge($space, 'status');
	}

	
	//最近来访  
	$query = DB::query("SELECT * FROM ".DB::table('home_visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,12");
    
	
	
	$visitoruids = array();
	while ($value = DB::fetch($query)) {
		$visitorlist[$value['vuid']] = $value;
        $visitoruids[] = $value[vuid];
		$oluids[] = $value['vuid'];
	}

    //$visitoruserrealname =  user_get_user_realname($visitoruids);
    
	
	
	
	if($oluids || $space['feedfriend']){
		
		if(!$oluids) $oluids = array();
		
		$tmp_uids  = array_unique(array_merge($oluids, explode(":",$space['feedfriend']))); 
		
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($tmp_uids).")");
		
		while ($value = DB::fetch($query)) {
			
			$info_ols [] = $value;
		}
		
		
	}
	
	

	if($oluids) {
		//$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($oluids).")");
		//while ($value = DB::fetch($query)) {
		foreach($info_ols as $value){	
			if(!$value['invisible']) {
				$ols[$value['uid']] = 1;
			} elseif ($visitorlist[$value['uid']]) {
				unset($visitorlist[$value['uid']]);
			}
		}
	}

	
	
	
	
	$oluids = array();
	$olfcount = 0;
	if($space['feedfriend']) {
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN ($space[feedfriend]) ORDER BY lastactivity DESC LIMIT 32");
		while ($value = DB::fetch($query)) {
		//foreach($info_ols as $value){	
			if($olfcount < 16 && !$value['invisible']) {
				$olfriendlist[] = $value;
				$ols[$value['uid']] = 1;
				$oluids[$value['uid']] = $value['uid'];
				$olfcount++;
			}
		}
	}
	if($olfcount < 16) {
		$query = DB::query("SELECT fuid AS uid, fusername AS username, num FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' ORDER BY num DESC, dateline DESC LIMIT 0,32");
		while ($value = DB::fetch($query)) {
			if(empty($oluids[$value['uid']])) {
				$olfriendlist[] = $value;
				$olfcount++;
				if($olfcount == 16) break;
			}
		}
	}

	if($space['feedfriend']) {
		list($s_month, $s_day) = explode('-', dgmdate($_G['timestamp']-3600*24*3, 'n-j'));
		list($n_month, $n_day) = explode('-', dgmdate($_G['timestamp'], 'n-j'));
		list($e_month, $e_day) = explode('-', dgmdate($_G['timestamp']+3600*24*7, 'n-j'));
		if($e_month == $s_month) {
			$wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
		} else {
			$wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
		}

		$query = DB::query("SELECT sf.uid,sf.birthyear,sf.birthmonth,sf.birthday,s.username
			FROM ".DB::table('common_member_profile')." sf
			LEFT JOIN ".DB::table('common_member')." s ON s.uid=sf.uid
			WHERE (sf.uid IN ($space[feedfriend])) AND ($wheresql)");
		while ($value = DB::fetch($query)) {
			$value['istoday'] = 0;
			if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
				$value['istoday'] = 1;
			}
			$key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
			$birthlist[$key][] = $value;
			ksort($birthlist);
		}
	}

    
    foreach($olfriendlist as $friend){
        $olfriendlistuids[] = $friend[uid];
    }
    if(!$visitoruids) $visitoruids = array();
    $userrealnameids  = array_unique(array_merge($visitoruids, $olfriendlistuids)); 
    if($userrealnameids){
    	$visitoruserrealname = $olfriendlistreal =  user_get_user_realname($userrealnameids);
    }
    
	if($_G['setting']['feedhotnum'] > 0 && ($_GET['view'] == 'we' || $_GET['view'] == 'all')) {
		$hotlist_all = array();
		$hotstarttime = $_G['timestamp'] - $_G['setting']['feedhotday']*3600*24;
		$query = DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(hot) WHERE dateline>='$hotstarttime' ORDER BY hot DESC LIMIT 0,10");
		while ($value = DB::fetch($query)) {
			if($value['hot']>0 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
				if(empty($hotlist)) {
					$hotlist[$value['feedid']] = $value;
				} else {
					$hotlist_all[$value['feedid']] = $value;
				}
			}
		}
		$nexthotnum = $_G['setting']['feedhotnum'] - 1;
		if($nexthotnum > 0) {
			if(count($hotlist_all)> $nexthotnum) {
				$hotlist_key = array_rand($hotlist_all, $nexthotnum);
				if($nexthotnum == 1) {
					$hotlist[$hotlist_key] = $hotlist_all[$hotlist_key];
				} else {
					foreach ($hotlist_key as $key) {
						$hotlist[$key] = $hotlist_all[$key];
					}
				}
			} else {
				$hotlist = array_merge($hotlist, $hotlist_all);
			}
		}
	}
}

dsetcookie('home_readfeed', $_G['timestamp'], 365*24*3600);

$actives = array($_GET['view'] => ' class="a"');



//用户首页广告
$query = DB::query("SELECT * FROM ".DB::table("common_advertisement")." ad WHERE ad.available=1 AND ad.title LIKE 'NH%' order by displayorder desc");
while($row=DB::fetch($query)){
    $row[parameters] = unserialize($row[parameters]);
    $return[] = $row;
}

//print_r($_G[myself][common_member_field_home][$_G[uid]][viewindex]);
}
if(empty($cp_mode)) include_once template("home/space_home"); //include_once template("diy:home/space_home");

?>