<?php


/* Function: 根据检索参数，获取动态列表
 * Com.:
 * Author: caimm
 * Date: 2012-5-16
 */
require dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_doc.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_home.php';
$discuz = & discuz_core :: instance();

$discuz->init();
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_discuzcode.php';
$uid = $_G['gp_uid'];
$feedid = $_G["gp_feedid"];
$pagetype = $_G["gp_pagetype"];
$shownum = empty ($_G['gp_shownum']) ? 10 : $_G['gp_shownum'];
$type = empty ($_G['gp_type']) ? 'user' : $_G['gp_type'];
$typeid = $_G['gp_typeid'];
$idtype = $_G['gp_idtype'];
$startdatetime = $_G['gp_startdatetime'];
$enddatetime = $_G['gp_enddatetime'];
$preurl = $_G[config]['image']['url'];
$need_count = true;

if ($type == 'follow') {
	if ($typeid === '0' || $typeid) {
		if ($typeid == '-1') {
			$query = DB :: query("select fuid from " . DB :: TABLE("home_friend") . " where (type=1 or type=3) and uid=" . $uid . " and gids=''");
		}
		elseif ($typeid == '-2') {
			$query = DB :: query("select fuid from " . DB :: TABLE("home_friend") . " where type=3 and uid=" . $uid);
		} else {
			$query = DB :: query("select fuid from " . DB :: TABLE("home_friend") . " where (type=1 or type=3) and uid=" . $uid . " and gids like '%," . $typeid . ",%'");
		}
		while ($value = DB :: fetch($query)) {
			$fuids[] = $value[fuid];
		}
		if (count($fuids)) {
			$wheresql['uid'] = "uid IN ('-1'," . implode(',', $fuids) . ")";
		} else {
			$need_count = false;
		}
	} else {
		$result[uid] = $uid;
		space_merge($result, 'field_home');
		if ($result[feedfollow]) {
			$wheresql['uid'] = "uid IN ($uid,$result[feedfollow])";
		} else {
			$wheresql['uid'] = "uid IN ($uid)";
		}
	}
	$wheresql['anonymity'] = " anonymity = 0 ";
	$wheresql['fid'] = "( fid=0  OR fid IN (select  f1.fid FROM " . DB :: table("forum_forumfield") . " as  f1 LEFT JOIN " . DB :: table('forum_forum') . " as f ON f.fid=f1.fid , " . DB :: table("forum_forumfield") . " as f2 WHERE f.status=3 AND f1.gviewperm=1 ) )";
}
elseif ($type == 'group') {
	if ($typeid) {
		$wheresql["fid"] = "( fid IN (" . $typeid . ") or sharetofids like '%," . $typeid . ",%' )";
	} else {
		$fids = join_groups($uid);
		if ($fids) {
			$wheresql["fid"] = "( fid IN (" . dimplode($fids) . ")";
			for ($i = 0; $i < count($fids); $i++) {
				$wheresql["fid"] = $wheresql["fid"] . " or sharetofids like '%," . $fids[$i] . ",%'";
			}
			$wheresql["fid"] = $wheresql["fid"] . " )";
		} else {
			$need_count = false;
		}
	}
}
elseif ($type == 'user') {
	$wheresql['uid'] = "uid =" . $uid;
	$wheresql['anonymity'] = "anonymity = 0";
}
elseif ($type == 'all') {
	$wheresql['all'] = ' 1=1 ';
	$wheresql['fid'] = "( fid=0  OR fid IN (select  f1.fid FROM " . DB :: table("forum_forumfield") . " as  f1 LEFT JOIN " . DB :: table('forum_forum') . " as f ON f.fid=f1.fid , " . DB :: table("forum_forumfield") . " as f2 WHERE f.status=3 AND f1.gviewperm=1 ) )";
}

