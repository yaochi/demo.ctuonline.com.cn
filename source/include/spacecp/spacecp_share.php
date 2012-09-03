<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_share.php 11252 2010-05-27 06:09:51Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sid = intval($_GET['sid']);

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		require_once libfile('function/delete');
		deleteshares(array($sid));
		showmessage('do_success', $_GET['type']=='view'?'home.php?mod=space&quickforward=1&do=share':dreferer(), array('sid' => $sid), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 1));
	}
} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('manageshare')) {
		showmessage('no_privilege');
	}

	if($sid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$sid'");
		if(!$share = DB::fetch($query)) {
			showmessage('no_privilege');
		}
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_share', array('hot'=>$_POST['hot']), array('sid'=>$sid));
		DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$sid, 'idtype'=>'sid'));

		showmessage('do_success', dreferer());
	}

} else {

	if(!checkperm('allowshare')) {
		showmessage('no_privilege');
	}
	ckrealname('share');

	ckvideophoto('share');

	cknewuser();
	
	if(empty($_GET['to'])) $_GET['to'] = 'all';
	$to=$_GET['to'];
	$actives = array($to => ' class="a"');

	$type = empty($_GET['type'])?'':$_GET['type'];
	$id = empty($_GET['id'])?0:intval($_GET['id']);
	$note_uid = 0;
	$note_message = '';
	$note_values = array();

	$hotarr = array();

	$arr = array();
	$feed_hash_data = '';
	
	$subject = '';
	$subjectlink = '';
	$authorid = '';
	$author = '';
	$message = '';
	$image = '';
	$sharemessage='';
	$fid='';

	//分享文档、课程、活动、问吧、投票、相册、图片、资源列表、问卷、直播、你我课堂特殊处理
	if($type=='activity' || $type=='class' || $type=='doc'|| $type=='case' || $type=='gpic' || $type=='galbum' || $type=='questionary' || $type=='poll' || $type=='question' || $type=='resourceid' || $type=='glive' || $type=='nwkt'||$type=='group'||$type=='noticeid'){
		$subject = empty($_GET['subject'])?'':$_GET['subject'];
		$subjectlink = empty($_GET['subjectlink'])?'':$_GET['subjectlink'];
		$authorid = empty($_GET['authorid'])?'':$_GET['authorid'];
		$author = empty($_GET['author'])?'':$_GET['author'];
		$message = empty($_GET['message'])?'':$_GET['message'];
		$image = empty($_GET['image'])?'':$_GET['image'];
		$fid = empty($_GET['fid'])?'':$_GET['fid'];
		if(empty($subject)){$subject = empty($_POST['subject'])?'':$_POST['subject'];}else{$sharemessage.=("&subject=".$subject);$subject=base64_decode(str_replace(" ","+",$subject));}
		if(empty($subjectlink)){$subjectlink = empty($_POST['subjectlink'])?'':$_POST['subjectlink'];}else{$sharemessage.=("&subjectlink=".$subjectlink);$subjectlink=base64_decode(str_replace(" ","+",$subjectlink));}
		if(empty($authorid)){$authorid = empty($_POST['authorid'])?'':$_POST['authorid'];}else{$sharemessage.=("&authorid=".$authorid);$authorid=$authorid;}
		if(empty($author)){$author = empty($_POST['author'])?'':$_POST['author'];}else{$sharemessage.=("&author=".$author);$author=base64_decode(str_replace(" ","+",$author));}
		if(empty($message)){$message = empty($_POST['message'])?'':$_POST['message'];}else{$sharemessage.=("&message=".$message);$message=base64_decode(str_replace(" ","+",$message));}
		if(empty($image)){$image = empty($_POST['image'])?'':$_POST['image'];}else{$sharemessage.=("&image=".$image);$image=base64_decode(str_replace(" ","+",$image));}
		if(empty($fid)){$fid = empty($_POST['fromfid'])?'':$_POST['fromfid'];}else{$sharemessage.=("&fid=".$fid);$fid=$fid;}
	}
	$atjson=$_POST['atjson'];
	$confirm=empty($_POST['confirm'])?'no':$_POST['confirm'];
	
	switch ($type) {
		//用户
		case 'space':

			$feed_hash_data = "uid{$id}";

			if($id == $space['uid']) {
				showmessage('share_space_not_self');
			}

			$tospace = getspace($id);
			if(empty($tospace)) {
				showmessage('space_does_not_exist');
			}
			if(isblacklist($tospace['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['title_template'] = lang('spacecp', 'share_space');
			$arr['body_template'] = '<b>{username}</b><br>{reside}<br>{spacenote}';
			$arr['body_data'] = array(
			'username' => "<a href=\"home.php?mod=space&uid=$id\">".$tospace['username']."</a>",
			'reside' => $tospace['resideprovince'].$tospace['residecity'],
			'spacenote' => $tospace['spacenote']
			);

			loaducenter();
			$isavatar = uc_check_avatar($id);
			$arr['image'] = $isavatar?avatar($id, 'middle', true):UC_API.'/images/noavatar_middle.gif';
			$arr['image_link'] = "home.php?mod=space&uid=$id";

			$note_uid = $id;
			$note_message = 'share_space';

			break;
		//日志
		case 'blog':

			$feed_hash_data = "blogid{$id}";

			$query = DB::query("SELECT b.*,bf.message,bf.hotuser FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			if(!$blog = DB::fetch($query)) {
				showmessage('blog_does_not_exist');
			}
			if($blog['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			if($blog['friend']) {
				showmessage('logs_can_not_share');
			}
			if(isblacklist($blog['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['title_template'] = lang('spacecp', 'share_blog');
			$arr['body_template'] = '<b>{subject}</b><br>{username}<br>{message}';
			$arr['body_data'] = array(
			'subject' => "<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]\">$blog[subject]</a>",
			'username' => "<a href=\"home.php?mod=space&uid=$blog[uid]\">".user_get_user_name_by_username($blog['username'])."</a>",
			'message' => getstr($blog['message'], 150, 0, 1, 0, 0, -1)
			);
			if($blog['pic']) {
				$arr['image'] = pic_cover_get($blog['pic'], $blog['picflag']);
				$arr['image_link'] = "home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]";
			}
			$note_uid = $blog['uid'];
			$note_message = 'share_blog';
			$note_values = array('url'=>"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]", 'subject'=>$blog['subject'], 'from_id' => $id, 'from_idtype' => 'blogid');

			$hotarr = array('blogid', $blog['blogid'], $blog['hotuser']);
			
			$subject=$blog[subject];
			$subjectlink="home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]";

			break;
		//个人相册
		case 'album':

			$feed_hash_data = "albumid{$id}";

			$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$id'");
			if(!$album = DB::fetch($query)) {
				showmessage('album_does_not_exist');
			}
			if($album['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			if($album['friend']) {
				showmessage('album_can_not_share');
			}
			if(isblacklist($album['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['title_template'] =  lang('spacecp', 'share_album');
			$arr['body_template'] = '<b>{albumname}</b><br>{username}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\">$album[albumname]</a>",
				'username' => "<a href=\"home.php?mod=space&uid=$album[uid]\">".user_get_user_name_by_username($album['username'])."</a>"
			);
			$arr['image'] = pic_cover_get($album['pic'], $album['picflag']);
			$arr['image_link'] = "home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]";
			$note_uid = $album['uid'];
			$note_message = 'share_album';
			$note_values = array('url'=>"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]", 'albumname'=>$album['albumname'], 'from_id' => $id, 'from_idtype' => 'albumid');


			$subject=$album[albumname];
			$subjectlink="home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]";
			break;
		//社区相册
		case 'galbum':

			$feed_hash_data = "albumid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_album');
			$arr['body_template'] = '<b>{albumname}</b><br>{username}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"$subjectlink\">$subject</a>",
				'username' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>"
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_galbum';
			$note_values = array('url'=>$subjectlink, 'albumname'=>$subject, 'from_id' => $id, 'from_idtype' => 'albumid'); 	
        	
			break;
		//个人图片
		case 'pic':

			$feed_hash_data = "picid{$id}";

			$query = DB::query("SELECT  pf.*, pic.*
				FROM ".DB::table('home_pic')." pic
				LEFT JOIN ".DB::table('home_picfield')." pf ON pf.picid=pic.picid
				WHERE pic.picid='$id'");
			if(!$pic = DB::fetch($query)) {
				showmessage('image_does_not_exist');
			}
			/*if($pic['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}*/
			if($pic['friend']) {
				showmessage('image_can_not_share');
			}
			if(isblacklist($pic['uid'])) {
				showmessage('is_blacklist');
			}
			if(empty($pic['albumid'])) $pic['albumid'] = 0;
			//if(empty($pic['albumname'])) $pic['albumname'] = lang('spacecp', 'default_albumname');


			$arr['title_template'] = lang('spacecp', 'share_image');
			$arr['body_template'] = '图片:<br>{username}<br>{title}';
			$arr['body_data'] = array(
			'username' => "<a href=\"home.php?mod=space&uid=$pic[uid]\">".user_get_user_name_by_username($pic['username'])."</a>",
			'title' => getstr($pic['title'], 100, 0, 1, 0, 0, -1)
			);
			if(strpos($pic['filepath'],'attachment/album')){
				$filepath=explode('.',$pic['filepath']);
				$arr['image'] =$filepath[0].'.thumb.'.$pic[type];
			}else{
				$arr['image']  = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
			}
			$arr['image_link'] = "home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]&diymode=1";
			$note_uid = $pic['uid'];
			$note_message = 'share_pic';
			$note_values = array('url'=>"home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]", 'albumname'=>$pic['albumname'], 'from_id' => $id, 'from_idtype' => 'picid');

			$hotarr = array('picid', $pic['picid'], $pic['hotuser']);
			
			$subject=$pic[albumname];
			$subjectlink="home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]";
			break;
		//专区图片
		case 'gpic':

			$feed_hash_data = "picid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_image');
			$arr['body_template'] = lang('spacecp', 'album').': <b>{albumname}</b><br>{username}<br>{title}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"$subjectlink\">$subject</a>",
				'username' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'title' => ""
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_gpic';
			$note_values = array('url'=>$subjectlink, 'albumname'=>$subject, 'from_id' => $id, 'from_idtype' => 'picid'); 	
        	
			break;
		//话题
		case 'thread':

			$feed_hash_data = "tid{$id}";

			$actives = array('share' => ' class="active"');

			$thread = DB::fetch(DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$id'"));
			$fid = $thread['fid'];
			$posttable = getposttable('p');
			$post = DB::fetch(DB::query("SELECT * FROM ".DB::table($posttable)." WHERE tid='$id' AND first='1'"));
            //add by qiaoyz,2011-3-8,15:49 EKSN 114 删除帖子动态和分享中“本主题最后由 XX 于 XXXX-XX-XX XX:XX 编辑”的字样
				$str=substr($post['message'],0,5);
				if($str=="[i=s]"){
				 	$str1=strstr($post['message'],'[/i]');
					if($str1!=false){$post['message']=substr($str1,4);}
			    }
            
            
            $post['message']=preg_replace("/&nbsp;/","",$post['message']);
			$arr['title_template'] = lang('spacecp', 'share_thread');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$thread[realname] = user_get_user_name_by_username($thread[author]);
			$arr['body_data'] = array(
				'subject' => "<a href=\"forum.php?mod=viewthread&tid=$id\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[realname]</a>",
				'message' => getstr($post['message'], 150, 0, 1, 0, 0, -1,'<!--more-->')
			);
			
			$note_uid = $thread['authorid'];
			$note_message = 'share_thread';
			$note_values = array('url'=>"forum.php?mod=viewthread&tid=$id", 'subject'=>$thread['subject'], 'from_id' => $id, 'from_idtype' => 'tid');
			
			$subject=$thread[subject];
			$subjectlink="forum.php?mod=viewthread&tid=$id";
			
			break;
        //文档
        case 'doc': 
        	
        	$feed_hash_data = "docid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_doc');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_doc';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'docid'); 
			
			$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='doc' and idtype='doc' and id='".$id."'");
			if(!$feedid){
				$resdocfeed = array(
					'appid' => 0,
					'icon' => 'doc',
					'id'=>$id,
					'idtype'=>'doc',
					'body_template'=> $arr['body_template'],
					'body_data' => serialize(dstripslashes($arr['body_data'])),
					'image_1' => $image,
					'image_1_link' => $subjectlink,
					'fromwhere'=>'2'
				);
				$feedid=DB::insert('home_feed',$resdocfeed,1);
			}	
        	break;
		case 'case': 
        	
        	$feed_hash_data = "caseid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_case');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_case';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'docid'); 
			
			$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='case' and idtype='case' and id='".$id."'");
			if(!$feedid){
				$casefeed = array(
					'appid' => 0,
					'icon' => 'case',
					'id'=>$id,
					'idtype'=>'case',
					'body_template'=> $arr['body_template'],
					'body_data' => serialize(dstripslashes($arr['body_data'])),
					'image_1' => $image,
					'image_1_link' => $subjectlink,
					'fromwhere'=>'2'
				);
				$feedid=DB::insert('home_feed',$casefeed,1);
			}	
        	break;
        //课程
        case 'class':
			
        	$feed_hash_data = "classid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_class');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_class';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'classid');
			
        	$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='class' and idtype='class' and id='".$id."'");
			if(!$feedid){
				$classfeed = array(
					'appid' => 0,
					'icon' => 'class',
					'id'=>$id,
					'body_template' => $arr['body_template'],
					'body_data' => serialize(dstripslashes($arr['body_data'])),
					'image_1' => $image,
					'image_1_link' => $subjectlink,
					'idtype'=>'class',
					'fromwhere'=>'2'
				);
				$feedid=DB::insert('home_feed',$classfeed,1);
			}
			
        	break;
        //活动
        case 'activity':
        	
        	$feed_hash_data = "activityid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_activity');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_activity';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'activityid');
        	break;
        //问卷
        case 'questionary':
        	
        	$feed_hash_data = "questionaryid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_questionary');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}

			$note_uid = $authorid;
			$note_message = 'share_questionary';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'questionaryid');
        	break;
        //投票
        case 'poll':
      		
        	$feed_hash_data = "pollid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_poll');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_poll';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'pollid');
        	
        	break;
        //问吧
        case 'question':
        	
        	$feed_hash_data = "questionid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_question');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_question';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'questionid');
        	        	
        	break;	
        //资源列表
        case 'resourceid':
        	
        	$feed_hash_data = "resourceid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_resourceid');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_resourceid';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'resourceid');
        	
        	break;
        //直播
        case 'glive':
        	$message = empty($_G['gp_message'])?'':$_G['gp_message'];
        	$newlive=empty($_GET['newlive'])?0:intval($_GET['newlive']);
        	if($newlive) $sql="SELECT * FROM ".DB::table('group_live')." WHERE newliveid='$id';";
        	else $sql="SELECT * FROM ".DB::table('group_live')." WHERE liveid='$id';";
        	
        	$live = DB::fetch(DB::query($sql));
        	
        	if($live[newliveid]) $subjectlink=$_G[config][expert][liveurl]."/live/beforelive.do?action=start&liveid=$live[newliveid]&fid=$live[fid]";
        	else $subjectlink=$live[url];
        	$feed_hash_data = "gliveid{$live[liveid]}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_glive');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$live[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$live[uid]\">".user_get_user_name_by_username($live[username])."</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $live[uid];
			$note_message = 'share_glive';
			$note_values = array('url'=>$subjectlink, 'subject'=>$live[subject], 'from_id' => $live[liveid], 'from_idtype' => 'gliveid');
        	$id=$live[liveid];
        	break;
		//通知公告
		case 'noticeid':
        	
        	$feed_hash_data = "noticeid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_notice');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">".user_get_user_name_by_username($author)."</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_notice';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'noticeid');
        	
        	break;
        //你我课堂
        case 'nwkt':
        	
        	$feed_hash_data = "nwktid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_nwkt');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_nwkt';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'nwktid');
        	
        	break;
        	//begin，update by qiaoyongzhi ,2011-2-25 EKSN 72 专区可分享
        	//专区
        case 'group':
        	$feed_hash_data = "groupid{$id}";
        	
        	$arr['title_template'] = lang('spacecp', 'share_group');
			$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"$subjectlink\">$subject</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$authorid\">$author</a>",
				'message' => getstr($message, 150, 0, 1, 0, 0, -1)
			);
			if($image) {
				$arr['image'] = $image;
				$arr['image_link'] = $subjectlink;
			}
			$note_uid = $authorid;
			$note_message = 'share_group';
			$note_values = array('url'=>$subjectlink, 'subject'=>$subject, 'from_id' => $id, 'from_idtype' => 'activityid');
        	break;
		//end
		case 'share':
			require_once libfile('function/share');
			$oldarr=DB::fetch_first("select * from ".DB::TABLE("home_share")." where sid=$id");
			$oldarr=mkshare($oldarr);
			$arr=$oldarr;
			//根据不同类型加载不同模板
			if($oldsrr[type]=='gpic'||$oldsrr[type]=='pic'){
				$arr['body_template'] = lang('spacecp', 'album').': <b>{albumname}</b><br>{username}<br>{title}';
			}elseif($oldsrr[type]=='galbum'||$oldsrr[type]=='album'){
				$arr['body_template'] = '<b>{albumname}</b><br>{username}';
			}elseif($oldsrr[type]=='blog'){
				$arr['body_template'] = '<b>{subject}</b><br>{username}<br>{message}';
			}elseif($oldsrr[type]=='flash'||$oldsrr[type]=='video'||$oldsrr[type]=='music'||$oldsrr[type]=='link'){
				$arr['body_template'] = "{link}";
			}else{
				$arr['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
			}
			$arr[sid]='';
			break;
		//网址、flash、音频、视频分享
		default:

			//$actives = array('share' => ' class="active"');

			$_G['refer'] = 'home.php?mod=space&do=share&view=me';
			$type = 'link';
			//$_GET['op'] = 'link';
			$linkdefault = 'http://';
			$generaldefault = '';
			if(empty($_G['gp_handlekey']))
				$_G['gp_handlekey']="fwin_".$_POST['handlekey'];
			
			if("sure" != $confirm){
				$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$id'");
				$linkshare= DB::fetch($query);
				require_once libfile('function/share');
				$linkshare = mkshare($linkshare);
				
				if("video"==$linkshare['type']){
					$arr['title_template'] = lang('spacecp', 'share_video');
				}else if("music"==$linkshare['type']){
					$arr['title_template'] = lang('spacecp', 'share_music');
				}else if("flash"==$linkshare['type']){
					$arr['title_template'] = lang('spacecp', 'share_flash');
				}else {
					$arr['title_template'] = lang('spacecp', 'share_link');
				}
				$arr['body_template'] = $linkshare['body_template'];
				$arr['body_data'] = $linkshare['body_data'];

				$type="link";
			}
			
			break;
	}

	if(submitcheck('sharesubmit', 0, $seccodecheck, $secqaacheck)) {
		if($type == 'link'  && "sure" == $confirm) {
			$link = dhtmlspecialchars(trim($_POST['link']));
			if($link) {
				if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
				showmessage('url_incorrect_format');
			}
			$arr['title_template'] = lang('spacecp', 'share_link');
			$arr['body_template'] = '{link}';

			$link_text = sub_url($link, 45);

			$arr['body_data'] = array('link'=>"<a href=\"$link\" target=\"_blank\">$link_text</a>", 'data'=>$link);
			$parseLink = parse_url($link);
			require_once libfile('function/discuzcode');
			$flashvar = parseflv($link);
			if(empty($flashvar) && preg_match("/\.flv$/i", $link)) {
				$flashvar = array(
					'flv' => IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($link),
					'imgurl' => ''
				);
			}
			//视频分享
			if(!empty($flashvar)) {
				$arr['title_template'] = lang('spacecp', 'share_video');
				$type = 'video';
				$arr['body_data']['flashvar'] = $flashvar['flv'];
				$arr['body_data']['host'] = 'flash';
				$arr['body_data']['imgurl'] = $flashvar['imgurl'];
			}
			//mp3分享
			if(preg_match("/\.(mp3|wma)$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_music');
				$arr['body_data']['musicvar'] = $link;
				$type = 'music';
			}
			//flash分享
			if(preg_match("/\.swf$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_flash');
				$arr['body_data']['flashaddr'] = $link;
				$type = 'flash';
			}
		}

		//分享网址、视频、音频、flash
		if(("link"==$type || "video"==$type || "music"==$type || "flash"==$type) &&  "sure" == $confirm){
			
			$arr['type'] = $type;
			$arr['uid'] = $_G['uid'];
			$arr['username'] = $_G['username'];
			$arr['dateline'] = $_G['timestamp'];
			$arr['status'] = 0;
			
			//保存分享基本信息
			$tempArry=$arr['body_data'];
			$arr['body_data'] = serialize($arr['body_data']);
			$setarr = daddslashes($arr);
			$sid = DB::insert('home_share', $setarr, 1);
			
			$id=$sid;
			$arr['body_data']=$tempArry;
		}
		//分享其它内容
		else{
	
			$arr['body_general'] = getstr($_POST['general'], 150, 1, 1, 1, 1);
			if(!$arr['body_general']){
				if($type=='class'){
					$arr['body_general']='转发课程！';
				}elseif($type=='doc'){
					$arr['body_general']='转发文档！';
				}elseif($type=='case'){
					$arr['body_general']='转发案例！';
				}elseif($type=='group'){
					$arr['body_general']='转发专区！';
				}else{
					$arr['body_general']='转发内容！';
				}
			}
			if($atjson){
				$atarr=parseat1($arr['body_general'],$_G[uid],$atjson);
			}else{
				$atarr=parseat($arr['body_general'],$_G[uid],1);
			}
			
			$arr['body_general']=$atarr['message'];
			if("link"!=$type){
				$arr['type'] = $type;
			}
			if($type==share){
				$arr['type']=$_POST['type'];
				$arr['hot']='0';
			}
			$arr['uid'] = $_G['uid'];
			$arr['username'] = $_G['username'];
			$arr['dateline'] = $_G['timestamp'];
			$arr['status'] = 1;
			//print_r($arr);exit;
			$selectIds=$_POST['selectIds'];
			if(ckprivacy('share', 'feed')) {
				require_once libfile('function/feed');
				$sharetofids=array();
				$idList=preg_split('[,]',$selectIds);
				if($to=="group"){
					$sharetofids=$idList;
				}
				if($arr['type']=='activity'||$arr['type']=='gpic'||$arr['type']=='galbum'||$arr['type']=='questionary'||$arr['type']=='question'||$arr['type']=='group'){
					feed_add('forward',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,0,$arr['type'],0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
					);
				}elseif($arr['type']=='thread'){
					$threadfeed=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where icon='thread' and idtype='tid' and id=".$id." order by dateline desc");
					if($threadfeed){
						feed_add('thread',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,$threadfeed[feedid],'feed',0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
						);
						DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$threadfeed[feedid]);
					}else{
						feed_add('forward',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,0,$arr['type'],0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
						);
					}
				}elseif($arr['type']=='poll'){
					$threadfeed=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where icon='poll' and idtype='tid' and id=".$id." order by dateline desc");
					if($threadfeed){
						feed_add('poll',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						unserialize($threadfeed['body_data']),
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,$threadfeed[feedid],'feed',0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
						);
						DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$threadfeed[feedid]);
					}else{
						feed_add('forward',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,0,$arr['type'],0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
						);
					}
				}elseif($arr['type']=='class'||$arr['type']=='doc'||$arr['type']=='case'){
					DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid);
					$returnfeedid=feed_add($arr['type'],
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',1,$feedid,'feed',0,'',0,
						$atarr['atfids'],get_groupname_by_fid($fid),$fid
					);
				}else{
					feed_add('share',
						'{actor} '.$arr['title_template'],
						array('hash_data' => $feed_hash_data),
						$arr['body_template'],
						$arr['body_data'],
						$arr['body_general'],
						array($arr['image']),
						array($arr['image_link']),
						'','','',0,0,$arr['type'],0,'',0,
						$sharetofids,get_groupname_by_fid($fid),$fid
					);
				}
			}
			//保存分享基本信息
			$arr['body_data'] = serialize($arr['body_data']);
			$setarr = daddslashes($arr);
			$sid="";
			if("link"==$type){
				$sid = $id;
				DB::update('home_share', $setarr, array('sid' => $id));
			}else{
				if($to=="group"){
					$sharetofids=$idList;
					foreach($sharetofids as $sharefid){
						$groupsetarr=$setarr;
						$groupsetarr[fid]=$sharefid;
						$groupsetarr[username]=user_get_user_name($groupsetarr[uid]);
						if(DB::result_first("show tables like 'pre_group_share'")){
							 DB::insert('group_share', $groupsetarr, 1);
						 }
					}
				}
				$sid = DB::insert('home_share', $setarr, 1);	
			}
			
			
			//保存分享给谁的信息
			$arrLog['sid']=$sid;
			if($to=="group"){
				$arrLog['idtype']="group";
			}else{
				$arrLog['idtype']="user";
			}
			//分享给指定好友和专区
			if($selectIds){
				$idList=preg_split('[,]',$selectIds);
				foreach($idList as $selectId){
					$arrLog['id']=$selectId;
					$setArrLog=daddslashes($arrLog);
					$slid = DB::insert('home_sharelog', $setArrLog, 1);
				}
			}else{
				//分享给所有好友
				if($to=="all"){
					//获取所有好友
					require_once libfile('function/friend');
					$friends = friend_all_list($space[uid]);
					if($friends){
						foreach($friends as $friend){
							$arrLog['id']=$friend['fuid'];
							$setArrLog=daddslashes($arrLog);
							$slid = DB::insert('home_sharelog', $setArrLog, 1);
						}
					}
				}
			}
			
			
			
			//更新系统分享总次数
			include_once libfile('function/stat');
			updatestat('share');
			
			//更新各资源的分享次数
			require_once libfile("function/share_log");
			if($type){
				share_log_inc($id, $type, $subject, $subjectlink, 1);
			}
	
			//通知
			/*if($note_uid && $note_uid != $_G['uid']) {
				if($type=='activity' || $type=='class' || $type=='doc' || $type=='gpic' || $type=='galbum' || $type=='questionary' || $type=='poll' || $type=='question' || $type=='resourceid' || $type=='glive' || $type=='thread'){
					$forumquery = DB::query("select f.name from ".DB::table("forum_forum")." f  where f.fid='".$fid."' ");
					$forumname=DB::fetch($forumquery);
					$note_values['forum'] = $forumname['name'];
					$note_values['forumurl'] = "forum.php?mod=group&fid=$fid";
				}
				notification_add($note_uid, 'sharenotice', $note_message, $note_values);
			}
			
			//专区内的分享才会调用该接口获取经验值
			if(($type=='activity' || $type=='class' || $type=='doc' || $type=='gpic' || $type=='galbum' || $type=='questionary' || $type=='poll' || $type=='question' || $type=='resourceid' || $type=='glive' || $type=='thread') && !empty($fid)){
				require_once libfile('function/group');
				group_add_empirical_by_setting($_G['uid'], $fid, 'share_someting', $id);
			}
			*/
			
			if($atarr[atuids]){
			foreach(array_keys($atarr[atuids]) as $uidkey){
				notification_add($atarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在其<a href="home.php?view=atme">发表的内容</a>中提到了您，赶快去看看吧', array(), 0);
			}
		}
		
		parsetag($title,$arr['body_general'],$type,$returnfeedid);
		if($atarr[atuids]){
			atrecord($atarr[atuids],$returnfeedid);
		}
		if($atarr['atfids']){
			for($i=0;$i<count($resarr['atfids']);$i++){
				group_add_empirical_by_setting($_G['uid'],$atarr['atfids'][$i], 'at_group', $resarr['atfids'][$i]);
			}
		}

			DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);

			
			//积分
			//$needle = $id ? $type.$id : '';
			//updatecreditbyaction('createshare', $_G['uid'], array('sharings' => 1), $needle);
			require_once libfile('function/credit');
			credit_create_credit_log($_G['uid'], 'createshare',$id);//针对信息去重
			
			//文档分享后，调用傲姿接口，更新分享信息
			if("doc"==$type||"class"==$type){
				require_once libfile('function/doc');
				addShareAmount($id);
			}

			//跳转到分享列表页面
			$referer = "home.php";
			showmessage('do_success', $referer, array('sid' => $sid), array('showdialog'=>1, 'showmsg' => true, 'msgtype' => 2,'locationtime'=>1));
		}
	}
	if(submitcheck('addsubmit')) {
		$anonymity=$_POST['anonymity'];
		if(!$anonymity){
			$anonymity=$_G[member][repeatsstatus];
		}
		if($type == 'link') {
			$link = dhtmlspecialchars(trim($_POST['linkinput']));
			if($link) {
				if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
				showmessage('url_incorrect_format');
			}
			$arr['title_template'] = lang('spacecp', 'share_link');
			$arr['body_template'] = '{link}';

			$link_text = sub_url($link, 45);

			$arr['body_data'] = array('link'=>"<a href=\"$link\" target=\"_blank\">$link_text</a>", 'data'=>$link);
			$parseLink = parse_url($link);
			require_once libfile('function/discuzcode');
			$flashvar = parseflv($link);
			if(empty($flashvar) && preg_match("/\.flv$/i", $link)) {
				$flashvar = array(
					'flv' => IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($link),
					'imgurl' => ''
				);
			}
			if(!empty($flashvar)) {
				$arr['title_template'] = lang('spacecp', 'share_video');
				$type = 'video';
				$arr['body_data']['flashvar'] = $flashvar['flv'];
				$arr['body_data']['host'] = 'flash';
				$arr['body_data']['imgurl'] = $flashvar['imgurl'];
			}
			if(preg_match("/\.(mp3|wma)$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_music');
				$arr['body_data']['musicvar'] = $link;
				$type = 'music';
			}
			if(preg_match("/\.swf$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_flash');
				$arr['body_data']['flashaddr'] = $link;
				$type = 'flash';
			}
		}
		$arr['body_general'] = $_POST['msginput'];
		if(empty($arr['body_general'])||$arr['body_general']=='音乐说明（可选）'||$arr['body_general']=='视频说明（可选）'||$arr['body_general']=='分享说明'){
			if($type=='music'){
				$arr['body_general']='推荐音乐';
			}
			if($type=='flash'){
				$arr['body_general']='推荐视频';
			}
			if($type=='link'){
				$arr['body_general']='推荐网址';
			}
		}
		//@和#号的拼接
		if($_POST['atinput']){
			$atarray=explode(',',$_POST['atinput']);
			for($i=0;$i<count($atarray);$i++){
				$arr['body_general']='@'.$atarray[$i].' '.$arr['body_general'];
			}
		}
		if($_POST['taginput']){
			$tagarray=explode(',',$_POST['taginput']);
			for($i=0;$i<count($tagarray);$i++){	
				if($i=0){
					$arr['body_general']=$arr['body_general'].' #'.$tagarray[$i].'#';
				}else{
					$arr['body_general']=$arr['body_general'].'#'.$tagarray[$i].'#';
				}				
			}
		}
		//@解析
		if($atjson){
			$resarr=parseat1($arr['body_general'],$_G[uid],$atjson);
		}else{
			$resarr=parseat($arr['body_general'],$_G[uid],1);
		}
		
		$arr['body_general']=$resarr['message'];
		
		
		$arr['type'] = $type;
		$arr['uid'] = $_G['uid'];
		$arr['username'] = $_G['username'];
		$arr['dateline'] = $_G['timestamp'];


	
		$feedarr['feedbody_data']=$arr['body_data'] ; 
		$arr['body_data'] = serialize($arr['body_data']);

		$setarr = daddslashes($arr);
		$sid = DB::insert('home_share', $setarr, 1);

		if(ckprivacy('share', 'feed')) {
			require_once libfile('function/feed');
			$returnfeedid=feed_add('share',
				'{actor} '.$arr['title_template'],
				array('hash_data' => $feed_hash_data),
				$arr['body_template'],
				$feedarr['feedbody_data'],
				$arr['body_general'],
				array($arr['image']),
				array($arr['image_link']),
				'','','',1,$sid,$type,0,'',0,$resarr['atfids'],'',0,0,$anonymity
			);
		}
		
		
		if($resarr[atuids]){
			foreach(array_keys($resarr[atuids]) as $uidkey){
				notification_add($resarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在其<a href="home.php?view=atme">发表的内容</a>中提到了您，赶快去看看吧', array(), 0);
			}
		}
		
		
		
		
		
		parsetag($title,$arr['body_general'],$type,$returnfeedid);
		if($resarr[atuids]){
			atrecord($resarr[atuids],$returnfeedid);
		}
		if($resarr['atfids']){
			for($i=0;$i<count($resarr['atfids']);$i++){
				group_add_empirical_by_setting($_G['uid'],$resarr['atfids'][$i], 'at_group', $resarr['atfids'][$i]);
			}
		}
		if($anonymity=='0'){
			DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);
		}
		
		include_once libfile('function/stat');
		updatestat('share');

		if($note_uid && $note_uid != $_G['uid']) {
			notification_add($note_uid, 'sharenotice', $note_message, $note_values);
		}

		$needle = $id ? $type.$id : '';
		updatecreditbyaction('createshare', $_G['uid'], array('sharings' => 1), $needle);

		$referer = "home.php?mod=space&do=home&view=me";
		showmessage('do_success', $referer, array('sid' => $sid));
	}
	
	$arr['body_data'] = serialize($arr['body_data']);

	require_once libfile('function/share');
	$arr = mkshare($arr);
	$arr['dateline'] = $_G['timestamp'];
	
	//如果是分享给好友
	if($to=='friend'){
		//加载好友分类
		require_once libfile('function/friend');
		$groups = friend_group_list();
		
		//获取所有好友
		require_once libfile('class/json');
		$friends = friend_all_list($space[uid]);
		$json=arrayToJson($friends);
	}
	//如果是分享给专区
	else if($to=='group'){
		//加载好友分类
		require_once libfile('function/group');
		$groups = group_get_by_user($space[uid]);
		if($groups){
			require_once libfile('class/json');
			$json=groupArrayToJson($groups);
		}
	}

}

include template('home/spacecp_share');
?>