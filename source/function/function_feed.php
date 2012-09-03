<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_feed.php 11789 2010-06-13 02:53:32Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// 修改 by songsp  2010-12-1 添加参数 fid 专区/活动id
function feed_add($icon, $title_template='', $title_data=array(), $body_template='', $body_data=array(), $body_general='', $images=array(), $image_links=array(), $target_ids='', $friend='', $appid='', $returnid=0, $id=0, $idtype='', $uid=0, $username='',$fid=0,$sharetofids=array(),$forwardfname='',$forwardfid=0,$commenttimes=0,$anonymity=0,$fromwhere=0) {
	global $_G;
	require_once libfile('function/home');
/*	if(!ckprivacy($icon, 'feed')){
		return;
	}*/
	$nofeed_template=array('feed_blog_password','feed_showcredit','feed_showcredit_self','feed_friend_title','feed_click_blog','feed_click_thread','feed_click_pic','feed_click_article','feed_profile_update_base','feed_profile_update_contact','feed_profile_update_edu','feed_profile_update_work','feed_profile_update_info','feed_add_attachsize','feed_invite','feed_questionary_answer','feed_lecturerecord','feed_reply_title','feed_reply_title_anonymous','feed_thread_votepoll_title','feed_reply_votepoll','feed_thread_goods_title','feed_reply_reward_title','feed_reply_activity_title','feed_thread_debate_title','feed_live_play_title','feed_live_replay_title','feed_stick_grouplive_title','feed_stick_no_grouplive_title','feed_digest_grouplive_title','feed_digest_no_grouplive_title','feed_click_notice','feed_click_nwkt','feed_click_doc','feed_stick_doc_title','feed_stick_no_doc_title','feed_digest_doc_title','feed_digest_no_doc_title','feed_view_pic_title','feed_group_join','feed_comment_space','feed_comment_image','feed_comment_blog','feed_comment_poll','feed_comment_topic','feed_comment_event','feed_comment_share','feed_comment_notice','feed_comment_nwkt','feed_comment_doc','feed_comment_case','feed_comment_class');
	if(in_array($title_template,$nofeed_template)){
	}else{
		$title_template = $title_template?lang('feed', $title_template):'';
		$body_template = $body_template?lang('feed', $body_template):'';
		$body_general = $body_general?lang('feed', $body_general):'';
		
//		$cache_key = "feed_add_".$uid."_".$title_template;
//		$cache = memory("get", $cache_key);
//		if(!empty($cache)){
//			return 1;
//		}
		
		if(empty($uid) || empty($username)) {
			$uid = $username = '';
		}
		if($username){
			if($uid){
				$username = user_get_user_name($uid);
			}
		}else{
			$username = user_get_user_name($_G['uid']);
		}
		if($anonymity>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($anonymity);
		}
		$sharetofids[count($sharetofids)]=$repeatsinfo['fid'];
		$feedarr = array(
			'appid' => $appid,
			'icon' => $icon,
			'uid' => $uid ? intval($uid) : $_G['uid'],
			'username' => $username,
			'dateline' => $_G['timestamp'],
			'title_template' => $title_template,
			'body_template' => $body_template,
			'body_general' => $body_general,
			'image_1' => empty($images[0])?'':$images[0],
			'image_1_link' => empty($image_links[0])?'':$image_links[0],
			'image_2' => empty($images[1])?'':$images[1],
			'image_2_link' => empty($image_links[1])?'':$image_links[1],
			'image_3' => empty($images[2])?'':$images[2],
			'image_3_link' => empty($image_links[2])?'':$image_links[2],
			'image_4' => empty($images[3])?'':$images[3],
			'image_4_link' => empty($image_links[3])?'':$image_links[3],
			'image_5' => empty($images[4])?'':$images[43],
			'image_5_link' => empty($image_links[4])?'':$image_links[4],
			'target_ids' => $target_ids,
			'friend' => $friend,
			'id' => $id,
			'idtype' => $idtype,
			'fid' =>$fid? $fid : 0 , //专区/活动id
			'sharetofids'=>','.implode(',',unserialize(serialize(dstripslashes($sharetofids)))).',',
			'forwardfname'=>$forwardfname,
			'forwardfid'=>$forwardfid,
			'commenttimes'=>$commenttimes,
			'anonymity'=>$anonymity,
			'fromwhere'=>$fromwhere
		);
		
		$feedarr = dstripslashes($feedarr);
		$feedarr['title_data'] = serialize(dstripslashes($title_data));
		$feedarr['body_data'] = serialize(dstripslashes($body_data));
		$feedarr['hash_data'] = empty($title_data['hash_data'])?'':$title_data['hash_data'];
		$feedarr = daddslashes($feedarr);
	//print_r($feedarr);exit;
		if(is_numeric($icon)) {
			$feed_table = 'home_feed_app';
			unset($feedarr['id'], $feedarr['idtype']);
		} else {
			if($feedarr['hash_data']) {
				$query = DB::query("SELECT feedid FROM ".DB::table('home_feed')." WHERE uid='$feedarr[uid]' AND hash_data='$feedarr[hash_data]' AND icon='profile' and dateline>'".(time()-24*3600)."' LIMIT 0,1");
				if($oldfeed = DB::fetch($query)) {
					return 0;
				}
			}
			$feed_table = 'home_feed';
		}
	
		if($returnid) {
//			memory("set", $cache_key, serialize($cache_key), 1200);
			return DB::insert($feed_table, $feedarr, $returnid);
		} else {
//			memory("set", $cache_key, serialize($cache_key), 1200);
			DB::insert($feed_table, $feedarr);
			return 1;
		}
	}
}

