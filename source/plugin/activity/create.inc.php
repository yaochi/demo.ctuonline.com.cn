<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
    require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
    if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
    return array(categorys=>$categorys, is_enable_category=>$is_enable_category);
}

function save(){
    global $_G;
    $fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	$anonymity=$_G[member][repeatsstatus];
	
    $teacher_ids = $_POST["teacherids"];
	$live_ids = $_POST["livesids"];

	$teacher_ids = explode(",", $teacher_ids);
	$live_ids = explode(",", $live_ids);
    array_pop($teacher_ids);
    array_pop($live_ids);
    
	$live_ids = serialize($live_ids);
    $teacher_ids = serialize($teacher_ids);
    
    if(!$teacher_ids && $_POST["type"]==2){
		showmessage('请选择讲师', join_plugin_action("index"));
	}
    $type = "live";
    if($_POST["type"]==1){
        $type = "general";
    }
	$pluginid = $_GET["plugin_name"];
    require_once libfile("function/category");
    $other = common_category_is_other($_G["fid"], $pluginid);
    if($other["state"]=='Y' && $other["required"]=='Y' && !isset($_POST["category"])){
        showmessage('请选择类型', join_plugin_action("index"));
    }
    $levelid = DB::result_first("SELECT levelid FROM ".DB::table('forum_grouplevel')." WHERE creditshigher<='0' AND '0'<creditslower LIMIT 1");
	
    DB::query("INSERT INTO ".DB::table('forum_forum')."(fup, type, name, status, level) VALUES (".$fup.", 'activity', '$_POST[name]', '3', '$levelid')");
    $newfid = DB::insert_id();
    hook_create_resource($newfid, "activity",$fup);
    if($newfid) {
        $jointype = intval($_G['gp_jointype']);
        $gviewperm = intval($_G['gp_gviewperm']);
	
		$descriptionnew = dhtmlspecialchars(censor(trim($_G['gp_descriptionnew'])));
        $tagnew = dhtmlspecialchars(censor(trim($_G['gp_tagnew'])));
        $query = DB::query("SELECT group_type,founderuid FROM ".DB::table("forum_forumfield")." WHERE fid=".$_G["fid"]);
        $group = DB::fetch($query);
		
		if($group["group_type"]){
            $query = DB::query("SELECT * FROM ".DB::table("common_member")." WHERE uid=".$group[founderuid]." LIMIT 1");
            $result = DB::fetch($query);

            $userid = $result["uid"];
            $username = $result["username"];

            if($_G[uid]==$result[uid]){
                $userid = $_G[uid];
                $username = $_G[username];
            }
            
            $group_type = $group["group_type"];
			$startime = strtotime($_POST["starttime"]);
			$endtime = strtotime($_POST["endtime"]);
            $signuptime = strtotime($_POST["signuptime"]);
            $category_id = $_POST["category"];
            $extra = serialize(array(startime=>$startime, endtime=>$endtime, signuptime=>$signuptime));
			DB::query("INSERT INTO ".DB::table('forum_forumfield')."(fid, description, jointype, category_id, gviewperm, dateline, founderuid,
                        foundername, membernum, tag, group_type, extra) VALUES ('$newfid', '$descriptionnew', '$jointype', '$category_id', '$gviewperm', '".TIMESTAMP."', '$_G[uid]', '$_G[username]', '1', '$tagnew', '$group_type', '$extra')");
            DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum=groupnum+1 WHERE fid='$_G[gp_fup]'");
            DB::query("INSERT INTO ".DB::table('forum_groupuser')."(fid, uid, username, level, joindateline) VALUES ('$newfid', '$_G[uid]', '$_G[username]', '1', '".TIMESTAMP."')");
			DB::query("INSERT INTO ".DB::table("forum_forum_activity")."(fid, viewnum, teacher_id, live_id, type) VALUES($newfid, 1, '$teacher_ids', '$live_ids', '$type')");

            if($_G[uid]!=$result[uid]){
                DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate) VALUES ('$newfid', '$userid', '$username', '1', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
                update_usergroups($uid);
                DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$newfid'");
            }
            
            //group_create_empirical($newfid);
            group_create_plugin($newfid);
            require_once libfile('function/cache');
            updatecache('grouptype');
        }
    }
    include_once libfile('function/stat');
    updatestat('group');
    require_once libfile("function/credit");
    credit_create_credit_log($_G[uid], "createevent", $newfid);
    //经验值
    group_add_empirical_by_setting($_G['uid'], $newfid, 'activity_create', $newfid);
	require_once libfile('function/post');
	$feed = array(
			'icon' => '',
			'title_template' => '',
			'title_data' => array(),
			'body_template' => '',
			'body_data' => array(),
			'title_data'=>array(),
			'images'=>array()
		);
	$feed['fid']=$_G['fid'];
	$feed['id']=$newfid;
	$feed['idtype']='activityid';
	$feed['icon'] = 'activitys';
	$feed['title_template'] = 'feed_thread_activity_title';
	$feed['body_template'] = 'feed_thread_activity_message';
	$feed['body_data'] = array(
		'subject' => "<a href=\"$_G[siteurl]forum.php?mod=activity&fid=$newfid\">$_POST[name]</a>",
		'message' => cutstr($descriptionnew, 150),
	);
	$feed['anonymity']=$anonymity;
	postfeed($feed);
    
    showmessage('新建活动成功', "forum.php?mod=activity&fid=$newfid");
}
?>
