<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_notice.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 100;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$list = array();
$count = 0;
$multi = '';

$view = (!empty($_GET['view']) && in_array($_GET['view'], array('userapp')))?$_GET['view']:'notice';
$actives = array($view=>' class="a"');

//应用的通知
if($view == 'userapp') {

	space_merge($space, 'status');

	if($_GET['op'] == 'del') {
		$appid = intval($_GET['appid']);
		DB::query("DELETE FROM ".DB::table('common_myinvite')." WHERE appid='$appid' AND touid='$_G[uid]'");

		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_myinvite')." WHERE touid='$_G[uid]'"), 0);
		$changecount = $count - $space['myinvitations'];
		if($changecount) {
			member_status_update($_G['uid'], array('myinvitations' => $changecount));
		}

		showmessage('do_success', "home.php?mod=space&do=notice&view=userapp&quickforward=1");
	}

	$filtrate = 0;
	$count = 0;
	$apparr = array();
	$type = intval($_GET['type']);
	$query = DB::query("SELECT * FROM ".DB::table('common_myinvite')." WHERE touid='$_G[uid]' ORDER BY dateline DESC");
	while ($value = DB::fetch($query)) {
		$count++;
		$key = md5($value['typename'].$value['type']);
		$apparr[$key][] = $value;
		if($filtrate) {
			$filtrate--;
		} else {
			if($count < $perpage) {
				if($type && $value['appid'] == $type) {
					$list[$key][] = $value;
				} elseif(!$type) {
					$list[$key][] = $value;
				}
			}
		}
	}

	if(empty($count) && $space['myinvitations']) {
		$changecount = 0 - $space['myinvitations'];
		if($changecount) {
			member_status_update($_G['uid'], array('myinvitations' => $changecount));
		}
	}

//社区通知
} else {
	//?
	space_merge($space, 'status');

	//忽略所有通知
	if(!empty($_GET['ignore'])) {
		DB::update('home_notification', array('new'=>'0', 'from_num'=>0), array('new'=>'1', 'uid'=>$_G['uid']));

		$changecount = 0 - $space['notifications'];
		if($changecount) {
			member_status_update($_G['uid'], array('notifications' => $changecount));
		}
	}

	foreach (array('wall', 'piccomment', 'blogcomment', 'clickblog', 'clickpic', 'sharecomment', 'doing', 'friend', 'credit', 'bbs', 'system', 'thread', 'task', 'group') as $key) {
		$noticetypes[$key] = lang('notification', "type_$key");//从 lang_notification.php 中的取
	}

// ### 需要添加查询条件 
	
	//类型type 查询条件
	//$typesql = $type?"AND type='$type'":'';
	$type = trim($_GET['type']);
	if($type){
		if("gfnotice"==$type){
			//通知公告
			$typesql = " AND (type='gfblog_notice' or type='zq_notice_org' )";
		}else if("sns" == $type){
			//社区类型通知
			$typesql = " AND ptype=0 ";
		}else{
			$typesql = " AND type='$type'";
		}
		$actives_sub = array($type=>' class="a"');
	}else{
		$actives_sub = array('all'=>' class="a"');
	}
	
	
	
	
	
//查看用户上次访问通知列表的时间
	$last_visit = db::fetch_first("SELECT * FROM ".db::table('home_notification_visit')." WHERE uid='$_G[uid]'");
	
	if(empty($last_visit['id'])){
		$last_visit_dateline =0;
	}else{
		$last_visit_dateline =$last_visit['dateline'];
	}
	
	
	//fuids 存放当前页中通知发送者ids
	//newids 存放当前页中未读的通知id
	$fuids = $newids = array();
	
	//查询总条数
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE (uid=0 or uid='$_G[uid]')  $typesql"), 0);
	
	//有数据
	if($count) {

	//分页查询  排序条件 new DESC, dateline DESC  
//## 需要修改排序条件  ：是否已读，是否官方 ，时间 
		//$query = DB::query("SELECT * FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' $typesql ORDER BY new DESC, ptype DESC, dateline DESC LIMIT $start,$perpage");
		
		/* 修改 2010-10-20 14:01:48  by songsp
		$query = DB::query("(SELECT *, new as isnew FROM ".DB::table('home_notification')." WHERE  uid='$_G[uid]' $typesql ) UNION ALL (SELECT * , dateline>$last_visit_dateline as isnew FROM ".DB::table('home_notification')."  WHERE   uid=0 $typesql )  ORDER BY isnew DESC, ptype DESC,dateline DESC LIMIT $start,$perpage" );
		*/
		
		$query = DB::query("(SELECT *, new as isnew , ptype as vptype FROM ".DB::table('home_notification')." WHERE new=1 AND uid='$_G[uid]' $typesql ) UNION ALL (SELECT *, new as isnew, 0 as vptype FROM ".DB::table('home_notification')." WHERE new=0 AND uid='$_G[uid]' $typesql ) UNION ALL (SELECT * , dateline>$last_visit_dateline as isnew, ptype as vptype FROM ".DB::table('home_notification')."  WHERE  dateline>$last_visit_dateline AND uid=0 $typesql ) UNION ALL (SELECT * , dateline>$last_visit_dateline as isnew, 0 as vptype FROM ".DB::table('home_notification')."  WHERE  dateline<=$last_visit_dateline AND uid=0 $typesql ) ORDER BY isnew DESC, vptype DESC,dateline DESC LIMIT $start,$perpage" );
/*
	echo "(SELECT *, new as isnew , ptype as vptype FROM ".DB::table('home_notification')." WHERE new=1 AND uid='$_G[uid]' $typesql ) UNION ALL (SELECT *, new as isnew, 0 as vptype FROM ".DB::table('home_notification')." WHERE new=0 AND uid='$_G[uid]' $typesql ) UNION ALL (SELECT * , dateline>$last_visit_dateline as isnew, ptype as vptype FROM ".DB::table('home_notification')."  WHERE  dateline>$last_visit_dateline AND uid=0 $typesql ) UNION ALL (SELECT * , dateline>$last_visit_dateline as isnew, 0 as vptype FROM ".DB::table('home_notification')."  WHERE  dateline<=$last_visit_dateline AND uid=0 $typesql ) ORDER BY isnew DESC, vptype DESC,dateline DESC LIMIT $start,$perpage";
	*/	
		
		//echo "(SELECT *, new as isnew FROM ".DB::table('home_notification')." WHERE  uid='$_G[uid]' $typesql ) UNION (SELECT * , dateline>$last_visit_dateline as isnew FROM ".DB::table('home_notification')."  WHERE   uid=0 $typesql )  ORDER BY isnew DESC, ptype DESC,dateline DESC LIMIT $start,$perpage";
		while ($value = DB::fetch($query)) {
			$value['new'] = $value[isnew];
			if($value['new']) {
				$newids[] = $value['id'];
				$value['style'] = 'color:#000;font-weight:bold;';
				
				//-- add  通知左侧的样式
				if($value['ptype']){//是否是官方通知
					$value['dl_class'] = 'item_r'; 
				}else{
					$value['dl_class'] = 'item_g';
				}
				
			} else {
				$value['style'] = '';
				
				//-- add 
				$value['dl_class'] = 'item_grey'; //灰色样式
			}
			
// ### 需要从sso 用户id 转换为 社区用户id     不需要转换，两者一致。
//authorid 应该用社区的用户id替换 数据库中记录的SSO的用户id 
			$fuids[$value['id']] = $value['authorid'];
			if($value['from_num'] > 0) $value['from_num'] = $value['from_num'] - 1;
			
			
			//扩展信息  json字符串转化为数组
			$extra_json=json_decode($value['extra'],true);  //将字符转成JSON
			/*
			$arr=array();
			foreach($extra_json as $k=>$w) $arr[$k]=$w;
			$value['extra'] = $arr;
			*/
			$value['extra'] = $extra_json;
			//print_r($value['extra'] );

			$list[$value['id']] = $value;


		}
		if($fuids) {			
			//判断是否是好友			
			require_once libfile('function/friend');
						
			friend_check($fuids);
			$gfboId=getOfficialBlogUid();
			foreach($fuids as $key => $fuid) {	
				if($fuid !=  $gfboId ){
					$value = array();
					$value['isfriend'] = $fuid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$fuid] ? 1 : 0;
					$list[$key] = array_merge($list[$key], $value);
				}else{
					//如果是官方博客，不允许加好友，不允许打招呼,王聪	
					$value = array();
					$value['isfriend']='1';
					$list[$key] = array_merge($list[$key], $value);
	
				}				
			}

		}
		
//#### url需要加条件 
		$page_url = "home.php?mod=space&do=$do";
		if($type){
			$page_url = $page_url."&type=$type";
		}
		
		//$multi = multi($count, $perpage, $page, "home.php?mod=space&do=$do");
		$multi = multi($count, $perpage, $page, $page_url);
	}


