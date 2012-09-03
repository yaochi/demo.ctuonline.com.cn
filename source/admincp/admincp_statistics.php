<?php

/**
 *
 *      $Id: admincp_statistics.php 2011-4-12 yangyang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if(!$_G[gp_inajax]){
cpheader();
if($operation != 'setting' && empty($_G['setting']['groupstatus'])) {
	cpmsg('group_status_off', 'action=group&operation=setting', 'error');
}
	echo <<<EOT
<script src="static/js/forum_calendar.js"></script>
EOT;
}
if($operation == 'visitnum') {
	shownav('statistics', '社区访问人数');
	showsubmenu('社区访问人数统计');
	if(!submitcheck('visitnumsubmit')) {
		showtips('statistics_visitnum_tips');
		showformheader('statistics&operation=visitnum');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('visitnumsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$visitnum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND lastvisit>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND lastvisit<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $sql");
		}else{
			$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status'));
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $visitnum)));

	}
}elseif($operation == 'modifymember'){
	shownav('statistics', '更新个人资料的人数');
	showsubmenu('更新个人资料的人数统计');
	if(!submitcheck('modifymembersubmit')) {
		showtips('statistics_modifymember_tips');
		showformheader('statistics&operation=modifymember');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('modifymembersubmit');
		showtablefooter();
		showformfooter();

	} else {
		$modifynum=0;
		$sql = '';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$modifynum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE title_template like '%更新了自己%' $sql");
		}else{
			$modifynum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE title_template like '%更新了自己%' ");
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $modifynum)));

	}
}elseif($operation == 'blog'){
	shownav('statistics', '发表记录的条数/人数');
	showsubmenu('发表记录的条数/人数统计');
	if(!submitcheck('blogsubmit')) {
		showtips('statistics_blog_tips');
		showformheader('statistics&operation=blog');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsetting('统计类型', array('type', array(
			array('numbers', '统计条数'),
			array('menbers', '统计人数')
		)), 'numbers', 'mradio');
		showsubmit('blogsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$blognum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';
		$type = $_G['gp_type'] != '' ? $_G['gp_type']:'numbers';
		if($sql){
			if($type=='numbers'){
				$blognum = DB::result_first("SELECT count(*) FROM ".DB::table('home_blog')." WHERE $sql");
			}else{
				$blognum = DB::result_first("SELECT count(DISTINCT(uid)) FROM ".DB::table('home_blog')." WHERE $sql");
			}
		}else{
			if($type=='numbers'){
				$blognum = DB::result_first("SELECT count(*) FROM ".DB::table('home_blog'));
			}else{
				$blognum = DB::result_first("SELECT count(DISTINCT(uid)) FROM ".DB::table('home_blog'));
			}
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $blognum)));

	}
}elseif($operation == 'feed'){
	shownav('statistics', '产生动态数');
	showsubmenu('产生动态数统计');
	if(!submitcheck('feedsubmit')) {
		showtips('statistics_feed_tips');
		showformheader('statistics&operation=feed');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('feedsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$feednum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$feednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $sql");
		}else{
			$feednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed'));
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $feednum)));

	}
}elseif($operation == 'forum'){
	shownav('statistics', '创建专区数');
	showsubmenu('创建专区数统计');
	if(!submitcheck('forumsubmit')) {
		showtips('statistics_forum_tips');
		showformheader('statistics&operation=forum');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('forumsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$forumnum=0;
		$sql = '';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$forumnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_forum')." pf,".DB::table('forum_forumfield')." pff WHERE pf.type='sub' AND pf.fid=pff.fid $sql");
		}else{
			$forumnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_forum')." pf,".DB::table('forum_forumfield')." pff WHERE pf.type='sub' AND pf.fid=pff.fid");
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $forumnum)));

	}
}elseif($operation == 'thread'){
	shownav('statistics', '专区发帖数');
	showsubmenu('专区发帖数统计');
	if(!submitcheck('threadsubmit')) {
		showtips('statistics_thread_tips');
		showformheader('statistics&operation=thread');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('threadsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$threadnum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$threadnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE $sql");
		}else{
			$threadnum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread'));
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $threadnum)));

	}
}elseif($operation == 'comment'){
	shownav('statistics', '回帖数');
	showsubmenu('回帖数统计');
	if(!submitcheck('commentsubmit')) {
		showtips('statistics_comment_tips');
		showformheader('statistics&operation=comment');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('commentsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$commentnum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$postnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('forum_post')." where $sql AND FIRST='0' ");
			$postcomnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('forum_postcomment')." where $sql ");
			$hcomnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('home_comment')." where $sql ");
			$commentnum=$postnum+$postcomnum+$hcomnum;
		}else{
			$postnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('forum_post')." where FIRST='0' ");
			$postcomnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('forum_postcomment'));
			$hcomnum=DB::result_first("SELECT COUNT(*) FROM ".DB::TABLE('home_comment'));
			$commentnum=$postnum+$postcomnum+$hcomnum;
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $commentnum)));

	}
}elseif($operation == 'grouppic'){
	shownav('statistics', '专区图片数');
	showsubmenu('专区图片数统计');
	if(!submitcheck('grouppicsubmit')) {
		showtips('statistics_grouppic_tips');
		showformheader('statistics&operation=grouppic');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('grouppicsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$grouppicnum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$grouppicnum = DB::result_first("SELECT count(*) FROM ".DB::table('group_pic')." WHERE $sql");
		}else{
			$grouppicnum = DB::result_first("SELECT count(*) FROM ".DB::table('group_pic'));
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $grouppicnum)));

	}
}elseif($operation == 'pic'){
	shownav('statistics', '个人图片数');
	showsubmenu('个人图片数统计');
	if(!submitcheck('picsubmit')) {
		showtips('statistics_pic_tips');
		showformheader('statistics&operation=pic');
		showtableheader();
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('picsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$picnum=0;
		$sql = ' 1=1 ';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';

		if($sql){
			$picnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_pic')." WHERE $sql");
		}else{
			$picnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_pic'));
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $picnum)));

	}
}elseif($operation == 'live'){
	shownav('statistics', '直播统计');
	showsubmenu('直播统计');
	if(!submitcheck('picsubmit')) {
		showtips('statistics_live_tips');
		showformheader('statistics&operation=pic');
        showtablerow('style="height:20px"', array('colspan="6"'), array('<a href="'.$_G[config][expert][liveurl].'/manage/stats.do?action=stats" target="_blank">进入直播统计页</a>'));
		showtableheader();
		showtablefooter();
		showformfooter();

	} else {
		//
    }
}elseif($operation == 'blogfeed'){
	/*if(!submitcheck('blogfeedsubmit')) {
		showtips('statistics_blogfeed_tips');
		showformheader('statistics&operation=blogfeed');
		showsetting('动作类型', array('actid', array(
			array('0', '-全部-'),
			array('1', '原创'),
			array('2', '转发')
		)), '0', 'select','',0,'');
		showsetting('内容类型', array('typeid', array(
			array('0', '-全部-'),
			array('blog', '记录'),
			array('album', '图片'),
			array('activitys', '活动'),
			array('doc', '文档'),
			array('group', '专区'),
			array('notice', '通知公告'),
			array('nwkt', '你我课堂'),
			array('poll', '投票'),
			array('thread', '话题'),
			array('live', '直播'),
			array('questionary', '问卷'), 
			array('resourcelist', '资源列表'),
			array('reward', '提问吧'),
			array('link', '链接'),
			array('video', '视频'),
			array('music', '音乐'),
			array('class', '课程'),
			array('case', '案例'),
		)), '0', 'select','',0,'');
		showsetting('起始日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsubmit('blogfeedsubmit');
		showtablefooter();
		showformfooter();

	} else {*/
	//if(!$_G[gp_inajax]){
		//$actid=empty($_G['gp_actid'])?0:$_G['gp_actid'];
		$typeid=empty($_G['gp_typeid'])?"0":$_G['gp_typeid'];
		$starttime=$_G['gp_starttime'];
		$endtime=$_G['gp_endtime'];
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';
	//}
		if($typeid=='album'||$typeid=='blog'||$typeid=='group'||$typeid=='notice'||$typeid=='activitys'||$typeid=='thread'||$typeid=='poll'||$typeid=='questionary'||$typeid=='reward'||$typeid=='live'||$typeid=='resourcelist'||$typeid=='nwkt'){
			$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='".$typeid."' ".$sql);
		}elseif($typeid=='class'||$typeid=='doc'||$typeid=='case'){
			$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='".$typeid."' and idtype!='".$typeid."' ".$sql);
		}elseif($typeid=='video'){
			$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%' ".$sql);
		}elseif($typeid=='link'){
			$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%' ".$sql);
		}elseif($typeid=='music'){
			$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share'  and title_template like '%音乐%' ".$sql);
		}
		$namearray=array('blog'=>'记录',
			'album'=>'图片',
			'activitys'=>'活动',
			'doc'=>'文档',
			'group'=>'专区',
			'notice'=>'通知公告',
			'nwkt'=>'你我课堂',
			'poll'=>'投票',
			'thread'=>'话题',
			'live'=>'直播',
			'questionary'=>'问卷', 
			'resourcelist'=>'资源列表',
			'reward'=>'提问吧',
			'link'=>'链接',
			'video'=>'视频',
			'music'=>'音乐',
			'class'=>'课程',
			'case'=>'案例');
		$firsttype=$_G[gp_firsttype];
		$secondtype=$_G[gp_secondtype];
		if($firsttype=='personal'){
			$albumcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='album' ".$sql);
			$videocount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%' ".$sql);
			$musiccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share'  and title_template like '%音乐%' ".$sql);
			$linkcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%' ".$sql);
			$blogcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='blog' ".$sql);
			$res[albumcount]=$albumcount;
			$res[videocount]=$videocount;
			$res[musiccount]=$musiccount;
			$res[linkcount]=$linkcount;
			$res[blogcount]=$blogcount;
			echo json_encode($res);
			exit;
		}
		if($firsttype=='group'){
			$groupcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='group' ".$sql);
			$noticecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='notice' ".$sql);
			$activityscount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='activitys' ".$sql);
			$threadcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='thread' ".$sql);
			$pollcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='poll' ".$sql);
			$questionarycount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='questionary' ".$sql);
			$rewardcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='reward' ".$sql);
			$livecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='live' ".$sql);
			$resourcelistcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='resourcelist' ".$sql);
			$res[groupcount]=$groupcount;
			$res[noticecount]=$noticecount;
			$res[activityscount]=$activityscount;
			$res[threadcount]=$threadcount;
			$res[pollcount]=$pollcount;
			$res[questionarycount]=$questionarycount;
			$res[rewardcount]=$rewardcount;
			$res[livecount]=$livecount;
			$res[resourcelistcount]=$resourcelistcount;
			echo json_encode($res);
			exit;
		}
		if($firsttype=='know'){
			$classcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='class' and idtype!='class' ".$sql);
			$doccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='doc' and idtype!='doc' ".$sql);
			$casecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='case' and idtype!='case' ".$sql);
			$res[classcount]=$classcount;
			$res[doccount]=$doccount;
			$res[casecount]=$casecount;
			echo json_encode($res);
			exit;
		}
		if($firsttype=='app'){
			$nwktcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='nwkt' ".$sql);
			$res[nwktcount]=$nwktcount;
			echo json_encode($res);
			exit;
		}
		//图片
		if($secondtype=='album'){
			$orialbumcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='album' and idtype!='feed' ".$sql);
			$foralbumcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='album' and idtype='feed' ".$sql);
			$res[orialbumcount]=$orialbumcount;
			$res[foralbumcount]=$foralbumcount;
			echo json_encode($res);
			exit;
		}
		//视频
		if($secondtype=='video'){
			$orivideocount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%' and idtype!='feed'  ".$sql);
			$forvideocount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%' and idtype='feed'  ".$sql);
			$res[orivideocount]=$orivideocount;
			$res[forvideocount]=$forvideocount;
			echo json_encode($res);
			exit;
		}
		//音乐
		if($secondtype=='music'){
			$orimusiccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share'  and title_template like '%音乐%' and idtype!='feed' ".$sql);
			$formusiccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share'  and title_template like '%音乐%' and idtype='feed' ".$sql);
			$res[orimusiccount]=$orimusiccount;
			$res[formusiccount]=$formusiccount;
			echo json_encode($res);
			exit;
		}
		//链接
		if($secondtype=='link'){
			$orilinkcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%' and idtype!='feed' ".$sql);
			$forlinkcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%' and idtype='feed' ".$sql);
			$res[orilinkcount]=$orilinkcount;
			$res[forlinkcount]=$forlinkcount;
			echo json_encode($res);
			exit;
		}
		
		//记录
		if($secondtype=='blog'){
			$oriblogcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='blog'  and idtype!='feed' ".$sql);
			$forblogcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='blog'  and idtype='feed' ".$sql);
			$res[oriblogcount]=$oriblogcount;
			$res[forblogcount]=$forblogcount;
			echo json_encode($res);
			exit;
		}
		
		//专区
		if($secondtype=='group'){
			$origroupcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='group' and idtype!='feed' ".$sql);
			$forgroupcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='group' and idtype='feed' ".$sql);			
			$res[origroupcount]=$origroupcount;
			$res[forgroupcount]=$forgroupcount;
			echo json_encode($res);
			exit;
		}
		
		//专区通知
		if($secondtype=='notice'){
			$orinoticecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='notice' and idtype!='feed' ".$sql);
			$fornoticecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='notice' and idtype='feed'".$sql);
			$res[orinoticecount]=$orinoticecount;
			$res[fornoticecount]=$fornoticecount;
			echo json_encode($res);
			exit;
		}
		
		//活动
		if($secondtype=='activitys'){
			$oriactivityscount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='activitys' and idtype!='feed' ".$sql);
			$foractivityscount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='activitys' and idtype='feed' ".$sql);
			$res[oriactivityscount]=$oriactivityscount;
			$res[foractivityscount]=$foractivityscount;
			echo json_encode($res);
			exit;
		}
		
		//话题
		if($secondtype=='thread'){
			$orithreadcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='thread' and idtype!='feed' ".$sql);
			$forthreadcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='thread' and idtype='feed' ".$sql);	
			$res[orithreadcount]=$orithreadcount;
			$res[forthreadcount]=$forthreadcount;
			echo json_encode($res);
			exit;
		}
		
		//投票
		if($secondtype=='poll'){
			$oripollcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='poll' and idtype!='feed' ".$sql);
			$forpollcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='poll' and idtype='feed' ".$sql);
			$res[oripollcount]=$oripollcount;
			$res[forpollcount]=$forpollcount;
			echo json_encode($res);
			exit;
		}
		//问卷
		if($secondtype=='questionary'){
			$oriquestionarycount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='questionary' and idtype!='feed' ".$sql);
			$forquestionarycount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='questionary' and idtype='feed' ".$sql);
			$res[oriquestionarycount]=$oriquestionarycount;
			$res[forquestionarycount]=$forquestionarycount;
			echo json_encode($res);
			exit;
		}
		
		//直播
		if($secondtype=='live'){
			$orilivecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='live' and idtype!='feed' ".$sql);
			$forlivecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='live' and idtype='feed' ".$sql);
			$res[orilivecount]=$orilivecount;
			$res[forlivecount]=$forlivecount;
			echo json_encode($res);
			exit;
		}
		
		//提问吧
		if($secondtype=='reward'){
			$orirewardcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='reward'  and idtype!='feed' ".$sql);
			$forrewardcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='reward'  and idtype='feed' ".$sql);
			$res[orirewardcount]=$orirewardcount;
			$res[forrewardcount]=$forrewardcount;
			echo json_encode($res);
			exit;
		}
		
		//资源列表
		if($secondtype=='resourcelist'){
			$oriresourcelistcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='resourcelist'  and idtype!='feed' ".$sql);
			$forresourcelistcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='resourcelist'  and idtype='feed' ".$sql);
			$res[oriresourcelistcount]=$oriresourcelistcount;
			$res[forresourcelistcount]=$forresourcelistcount;
			echo json_encode($res);
			exit;
		}
		
		//课程
		if($secondtype=='class'){
			$oriclasscount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='class' and idtype!='class' and idtype!='feed' ".$sql);
			$forclasscount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='class' and idtype!='class' and idtype='feed' ".$sql);			
			$res[oriclasscount]=$oriclasscount;
			$res[forclasscount]=$forclasscount;
			echo json_encode($res);
			exit;
		}
		
		//文档
		if($secondtype=='doc'){
			$oridoccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='doc' and idtype!='doc' and idtype!='feed' ".$sql);
			$fordoccount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='doc' and idtype!='doc' and idtype='feed' ".$sql);	
			$res[oridoccount]=$oridoccount;
			$res[fordoccount]=$fordoccount;
			echo json_encode($res);
			exit;
		}	
			
		//案例
		if($secondtype=='case'){
			$oricasecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='case' and idtype!='case' and idtype!='feed' ".$sql);
			$forcasecount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='case' and idtype!='case' and idtype='feed' ".$sql);	
			$res[oricasecount]=$oricasecount;
			$res[forcasecount]=$forcasecount;
			echo json_encode($res);
			exit;
		}
		
		//你我课堂
		if($secondtype=='nwkt'){
			$orinwktcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='nwkt' and idtype!='feed' ".$sql);
			$fornwktcount=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='nwkt' and idtype='feed' ".$sql);
			$res[orinwktcount]=$orinwktcount;
			$res[fornwktcount]=$fornwktcount;
			echo json_encode($res);
			exit;
		}
		if(!$firsttype&&!$secondtype){
			include_once template("admin/statistics");
		}
}elseif($operation == 'singlefeed'){
	shownav('statistics','动态详情统计');
	showsubmenu('动态详情统计');
	if(!submitcheck('singlefeedsubmit')) {
		showtips('statistics_singlefeed_tips');
		showformheader('statistics&operation=singlefeed');
		showtableheader();
		showsetting('内容类型', array('typeid', array(
			array('blog', '记录'),
			array('album', '图片'),
			array('activitys', '活动'),
			array('doc', '文档'),
			array('group', '专区'),
			array('notice', '通知公告'),
			array('nwkt', '你我课堂'),
			array('poll', '投票'),
			array('thread', '话题'),
			array('live', '直播'),
			array('questionary', '问卷'), 
			array('resourcelist', '资源列表'),
			array('reward', '提问吧'),
			array('link', '链接'),
			array('video', '视频'),
			array('music', '音乐'),
			array('class', '课程'),
			array('case', '案例') 
		)), '0', 'select');
		showsetting('内容id', 'contentid','',text);
		showsubmit('singlefeedsubmit');
		showtablefooter();
		showformfooter();
	}else{
		$contentid=intval($_G['gp_contentid']);
		$typeid=$_G['gp_typeid'];
		if($contentid){
			if($typeid!='music'||$typeid!='video'||$typeid!='link'){
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='".$typeid."' and id=".$contentid);
				if($feedid){
					$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='".$typeid."' and idtype='feed' and id=".$feedid);
				}
			}elseif($typeid=='music'||$typeid=='video'||$typeid=='link'){
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='share' and id=".$contentid);
				if($feedid){
					$count=DB::result_first("select count(*) from ".DB::TABLE("home_feed")." where icon='share' and idtype='feed' and id=".$feedid);
				}
			}
			showtableheader(cplang('statistics_numbers', array('stattsnumber' => $count)));
			if($count){
				showtablerow('style="height:20px"', array('class="td28 td23"'), array("<a href=\"".ADMINSCRIPT."?action=export&operation=exportsinglefeed&typeid=".$typeid."&contentid=".$contentid."\" class=\"act\">导出明细</a>"));
			}
		}else{
			cpmsg('id填写有误','','error');
		}
	}
}elseif($operation=='operationform'){
		include_once template("admin/ybstics");
}elseif($operation=='operationform2'){
	$typeid=empty($_G['gp_typeid'])?"0":$_G['gp_typeid'];
	$ybstarttime=$_G['gp_ybstarttime'];
	$ybendtime=$_G['gp_ybendtime'];
	if($ybendtime){
    if($typeid=='0'){
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime)."'" : '';
	$visitsql .= $ybendtime != '' ? " AND lastvisit<='".strtotime($ybendtime)."' " : '';

	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");
    $commentpostsql = ' 1=1 and fromwhere=0 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentpostsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 and fromwhere=0 ';
    $commentfeedsql .$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentfeedsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    $tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
	$tagsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	$taghotsql = ' 1=1 ';
    $taghotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $taghotsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $hottag = DB::result_first("SELECT max(content) FROM ".DB::table('home_tag')." WHERE $taghotsql");
    if(empty($hottag)){
    	$hottag=0;
    }
	if(empty($commentnum)){
		$commentnum=0;
	}if(empty($commentfeednum)){
		$commentfeednum=0;
	}
	}
	if($typeid=='visit'){
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime)."'" : '';
	$visitsql .= $ybendtime != '' ? " AND lastvisit<='".strtotime($ybendtime)."' " : '';
	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");
	}elseif($typeid=='commpost'){
	$commentpostsql = ' 1=1  and fromwhere=0 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentpostsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
	}elseif($typeid=='feed'){
     $commentfeedsql = ' 1=1 and fromwhere=0 ';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentfeedsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    if(empty($commentfeednum)){
    	$commentfeednum=0;
    }
	}elseif($typeid=='comment'){
	$commentsql = ' 1=1 and fromwhere=0 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
		$commentnum=0;
	}
	}elseif($typeid=='addtag'){
	$tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
	$tagsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	}elseif($typei=='hottag'){
	$taghotsql = ' 1=1 ';
    $taghotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $taghotsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $hottag = DB::result_first("SELECT max(content) FROM ".DB::table('home_tag')." WHERE $taghotsql");
    if(empty($hottag)){
    	$hottag=0;
    }
	}
	}else{
	if($typeid=='0'){
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");
    $commentpostsql = ' 1=1 and fromwhere=0 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 and fromwhere=0 ';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    $tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$tagsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	$taghotsql = ' 1=1';
    $taghotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $taghotsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
    $hottag = DB::result_first("SELECT max(content) FROM ".DB::table('home_tag')." WHERE $taghotsql");

//    echo "登录人数"."SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql"."<br>";
//    echo "发表内容"."SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql"."<br>";
//    echo "转发数"."SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql"."<br>";
//    echo "评论数"."SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql"."<br>";
//   echo "新增标签"."SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql"."<br>";
    if(empty($hottag)){
    	$hottag=0;
    }
	if(empty($commentnum)){
		$commentnum=0;
	}
	}
	if($typeid=='visit'){
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit<='".strtotime($ybstarttime."23:59:59")."' " : '';
	}elseif($typeid=='commpost'){
	$commentpostsql = ' 1=1 and fromwhere=0 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
	}elseif($typeid=='feed'){
    $commentfeedsql = ' 1=1 and fromwhere=0 ';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
	}elseif($typeid=='comment'){
	$commentsql = ' 1=1 and fromwhere=0 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}elseif($typeid=='addtag'){
	$tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$tagsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	}elseif($typeid==='hottag'){
    $taghotsql = ' 1=1 ';
    $taghotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $taghotsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
    $hottag = DB::result_first("SELECT max(content) FROM ".DB::table('home_tag')." WHERE $taghotsql");
    if(empty($hottag)){
    	$hottag=0;
    }
	}
	}
	if($ybendtime){
		$days=$ybstarttime.'/'.$ybendtime;
	}else{
		$days=$ybstarttime;
	}