if ($feedid) {
	if ($pagetype == "up") {
		$wheresql['feedid'] = " feedid<" . $feedid;
	}
	elseif ($pagetype == "down") {
		$wheresql['feedid'] = " feedid>" . $feedid;
	}
} else {
	$pagetype = "up";
}
if ($idtype) {
	$wheresql['idtype'] = "idtype in ('" . implode('\',\'', $idtype) . "')";
}
if ($startdatetime) {
	$wheresql['startdatetime'] = "dateline >='" . $startdatetime . "'";
}
if ($enddatetime) {
	$wheresql['enddatetime'] = "dateline<'" . $enddatetime . "'";
}
if (!$wheresql) {
	$wheresql['none'] = ' 1=0 ';
}
if ($need_count) {
	$ordersql = updownsql($pagetype, 'home_feed', 'feedid', $feedid, $shownum, 'feedid', implode(' AND ', $wheresql) . " and ( icon!='comment' and idtype!='gpicid' and idtype!='galbumid' and idtype!='picid' and icon!='profile' and icon!='sitefeed' and icon!='click' and idtype!='class' and idtype!='case' and idtype!='doc' and !(icon='share' and idtype=''))");
	$res['refresh'] = $ordersql['refresh'];
	$query = DB :: query("SELECT * FROM " . DB :: table('home_feed') . " WHERE " . implode(' AND ', $wheresql) . " and ( icon!='comment' and idtype!='gpicid' and idtype!='galbumid' and idtype!='picid' and uid!='0' and icon!='profile' and icon!='sitefeed' and icon!='click' and idtype!='class' and idtype!='case' and idtype!='doc' and !(icon='share' and idtype=''))" . $ordersql['ordersql']);
	while ($value = DB :: fetch($query)) {
		if ($value[icon] == 'thread' || $value[icon] == 'poll' || $value[icon] == 'reward') {
			$commentquery = DB :: query("select *,anonymous as anonimity from " . DB :: TABLE("forum_post") . " where tid =" . $value[id] . " and first!='1' and authorid!=0 order by dateline desc limit 0,2");
		}
		elseif ($value[icon] == 'resourcelist') {
			$commentquery = DB :: query("select * from " . DB :: TABLE("home_comment") . " where idtype ='docid' and id=" . $value[id] . " and authorid!=0 order by dateline desc limit 0,2");
		} else {
			$commentquery = DB :: query("select * from " . DB :: TABLE("home_comment") . " where idtype ='feed' and id=" . $value[feedid] . " and authorid!=0 order by dateline desc limit 0,2");
		}
		while ($commentvalue = DB :: fetch($commentquery)) {
			$cvalue = array ();
			$newcommentvalue[cid] = empty ($commentvalue[pid]) ? $commentvalue[cid] : $commentvalue[pid];
			$newcommentvalue[id] = empty ($commentvalue[tid]) ? $commentvalue[id] : $commentvalue[tid];
			$newcommentvalue[dateline] = $commentvalue[dateline];
			$newcommentvalue[ip] = empty ($commentvalue[useip]) ? $commentvalue[ip] : $commentvalue[useip];
			if (strripos($commentvalue[message], '</blockquote>')) {
				$newcommentvalue[message] = substr($commentvalue[message], strripos($commentvalue[message], '</blockquote>') + 29);
			}
			elseif (strripos($commentvalue[message], '[/i]')) {
				$newcommentvalue[message] = substr($commentvalue[message], strripos($commentvalue[message], '[/i]') + 16);
			} else {
				$newcommentvalue[message] = $commentvalue[message];
			}
			if (strripos($newcommentvalue[message], '<br />')) {
				$newcommentvalue[message] = substr($newcommentvalue[message], 0, strripos($newcommentvalue[message], '<br />'));
			}
			if ($commentvalue[anonymity]) {
				if ($commentvalue[anonymity] == -1) {
					$cvalue[user][uid] = -1;
					$cvalue[user][username] = '匿名';
					$cvalue[user][iconImg] = useravatar(-1);
				} else {
					include_once libfile('function/repeats', 'plugin/repeats');
					$repeatsinfo = getforuminfo($commentvalue[anonymity]);
					if ($repeatsinfo['icon']) {
						$newcommentvalue['ficon'] = $_G[config]['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
					} else {
						$newcommentvalue['ficon'] = $_G[config]['image']['url'] . '/static/image/images/def_group.png';
					}
					$cvalue[user][uid] = $repeatsinfo[fid];
					$cvalue[user][username] = $repeatsinfo[name];
					$cvalue[user][iconImg] = $newcommentvalue['ficon'];
				}
			} else {
				$cvalue[user][uid] = $commentvalue[authorid];
				$cvalue[user][username] = user_get_user_name($cvalue[user][uid]);
				$cvalue[user][iconImg] = useravatar($cvalue[user][uid]);
			}
			$cvalue[anonymity] = $commentvalue[anonymity];
			$cvalue[commentDate] = $commentvalue[dateline];
			$cvalue[commentContent] =$newcommentvalue[message];
			$cvalue[cid] = $newcommentvalue[cid];
			$value[comments][] = $cvalue;
		}

		if ($value[image_1]) {
			$pic[pictureUrl] = packImg($value[image_1], $preurl);
			$value[pics][] = $pic;
		}
		if ($value[image_2]) {
			$pic[pictureUrl] = packImg($value[image_2], $preurl);
			$value[pics][] = $pic;
		}
		if ($value[image_3]) {
			$pic[pictureUrl] = packImg($value[image_3], $preurl);
			$value[pics][] = $pic;
		}
		if ($value[image_4]) {
			$pic[pictureUrl] = packImg($value[image_4], $preurl);
			$value[pics][] = $pic;
		}
		if ($value[image_5]) {
			$pic[pictureUrl] = packImg($value[image_5], $preurl);
			$value[pics][] = $pic;
		}
		if ($value['idtype'] == 'feed') {
			$oldfeedidarr[$value[feedid]] = $value[id];
			$value[isOriginal] = 2;
		} else {
			$value[isOriginal] = 1;
		}
		if ($value[anonymity]) {
			if ($value[anonymity] == -1) {
				$value[user][uid] = -1;
				$value[user][username] = '匿名';
				$value[user][iconImg] = useravatar(-1);
			} else {
				include_once libfile('function/repeats', 'plugin/repeats');
				$repeatsinfo = getforuminfo($value[anonymity]);
				$value[user][uid] = $repeatsinfo[fid];
				$value[user][username] = $repeatsinfo[name];
				if ($repeatsinfo['icon']) {
					$value['ficon'] = $_G[config]['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
				} else {
					$value['ficon'] = $_G[config]['image']['url'] . '/static/image/images/def_group.png';
				}
				$value[user][iconImg] = $value['ficon'];
			}
		} else {
			$value[user][uid] = $value[uid];
			$value[user][iconImg] = useravatar($value[uid]);
			$value[user][username] = user_get_user_name($value[uid]);
		}
		$feedidarr[] = $value[feedid];
		$value = mkfeed($value);
		if ($value[dateline]) {
			$value[createDate] = $value[dateline];
		} else {
			$value[createDate] = '';
		}
		$value[forwardCount] = $value[sharetimes];
		$value[commentCount] = $value[commenttimes];
		$value[fromwhere] = $value[fromwhere];
		if ($value[idtype] == "feed") {
			$value[content] = $value[body_general];
		}
		//按类型封装
		//记录ok
		if ($value[icon] == "blog" && $value[idtype] != "feed") {
			$value[title] = $value[body_data][subject];
			$value[content] = $value[body_data][summary];
		}
		if ($value[icon] == "doing" && $value[idtype] != "feed") {
			$value[title] = null;
			$value[content] = $value[title_data][message];
		}

		//图片ok
		if ($value[icon] == "album" && $value[idtype] == "albumid") {
			$value[content] = $value[body_general];
			$value[pics] = null;
			if ($value[target_ids]) {
				$v = DB :: query("select title,filepath from pre_home_pic where picid in(" . $value[target_ids] . ") order by dateline asc");
				$ps = null;
				while ($p = DB :: fetch($v)) {
					$pic[pictureUrl] = $preurl . "/" . $p[filepath];
					$ps[] = $pic;
				}
				$value[pics] = $ps;
			}
			elseif ($value[id]) {
				$v = DB :: query("select title,filepath from pre_home_pic where albumid=" . $value[id] . " order by dateline desc limit 0," . $value[body_data][picnum]);
				$ps = null;
				while ($p = DB :: fetch($v)) {
					$pic[pictureUrl] = $preurl . "/data/attachment/album/" . $p[filepath];
					$ps[] = $pic;
				}
				$ps = array_reverse($ps);
				$value[pics] = $ps;
			}
		}
		if ($value[icon] == "album" && $value[idtype] == "gpicid") {
			$pics = null;
			$value[pics] = null;
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$value[pics] = $pics;
			$value[content] = $value[body_data][title];
		}
		if ($value[icon] == "album" && $value[idtype] == "picid") {
			$pics = null;
			$value[pics] = null;
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$value[pics] = $pics;
			$value[content] = $value[body_data][title];
		}
		if ($value[icon] == "share" && $value[idtype] == "pic") {
			$value[content] = $value[body_general];
		}
		//链接ok
		if ($value[icon] == "share" && $value[idtype] == "link") {
			$value[content] = $value[body_general];
		}
		//文档ok
		if ($value[icon] == "doc" && $value[idtype] != "feed") {
			$docarr = getFile($value[id]);
			$value[title] = $docarr[title];
			$value[content] = $docarr[context];
		}
		//视频ok
		if ($value[idtype] == "video" || $value[idtype] == "flash") {
			$value[content] = $value[body_general];
		}
		//音频ok
		if ($value[icon] == "share" && $value[idtype] == "music") {
			$value[content] = $value[body_general];
		}
		//课程ok
		if ($value[icon] == "class" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = $value[body_data][message];
		}
		//案例ok
		if ($value[icon] == "case" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = $value[body_data][message];
		}
		//话题ok
		if ($value[icon] == "thread" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = $value[body_data][message];
		}
		//通知公告ok
		if ($value[icon] == "notice" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[title_data][noticetitle]);
			$value[content] = $value[body_data][message];
		}
		//活动ok
		if ($value[icon] == "activitys" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = $value[body_data][message];
		}
		//直播ok
		if ($value[icon] == "live" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = "";
		}
		//评选ok
		if ($value[icon] == "selection" && $value[idtype] != "feed") {
			$a = explode("&selectionid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select selectionname,selectiondescr from pre_selection where selectionid=" . $id);
			$value[title] = $v[selectionname];
			$value[content] = $v[selectiondescr];
		}
		//提问ok
		if ($value[icon] == "reward" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$value[content] = $v;
		}
		//资源列表ok
		if ($value[icon] == "resourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,about from pre_resourcelist where resourceid=" . $id);
			$value[title] = $v[title];
			$value[content] = $v[about];
		}
		//资源列表ok
		if ($value[icon] == "shresourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,about from pre_shresourcelist where resourceid=" . $id);
			$value[title] = $v[title];
			$value[content] = $v[about];
		}
		//外部资源
		if ($value[icon] == "extraresource" && $value[idtype] != "feed") {
			$a = explode("&id=", $value[title_template]);
			$b = explode("\"", $a[1]);
			$id = $b[0];
			if ($value[title_data]["class"]) {
				$v = DB :: fetch_first("select name,descr from pre_extra_class where id=" . $id);
			}
			elseif ($value[title_data]["lecture"]) {
				$v = DB :: fetch_first("select name,descr from pre_extra_lecture where id=" . $id);
			}
			elseif ($value[title_data]["org"]) {
				$v = DB :: fetch_first("select name,descr from pre_extra_org where id=" . $id);
			}
			$value[title] = $v[name];
			$value[content] = $v[descr];
		}
		//问卷ok
		if ($value[icon] == "questionary" && $value[idtype] != "feed") {
			$a = explode("&questid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select questname,questdescr from pre_questionary where questid=" . $id);
			$value[title] = $v[questname];
			$value[content] = $v[questdescr];
		}
		//投票ok
		if ($value[icon] == "poll" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$value[content] = $v;
			if ($v == "" || $v == null) {
				$value[content] = $value[body_data][message];
			}
		}
		//你我课堂ok
		if ($value[icon] == "nwkt" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[body_data][subject]);
			$value[content] = $value[body_data][summary];
		}
		//专区转发ok
		if ($value[icon] == "forward" && $value[idtype] != "feed") {
			$value[content] = $value[body_general];
			$value[isOriginal] = 2;
			$value[originalFeed][title] = strip_tags($value[body_data][subject]);
			$value[originalFeed][content] = $value[body_data][message];
			$value[originalFeed][content] = str_replace("&nbsp;", "", $value[originalFeed][content]);
			$value[originalFeed][content] = str_replace(" ", "", $value[originalFeed][content]);
			$value[originalFeed][feedid] = 0;
			$value[originalFeed][icon] = $value[icon];
			$value[originalFeed][idtype] = $value[idtype];
			$value[originalFeed][user] = null;
			$value[originalFeed][createDate] = "";
			$value[originalFeed][commentCount] = "";
			$value[originalFeed][forwardCount] = "";
			$value[originalFeed][fromwhere] = "";
			$value[originalFeed][anonymity] = $value[anonymity];
		}
		$value[content] = discuzcode($value[content], -1, 0, 1, 1, 1, 1, 1);
		$value[content] = cutstr($value[content], 400);
		$value[content] = str_replace("&nbsp;", "", $value[content]);
		$value[content] = preg_replace("/\s(?=\s)/", "", $value[content]);
		$value[content] = trim($value[content]);
		$feedarray[$value[feedid]] = $value;

	}

	if ($feedidarr && $uid) {
		$query = DB :: query("select feedid from " . DB :: TABLE("home_favorite") . " where uid=" . $uid . " and feedid in (" . implode(',', $feedidarr) . ")");
		while ($value = DB :: fetch($query)) {
			$feedarray[$value[feedid]][isfavorite] = "1";
		}
	}
	if ($oldfeedidarr) {
		$query = DB :: query("select * from " . DB :: TABLE("home_feed") . " where feedid in (" . implode(',', $oldfeedidarr) . ")");
		while ($value = DB :: fetch($query)) {
			$value = mkfeed($value);
			while (in_array($value[feedid], $oldfeedidarr)) {
				$newid = array_search($value[feedid], $oldfeedidarr);
				$oldfeedidarr2[$newid] = $value[feedid];
				$oldfeedidarr = array_diff_assoc($oldfeedidarr, $oldfeedidarr2);

				if ($value[anonymity]) {
					if ($value[anonymity] == -1) {
						$feedarray[$newid][originalFeed][user][uid] = -1;
						$feedarray[$newid][originalFeed][user][username] = '匿名';
						$feedarray[$newid][originalFeed][user][iconImg] = useravatar(-1);
					} else {
						include_once libfile('function/repeats', 'plugin/repeats');
						$repeatsinfo = getforuminfo($value[anonymity]);
						$feedarray[$newid][originalFeed][user][uid] = $repeatsinfo[fid];
						$feedarray[$newid][originalFeed][user][username] = $repeatsinfo[name];
						if ($repeatsinfo['icon']) {
							$value['ficon'] = $_G[config]['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
						} else {
							$value['ficon'] = $_G[config]['image']['url'] . '/static/image/images/def_group.png';
						}
						$feedarray[$newid][originalFeed][user][iconImg] = $value['ficon'];
					}
				} else {
					$feedarray[$newid][originalFeed][user][uid] = $value[uid];
					$feedarray[$newid][originalFeed][user][iconImg] = useravatar($value[uid]);
					$feedarray[$newid][originalFeed][user][username] = user_get_user_name($value[uid]);
				}
				$feedarray[$newid][originalFeed][feedid] = $value[feedid];
				$feedarray[$newid][originalFeed][icon] = $value[icon];
				$feedarray[$newid][originalFeed][idtype] = $value[idtype];
				$feedarray[$newid][originalFeed][content] = $value[body_data][summary];
				if ($value[dateline]) {
					$feedarray[$newid][originalFeed][createDate] = $value[dateline];
				} else {
					$feedarray[$newid][originalFeed][createDate] = '';
				}
				$feedarray[$newid][originalFeed][forwardCount] = $value[sharetimes];
				$feedarray[$newid][originalFeed][commentCount] = $value[commenttimes];
				$feedarray[$newid][originalFeed][fromwhere] = $value[fromwhere];
				$feedarray[$newid][originalFeed][anonymity] = $value[anonymity];
				if ($value[image_1]) {
					$pic[pictureUrl] = packImg($value[image_1], $preurl);
					$feedarray[$newid][originalFeed][pics][] = $pic;
				}
				if ($value[image_2]) {
					$pic[pictureUrl] = packImg($value[image_2], $preurl);
					$feedarray[$newid][originalFeed][pics][] = $pic;
				}
				if ($value[image_3]) {
					$pic[pictureUrl] = packImg($value[image_3], $preurl);
					$feedarray[$newid][originalFeed][pics][] = $pic;
				}
				if ($value[image_4]) {
					$pic[pictureUrl] = packImg($value[image_4], $preurl);
					$feedarray[$newid][originalFeed][pics][] = $pic;
				}
				if ($value[image_5]) {
					$pic[pictureUrl] = packImg($value[image_5], $preurl);
					$feedarray[$newid][originalFeed][pics][] = $pic;
				}
				//按类型封装
				//记录ok
				if ($value[icon] == "blog" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = $value[body_data][subject];
					$feedarray[$newid][originalFeed][content] = $value[body_data][summary];
				}
				if ($value[icon] == "doing" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = null;
					$feedarray[$newid][originalFeed][content] = $value[title_data][message];
				}

				//图片ok
				if ($value[icon] == "album" && $value[idtype] == "albumid") {
					$feedarray[$newid][originalFeed][content] = $value[body_general];
					$feedarray[$newid][originalFeed][pics] = null;
					if ($value[target_ids]) {
						$v = DB :: query("select title,filepath from pre_home_pic where picid in(" . $value[target_ids] . ") order by dateline asc");
						$ps = null;
						while ($p = DB :: fetch($v)) {
							$pic[pictureUrl] = $preurl . "/" . $p[filepath];
							$ps[] = $pic;
						}
						$feedarray[$newid][originalFeed][pics] = $ps;
					}
					elseif ($value[id]) {
						$v = DB :: query("select title,filepath from pre_home_pic where albumid=" . $value[id] . " order by dateline desc limit 0," . $value[body_data][picnum]);
						$ps = null;
						while ($p = DB :: fetch($v)) {
							$pic[pictureUrl] = $preurl . "/data/attachment/album/" . $p[filepath];
							$ps[] = $pic;
						}
						$ps = array_reverse($ps);
						$feedarray[$newid][originalFeed][pics] = $ps;
					}

				}
				if ($value[icon] == "album" && $value[idtype] == "gpicid") {
					$pics = null;
					$feedarray[$newid][originalFeed][pics] = null;
					$_p = explode(".thumb", $value[image_1]);
					$pic[pictureUrl] = $preurl . "/" . $_p[0];
					$pics[] = $pic;
					$feedarray[$newid][originalFeed][pics] = $pics;
					$feedarray[$newid][originalFeed][content] = $value[body_data][title];
				}
				if ($value[icon] == "album" && $value[idtype] == "picid") {
					$pics = null;
					$feedarray[$newid][originalFeed][pics] = null;
					$_p = explode(".thumb", $value[image_1]);
					$pic[pictureUrl] = $preurl . "/" . $_p[0];
					$pics[] = $pic;
					$feedarray[$newid][originalFeed][pics] = $pics;
					$feedarray[$newid][originalFeed][content] = $value[body_data][title];
				}
				if ($value[icon] == "share" && $value[idtype] == "pic") {
					$feedarray[$newid][originalFeed][content] = $value[body_general];
				}
				//链接ok
				if ($value[icon] == "share" && $value[idtype] == "link") {
					$feedarray[$newid][originalFeed][content] = $value[body_general];
				}
				//文档ok
				if ($value[icon] == "doc" && $value[idtype] != "feed") {
					$docarr = getFile($value[id]);
					$feedarray[$newid][originalFeed][title] = $docarr[title];
					$feedarray[$newid][originalFeed][content] = $docarr[context];
				}
				//视频ok
				if ($value[idtype] == "video" || $value[idtype] == "flash") {
					$feedarray[$newid][originalFeed][content] = $value[body_general];
				}
				//音频ok
				if ($value[icon] == "share" && $value[idtype] == "music") {
					$feedarray[$newid][originalFeed][content] = $value[body_general];
				}
				//课程ok
				if ($value[icon] == "class" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];
				}
				//案例ok
				if ($value[icon] == "case" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];
				}
				//话题ok
				if ($value[icon] == "thread" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];
				}
				//通知公告ok
				if ($value[icon] == "notice" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[title_data][noticetitle]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];
				}
				//活动ok
				if ($value[icon] == "activitys" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];
				}
				//直播ok
				if ($value[icon] == "live" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = "";
				}
				//评选ok
				if ($value[icon] == "selection" && $value[idtype] != "feed") {
					$a = explode("&selectionid=", $value[title_data][questname]);
					$b = explode("&", $a[1]);
					$id = $b[0];
					$v = DB :: fetch_first("select selectionname,selectiondescr from pre_selection where selectionid=" . $id);
					$feedarray[$newid][originalFeed][title] = $v[selectionname];
					$feedarray[$newid][originalFeed][content] = $v[selectiondescr];
				}
				//提问ok
				if ($value[icon] == "reward" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
					$feedarray[$newid][originalFeed][content] = $v;
				}
				//资源列表ok
				if ($value[icon] == "resourcelist" && $value[idtype] != "feed") {
					$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
					$b = explode(".htm", $a[1]);
					$id = $b[0];
					$v = DB :: fetch_first("select title,titlelink,about from pre_resourcelist where resourceid=" . $id);
					$feedarray[$newid][originalFeed][title] = $v[title];
					$feedarray[$newid][originalFeed][content] = $v[about];
				}
				//资源列表ok
				if ($value[icon] == "shresourcelist" && $value[idtype] != "feed") {
					$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
					$b = explode(".htm", $a[1]);
					$id = $b[0];
					$v = DB :: fetch_first("select title,titlelink,about from pre_shresourcelist where resourceid=" . $id);
					$feedarray[$newid][originalFeed][title] = $v[title];
					$feedarray[$newid][originalFeed][content] = $v[about];
				}
				//外部资源
				if ($value[icon] == "extraresource" && $value[idtype] != "feed") {
					$a = explode("&id=", $value[title_template]);
					$b = explode("\"", $a[1]);
					$id = $b[0];
					if ($value[title_data]["class"]) {
						$v = DB :: fetch_first("select name,descr from pre_extra_class where id=" . $id);
					}
					elseif ($value[title_data]["lecture"]) {
						$v = DB :: fetch_first("select name,descr from pre_extra_lecture where id=" . $id);
					}
					elseif ($value[title_data]["org"]) {
						$v = DB :: fetch_first("select name,descr from pre_extra_org where id=" . $id);
					}
					$feedarray[$newid][originalFeed][title] = $v[name];
					$feedarray[$newid][originalFeed][content] = $v[descr];
				}
				//问卷ok
				if ($value[icon] == "questionary" && $value[idtype] != "feed") {
					$a = explode("&questid=", $value[title_data][questname]);
					$b = explode("&", $a[1]);
					$id = $b[0];
					$v = DB :: fetch_first("select questname,questdescr from pre_questionary where questid=" . $id);
					$feedarray[$newid][originalFeed][title] = $v[questname];
					$feedarray[$newid][originalFeed][content] = $v[questdescr];
				}
				//投票ok
				if ($value[icon] == "poll" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
					$feedarray[$newid][originalFeed][content] = $v;
					if ($v == "" || $v == null) {
						$feedarray[$newid][originalFeed][content] = $value[body_data][message];
					}
				}
				//你我课堂ok
				if ($value[icon] == "nwkt" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][summary];
				}
				//专区转发ok
				if ($value[icon] == "forward" && $value[idtype] != "feed") {
					$feedarray[$newid][originalFeed][user] = null;
					$feedarray[$newid][originalFeed][title] = strip_tags($value[body_data][subject]);
					$feedarray[$newid][originalFeed][content] = $value[body_data][message];

				}
				$feedarray[$newid][originalFeed][content] = discuzcode($feedarray[$newid][originalFeed][content], -1, 0, 1, 1, 1, 1, 1);
				$feedarray[$newid][originalFeed][content] = cutstr($feedarray[$newid][originalFeed][content], 400);
				$feedarray[$newid][originalFeed][content] = str_replace("&nbsp;", "", $feedarray[$newid][originalFeed][content]);
				$feedarray[$newid][originalFeed][content] = preg_replace("/\s(?=\s)/", "", $feedarray[$newid][originalFeed][content]);
				$feedarray[$newid][originalFeed][content] = trim($feedarray[$newid][originalFeed][content]);

			}
		}
	}
	for ($i = 0; $i < count($feedarray); $i++) {
		$newfeedarray[] = $feedarray[$feedidarr[$i]];
	}
	$res[feeds] = $newfeedarray;
} else {
	$res[feeds] = null;
}

echo json_encode($res);
function packImg($url, $preurl) {
	if (strrpos($url, 'http://') === 0 || strrpos($url, 'http://')) {
	} else {
		$url = $preurl . '/' . $url;
	}
	return $url;
}
function packUser($uid, $anonymity, $preurl) {
	$user = Array ();
	if ($anonymity) {
		if ($anonymity == -1) {
			$user[uid] = -1;
			$user[username] = '匿名';
			$user[iconImg] = useravatar(-1);
		} else {
			include_once libfile('function/repeats', 'plugin/repeats');
			$repeatsinfo = getforuminfo($anonymity);
			$user[uid] = $repeatsinfo[fid];
			$user[username] = $repeatsinfo[name];
			if ($repeatsinfo['icon']) {
				$ficon = $preurl . '/data/attachment/group/' . $repeatsinfo['icon'];
			} else {
				$ficon = $preurl . '/static/image/images/def_group.png';
			}
			$user[iconImg] = $ficon;
		}
	} else {
		$user[uid] = $uid;
		$user[iconImg] = useravatar($uid);
		$user[username] = user_get_user_name($uid);
	}
	return $user;
}
?>
