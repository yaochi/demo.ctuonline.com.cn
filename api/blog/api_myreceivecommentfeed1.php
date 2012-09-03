<?php


/* Function: 我收到的评论(移动)
 * Com.:
 * Author: caimm
 * Date: 2012-4-5
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
$cid = $_G['gp_cid'];
$pagetype = $_G['gp_pagetype'];
$shownum = empty ($_G['gp_shownum']) ? 10 : $_G['gp_shownum'];
$preurl = $_G[config]['image']['url'];
if ($uid) {
	if ($cid) {
		if ($pagetype == 'down') {
			$sql = " and c.cid > " . $cid;
		} else
			if ($pagetype == 'up') {
				$sql = " and c.cid < " . $cid;
			}
	} else {
		$pagetype = 'up';
	}
	if ($pagetype == 'down') {
		$count = DB :: result_first("select count(*) from " . DB :: table('home_feed') . " f," . DB :: table("home_comment") . " c where c.idtype='feed' and c.id=f.feedid and f.idtype!='flash' and f.idtype!='music' and f.idtype!='video' and f.icon!='profile'  and f.icon!='click' and f.title_template!='{actor} 转发了一个视频' and f.title_template!='{actor} 转发了一个音乐' and c.uid!=c.authorid and c.authorid!=0 and c.uid=" . $uid . $sql);
		if ($count > $shownum) {
			$res[refresh] = '1';
		} else {
			$res[refresh] = '0';
		}
	} else
		if ($pagetype == 'up') {
			$res[refresh] = '0';
		}
	$query = DB :: query("select f.*,c.*,f.idtype as idtype,f.dateline as fdate,c.dateline as cdate,c.anonymity as canonymity,f.anonymity as fanonymity from " . DB :: table('home_feed') . " f," . DB :: table("home_comment") . " c where c.idtype='feed' and c.id=f.feedid and f.idtype!='flash' and f.idtype!='music' and f.idtype!='video' and f.icon!='profile'  and f.icon!='click' and f.title_template!='{actor} 转发了一个视频' and f.title_template!='{actor} 转发了一个音乐' and c.uid!=c.authorid and c.authorid!=0 and c.uid=" . $uid . $sql . " ORDER BY c.dateline desc limit 0," . $shownum);
	while ($value = DB :: fetch($query)) {
		$value = mkfeed($value);
		$value[author] = user_get_user_name($value[authorid]);
		if ($value[fanonymity]) {
			if ($value[fanonymity] == -1) {
				$fvalue[user][uid] = -1;
				$fvalue[user][username] = '匿名';
				$fvalue[user][iconImg] = useravatar(-1);
			} else {
				include_once libfile('function/repeats', 'plugin/repeats');
				$repeatsinfo = getforuminfo($value[fanonymity]);
				$fvalue[user][uid] = $repeatsinfo[fid];
				$fvalue[user][username] = $repeatsinfo[name];
				if ($repeatsinfo['icon']) {
					$value['ficon'] = $_G[config]['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
				} else {
					$value['ficon'] = $_G[config]['image']['url'] . '/static/image/images/def_group.png';
				}
				$fvalue[user][iconImg] = $value['ficon'];
			}
		} else {
			$fvalue[user][uid] = $value[uid];
			$fvalue[user][username] = user_get_user_name($value[uid]);
			$fvalue[user][iconImg] = useravatar($value[uid]);
		}
		if ($value[image_1]) {
			if (strrpos($value[image_1], 'http://') === 0 || strrpos($value[image_1], 'http://')) {
			} else {
				$value[image_1] = $_G[config][image][url] . '/' . $value[image_1];
			}
			$pic[pictureUrl] = $value[image_1];
			$fvalue[pics][] = $pic;
		}
		if ($value[image_2]) {
			if (strrpos($value[image_2], 'http://') === 0 || strrpos($value[image_2], 'http://')) {
			} else {
				$value[image_2] = $_G[config][image][url] . '/' . $value[image_2];
			}
			$pic[pictureUrl] = $value[image_2];
			$fvalue[pics][] = $pic;
		}
		if ($value[image_3]) {
			if (strrpos($value[image_3], 'http://') === 0 || strrpos($value[image_3], 'http://')) {
			} else {
				$value[image_3] = $_G[config][image][url] . '/' . $value[image_3];
			}
			$pic[pictureUrl] = $value[image_3];
			$fvalue[pics][] = $pic;
		}
		if ($value[image_4]) {
			if (strrpos($value[image_4], 'http://') === 0 || strrpos($value[image_4], 'http://')) {
			} else {
				$value[image_4] = $_G[config][image][url] . '/' . $value[image_4];
			}
			$pic[pictureUrl] = $value[image_4];
			$fvalue[pics][] = $pic;
		}
		if ($value[image_5]) {
			if (strrpos($value[image_5], 'http://') === 0 || strrpos($value[image_5], 'http://')) {
			} else {
				$value[image_5] = $_G[config][image][url] . '/' . $value[image_5];
			}
			$pic[pictureUrl] = $value[image_5];
			$fvalue[pics][] = $pic;
		}
		//封装动态

		if ($value['idtype'] == 'feed') {
			$fvalue[content] = $value[body_general];
		}
		//按类型封装
		//记录ok
		if ($value[icon] == "blog" && $value[idtype] != "feed") {
			$fvalue[title] = $value[body_data][subject];
			$fvalue[content] = $value[body_data][summary];
		}
		if ($value[icon] == "doing" && $value[idtype] != "feed") {
			$fvalue[title] = null;
			$fvalue[content] = $value[title_data][message];
		}

		//图片ok
		if ($value[icon] == "album" && $value[idtype] == "albumid") {
			$fvalue[content] = $value[body_general];
			if ($value[target_ids] || $value[id]) {
			} else {
				$fvalue[pics] = null;
			}
		}
		if ($value[icon] == "album" && $value[idtype] == "gpicid") {
			$pics = null;
			$fvalue[pics] = null;
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$fvalue[pics] = $pics;
			$fvalue[content] = $value[body_data][title];
		}
		if ($value[icon] == "album" && $value[idtype] == "picid") {
			$pics = null;
			$fvalue[pics] = null;
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$fvalue[pics] = $pics;
			$fvalue[content] = $value[body_data][title];
		}
		if ($value[icon] == "share" && $value[idtype] == "pic") {
			$fvalue[content] = $value[body_general];
		}
		//链接ok
		if ($value[icon] == "share" && $value[idtype] == "link") {
			$fvalue[content] = $value[body_general];
		}
		//文档ok
		if ($value[icon] == "doc" && $value[idtype] != "feed") {
			$docarr = getFile($value[id]);
			$fvalue[title] = $docarr[title];
			$fvalue[content] = $docarr[context];
		}
		//视频ok
		if ($value[idtype] == "video" || $value[idtype] == "flash") {
			$fvalue[content] = $value[body_general];
		}
		//音频ok
		if ($value[icon] == "share" && $value[idtype] == "music") {
			$fvalue[content] = $value[body_general];
		}
		//课程ok
		if ($value[icon] == "class" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = $value[body_data][message];
		}
		//案例ok
		if ($value[icon] == "case" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = $value[body_data][message];
		}
		//话题ok
		if ($value[icon] == "thread" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = $value[body_data][message];
		}
		//通知公告ok
		if ($value[icon] == "notice" && $value[idtype] != "feed") {
			$value[title] = strip_tags($value[title_data][noticetitle]);
			$value[content] = $value[body_data][message];
		}
		//活动ok
		if ($value[icon] == "activitys" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = $value[body_data][message];
		}
		//直播ok
		if ($value[icon] == "live" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = "";
		}
		//评选ok
		if ($value[icon] == "selection" && $value[idtype] != "feed") {
			$a = explode("&selectionid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select selectionname,selectiondescr from pre_selection where selectionid=" . $id);
			$fvalue[title] = $v[selectionname];
			$fvalue[content] = $v[selectiondescr];
		}
		//提问ok
		if ($value[icon] == "reward" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$fvalue[content] = $v;
		}
		//资源列表ok
		if ($value[icon] == "resourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,about from pre_resourcelist where resourceid=" . $id);
			$fvalue[title] = $v[title];
			$fvalue[content] = $v[about];
		}
		//资源列表ok
		if ($value[icon] == "shresourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,about from pre_shresourcelist where resourceid=" . $id);
			$fvalue[title] = $v[title];
			$fvalue[content] = $v[about];
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
			$fvalue[title] = $v[name];
			$fvalue[content] = $v[descr];
		}
		//问卷ok
		if ($value[icon] == "questionary" && $value[idtype] != "feed") {
			$a = explode("&questid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select questname,questdescr from pre_questionary where questid=" . $id);
			$fvalue[title] = $v[questname];
			$fvalue[content] = $v[questdescr];
		}
		//投票ok
		if ($value[icon] == "poll" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$fvalue[content] = $v;
			if ($v == "" || $v == null) {
				$fvalue[content] = $value[body_data][message];
			}
		}
		//你我课堂ok
		if ($value[icon] == "nwkt" && $value[idtype] != "feed") {
			$fvalue[title] = strip_tags($value[body_data][subject]);
			$fvalue[content] = $value[body_data][summary];
		}
		//专区转发ok
		if ($value[icon] == "forward" && $value[idtype] != "feed") {
			$fvalue[content] = $value[body_general];
		}
		$fvalue[content] = discuzcode($fvalue[content], -1, 0, 1, 1, 1, 1, 1);
		$fvalue[content] = cutstr($fvalue[content], 400);
		$fvalue[content] = str_replace("&nbsp;", "", $fvalue[content]);
		$fvalue[content] = preg_replace("/\s(?=\s)/", "", $fvalue[content]);
		$fvalue[content] = trim($fvalue[content]);

		if ($value[fdate]) {
			$fvalue[createDate] = $value[fdate];
		} else {
			$fvalue[createDate] = '';
		}
		$fvalue[feedid] = $value[feedid];
		$fvalue[icon] = $value[icon];
		$fvalue[idtype] = $value[idtype];
		$fvalue[fromwhere] = $value[where];
		$fvalue[anonymity] = $value[fanonymity];
		//封装评论主体
		if ($value[canonymity]) {
			if ($value[canonymity] == -1) {
				$newvalue[user][uid] = -1;
				$newvalue[user][username] = '匿名';
				$newvalue[user][iconImg] = useravatar(-1);
			} else {
				include_once libfile('function/repeats', 'plugin/repeats');
				$repeatsinfo = getforuminfo($value[canonymity]);
				if ($repeatsinfo['icon']) {
					$newcommentvalue['ficon'] = $_G[config]['image']['url'] . '/data/attachment/group/' . $repeatsinfo['icon'];
				} else {
					$newcommentvalue['ficon'] = $_G[config]['image']['url'] . '/static/image/images/def_group.png';
				}
				$newvalue[user][uid] = $repeatsinfo[fid];
				$newvalue[user][username] = $repeatsinfo[name];
				$newvalue[user][iconImg] = $newcommentvalue['ficon'];
			}
		} else {
			$newvalue[user][uid] = $value[authorid];
			$newvalue[user][username] = user_get_user_name($value[authorid]);
			$newvalue[user][iconImg] = useravatar($value[authorid]);
		}
		$newvalue[commentDate] = $value[cdate];
		$newvalue[commentContent] = $value[message];
		$newvalue[anonymity] = $value[canonymity];
		$newvalue[cid] = $value[cid];
		$newvalue[feed] = $fvalue;
		$res[comments][] = $newvalue;
	}
	if ($pagetype == 'down') {
		DB :: query("update " . DB :: table('home_notification') . " set new=0 where uid=" . $uid . " and new=1 and type='zq_comment'");
	}
}
echo json_encode($res);
?>