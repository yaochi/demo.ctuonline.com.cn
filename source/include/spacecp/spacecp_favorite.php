<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_share.php 7910 2010-04-15 01:55:08Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['op'] == 'delete') {

	$favid = intval($_GET['favid']);
	$thevalue = DB::fetch_first('SELECT * FROM '.DB::table('home_favorite')." WHERE favid='$favid'");
	if(empty($thevalue) || $thevalue['uid'] != $_G['uid']) {
		showmessage('favorite_does_not_exist');
	}

	if(submitcheck('deletesubmit')) {
		DB::query('DELETE FROM '.DB::table('home_favorite')." WHERE favid='$favid'");
		showmessage('do_success', 'home.php?mod=space&do=favorite&view=me&type='.$_GET['type'].'&quickforward=1', array('favid' => $favid), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
	}

} else {

	ckrealname('favorite');

	ckvideophoto('favorite');

	cknewuser();

	$type = empty($_GET['type']) ? '' : $_GET['type'];
	$feedid=empty($_GET['feedid']) ? '' : $_GET['feedid'];
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$spaceuid = empty($_GET['spaceuid']) ? 0 : intval($_GET['spaceuid']);
	$idtype = $title = $icon = '';
	switch($type) {
		case 'thread':
			$idtype = 'tid';
			$title = DB::result_first('SELECT subject FROM '.DB::table('forum_thread')." WHERE tid='$id'");
			$icon = '<img src="static/image/feed/thread.gif" alt="thread" class="vm" /> ';
			break;
		case 'poll':
			$idtype = 'pollid';
			$title = DB::result_first('SELECT subject FROM '.DB::table('forum_thread')." WHERE tid='$id'");
			$icon = '<img src="static/image/feed/poll.gif" alt="thread" class="vm" /> ';
			break;
		case 'forum':
			$idtype = 'fid';
			$title = DB::result_first('SELECT `name` FROM '.DB::table('forum_forum')." WHERE fid='$id' AND status !='3'");
			$icon = '<img src="static/image/feed/discuz.gif" alt="forum" class="vm" /> ';
			break;
		case 'blog';
			$idtype = 'blogid';
			$title = DB::result_first('SELECT subject FROM '.DB::table('home_blog')." WHERE blogid='$id' AND uid='$spaceuid'");
			$icon = '<img src="static/image/feed/blog.gif" alt="blog" class="vm" /> ';
			break;
		case 'group';
			$idtype = 'gid';
			$title = DB::result_first('SELECT `name` FROM '.DB::table('forum_forum')." WHERE fid='$id' AND status ='3'");
			$icon = '<img src="static/image/feed/group.gif" alt="group" class="vm" /> ';
			break;
		case 'album';
			$idtype = 'albumid';
			$title = DB::result_first('SELECT albumname FROM '.DB::table('home_album')." WHERE albumid='$id' AND uid='$spaceuid'");
			$icon = '<img src="static/image/feed/album.gif" alt="album" class="vm" /> ';
			break;
		case 'space';
			$idtype = 'uid';
			$title = DB::result_first('SELECT username FROM '.DB::table('common_member')." WHERE uid='$id'");
			$icon = '<img src="static/image/feed/profile.gif" alt="space" class="vm" /> ';
			break;
        case 'galbum': // 专区相册
        	$idtype = 'galbumid';
        	$title = DB::result_first('SELECT albumname FROM '.DB::table('group_album')." WHERE albumid='$id'");
        	$icon = '<img src="static/image/plugins/groupalbum2.gif" alt="album" class="vm" /> ';
        	break;
        case 'gpic'://专区图片
        	$idtype = 'gpicid';
        	$query = DB::query('SELECT a.albumname, p.title FROM '.DB::table('group_album')." a, ".DB::table('group_pic')." p WHERE a.albumid=p.albumid AND p.picid='$id'");
        	$ptitle = DB::fetch($query);
        	if($ptitle){
        		$title = $ptitle['title'];
        		if(empty($title)){
        			$title = $ptitle['albumname']."的图片";
        		}
        	}
        	$icon = '<img src="static/image/plugins/groupalbum2.gif" alt="pic" class="vm" /> ';
        	break;
        case 'nwkt': // 你我课堂
        	$idtype = 'nwktid';
        	$title = DB::result_first('SELECT subject FROM '.DB::table('home_nwkt')." WHERE nwktid='$id'");
        	$icon = '<img src="static/image/feed/nwkt.gif" alt="nwkt" class="vm" /> ';
        	break;
        case 'gnotice': // 通知公告
        	$idtype = 'noticeid';
        	$title = DB::result_first('SELECT title FROM '.DB::table('notice')." WHERE id='$id'");
        	$icon = '<img src="static/image/feed/notice.gif" alt="notice" class="vm" /> ';
        	break;
        case 'glive'://直播
        	$idtype = 'gliveid';
        	$newlive=empty($_GET['newlive'])?0:intval($_GET['newlive']);
        	if($newlive) $sql="SELECT * FROM ".DB::table('group_live')." WHERE newliveid='$id';";
        	else $sql="SELECT * FROM ".DB::table('group_live')." WHERE liveid='$id';";
        	
        	$live = DB::fetch(DB::query($sql));
        	$id=$live[liveid];
        	$title = $live[subject];
        	if($live[newliveid]) $titlelink=$_G[config][expert][liveurl]."/live/beforelive.do?action=start&liveid=$live[newliveid]&fid=$live[fid]";
        	else $titlelink=$live[url];
        	$icon = '<img src="static/image/plugins/grouplive.gif" alt="nwkt" class="vm" /> ';
        	break;
        case 'doc'://文档
        	$idtype = 'feed';
        	
        	include_once libfile("function/doc");
        	$doc = getFile($id);
			if($doc && !empty($doc['id'])){
				$doc['uid'] = $doc['userid'];
			}else{
				showmessage('view_to_info_did_not_exist');	
			}
			$spaceuid = $doc['uid'];
			$title = $doc['title'];
        	
	        $icon = '<img src="static/image/plugins/groupdoc.gif" alt="doc" class="vm" /> ';
			
			$body_data= array(
				'subject' => "<a href=\"$doc[titlelink]\">$doc[title]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$doc[userid]\">$doc[username]</a>",
				'message' => getstr($doc['context'], 150, 0, 1, 0, 0, -1));
			if($doc[type]=='1'){	
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='doc' and idtype='doc' and id='".$id."'");
				if(!$feedid){
					$docfeed = array(
						'appid' => 0,
						'icon' => 'doc',
						'id'=>$id,
						'idtype'=>'doc',
						'body_template'=> '<b>{subject}</b><br>{author}<br>{message}',
						'body_data' => serialize(dstripslashes($body_data)),
						'image_1' => $doc[imglink],
						'image_1_link' => $doc[titlelink],
						'fromwhere'=>'2'
					);
					$feedid=DB::insert('home_feed',$docfeed,1);
				}	
			}elseif($doc[type]=='2'){
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='case' and idtype='case' and id='".$id."'");
				if(!$feedid){
					$casefeed = array(
						'appid' => 0,
						'icon' => 'case',
						'id'=>$id,
						'idtype'=>'case',
						'body_template'=> '<b>{subject}</b><br>{author}<br>{message}',
						'body_data' => serialize(dstripslashes($body_data)),
						'image_1' => $doc[imglink],
						'image_1_link' => $doc[titlelink],
						'fromwhere'=>'2'
					);
					$feedid=DB::insert('home_feed',$casefeed,1);
				}	
			}
        	break;
        case 'class'://课程
        	$idtype = 'feed';        	
        	include_once libfile("function/doc");
        	$doc = getFile($id);
			if($doc && !empty($doc['id'])){
				$doc['uid'] = $doc['userid'];
			}else{
				showmessage('view_to_info_did_not_exist');	
			}
			$spaceuid = $doc['uid'];
			$title = $doc['title'];        	
	        $icon = '<img src="static/image/plugins/resourcelist.gif" alt="class" class="vm" /> ';
			$body_data= array(
				'subject' => "<a href=\"$doc[titlelink]\">$doc[title]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$doc[userid]\">$doc[username]</a>",
				'message' => getstr($doc['context'], 150, 0, 1, 0, 0, -1));
			$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='class' and idtype='class' and id='".$id."'");
			if(!$feedid){
				$classfeed = array(
					'appid' => 0,
					'icon' => 'class',
					'id'=>$id,
					'idtype'=>'class',
					'body_template'=> '<b>{subject}</b><br>{author}<br>{message}',
					'body_data' => serialize(dstripslashes($body_data)),
					'image_1' => $doc[imglink],
					'image_1_link' => $doc[titlelink],
					'fromwhere'=>'2'
				);
				$feedid=DB::insert('home_feed',$classfeed,1);
			}	
        	break;
        case 'activity':
            $idtype = 'activityid';
            $title = DB::result_first('SELECT name FROM '.DB::table('forum_forum')." WHERE fid='$id'");
	}
	if(empty($idtype) || empty($title)) {
		showmessage('favorite_cannot_favorite');
	}

	$thevalue = DB::fetch_first('SELECT * FROM '.DB::table('home_favorite')." WHERE uid='$_G[uid]' AND idtype='$idtype' AND id='$id'");
	$description = !empty($thevalue) ? $thevalue['description'] : '';
	$description_show = nl2br($description);

	//if(submitcheck('favoritesubmit')) {
		$arr = array(
			'uid' => intval($_G['uid']),
			'idtype' => $idtype,
			'feedid'=>$feedid,
			'id' => $id,
			'spaceuid' => $spaceuid,
			'title' => getstr($title, 255, 0, 1),
			'description' => getstr($_POST['description'], '', 1, 1, 1),
			'dateline' => time()
		);
		if($thevalue) {
			DB::update('home_favorite', $arr, array('favid'=>intval($thevalue['favid'])));
		} else {
			DB::insert('home_favorite', $arr);
			DB::query("update ".DB::TABLE("home_feed")." set favorites=favorites+1  where feedid=".$feedid);
		}
		if($idtype == 'docid'||$idtype == 'classid'){
			include_once libfile("function/doc");
			addFavoriteAmount($id);
			showmessage('favorite_do_success', "home.php?mod=space&do=favorite", array(), array('showdialog'=>true, 'closetime'=>1));
		}else{
			showmessage('favorite_do_success', "home.php?mod=space&do=favorite", array(), array('showdialog'=>true, 'closetime'=>1));
		}
	//}
}

include template('home/spacecp_favorite');


?>