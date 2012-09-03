<?php
/*
 * create by qiaoyz
 */
 require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

/**
 *详情页
 */
 function index(){
 	global $_G;
 	$id= intval($_G["gp_id"]) ? intval($_G["gp_id"]) : 1;
	require_once (dirname(__FILE__)."/function/function_sharesource.php");
	$detail=getsharesource($id);
	DB::query("update ".DB::table("sharesource")." set viewnum=viewnum+1 WHERE id=".$id);

	//评论
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)	$page = 1;
	$perpage = 20;
	$start = ($page -1) * $perpage;
	$commentlist = array ();
	$comment_replynum = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='sharesourceid'"), 0);
	if ($comment_replynum) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='sharesourceid' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$commentlist[] = $value;
		}
	}
	$multi = multi($comment_replynum, $perpage, $page, "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=viewmenu&id=".$id);

 	include template("sharesource:view/index");
	dexit();
 }

/**
 * 添加评论
 */
 function comment(){
 	global $_G;
 	$comment[uid]=$comment[authorid]=$_G[uid];
 	$comment[author]=$_G[username];
 	$comment[id]=$_G[gp_id];
 	$comment[idtype]='sharesourceid';
 	$comment[dateline]=time();
 	$comment[message]=$_G[gp_message];
 	$comment[ip]='10.127.1.25';
	if(trim($comment[message])!='') {
		DB :: insert("home_comment", $comment);
		DB::query("update ".DB::table("sharesource")." set commentnum=commentnum+1,lastuid=".$_G[uid].",lastname='".user_get_user_name($_G[uid])."' WHERE id=".$_G[gp_id]);
	}
	showmessage("评论成功！","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=viewmenu&id=".$_G[gp_id]);
 }

 /**
 * 排行榜
 */
 function top(){
 	global $_G;
 	require_once (dirname(__FILE__)."/function/function_sharesource.php");
 	$list=getrank(0,50);
	return array("list"=>$list);
}


?>
