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
        $sql = "SELECT count(1) as c FROM " . DB::table("questionary") . " t
		WHERE t.fid=$fup  AND moderated!=-1 ";
        $query = DB::query($sql);
        $count = DB::fetch($query, 0);
        $count = $count["c"];
        if ($count) {
            $sql = "SELECT t.* FROM " . DB::table("questionary") . " t
			WHERE t.fid=$fup AND moderated!=-1
			ORDER BY t.displayorder DESC, t.dateline DESC
			LIMIT $start, $pagesize";
            $query = DB::query($sql);
            while ($row = DB::fetch($query)) {
                $row[dateline] = dgmdate($row[dateline]);
				$row[username]=user_get_user_name_by_username($row[username]);
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
    $sql = "SELECT t.* FROM " . DB::table("questionary") . " t WHERE questid IN(" . implode(",", $ids) . ")";
    $query = DB::query($sql);
    $tids = array();
    $count = 0;
    while ($row = DB::fetch($query)) {
        $count++;
        $sql = "INSERT INTO " . DB::table("questionary") . " (fid,uid,username,questname,questdescr,visible,classid,scored,dateline)
		VALUES ('$fid', '$row[uid]', '$row[username]', '$row[questname]', '$row[questdescr]', '$row[visible]', '0', '$row[scored]', '$row[dateline]')";
        DB::query($sql);
        $tid = DB::insert_id();
		hook_create_resource($tid,'question',$fid);
		$questionsql = "SELECT t.* FROM " . DB::table("questionary_question") . " t WHERE questid=$row[questid]";
		$questionquery = DB::query($questionsql);
		while($questionrow=DB::fetch($questionquery)){
			 $questionsql = "INSERT INTO " . DB::table("questionary_question") . " (question,questiondescr,multiple,maxchoices,questid)
		VALUES ('$questionrow[question]', '$questionrow[questiondescr]', '$questionrow[multiple]', '$row[maxchoices]', '$tid')";
			DB::query($questionsql);
			$questionid=DB::insert_id();
			$qoptionsql = "SELECT t.* FROM " . DB::table("questionary_questionoption") . " t WHERE questionid=$questionrow[questionid]";
			$qoptionquery = DB::query($qoptionsql);
			while($qoptionrow=DB::fetch($qoptionquery)){
				 $qoptionsql = "INSERT INTO " . DB::table("questionary_questionoption") . " (questionid,questionoption,descr,weight,questid)
		VALUES ('$questionid', '$qoptionrow[questionoption]', '$qoptionrow[descr]', '$qoptionrow[weight]', '$qoptionrow[questid]')";
			 DB::query($qoptionsql);
			}
		}
        $tids[] = $row[tid];
        $tidmap[$row[tid]] = $tid;
    }
    $url = "forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
    DB::query("UPDATE " . DB::table("forum_forum") . " SET threads=$count WHERE fid=$fid");
    showmessage("导入完毕", $url);
}

?>
