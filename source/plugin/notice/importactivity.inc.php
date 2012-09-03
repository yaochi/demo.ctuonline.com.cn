<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");

function index() {
    global $_G;
    $query = DB::query("SELECT fup FROM " . DB::table("forum_forum") . " WHERE fid=" . $_G["fid"]);
    $result = DB::fetch($query);
    if ($result) {
        $fup = $result[fup];
        $pagesize = 10;
        $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
        $start = ($page - 1) * $pagesize;
        $sql = "SELECT count(1) as c FROM " . DB::table("notice") . " t
		WHERE t.group_id=$fup  AND moderated!=-1 ";
        $query = DB::query($sql);
        $count = DB::fetch($query, 0);
        $count = $count["c"];
        if ($count) {
            $sql = "SELECT t.* FROM " . DB::table("notice") . " t
			WHERE t.group_id=$fup AND moderated!=-1
			ORDER BY t.displayorder DESC, t.create_time DESC
			LIMIT $start, $pagesize";
            $query = DB::query($sql);
            while ($row = DB::fetch($query)) {
                $row[dateline] = dgmdate($row[create_time]);
                $row[realname] = user_get_user_name($row[uid]);
                $topics[] = $row;
            }
            $url = "forum.php?mod=activity&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=$_G[gp_plugin_op]";
            $multipage = multi($count, $pagesize, $page, $url);
            return array(data => $topics, multipage => $multipage);
        }
    }
    return array();
}

function create() {
    global $_G;
    $fid = $_G["fid"];
    $ids = $_POST[ids];
	if (!$ids) {
    	showmessage("请先选择通知公告", $_SERVER['HTTP_REFERER']);
    }
    $sql = "SELECT t.* FROM " . DB::table("notice") . " t WHERE id IN(" . implode(",", $ids) . ")";
    $query = DB::query($sql);
    $tids = array();
    $count = 0;
    while ($row = DB::fetch($query)) {
        $count++;
        $sql = "INSERT INTO " . DB::table("notice") . " (group_id,uid,username,title,content,imgurl,create_time,update_time,status)
		VALUES ('$fid', '$row[uid]', '$row[username]', '".mysql_real_escape_string($row[title])."', '".mysql_real_escape_string($row[content])."', '$row[imgurl]', '$row[create_time]', '$row[update_time]', '$row[status]')";
        DB::query($sql);
        $tid = DB::insert_id();
		hook_create_resource($tid,"notice",$fid);
        $tids[] = $row[tid];
        $tidmap[$row[tid]] = $tid;
    }
    $url = "forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
    DB::query("UPDATE " . DB::table("forum_forum") . " SET threads=$count WHERE fid=$fid");
    showmessage("导入完毕", $url);
}

?>