function mkfeed($feed, $actors=array()) {
	global $_G;

	$feed['title_data'] = empty($feed['title_data'])?array():(is_array($feed['title_data'])?$feed['title_data']:@unserialize($feed['title_data']));
	if(!is_array($feed['title_data'])) $feed['title_data'] = array();
	$feed['body_data'] = empty($feed['body_data'])?array():(is_array($feed['body_data'])?$feed['body_data']:@unserialize($feed['body_data']));
	if(!is_array($feed['body_data'])) $feed['body_data'] = array();

	$searchs = $replaces = array();
	if($feed['title_data']) {
		foreach (array_keys($feed['title_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $feed['title_data'][$key];
		}
	}

	$searchs[] = '{actor}';
	if($feed[olduid]){
		$username = user_get_user_name($feed[olduid]);
		$actuid=$feed[olduid];
	}else{
    	$username = user_get_user_name($feed[uid]);
		$actuid=$feed[uid];
	}
	if($feed[anonymity]){
		if($feed[anonymity]=='-1'){
			$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"home.php?mod=space&uid=-1\" target=\"_blank\">匿名</a>":implode(lang('core', 'dot'), $actors);
		}else{
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($feed[anonymity]);
			$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"forum.php?mod=group&fid=$repeatsinfo[fid]\" target=\"_blank\">$repeatsinfo[name]</a>":implode(lang('core', 'dot'), $actors);
		}
	}else{
		$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"home.php?mod=space&uid=$actuid\" target=\"_blank\">$username</a>":implode(lang('core', 'dot'), $actors);
	}
	
	$feed['title_template'] = str_replace($searchs, $replaces, $feed['title_template']);
	$feed['title_template'] = feed_mktarget($feed['title_template']);
	
	$searchs = $replaces = array();
	$searchs[] = '{actor}';
    $username = user_get_user_name($feed[uid]);
	if($feed[anonymity]){
		if($feed[anonymity]=='-1'){
			$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"home.php?mod=space&uid=-1\" target=\"_blank\">匿名</a>":implode(lang('core', 'dot'), $actors);
		}else{
			$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"forum.php?mod=group&fid=$repeatsinfo[fid]\" target=\"_blank\">$repeatsinfo[name]</a>":implode(lang('core', 'dot'), $actors);
		}
	}else{
		$replaces[] = empty($actors)?"<a class=\"perPanel\" href=\"home.php?mod=space&uid=$actuid\" target=\"_blank\">$username</a>":implode(lang('core', 'dot'), $actors);
	}
	if($feed['body_data'] && is_array($feed['body_data'])) {
		foreach (array_keys($feed['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $feed['body_data'][$key];
			$feed['body_data'][$key]=feed_mktarget($feed['body_data'][$key]);
		}
	}
	
	$feed['magic_class'] = '';
	if(!empty($feed['body_data']['magic_thunder'])) {
		$feed['magic_class'] = 'magicthunder';
	}

	$feed['body_template'] = str_replace($searchs, $replaces, $feed['body_template']);
	$feed['body_template'] = feed_mktarget($feed['body_template']);

	$feed['body_general'] = feed_mktarget($feed['body_general']);

	if(is_numeric($feed['icon'])) {
		$feed['icon_image'] = "http://appicon.manyou.com/icons/{$feed['icon']}";
	} else {
		$feed['icon_image'] = STATICURL."image/feed/{$feed['icon']}.gif";
	}

	$feed['style'] = $feed['target'] = '';
	if($_G['setting']['feedtargetblank']) {
		$feed['target'] = ' target="_blank"';
	}

	$feed['new'] = 0;
	if($_G['cookie']['home_readfeed'] && $feed['dateline']+300 > $_G['cookie']['home_readfeed']) {
		$feed['new'] = 1;
	}

	return $feed;
}

function feed_mktarget($html) {
	global $_G;

	if($html && $_G['setting']['feedtargetblank']) {
		$html = preg_replace("/<a(.+?)href=([\'\"]?)([^>\s]+)\\2([^>]*)>/i", '<a target="_blank" \\1 href="\\3" \\4>', $html);
	}
	return $html;
}


function feed_publish($id, $idtype, $add=0,$fromwhere=0,$atjson,$postmessage) {
	global $_G;
	require_once libfile('function/home');
	
	$setarr = array();
	switch ($idtype) {
		case 'blogid':
			$query = DB::query("SELECT b.*, bf.* FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			if($value = DB::fetch($query)) {
				if($value['friend'] != 3) {
					$setarr['icon'] = 'blog';
					$setarr['id'] = $value['blogid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					$setarr['anonymity'] = $value['anonymity'];
					if($value['anonymity']>0){
						include_once libfile('function/repeats','plugin/repeats');
						$repeatsinfo=getforuminfo($value[anonymity]);
						$setarr['fid']=$repeatsinfo['fid'];
					}
					$title=$value['subject'];
					$message=$value['message'];
					if($atjson){
						if($postmessage){
							$messageatarr=parseat1(getstr($postmessage,0,1,1,0,0,-1),$value['uid'],$atjson);
							$atarr=parseat1(getstr($postmessage, 300, 1, 1, 0, 2, -1,'<!--more-->'),$value['uid'],$atjson);
							$atarr[$message]=str_replace('&nbsp;','',$atarr[$message]);
						}
					}else{
						$messageatarr=parseat(getstr($value['message'],0,1,1,0,0,-1),$value['uid']);
						$atarr=parseat(getstr($value['message'], 300, 1, 1, 0, 2, -1,'<!--more-->'),$value['uid']);
					}
					if($messageatarr['atfids']){
						$setarr['sharetofids'] =",".implode(',',$messageatarr['atfids']).",";
					}
					$url = "home.php?mod=space&uid=$value[uid]&do=blog&id=$value[blogid]";
					if($value['friend'] == 4) {
						$setarr['title_template'] = 'feed_blog_password';
						$setarr['title_data'] = array('subject' => "<a href=\"$url\">$value[subject]</a>");
					} else {
						if($value['pic']) {
							$setarr['image_1'] = pic_cover_get($value['pic'], $value['picflag']);
							$setarr['image_1_link'] = $url;
						}
						$setarr['title_template'] = 'feed_blog_title';
						$setarr['body_template'] = 'feed_blog_body';
						$value['message'] = preg_replace("/&[a-z]+\;/i", '', $value['message']);
						$setarr['body_data'] = array(
							'subject' => "<a href=\"$url\">$value[subject]</a>",
							'summary' => $atarr[message]
						);
					}
				}
			}
			break;
		case 'albumid':
			$key = 1;
			if($id > 0) {
				$query = DB::query("SELECT a.username, a.albumname, a.picnum, a.friend, a.target_ids, p.* FROM ".DB::table('home_pic')." p
					LEFT JOIN ".DB::table('home_album')." a ON a.albumid=p.albumid
					WHERE p.albumid='$id' ORDER BY dateline DESC LIMIT 0,5");
				while ($value = DB::fetch($query)) {
					if($value['friend'] <= 2) {
						if(empty($setarr['icon'])) {
							$setarr['icon'] = 'album';
							$setarr['id'] = $value['albumid'];
							$setarr['idtype'] = $idtype;
							$setarr['uid'] = $value['uid'];
							$setarr['username'] = $value['username'];
							$setarr['dateline'] = $value['dateline'];
							$setarr['target_ids'] = $value['target_ids'];
							$setarr['friend'] = $value['friend'];
							$setarr['title_template'] = 'feed_album_title';
							$setarr['body_template'] = 'feed_album_body';
							$setarr['body_data'] = array(
								'album' => "<a href=\"home.php?mod=space&uid=$value[uid]&do=album&id=$value[albumid]\">$value[albumname]</a>",
								'picnum' => $value['picnum']
							);
						}
						$setarr['image_'.$key] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
						$setarr['image_'.$key.'_link'] = "home.php?mod=space&uid=$value[uid]&do=album&picid=$value[picid]";
						$key++;
					} else {
						break;
					}
				}
				if($key>4){
				}else{
					for($key;$key<5;$key++){
						$setarr['image_'.$key] = '';
						$setarr['image_'.$key.'_link'] = '';
					}
				}
			}
			break;
		case 'picid':
			$plussql = $id>0?"p.picid='$id'":"p.uid='$_G[uid]' ORDER BY dateline DESC LIMIT 1";
			$query = DB::query("SELECT p.*, a.friend, a.target_ids FROM ".DB::table('home_pic')." p
				LEFT JOIN ".DB::table('home_album')." a ON a.albumid=p.albumid WHERE $plussql");
			if($value = DB::fetch($query)) {
				if(empty($value['friend'])) {
					$setarr['icon'] = 'album';
					$setarr['id'] = $value['picid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					$url = "home.php?mod=space&uid=$value[uid]&do=album&picid=$value[picid]";
					$setarr['image_1'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
					$setarr['image_1_link'] = $url;
					$setarr['title_template'] = 'feed_pic_title';
					$setarr['body_template'] = 'feed_pic_body';
					$setarr['body_data'] = array('title' => $value['title']);
				}
			}
			break;
	}

	if($setarr['icon']) {
		$setarr['title_template'] = $setarr['title_template']?lang('feed', $setarr['title_template']):'';
		$setarr['body_template'] = $setarr['body_template']?lang('feed', $setarr['body_template']):'';
		$setarr['body_general'] = $setarr['body_general']?lang('feed', $setarr['body_general']):'';
		$setarr['fromwhere']= $fromwhere;
		$setarr['title_data']['hash_data'] = "{$idtype}{$id}";
		$setarr['title_data'] = serialize($setarr['title_data']);
		$setarr['body_data'] = serialize($setarr['body_data']);
		$setarr = daddslashes($setarr);

		$feedid = 0;
		if(!$add && $setarr['id']) {
			$query = DB::query("SELECT feedid FROM ".DB::table('home_feed')." WHERE id='$id' AND idtype='$idtype'");
			$feedid = DB::result($query, 0);
		}
		if($feedid) {
			DB::update('home_feed', $setarr, array('feedid'=>$feedid));
		} else {
			DB::insert('home_feed', $setarr);
			$newfeedid=DB::insert_id();
			$tagidarr=parsetag($title,$message,$idtype,$newfeedid);
			if($idtype=='blogid'&&$tagidarr){
				DB::insert('blog_tag',array('blogid'=>$id,'tags'=>','.implode(',',array_keys($tagidarr)).','));
			}
			if($atarr[atuids]){
				atrecord($atarr[atuids],$newfeedid);
			}
			if($messageatarr[atuids]){
				foreach(array_keys($messageatarr[atuids]) as $uidkey){
					notification_add($messageatarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在其<a href="home.php?view=atme">发表的内容</a>中提到了您，赶快去看看吧', array(), 0);
				}
			}
			
		}
	}

}

function feed_activity_user($activityuser){
    $groupfeedlist = array();
    if($activityuser) {
        $query = DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(dateline) WHERE uid IN (".dimplode($activityuser).") ORDER BY dateline desc LIMIT 0, 5");
        while($feed = DB::fetch($query)) {
            if($feed['friend'] == 0) {
                $groupfeedlist[] = mkfeed($feed);
            }
        }
    }
    return $groupfeedlist;
}

function feed_group($uid){
    //查询用户最活跃的专区
    $query = DB::query("SELECT fid FROM ".DB::table("forum_groupuser")." WHERE uid=".$uid." ORDER BY lastupdate LIMIT 0, 100");
    $fids = array();
    while($row = DB::fetch($query)){
        $fids[] = $row["fid"];
    }
    if($fids){
        //查询专区内最新活动的用户
        $query = DB::query("SELECT uid FROM ".DB::table("forum_groupuser")." WHERE fid IN (".dimplode($fids).") ORDER BY lastupdate LIMIT 0, 100");
        $uids = array();
        while($row=DB::fetch($query)){
            $uids[] = $row["uid"];
        }
    }
    if($uids){
        $sql = "uid IN (".dimplode($uids).")";
    }
    return $sql;
}


//查询用户所参加的专区或活动
//返回 专区id数组
function  join_groups($uid){
	$query = DB::query("SELECT fid FROM ".DB::table("forum_groupuser")." WHERE uid=".$uid." ORDER BY lastupdate ");
    $fids = array();
    while($row = DB::fetch($query)){
        $fids[] = $row["fid"];
    }
    return $fids;
}

//查询浏览权限为所有人的专区或活动 (活动包括继承专区的并且为所有人)
function open_groups(){
	$query = DB::query("SELECT distinct f1.fid FROM ".DB::table("forum_forumfield")." as  f1 LEFT JOIN " . DB::table('forum_forum') . " as f ON f.fid=f1.fid , ".DB::table("forum_forumfield")." as f2 WHERE f.status=3 AND f1.gviewperm=1 OR ( f1.gviewperm=3 AND f.type='activity'AND f.fup=f2.fid AND f2.gviewperm=1 ) ORDER BY fid ");
	$fids = array();
    while($row = DB::fetch($query)){
        $fids[] = $row["fid"];
    }
    return $fids;
	
	
	
}

?>