//	DB::insert("home_mapping",array(
//  "conntentpostnum"=>$commentpostnum,
//  "feednum"=>$commentfeednum,
//  "content"=>$commentnum,
//  "addtag"=>$newtag,
//  "days"=>$days,
//  "dateline"=>time(),
//  ));
	include_once template("admin/ybstics");

}elseif($operation=='fansnum'){
	shownav('statistics', '好友/粉丝数统计');
	showsubmenu('好友/粉丝数统计');
	if(!submitcheck('fansnumsubmit')) {
		showformheader('statistics&operation=fansnum');
		showtableheader();
		$month = date("m");   
		$year = date("Y");  
		$day = date("d");
		$j = date("N");
		if(empty($_G['gp_starttime'])){
			$_G['gp_starttime']=date("Y-m-d",mktime(0,0,0,$month,$day-$j+1,$year));
		}
		if(empty($_G['gp_endtime'])){
			$_G['gp_endtime']=date("Y-m-d",mktime(0,0,0,$month,$day-$j+7,$year));
		}
		showsetting('类型',array('typeid',array(array('friends','好友'),array('fans','粉丝'))),'fans','select');
		showsetting('起止日期', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsetting('统计类型', array('type', array(
			array('add', '新增'),
			array('delete', '减少')
		)), 'add', 'mradio');
		showsubmit('fansnumsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$fansnum=0;
		$sql = '';
		$sql .= $_G['gp_starttime'] != '' ? " AND updatetime>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND updatetime<='".strtotime($_G['gp_endtime'])."'" : '';
		$type = $_G['gp_type'] != '' ? $_G['gp_type']:'add';
		$typeid = $_G['gp_typeid'] != '' ? $_G['gp_typeid']:'fans';
		if($sql){
			if($type=='add'){
				if($typeid=='fans'){
					$fansnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_friend')." WHERE fansmark='1' $sql");
				}else{
					$fansnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_friend')." WHERE friendmark='1' $sql");
				}
			}else{
				if($typeid=='fans'){
					$fansnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_friend')." WHERE fansmark='-1' $sql");
				}else{
					$fansnum = DB::result_first("SELECT count(*) FROM ".DB::table('home_friend')." WHERE friendmark='-1' $sql");
				}
			}
		}
		showtableheader(cplang('statistics_numbers', array('stattsnumber' => $fansnum)));

	}
	
}elseif($operation=='activeinfoform2'){
	$typeid=empty($_G['gp_typeid'])?"0":$_G['gp_typeid'];
	$username=$_G['gp_username'];
	$activestarttime=$_G['gp_activestarttime'];
	$activeendtime=$_G['gp_activeendtime'];
	if($username){
    $info=DB::query("select uid as uid from pre_common_member where username='".$_G['gp_username']."'");
	$value=DB::fetch($info);
	$str=$value[uid];
	if(!$str){
		$str=$_G[uid];
	}
	if($activeendtime)
	{
	if($typeid=='0'){
 	$commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");

    if(empty($commentnum)){
    	$commentnum=0;
    }
	}
    elseif($typeid=='commpost'){
    $commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    }
    elseif($typeid=='feed'){
 	$commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    }elseif($typeid=='comment'){
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    $commentnum=0;
    }
    }
	}else{
	if($typeid=='0'){
	$commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}
    elseif($typeid=='commpost'){
    $commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    }
    elseif($typeid=='feed'){
 	$commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    }elseif($typeid=='comment'){
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    $commentnum=0;
    }
    }
	}
	}else{
    if($activeendtime)
	{
	$visitsql = ' 1=1 ';
	$visitsql .= $activestarttime != '' ? " AND lastvisit>'".strtotime($activestarttime)."'" : '';
	$visitsql .= $activeendtime != '' ? " AND lastvisit<='".strtotime($activeendtime)."' " : '';
	$visquer = DB::query("SELECT uid FROM ".DB::table('common_member_status')." WHERE $visitsql");
    if($visquer){
	while($visinfo=DB::fetch($visquer)){
		$visval[uid]=$visinfo[uid];
		$visdata[]=$visval;
    }
    }
    if (count($visdata) != 0) {
	  for ($i = 0; $i < sizeof($visdata); $i++) {
			if($i==sizeof($visdata)-1){
				$str.=$visdata[$i][uid];
			}else{
			    $str.=$visdata[$i][uid].",";
			    }
			}
		}
		if(!$str){
			$str=$_G[uid];
		}
	if($typeid=='0'){
	$commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}elseif($typeid=='commpost'){
    $commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    }elseif($typeid=='feed'){
 	$commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    }elseif($typeid=='comment'){
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
    }
	}else{
	$visitsql = ' 1=1 ';
	$visitsql .= $activestarttime != '' ? " AND lastvisit>'".strtotime($activestarttime."0:00:0")."'" : '';
	$visitsql .= $activestarttime != '' ? " AND lastvisit<='".strtotime($activestarttime."23:59:59")."' " : '';
	$visquer = DB::query("SELECT uid FROM ".DB::table('common_member_status')." WHERE $visitsql");
    if($visquer){
	while($visinfo=DB::fetch($visquer)){
		$visval[uid]=$visinfo[uid];
		$visdata[]=$visval;
    }
    }
    if (count($visdata) != 0) {
	  for ($i = 0; $i < sizeof($visdata); $i++) {
			if($i==sizeof($visdata)-1){
				$str.=$visdata[$i][uid];
			}else{
			    $str.=$visdata[$i][uid].",";
			    }
			}
		}
		if(!$str){
			$str=$_G[uid];
		}
	if($typeid=='0'){
    $commentpostsql = ' 1=1 fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}
    elseif($typeid=='commpost'){
    $commentpostsql = ' 1=1 and fromwhere=0';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    }
    elseif($typeid=='feed'){
 	$commentfeedsql = ' 1=1 and fromwhere=0';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    }elseif($typeid=='comment'){
    $commentsql = ' 1=1 and fromwhere=0';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
    }
	}
	}
	  include_once template("admin/activetices");
	}
	elseif($operation=='activeinfoform'){
	    include_once template("admin/activetices");
	}elseif($operation=='top100'){
		$perpage = 10;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page - 1) * $perpage;
		$typeid=empty($_G['gp_typeid'])?'fans':$_G['gp_typeid'];
		$starttime=$_G['gp_starttime'];
		$endtime=$_G['gp_endtime'];
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';
		$extra .= $_G['gp_starttime'] != '' ? "&starttime=".$_G['gp_starttime'] : '';
		$extra .= $_G['gp_endtime'] != '' ? "&endtime=".$_G['gp_endtime']: '';
		$extra .='&typeid='.$typeid;
		if($sql){
			DB::query("create temporary table tmp_table1 (SELECT uid,type,count(type) as c FROM pre_home_friend where 1=1 ".$sql." group by uid,type)");
		}else{
			DB::query("create temporary table tmp_table1 (SELECT uid,type,count(type) as c FROM pre_home_friend group by uid,type)");
		}
		$count=DB::result_first("select count(*) from tmp_table1");
		if($count>100){
			$count=100;
		}
		if($count){
			if($typeid=='fans'){
				$query=DB::query("select uid,sum(c) as b from tmp_table1  where (type=2 or type=3)  group by uid order by b desc limit $start,$perpage ");
			}elseif($typeid=='follows'){
				$query=DB::query("select uid,sum(c) as b from tmp_table1  where (type=1 or type=3)  group by uid order by b desc limit $start,$perpage ");
			}elseif($typeid=='friends'){
				$query=DB::query("select uid,sum(c) as b from tmp_table1  where type=3 group by uid order by b desc limit $start,$perpage ");
			}else{
			}
			while($value=DB::fetch($query)){
				$list[$value[uid]]=$value;
				$uidarray[]=$value[uid];
			}
			if($uidarray){
				$uids=implode(',',$uidarray);
				$url=$_G[config]['api']['url'].'/api/sso/getusersprovince.php?uids='.$uids;
				$usercompany=json_decode(openURL($url),true);
				$namequery=DB::query("select cm.uid,username,realname from ".DB::TABLE("common_member")." cm,".DB::TABLE("common_member_profile")." cmp where cm.uid=cmp.uid and cm.uid in(".$uids.")");
				while($namevalue=DB::fetch($namequery)){
					$list[$namevalue[uid]][username]=$namevalue[username];
					$list[$namevalue[uid]][realname]=$namevalue[realname];
					$list[$namevalue[uid]][province]=empty($usercompany[$namevalue[uid]][groupName])?'中国电信':$usercompany[$namevalue[uid]][groupName];
				}
			}
		}

	    include_once template("admin/top100");
		echo multi($count, $perpage, $page, ADMINSCRIPT."?action=statistics&operation=top100$extra");
	}elseif($operation=='expoting'){
	    include_once template("admin/index");
	}
?>