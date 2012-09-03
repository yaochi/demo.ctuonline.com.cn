<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	$groupid = $_GET["fid"];
	$gettype = $_GET["gettype"];
	require_once libfile("function/core");
	$addsql = "";
	$addurl = "";
	if ($gettype!='') {
		$addsql = " AND category_id=".$gettype;
		$addurl = "&gettype=".$gettype;
	}
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	
    $query = DB::query("SELECT * FROM ".DB::table("notice")." WHERE moderated>=0 AND group_id=".$groupid.$addsql." ORDER BY displayorder DESC, update_time DESC LIMIT $start,$perpage ");
    $notices = array();
    $_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
    while ($notice = DB::fetch($query)) {
	    if($notice['highlight']) {
			$string = sprintf('%02d', $notice['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			
			$notice['highlight'] = ' style="';
			$notice['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$notice['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
			$notice['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
			$notice['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
			$notice['highlight'] .= '"';
		} else {
			$notice['highlight'] = '';
		}
		
		require_once libfile("function/post");
		$notice['title'] = messagecutstr($notice['title'], 100);	//用于通知公告列表 by Izzln
		$notice['content'] = messagecutstr($notice['content'], 300);
		
		$relationid[]=$notice[id];
		$notices[$notice[id]] = $notice;
    }
   if($relationid){
		$query=DB::query("select anonymity,id from ".DB::TABLE("home_feed")." where icon='notice' and idtype!='feed' and id in (".implode(',',$relationid).")");
		while($value=DB::fetch($query)){
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$notices[$value[id]][repeats]=getforuminfo($value[anonymity]);
			}
		}
	}
   
	$getcount = getnoticecount($groupid, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$groupid."&plugin_name=notice&plugin_op=groupmenu".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
    
//	分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
	$other_plugin_info = common_category_is_other($groupid, $pluginid);
    if($other_plugin_info["state"]=='Y' && $other_plugin_info['prefix']=='Y'){
        $is_enable_category = true;
        $categorys = common_category_get_category($groupid, $pluginid);
    }
    
    return array("multipage"=>$multipage,"notices"=>$notices,"categorys"=>$categorys,"getcount"=>$getcount,"gettype"=>$gettype,"is_enable_category"=>$is_enable_category);
}

function getnoticecount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) FROM ".DB::table('notice')." WHERE group_id=".$groupid.$addsql." AND moderated>=0");
	return DB::result($query, 0);
}

function view(){
	global $_G;
    $noticeid = $_GET["noticeid"];
	$anonymity=DB::result_first("select anonymity from ".DB::TABLE("home_feed")." where icon='notice' and id=".$noticeid);
	if($anonymity>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeats=getforuminfo($anonymity);
	}
    //经验值
    group_add_empirical_by_setting($_G['uid'], $_G['fid'], 'article_view', $noticeid);
    //阅读次数+1
    view_num_add($noticeid);
    require_once libfile('function/credit');
    //积分
    credit_create_credit_log($_G['uid'], "viewnotice", $noticeid);
    
	$notice = array();
    $query = DB::query("SELECT * FROM ".DB::table("notice")." WHERE id=".$noticeid);
    $notice = DB::fetch($query);
    if (!$notice) {
    	showmessage('notice_noexist', 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=notice&plugin_op=groupmenu');
    }
    $notice['realname'] = user_get_user_name($notice['uid']);
    require_once libfile('function/discuzcode');
    $notice['content'] = discuzcode($notice['content']);
    
	$query = DB::query("SELECT * FROM ".DB::table("common_category")." WHERE id=".$notice["category_id"]);
    $category = DB::fetch($query);
    
    //获得专区信息
    $query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
    		 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
    $group = DB::fetch($query);
	$group['icon'] = get_groupimg($group['icon'], 'icon');
	
	//表态
	loadcache('click');
	$clicks = empty ($_G['cache']['click']['blogid']) ? array () : $_G['cache']['click']['blogid'];
	$_G['cache']['click']['noticeid'] = $clicks;
	$hash = md5($notice['uid']."\t".$notice['dateline']);
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $notice["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if ($value['clicknum'] > $maxclicknum)
			$maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$clickuserlist = array ();
	$idtype = "noticeid";
	$query = DB :: query("SELECT * FROM " . DB :: table('home_clickuser') . "
			WHERE id='$notice[id]' AND idtype='$idtype'
			ORDER BY dateline DESC
			LIMIT 0,20");
	while ($value = DB :: fetch($query)) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		if($value['anonymity']>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value['anonymity']);
			$value[repeats]=$repeatsinfo;
		}
		$clickuserlist[] = $value;
	}
	
	//评论
	$feedarr=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where icon='notice' and idtype='noticeid' and id=".$noticeid);
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;
		
	require_once libfile('function/home');
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$commentlist = array ();
	if($feedarr){
		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$feedarr[feedid]' AND idtype='feed'"), 0);
	}else{
		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$notice[id]' AND idtype='noticeid'"), 0);
	}
	if ($count) {
		if($feedarr){
			$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$feedarr[feedid]' AND idtype='feed' ORDER BY dateline LIMIT $start,$perpage");
		}else{
			$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$notice[id]' AND idtype='noticeid' ORDER BY dateline LIMIT $start,$perpage");
		}
		while ($value = DB :: fetch($query)) {
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$value[repeats]=$repeatsinfo;
			}
			$commentlist[] = $value;
		}
		$commentreplynum = $count;
	} else {
		$commentreplynum = 0;
	}
	require_once libfile("function/post");
	//分享
	$titlelink = 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='.$notice['id'].'&notice_action=view';
	$params['id'] = $notice['id'];
	$params['subject'] = base64_encode($notice['title']);
	$params['subjectlink'] = base64_encode($titlelink);
	$params['authorid'] = $notice['uid'];
	$params['author'] = base64_encode($notice['username']);
	$params['message'] = base64_encode(messagecutstr($notice['content'], 300));
	$params['image'] = base64_encode($notice['imgurl']);
	$params['type'] = "noticeid";
	$params['fid'] = $notice['group_id'];
	
	foreach($params as $key => $value){
		$url .= $key ."=".$value."&";
	}
	
	$notice['shareurl'] = "home.php?mod=spacecp&ac=share&handlekey=sharehk_1&".$url;
	
	$groupnav = get_groupnav($_G['forum']);
	$generalid=$notice['id'];
	include template("notice:view");
	dexit();
//    return array("_G"=>$_G, "clickuserlist"=>$clickuserlist, "commentlist"=>$commentlist, "commentreplynum"=>$count, "hash"=>$hash, "generalid"=>$notice['id'], "idtype"=>"noticeid", "clicks"=>$clicks, "notice"=>$notice, "category"=>$category, "group"=>$group);
}

function view_num_add($noticeid) {
	DB::query("UPDATE ".DB::table("notice")." SET viewnum=viewnum+1 WHERE id=".$noticeid);
}

function to_invalid(){
	$noticeid = $_GET["noticeid"];
	DB::query("UPDATE ".DB::table("notice")." SET status=2 WHERE id=".$noticeid);
	showmessage('状态已更新', join_plugin_action('index'));
}

function to_valid(){
	$noticeid = $_GET["noticeid"];
	DB::query("UPDATE ".DB::table("notice")." SET status=1 WHERE id=".$noticeid);
	showmessage('状态已更新', join_plugin_action('index'));
}

function delete(){
	$noticeid = $_GET["noticeid"];
	DB::query("DELETE FROM ".DB::table("notice")." WHERE id=".$noticeid);
	hook_delete_resource($noticeid,"notice");
	showmessage('删除成功', join_plugin_action('index'));
}

function update(){
	$noticeid = $_GET["noticeid"];
	$updatetime = time();
	DB::query("UPDATE ".DB::table("notice")." SET content='".$_POST["content"]."', title='".$_POST["noticetitle"]."', type='".$_POST["typeid"]."', update_time=".$updatetime." WHERE id=".$noticeid);
    showmessage('更新成功', join_plugin_action('index'));
}

function search($title) {
	if($name) {
		$query = DB::query("SELECT * FROM ".DB::table("notice")." WHERE title LIKE '%".$title."%' ORDER BY create_time DESC LIMIT 0,20");
	    $notices = array();
	    while ($notice = DB::fetch($query)) {
	    	$notices[] = $notice;
	    }
	}
	return array("notices"=>$notices, "searchtitle"=>$title);
}

?>
