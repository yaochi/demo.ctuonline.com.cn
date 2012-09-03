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
        $sql = "SELECT count(1) as c FROM " . DB::table("forum_forum_lecturer") . " t
		WHERE t.fid=$fup ";
        $query = DB::query($sql);
        $count = DB::fetch($query, 0);
        $count = $count["c"];
        if ($count) {
            $sql = "SELECT t.* FROM " . DB::table("forum_forum_lecturer") . " t
			WHERE t.fid=$fup 
			ORDER BY t.displayorder DESC, t.dateline DESC
			LIMIT $start, $pagesize";
            $query = DB::query($sql);
            while ($row = DB::fetch($query)) {
                $row[dateline] = dgmdate($row[dateline]);
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
    $fname = $_G["forum"]["name"];
    $ids = $_POST[ids];
    if (!$ids) {
    	showmessage("请先选择讲师", $_SERVER['HTTP_REFERER']);
    }
    $sql = "SELECT t.* FROM " . DB::table("forum_forum_lecturer") . " t WHERE id IN(" . implode(",", $ids) . ")";
    $query = DB::query($sql);
    $tids = array();
    $count = 0;
    while ($row = DB::fetch($query)) {
        $count++;
        $sql = "INSERT INTO " . DB::table("forum_forum_lecturer") . " (fid,fname,lecid,lecname,imgurl,about,dateline)
		VALUES ('$fid', '$fname', '$row[lecid]', '$row[lecname]', '$row[imgurl]', '$row[about]', '$row[dateline]')";
        DB::query($sql);
        $tid = DB::insert_id();
        $tids[] = $row[tid];
        $tidmap[$row[tid]] = $tid;
    }
    $url = "forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
    DB::query("UPDATE " . DB::table("forum_forum") . " SET threads=$count WHERE fid=$fid");
    showmessage("导入完毕", $url);
}

?>