//更新用户访问通知列表的时间
	
	$dateline = time();
	$setarr = array(
			'uid' => $_G['uid'],
			'dateline' => $dateline,
	);
	
	if(empty($last_visit['id'])){
		//新增
		DB::insert('home_notification_visit', $setarr);
	} else {
		db::update('home_notification_visit', $setarr, array('id'=>$last_visit['id']));
	}
	
	
	
	if($newids) {
		
		//将当前页未读的修该为已读
		DB::query("UPDATE ".DB::table('home_notification')." SET new='0', from_num='0' WHERE id IN (".dimplode($newids).")");
		//$newcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' AND new='1'"), 0);
		
		$newcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE (uid='$_G[uid]' AND new='1' ) or ( uid=0 and dateline> $dateline) "), 0);

		//更新新通知数
		$changecount = $newcount - $space['notifications'];
		if($changecount) {
			member_status_update($_G['uid'], array('notifications' => $changecount));
		}
		$space['notifications'] = $newcount;
	}

	//提醒数
	$newprompt = 0;
	foreach (array('notifications','groupinvitations','activityinvitations','myinvitations','pokes','pendingfriends') as $key) {
		$newprompt = $newprompt + $space[$key];
	}
	//修改提醒数
	if($newprompt != $space['newprompt']) {
		$space['newprompt'] = $newprompt;
		DB::update('common_member', array('newprompt'=>$newprompt), array('uid'=>$_G['uid']));
	}

}
include_once template("diy:home/space_notice");

?>