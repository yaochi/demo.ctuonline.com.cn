<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_activity.php 10930 2010-05-18 07:34:59Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

     require_once libfile("function/category");
     
$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if(empty($_GET['view'])) $_GET['view'] = 'we';
$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'activity',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$sql="";

$f_index = '';
$need_count = true;
require_once libfile('function/misc');
if($_GET['view'] == 'all') {
	if($_GET['order'] == 'hot') {
		
		//$apply_sql = "INNER JOIN ".DB::table('forum_thread')." t ON t.special='4' AND t.tid = a.tid AND t.replies>='$minhot'";
	}
	$orderactives = array($_GET['order'] => ' class="a"');
} elseif($_GET['view'] == 'me') {
	$viewtype = in_array($_G['type'], array('orig', 'apply')) ? $_G['type'] : 'orig';
   
	if($_GET['type'] == 'apply') {
		
		$wheresql = "1";
		//$apply_sql = "INNER JOIN ".DB::table('forum_activityapply')." apply ON apply.uid = '$space[uid]' AND apply.tid = a.tid";
	} else {
		//我创建的活动 forum_forumfield 记录了创建人 
		// forum_forum 活动表   forum_forumfield活动附属表   
		// forum_forum_activity记录了活动的类似查看数等信息    forum_groupuser  专区成员表
		$wheresql = " and  fff.founderuid = '$space[uid]' ";
		$sql = "SELECT ff.*, fff.digest, fff.banner, fff.category_id, fff.founderuid, fff.foundername, fff.membernum, ffa.type as ffatype, ffa.viewnum, ffa.average,ffa.live_id,ffa.teacher_id ,fff.extra
		FROM ".DB::table("forum_forum")." ff, "
            .DB::table("forum_forumfield") ." fff ,"
            .DB::table("forum_forum_activity")." ffa "
            ." WHERE ff.fid=fff.fid AND ff.fid=ffa.fid".
            " $wheresql AND ff.type='activity' ";
		
	}
	
	$orderactives = array($viewtype => ' class="a"');
} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = "a.uid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql = "a.uid IN ($space[feedfriend])";
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' ORDER BY num DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$userlist[] = $value;
		}
	} else {
		$need_count = false;
	}
}

$orderactives = array($_GET['type'] =>' class="a"');

if($need_count) {
	
	 $is_enable_category = false;
	 
	if( ($_GET['view'] == 'me')&& ($_GET['type'] == 'orig') ) {
		//查找用户自己创建的活动
		$count = DB::result(DB::query("SELECT COUNT(*) FROM  "
            .DB::table("forum_forum")." ff, "
            .DB::table("forum_forumfield") ." fff "
            ." WHERE ff.fid=fff.fid ".
            " $wheresql AND ff.type='activity' "),0);

	
	  if($count) {
		if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
			$apply_sql = '';
		}
	   // print_r("------". " $sql    LIMIT $start, $perpage");
		$query = DB::query("  $sql    LIMIT $start, $perpage");
		
		
	   while($ret= DB::fetch($query)){
		 	
		  
		 	
		  $extra = unserialize($ret["extra"]);
		  
		
          if($extra["startime"]!=0 && $extra["endtime"]!=0){
            $ret["str_start_time"] = getdate($extra["startime"]);
            $ret["str_end_time"] = getdate($extra["endtime"]);
          }
          
         // print_r("分类----".$ret["category_id"]);
          $category=common_category_get_category_by_id($ret["category_id"]);
       // print_r("分类----".$category["name"]);
          $categorys[$ret["category_id"]]=$category;
          if(is_null($category["name"])){
          	 $is_enable_category[$ret["fid"]] = true;
          }
          $activitylist[]=$ret;
		 }
		 
	  }
	  
	}
  if( ($_GET['view'] == 'me')&& ($_GET['type'] == 'apply') ) {
		//我参与的
		$count = DB::result(DB::query("SELECT COUNT(*) FROM  "
            .DB::table("forum_forum")." ff, "
            .DB::table("forum_groupuser") ." fg "
            ." WHERE ff.fid=fg.fid ".
            " and  fg.uid='$space[uid]'  AND ff.type='activity' "),0);
	
	
	  if($count) {
		if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
			$apply_sql = '';
		}
		
		$sql = " SELECT ff.*,fff.digest, fff.banner, fff.category_id, fff.founderuid  as founderuid, fff.foundername as foundername, fff.membernum as membernum, ffa.type as ffatype, ffa.viewnum as viewnum, ffa.average,ffa.live_id,ffa.teacher_id ,fff.extra
		FROM ".DB::table("forum_forum")." ff, "
            .DB::table("forum_forumfield") ." fff ,"
            .DB::table("forum_forum_activity")." ffa ,"
            .DB::table("forum_groupuser")." fg "
            ." WHERE ff.fid=fff.fid AND ff.fid=ffa.fid AND fg.fid=ff.fid ".
            " AND fg.uid='$space[uid]' AND ff.type='activity' ";
            
	   // print_r("------". " $sql    LIMIT $start, $perpage");
		$query = DB::query("  $sql    LIMIT $start, $perpage");
		
		 while($ret= DB::fetch($query)){
		 	
		  
		 	
		  $extra = unserialize($ret["extra"]);
		  
		
          if($extra["startime"]!=0 && $extra["endtime"]!=0){
            $ret["str_start_time"] = getdate($extra["startime"]);
            $ret["str_end_time"] = getdate($extra["endtime"]);
          }
          
          $category=common_category_get_category_by_id($ret["category_id"]);
        
          $categorys[$ret["category_id"]]=$category;
          if(is_null($category["name"])){
          	//print_r("----我参与的id--". $ret["fid"]);
          	$is_enable_category[$ret["fid"]] = true;
          }
          $activitylist[]=$ret;
		 }
		 
		
	  }
	  
	}else{
	    //随便看看
	}
	
	$today = dstrtotime(dgmdate($_G['timestamp'], 'Y-m-d'));
		

}

if($count) {
	$multi = multi($count, $perpage, $page, $theurl);
}

include_once template("diy:home/space_activity");

?>