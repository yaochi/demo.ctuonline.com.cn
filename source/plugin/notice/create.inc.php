<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function edit() {
	global  $_G;
	require_once libfile("function/forum");
	loadforum();
	$editorid = "e";
    $editormode = 1;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message");
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
    
    $noticeid = $_GET["noticeid"];
    
    //获取信息
	$notice = array();
    $query = DB::query("SELECT * FROM ".DB::table("notice")." WHERE id=".$noticeid);
    $notice = DB::fetch($query);
    
    $editor['value'] = $notice['content'];
    
    //分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
    if(common_category_is_enable($_G['fid'],$pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G['fid'],$pluginid);
    }
    
    return array("notice"=>$notice, "albumlist"=>$albumlist, "is_enable_category"=>$is_enable_category, "categorys"=>$categorys, "levels"=>$levels, "editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G);
    
}

function index(){
    global  $_G;
	require_once libfile("function/forum");
	loadforum();
	$editorid = "e";
    $editormode = 1;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message");
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
    
    $groupid = $_GET["fid"];
    
//查询用户等级
	$levels = array(0 => "全部",
		1 => lang('group/template', 'group_moderator'),
		2 => lang('group/template', 'group_moderator_vice'),
		4 => lang('group/template', 'group_normal_member'));
	$query = DB::query("SELECT * FROM " . DB::table("forum_userlevel") . " WHERE fid=" . $_G["fid"]);
	while ($row = DB::fetch($query)) {
		$levels[$row["id"]] = $row["level_name"];
	}
    
    //分类
	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
    if(common_category_is_enable($groupid, $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($groupid, $pluginid);
    }
    
    return array("albumlist"=>$albumlist, "is_enable_category"=>$is_enable_category, "categorys"=>$categorys, "levels"=>$levels, "editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G);
}

function new_type(){
	DB::insert("notice_type", array("name"=>$_POST["typename"],
			"group_id"=>$_POST["groupid"],
			"create_time"=>time(),
			uid=>$_G['uid']));
	showmessage('创建成功', join_plugin_action('index'));		
}

function default_type(){
	DB::insert("notice_type", array("name"=>"列表",
			"group_id"=>$_GET["groupid"],
			"create_time"=>time(),
			uid=>$_G['uid']));
	DB::insert("notice_type", array("name"=>"公告",
			"group_id"=>$_GET["groupid"],
			"create_time"=>time(),
			uid=>$_G['uid']));
	DB::insert("notice_type", array("name"=>"新闻",
			"group_id"=>$_GET["groupid"],
			"create_time"=>time(),
			uid=>$_G['uid']));
	showmessage('初始化成功', join_plugin_action('index'));
}

function to_new_type(){
	$groupid = $_GET["groupid"];
	return array("groupid"=>$groupid);
}

function type_list(){
	$groupid = $_GET["fid"];
	$query = DB::query("SELECT * FROM ".DB::table("notice_type")." WHERE group_id=".$groupid);
    $noticetypes = array();
    while ($noticetype = DB::fetch($query)) {
    	$noticetypes[] = $noticetype;
    }
	return array("noticetypes"=>$noticetypes);
}

function create(){
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
	$createtime = time();
	$title = $_POST["noticetitle"];
	$groupid = $_POST["groupid"];
	$username = $_G['username'];
	
	$repliesdisplayoff = '0';
	if ($_POST["repliesdisplayoff"]) {
		$repliesdisplayoff = $_POST["repliesdisplayoff"];
	}
	$repliesoff = '0';
	if ($_POST["repliesoff"]) {
		$repliesoff = $_POST["repliesoff"];
	}
	$status = '0';
	if ($_POST["status"]) {
		$status = $_POST["status"];
	}
	$anonymity=$_POST["anonymity"];
	if(!$anonymity){
		$anonymity=$_G['member']['repeatsstatus'];
	}
	if($anonymity>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeatsinfo=getforuminfo($anonymity);
	}
	
//	获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    }
    $imgurl = "";
    if(!empty($img["attachment"])) {
    	$imgurl = "data/attachment/home/".$img["attachment"];
    }
	
    DB::insert("notice", array("title"=>$_POST["noticetitle"],
			"content"=>$_POST["message"],
    		"imgurl"=>$imgurl,
			"status"=>$status,
			"group_id"=>$_POST["groupid"],
    		"category_id"=>$_POST["category"],    
			"create_time"=>$createtime,
    		"update_time"=>$createtime,
			uid=>$_G['uid'],
			"displayorder"=>$_POST["isdisplayorder"],
			"repliesdisplayoff"=>$repliesdisplayoff,
			"repliesoff"=>$repliesoff,
			"ip"=>$_G['clientip'],//获取IP
			"username"=>$_G['username']));
	$newid = DB::insert_id();
	hook_create_resource($newid,"notice",$_G['fid']);
	
	
	//发送动态
	//if($_POST["sendfeed"]==1) {
		require_once libfile('function/feed');
			if($repeatsinfo){
				$tite_data = array('username' => '<a class="perPanel" href="forum.php?mod=group&fid='.$repeatsinfo['fid'].'">'.$repeatsinfo['name'].'</a>', 'noticetitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$groupid.'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='.$newid.'&notice_action=view">'.$title.'</a>');
			}else{
				$tite_data = array('username' => '<a class="perPanel" href="home.php?mod=space&uid='.$_G['uid'].'">'.user_get_user_name_by_username($username).'</a>', 'noticetitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$groupid.'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='.$newid.'&notice_action=view">'.$title.'</a>');
			}
				$body_data= array('message'=>getstr($_POST["message"], 150, 1, 1, 0, 0, -1));
		feed_add('notice', 'feed_notice', $tite_data, '{message}', $body_data, '', array(), array(), '', '', '', 0, $newid, 'noticeid', $_G['uid'], user_get_user_name_by_username($username),$_G['fid'],array(),'',0,0,$anonymity);
	//}

	//发送通知
	$issendmsg = $_POST["issendmsg"];
	if($issendmsg) {
		$senduser = $_POST["senduser"];
//		$levels = dimplode(array_keys($senduser));
		$addsql = "";
		if ($senduser) {
			$addsql = "AND level >0 AND level <= $senduser";
		}
		$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$groupid'".$addsql);
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		//判断是否机构专区
		if ($_G["forum"]["group_type"]<20) {
			$notificationtype = "zq_notice_org";
		} else {
			$notificationtype = "zq_notice";
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
//				notification_add($uid, 'group', 'group_notice_create', array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$username.'</a>', 'noticetitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$groupid.'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='.$newid.'&notice_action=view">'.$title.'</a>'), 1);
				notification_add($uid, $notificationtype, '[专区通知公告]“{groupname}”在{date}发布“{noticetitle}”的通知公告，赶快去看看吧', array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'date'=>date("Y年m月d日",time()), 'noticetitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$groupid.'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='.$newid.'&notice_action=view">'.$title.'</a>'), 1);
			}
		}
	}
    showmessage('创建成功', join_plugin_action('index'));
}

function save() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    //获取信息
    $noticeid = $_GET["noticeid"];
    
    $isdisplayorder = "";
    $allowreplies = "";
    $category = "";
    if ($_POST["isdisplayorder"]) {
    	$isdisplayorder = ", displayorder=".$_POST["isdisplayorder"];
    } else {
    	$isdisplayorder = ", displayorder=0";
    }
    if ($_POST["repliesdisplayoff"]) {
    	$repliesdisplayoff = ", repliesdisplayoff=".$_POST["repliesdisplayoff"];
    } else {
    	$repliesdisplayoff = ", repliesdisplayoff=0";
    }
	if ($_POST["repliesoff"]) {
    	$repliesoff = ", repliesoff=".$_POST["repliesoff"];
    } else {
    	$repliesoff = ", repliesoff=0";
    }
    if ($_POST["category"]) {
    	$category = ", category_id=".$_POST["category"];
    }
    if ($_POST["status"]) {
    	$status = ", status=".$_POST["status"];
    }
    
    //	获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    	$imgurl = "data/attachment/home/".$img["attachment"];
    	$imgurlsql = ", imgurl='".$imgurl."'";
    }
    
    $updatetime = time();
	DB::query("UPDATE ".DB::table("notice")." SET content='".$_POST["message"]."', title='".$_POST["noticetitle"]."' ".$status.$isdisplayorder.$repliesdisplayoff.$repliesoff.$category.$imgurlsql.", update_time=".$updatetime." WHERE id=".$noticeid);
    showmessage('更新成功', join_plugin_action('edit'));
}


?>
