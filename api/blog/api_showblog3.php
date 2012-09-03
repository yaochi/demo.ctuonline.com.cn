<?php


/* Function: 根据检索参数，获取日志的详情和日志的评论
 * Com.:
 * Author: caimm
 * Date: 2012-5-16
 */
require dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_home.php';
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_doc.php';
$discuz = & discuz_core :: instance();

$discuz->init();
require dirname(dirname(dirname(__FILE__))) . '/source/function/function_discuzcode.php';
$uid = $_G['gp_uid'];
$feedid = $_G['gp_feedid'];
$preurl = $_G[config]['image']['url'];
if ($feedid) {
	$value = DB :: fetch_first("SELECT *  FROM " . DB :: table('home_feed') . " WHERE feedid =" . $feedid);
	$value = mkfeed($value);
	if($value[feedid]){
		$feed[feedid] = $value[feedid];
		$feed[icon] = $value[icon];
		$feed[idtype] = $value[idtype];
		$feed[user] = packUser($value[uid], $value[anonymity], $preurl);
		if ($value[dateline]) {
			$feed[createDate] = $value[dateline];
		} else {
			$feed[createDate] = '';
		}
		$feed[commentCount] = $value[commenttimes];
		$feed[forwardCount] = $value[sharetimes];
		$feed[fromwhere] = $value[fromwhere];
		$feed[anonymity] = $value[anonymity];
		$feed[isOriginal] = 1;
		$feed[originalFeed] = null;
	
		//按类型封装
		//记录ok
		if ($value[icon] == "blog" && $value[idtype] != "feed") {
			$v = DB :: fetch_first("select b.subject ,bf.message from pre_home_blog b left join pre_home_blogfield bf on bf.blogid = b.blogid where b.blogid =" . $value[id]);
			$feed[title] = $v[subject];
			$feed[content] = $v[message];
		}
		if ($value[icon] == "doing" && $value[idtype] != "feed") {
			$v = DB :: fetch_first("select message from pre_home_doing where doid=" . $value[id]);
			$feed[title] = null;
			$feed[content] = $v[message];
		}
	
		//图片ok
		if ($value[icon] == "album" && $value[idtype] == "albumid") {
			if ($value[target_ids]) {
				$v = DB :: query("select title,filepath from pre_home_pic where picid in(" . $value[target_ids] . ") order by dateline asc");
				$feed[pics] = null;
				while ($p = DB :: fetch($v)) {
					$pic[pictureUrl] = $preurl . "/" . $p[filepath];
					$ps[] = $pic;
				}
				$feed[pics] = $ps;
			}
			elseif ($value[id]) {
				$v = DB :: query("select title,filepath from pre_home_pic where albumid=" . $value[id] . " order by dateline desc limit 0," . $value[body_data][picnum]);
				$feed[pics] = null;
				while ($p = DB :: fetch($v)) {
					$pic[pictureUrl] = $preurl . "/data/attachment/album/" . $p[filepath];
					$ps[] = $pic;
				}
				$ps = array_reverse($ps);
				$feed[pics] = $ps;
			}
			$feed[content] = $value[body_general];
		}
		if ($value[icon] == "album" && $value[idtype] == "gpicid") {
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$feed[pics] = $pics;
			$feed[content] = $value[body_data][title];
		}
		if ($value[icon] == "album" && $value[idtype] == "picid") {
			$_p = explode(".thumb", $value[image_1]);
			$pic[pictureUrl] = $preurl . "/" . $_p[0];
			$pics[] = $pic;
			$feed[pics] = $pics;
			$feed[content] = $value[body_data][title];
		}
		if ($value[icon] == "share" && $value[idtype] == "pic") {
			$pic[pictureUrl] = $preurl . "/" . $value[image_1];
			$pics[] = $pic;
			$feed[pics] = $pics;
			$feed[content] = $value[body_general];
		}
		//链接ok
		if ($value[icon] == "share" && $value[idtype] == "link") {
			$feed[content] = $value[body_general];
			$feed["link"] = $value[body_data]["link"];
		}
		//文档ok
		if ($value[icon] == "doc" && $value[idtype] != "feed") {
			$docarr = getFile($value[id]);
			$feed[title] = $docarr[title];
			$feed[content] = $docarr[context];
			$feed["link"] = "<a href=\"" . $docarr[titlelink] . "\">" . $docarr[titlelink] . "</a>";
		}
		//视频ok
		if ($value[idtype] == "video" || $value[idtype] == "flash") {
			$feed["link"] = $value[body_data]["link"];
			$feed[content] = $value[body_general];
		}
		//音频ok
		if ($value[icon] == "share" && $value[idtype] == "music") {
			$feed["link"] = $value[body_data]["link"];
			$feed[content] = $value[body_general];
		}
		//课程ok
		if ($value[icon] == "class" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$feed[content] = $value[body_data][message];
			$feed["link"] = "<a href=\"" . $value[image_1_link] . "\">" . $value[image_1_link] . "</a>";
		}
		//案例ok
		if ($value[icon] == "case" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$feed[content] = $value[body_data][message];
			$feed["link"] = "<a href=\"" . $value[image_1_link] . "\">" . $value[image_1_link] . "</a>";
		}
		//话题ok
		if ($value[icon] == "thread" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$feed['content'] = preg_replace("/\[i=s\](.*?)\[\/i\]/", '', $v);
			$s = $preurl . "/forum.php?mod=viewthread&tid=" . $value[id];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//通知公告ok
		if ($value[icon] == "notice" && $value[idtype] != "feed") {
			$a = explode("href=\"", $value[title_data][noticetitle]);
			$b = explode("\">", $a[1]);
			$s = $preurl . "/" . $b[0];
			$feed[title] = strip_tags($value[title_data][noticetitle]);
			$v = DB :: result_first("select content from pre_notice where id=" . $value[id]);
			$feed[content] = $v;
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//活动ok
		if ($value[icon] == "activitys" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$feed[content] = $value[body_data][message];
			$s = explode("&fid=", $value[body_data][subject]);
			$t = explode("\" >", $s[1]);
			$s = $preurl . "/forum.php?mod=activity&fid=" . $t[0];
			$feed[link] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//直播ok
		if ($value[icon] == "live" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$feed[content] = "";
			$a = explode("href=\"/forum/", $value[body_data][subject]);
			$b = explode("\" >", $a[1]);
			$s = $preurl . "/forum.php?mod=group&action=plugin&fid=" . $value[fid] . "&plugin_name=grouplive&plugin_op=groupmenu&liveid=" . $value[id] . "&op=join&grouplive_action=livecp&";
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//评选ok
		if ($value[icon] == "selection" && $value[idtype] != "feed") {
			$a = explode("&selectionid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select selectionname,selectiondescr from pre_selection where selectionid=" . $id);
			$feed[title] = $v[selectionname];
			$feed[content] = $v[selectiondescr];
			$feed[content] = cutstr($feed[content], 400);
			$j = explode("href=\"", $value[title_data][questname]);
			$k = explode("\">", $j[1]);
			$s = $preurl . "/" . $k[0];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//提问ok
		if ($value[icon] == "reward" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$feed[content] = $v;
			$s = $preurl . "/forum.php?mod=viewthread&tid=" . $value[id];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//资源列表ok
		if ($value[icon] == "resourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,titlelink,about from pre_resourcelist where resourceid=" . $id);
			$feed[title] = $v[title];
			$feed[content] = $v[about];
			$s = $v[titlelink];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//资源列表ok
		if ($value[icon] == "shresourcelist" && $value[idtype] != "feed") {
			$a = explode("WebRoot/r-", $value[title_data][resourcetitle]);
			$b = explode(".htm", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select title,titlelink,about from pre_shresourcelist where resourceid=" . $id);
			$feed[title] = $v[title];
			$feed[content] = $v[about];
			$s = $v[titlelink];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
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
			$feed[title] = $v[name];
			$feed[content] = $v[descr];
			$j = explode("href=\"", $value[title_template]);
			$k = explode("\" >", $j[2]);
			$s = $preurl . "/" . $k[0];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//问卷ok
		if ($value[icon] == "questionary" && $value[idtype] != "feed") {
			$a = explode("&questid=", $value[title_data][questname]);
			$b = explode("&", $a[1]);
			$id = $b[0];
			$v = DB :: fetch_first("select questname,questdescr from pre_questionary where questid=" . $id);
			$feed[title] = $v[questname];
			$feed[content] = $v[questdescr];
			$j = explode("href=\"", $value[title_data][questname]);
			$k = explode("\">", $j[1]);
			$s = $preurl . "/" . $k[0];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//投票ok
		if ($value[icon] == "poll" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$v = DB :: result_first("select message from pre_forum_post where tid=" . $value[id] . " and first=1");
			$feed[content] = $v;
			if ($v == "" || $v == null) {
				$feed[content] = $value[body_data][message];
			}
			$s = $preurl . "/forum.php?mod=viewthread&tid=" . $value[id];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//你我课堂ok
		if ($value[icon] == "nwkt" && $value[idtype] != "feed") {
			$feed[title] = strip_tags($value[body_data][subject]);
			$feed[content] = $value[body_data][summary];
			$s = $preurl . "/home.php?mod=space&uid=" . $value[uid] . "&do=nwkt&id=" . $value[id];
			$feed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
		}
		//专区转发ok
		if ($value[icon] == "forward" && $value[idtype] != "feed") {
			$feed[content] = $value[body_general];
			$feed[isOriginal] = 2;
			$feed[originalFeed][title] = strip_tags($value[body_data][subject]);
			$feed[originalFeed][content] = $value[body_data][message];
			$s = explode("href=\"", $value[body_data][subject]);
			$t = explode("\" >", $s[1]);
			$s = $preurl . "/" . $t[0];
			$feed[originalFeed]["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			$feed[originalFeed][feedid] = 0;
			$feed[originalFeed][icon] = $value[icon];
			$feed[originalFeed][idtype] = $value[idtype];
			$feed[originalFeed][user] = null;
			$feed[originalFeed][createDate] = "";
			$feed[originalFeed][commentCount] = "";
			$feed[originalFeed][forwardCount] = "";
			$feed[originalFeed][fromwhere] = "";
			$feed[originalFeed][anonymity] = $value[anonymity];
	
		}
	
		if ($value[idtype] == "feed") {
			$feed[content] = $value[body_general];
			$feed["link"] = "";
			$feed[isOriginal] = 2;
			$oldvalue = DB :: fetch_first("SELECT * FROM " . DB :: table('home_feed') . " WHERE feedid =" . $value[id]);
			$oldvalue = mkfeed($oldvalue);
			$oldfeed[feedid] = $oldvalue[feedid];
			$oldfeed[icon] = $oldvalue[icon];
			$oldfeed[idtype] = $oldvalue[idtype];
			$oldfeed[user] = packUser($oldvalue[uid], $oldvalue[anonymity], $preurl);
			$oldfeed[dateline] = $oldvalue[dateline];
			if ($oldvalue[dateline]) {
				$oldfeed[createDate] = $oldvalue[dateline];
			} else {
				$oldfeed[createDate] = '';
			}
			$oldfeed[commentCount] = $oldvalue[commenttimes];
			$oldfeed[forwardCount] = $oldvalue[sharetimes];
			$oldfeed[fromwhere] = $oldvalue[fromwhere];
			$oldfeed[anonymity] = $oldvalue[anonymity];
	
			//按类型封装
			//记录ok
			if ($oldvalue[icon] == "blog" && $oldvalue[idtype] != "feed") {
				$v = DB :: fetch_first("select b.subject ,bf.message from pre_home_blog b left join pre_home_blogfield bf on bf.blogid = b.blogid where b.blogid =" . $oldvalue[id]);
				$oldfeed[title] = $v[subject];
				$oldfeed[content] = $v[message];
			}
			if ($oldvalue[icon] == "doing" && $oldvalue[idtype] != "feed") {
				$v = DB :: fetch_first("select message from pre_home_doing where doid=" . $oldvalue[id]);
				$oldfeed[title] = null;
				$oldfeed[content] = $v[message];
			}
	
			//图片ok
			if ($oldvalue[icon] == "album" && $oldvalue[idtype] == "albumid") {
				if ($oldvalue[target_ids]) {
					$v = DB :: query("select title,filepath from pre_home_pic where picid in(" . $oldvalue[target_ids] . ") order by dateline asc");
					$oldfeed[pics] = null;
					while ($p = DB :: fetch($v)) {
						$pic[pictureUrl] = $preurl . "/" . $p[filepath];
						$ps[] = $pic;
					}
					$oldfeed[pics] = $ps;
				}
				elseif ($oldvalue[id]) {
					$v = DB :: query("select title,filepath from pre_home_pic where albumid=" . $oldvalue[id] . " order by dateline desc limit 0," . $value[body_data][picnum]);
					$oldfeed[pics] = null;
					while ($p = DB :: fetch($v)) {
						$pic[pictureUrl] = $preurl . "/data/attachment/album/" . $p[filepath];
						$ps[] = $pic;
					}
					$ps = array_reverse($ps);
					$oldfeed[pics] = $ps;
				}
				$oldfeed[content] = $oldvalue[body_general];
			}
			if ($oldvalue[icon] == "album" && $oldvalue[idtype] == "gpicid") {
				$_p = explode(".thumb", $oldvalue[image_1]);
				$pic[pictureUrl] = $preurl . "/" . $_p[0];
				$pics[] = $pic;
				$oldfeed[pics] = $pics;
				$oldfeed[content] = $oldvalue[body_data][title];
			}
			if ($oldvalue[icon] == "album" && $oldvalue[idtype] == "picid") {
				$_p = explode(".thumb", $oldvalue[image_1]);
				$pic[pictureUrl] = $preurl . "/" . $_p[0];
				$pics[] = $pic;
				$oldfeed[pics] = $pics;
				$oldfeed[content] = $oldvalue[body_data][title];
			}
			if ($oldvalue[icon] == "share" && $oldvalue[idtype] == "pic") {
				$pic[pictureUrl] = $preurl . "/" . $oldvalue[image_1];
				$pics[] = $pic;
				$oldfeed[pics] = $pics;
				$oldfeed[content] = $oldvalue[body_general];
			}
			//链接ok
			if ($oldvalue[icon] == "share" && $oldvalue[idtype] == "link") {
				$oldfeed[content] = $oldvalue[body_general];
				$oldfeed["link"] = $oldvalue[body_data]["link"];
			}
			//文档ok
			if ($oldvalue[icon] == "doc" && $oldvalue[idtype] != "feed") {
				$docarr = getFile($oldvalue[id]);
				$oldfeed[title] = $docarr[title];
				$oldfeed[content] = $docarr[context];
				$oldfeed["link"] = "<a href=\"" . $docarr[titlelink] . "\">" . $docarr[titlelink] . "</a>";
			}
			//视频ok
			if ($oldvalue[idtype] == "video" || $oldvalue[idtype] == "flash") {
				$oldfeed["link"] = $oldvalue[body_data]["link"];
				$oldfeed[content] = $oldvalue[body_general];
			}
			//音频ok
			if ($oldvalue[icon] == "share" && $oldvalue[idtype] == "music") {
				$oldfeed["link"] = $oldvalue[body_data]["link"];
				$oldfeed[content] = $oldvalue[body_general];
			}
			//课程ok
			if ($oldvalue[icon] == "class" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = $oldvalue[body_data][message];
				$oldfeed["link"] = "<a href=\"" . $oldvalue[image_1_link] . "\">" . $oldvalue[image_1_link] . "</a>";
			}
			//案例ok
			if ($oldvalue[icon] == "case" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = $oldvalue[body_data][message];
				$oldfeed["link"] = "<a href=\"" . $oldvalue[image_1_link] . "\">" . $oldvalue[image_1_link] . "</a>";
			}
			//话题ok
			if ($oldvalue[icon] == "thread" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$v = DB :: result_first("select message from pre_forum_post where tid=" . $oldvalue[id] . " and first=1");
				$oldfeed[content] = preg_replace("/\[i=s\](.*?)\[\/i\]/", '', $v);
				$s = $preurl . "/forum.php?mod=viewthread&tid=" . $oldvalue[id];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//通知公告ok
			if ($oldvalue[icon] == "notice" && $oldvalue[idtype] != "feed") {
				$a = explode("href=\"", $oldvalue[title_data][noticetitle]);
				$b = explode("\">", $a[1]);
				$s = $preurl . "/" . $b[0];
				$oldfeed[title] = strip_tags($oldvalue[title_data][noticetitle]);
				$v = DB :: result_first("select content from pre_notice where id=" . $oldvalue[id]);
				$oldfeed[content] = $v;
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//活动ok
			if ($oldvalue[icon] == "activitys" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = $oldvalue[body_data][message];
				$s = explode("&fid=", $oldvalue[body_data][subject]);
				$t = explode("\" >", $s[1]);
				$s = $preurl . "/forum.php?mod=activity&fid=" . $t[0];
				$oldfeed[link] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//直播ok
			if ($oldvalue[icon] == "live" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = "";
				$a = explode("href=\"/forum/", $oldvalue[body_data][subject]);
				$b = explode("\" >", $a[1]);
				$s = $preurl . "/forum.php?mod=group&action=plugin&fid=" . $oldvalue[fid] . "&plugin_name=grouplive&plugin_op=groupmenu&liveid=" . $oldvalue[id] . "&op=join&grouplive_action=livecp&";
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//评选ok
			if ($oldvalue[icon] == "selection" && $oldvalue[idtype] != "feed") {
				$a = explode("&selectionid=", $oldvalue[title_data][questname]);
				$b = explode("&", $a[1]);
				$id = $b[0];
				$v = DB :: fetch_first("select selectionname,selectiondescr from pre_selection where selectionid=" . $id);
				$oldfeed[title] = $v[selectionname];
				$oldfeed[content] = $v[selectiondescr];
				$j = explode("href=\"", $oldvalue[title_data][questname]);
				$k = explode("\">", $j[1]);
				$s = $preurl . "/" . $k[0];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//提问ok
			if ($oldvalue[icon] == "reward" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$v = DB :: result_first("select message from pre_forum_post where tid=" . $oldvalue[id] . " and first=1");
				$oldfeed[content] = $v;
				$s = $preurl . "/forum.php?mod=viewthread&tid=" . $oldvalue[id];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//资源列表ok
			if ($oldvalue[icon] == "resourcelist" && $oldvalue[idtype] != "feed") {
				$a = explode("WebRoot/r-", $oldvalue[title_data][resourcetitle]);
				$b = explode(".htm", $a[1]);
				$id = $b[0];
				$v = DB :: fetch_first("select title,titlelink,about from pre_resourcelist where resourceid=" . $id);
				$oldfeed[title] = $v[title];
				$oldfeed[content] = $v[about];
				$s = $v[titlelink];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//资源列表ok
			if ($oldvalue[icon] == "shresourcelist" && $oldvalue[idtype] != "feed") {
				$a = explode("WebRoot/r-", $oldvalue[title_data][resourcetitle]);
				$b = explode(".htm", $a[1]);
				$id = $b[0];
				$v = DB :: fetch_first("select title,titlelink,about from pre_shresourcelist where resourceid=" . $id);
				$oldfeed[title] = $v[title];
				$oldfeed[content] = $v[about];
				$s = $v[titlelink];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//外部资源
			if ($oldvalue[icon] == "extraresource" && $oldvalue[idtype] != "feed") {
				$a = explode("&id=", $oldvalue[title_template]);
				$b = explode("\"", $a[1]);
				$id = $b[0];
				if ($oldvalue[title_data]["class"]) {
					$v = DB :: fetch_first("select name,descr from pre_extra_class where id=" . $id);
				}
				elseif ($oldvalue[title_data]["lecture"]) {
					$v = DB :: fetch_first("select name,descr from pre_extra_lecture where id=" . $id);
				}
				elseif ($oldvalue[title_data]["org"]) {
					$v = DB :: fetch_first("select name,descr from pre_extra_org where id=" . $id);
				}
				$oldfeed[title] = $v[name];
				$oldfeed[content] = $v[descr];
				$j = explode("href=\"", $oldvalue[title_template]);
				$k = explode("\" >", $j[2]);
				$s = $preurl . "/" . $k[0];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//问卷ok
			if ($oldvalue[icon] == "questionary" && $oldvalue[idtype] != "feed") {
				$a = explode("&questid=", $oldvalue[title_data][questname]);
				$b = explode("&", $a[1]);
				$id = $b[0];
				$v = DB :: fetch_first("select questname,questdescr from pre_questionary where questid=" . $id);
				$oldfeed[title] = $v[questname];
				$oldfeed[content] = $v[questdescr];
				$j = explode("href=\"", $oldvalue[title_data][questname]);
				$k = explode("\">", $j[1]);
				$s = $preurl . "/" . $k[0];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//投票ok
			if ($oldvalue[icon] == "poll" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$v = DB :: result_first("select message from pre_forum_post where tid=" . $oldvalue[id] . " and first=1");
				$oldfeed[content] = $v;
				if ($v == "" || $v == null) {
					$oldfeed[content] = $value[body_data][message];
				}
				$s = $preurl . "/forum.php?mod=viewthread&tid=" . $oldvalue[id];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//你我课堂ok
			if ($oldvalue[icon] == "nwkt" && $oldvalue[idtype] != "feed") {
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = $oldvalue[body_data][summary];
				$s = $preurl . "/home.php?mod=space&uid=" . $oldvalue[uid] . "&do=nwkt&id=" . $oldvalue[id];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			//专区转发ok
			if ($oldvalue[icon] == "forward" && $oldvalue[idtype] != "feed") {
				$oldfeed[user] = null;
				$oldfeed[title] = strip_tags($oldvalue[body_data][subject]);
				$oldfeed[content] = $oldvalue[body_data][message];
				$s = explode("href=\"", $oldvalue[body_data][subject]);
				$t = explode("\" >", $s[1]);
				$s = $preurl . "/" . $t[0];
				$oldfeed["link"] = "<a href=\"" . $s . "\">" . $s . "</a>";
			}
			$oldfeed[content] = discuzcode($oldfeed[content], -1, 0, 1, 1, 1, 1, 1);
			if ($oldvalue[icon] == "blog" || $oldvalue[icon] == "doing" || $oldvalue[icon] == "thread" || $oldvalue[icon] == "notice" || $oldvalue[icon] == "share"|| $oldvalue[icon] == "class"|| $oldvalue[icon] == "doc"|| $oldvalue[icon] == "case") {
			} else {
				$oldfeed[content] = cutstr($oldfeed[content], 400);
			}
			$oldfeed[richContent] = $oldfeed[content];
	
			$feed[originalFeed] = $oldfeed;
		}
		$feed[content] = discuzcode($feed[content], -1, 0, 1, 1, 1, 1, 1);
		if ($value[icon] == "blog" || $value[icon] == "doing" || $value[icon] == "thread" || $value[icon] == "notice" || $value[icon] == "share"|| $value[icon] == "class"|| $value[icon] == "doc"|| $value[icon] == "case") {
		} else {
			$feed[content] = cutstr($feed[content], 400);
		}
		$feed[richContent] = $feed[content];
		//评论
		if ($value[icon] == "thread" || $value[icon] == "poll" || $value[icon] == "reward") {
			$commentquery = DB :: query("select *,anonymous as anonimity from " . DB :: TABLE("forum_post") . " where tid =" . $value[id] . " and first!='1' and authorid!=0 order by dateline desc limit 0,5");
		}
		elseif ($value[icon] == "resourcelist") {
			$commentquery = DB :: query("select * from " . DB :: TABLE("home_comment") . " where idtype ='docid' and id=" . $value[id] . " and authorid!=0 order by dateline desc limit 0,5");
		} else {
			$commentquery = DB :: query("select * from " . DB :: TABLE("home_comment") . " where idtype ='feed' and id=" . $value[feedid] . " and authorid!=0 order by dateline desc limit 0,5");
		}
		while ($commentvalue = DB :: fetch($commentquery)) {
			$cvalue = array ();
			$newcommentvalue[cid] = empty ($commentvalue[pid]) ? $commentvalue[cid] : $commentvalue[pid];
			$newcommentvalue[dateline] = $commentvalue[dateline];
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
			$cvalue[commentDate] = $commentvalue[dateline];
			$cvalue[commentContent] = $newcommentvalue[message];
			$cvalue[anonymity] = $commentvalue[anonymity];
			$cvalue[cid] = $newcommentvalue[cid];
			$feed[comments][] = $cvalue;
		}
	}else{
		$feed[feedid]='null';
	}
}
echo json_encode($feed);
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