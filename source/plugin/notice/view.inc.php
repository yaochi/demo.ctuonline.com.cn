<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
    $noticeid = $_GET["noticeid"];
    
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
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$notice[id]' AND idtype='noticeid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$notice[id]' AND idtype='noticeid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$commentlist[] = $value;
		}
	}
	
	//分享
	$titlelink = 'forum.php?mod=group&action=plugin&fid='.$_G['fid'].'&plugin_name=notice&plugin_op=viewmenu&diy=&noticeid='.$notice['id'].'&notice_action=index';
	$params['id'] = $notice['id'];
	$params['subject'] = base64_encode($notice['title']);
	$params['subjectlink'] = base64_encode($titlelink);
	$params['authorid'] = base64_encode($notice['uid']);
	$params['author'] = base64_encode($notice['username']);
	$params['message'] = base64_encode($notice['content']);
	$params['image'] = base64_encode($notice['imgurl']);
	$params['type'] = "noticeid";
	$params['fid'] = $notice['group_id'];
	
	foreach($params as $key => $value){
		$url .= $key ."=".$value."&";
	}
	
	$notice['shareurl'] = "home.php?mod=spacecp&ac=share&handlekey=sharehk_1&".$url;
	
    return array("_G"=>$_G, "clickuserlist"=>$clickuserlist, "commentlist"=>$commentlist, "commentreplynum"=>$count, "hash"=>$hash, "generalid"=>$notice['id'], "idtype"=>"noticeid", "clicks"=>$clicks, "notice"=>$notice, "category"=>$category, "group"=>$group);
}

function view_num_add($noticeid) {
	DB::query("UPDATE ".DB::table("notice")." SET viewnum=viewnum+1 WHERE id=".$noticeid);
}

?>
