<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");



function index(){
	global $_G;
	$query = DB::query("SELECT fup FROM " . DB::table("forum_forum") . " WHERE fid=" . $_G["fid"]);
	$result = DB::fetch($query);
	if ($result) {
		$fup = $result['fup'];
		$pagesize=10;
		$page=intval($_GET['page'])?intval($_GET['page']):1;
		$start = ($page - 1) * $pagesize;
		$sql="SELECT count(*) AS c FROM ".DB::table("forum_thread")." WHERE special=3 AND fid=$fup AND displayorder IN (0, 1, 2, 3, 4)";
		$query=DB::query($sql);
		$count=DB::fetch($query,0);
		$count=$count['c'];

		$qlist=array();
		if($count){
			$sql="SELECT ft.*, cmp.realname realname FROM ".DB::table('forum_thread')." ft, ".DB::table("common_member_profile")." cmp WHERE ft.authorid=cmp.uid AND ft.special=3 AND ft.fid=$fup AND ft.displayorder IN (0, 1, 2, 3, 4) ORDER BY ft.displayorder DESC, ft.lastpost DESC LIMIT $start,$pagesize";

			$query=DB::query($sql);
			while($question=DB::fetch($query)){
				$question['lastpostername'] = user_get_user_name_by_username($question['lastposter']);
				$question['dateline']=dgmdate($question['dateline']);
				$question['lastpost']=dgmdate($question['lastpost']);
				$qlist[$question['tid']]=$question;
			}
			$url = "forum.php?mod=activity&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=$_G[gp_plugin_op]";
			$multipage = multi($count, $pagesize, $page, $url);
			//exit("listsize:".count($qlist));
			$_G['forum_threadlist']=$qlist;
			return array(data=>$qlist, multipage=>$multipage);
		}

	}
	return array();
}

function create() {
	global $_G;
	$fid=$_G['fid'];
	$ids = $_POST[ids];
	$sql="SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN (".implode(',',$ids).")";
	$query=DB::query($sql);
	$tids = array();
	$count = 0;
	while($row=DB::fetch($query)){
		$count++;
		$sql="INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup, category_id)
		VALUES ('$fid', '$row[posttableid]', '$row[readperm]', '$row[price]', '$typeid', '$row[sortid]', '$row[author]', '$row[authorid]', '$row[subject]', '$row[dateline]', '$row[dateline]', '$row[author]', '$row[displayorder]', '$row[digest]', '$row[special]', '0', '$row[moderated]', '$row[status]', '$row[isgroup]', '0')";
		DB::query($sql);
		$tid = DB::insert_id();
		hook_create_resource($tid, "qbar",$fid);
		$tids[] = $row['tid'];
		$tidmap[$row['tid']] = $tid;
	}
	$sql = "SELECT p.* FROM pre_forum_post p WHERE p.tid IN(".implode(",", $tids).") AND p.invisible='0'";
	$query = DB::query($sql);
	while($row = DB::fetch($query)){
		insertpost(array(
		'fid' => $fid,
		'tid' => $tidmap[$row[tid]],
		'first' => '1',
		'author' => $row['author'],
		'authorid' => $row['authorid'],
		'subject' => $row[subject],
		'dateline' => $row['dateline'],
		'message' => $row[message],
		'useip' => $row['useip'],
		'invisible' => $row[pinvisible],
		'anonymous' => $row[isanonymous],
		'usesig' => $row['usesig'],
		'htmlon' => $row[htmlon],
		'bbcodeoff' => $row[bbcodeoff],
		'smileyoff' => $row[smileyoff],
		'parseurloff' => $row[parseurloff],
		'attachment' => '0',
		'tags' => $row[tags],
		));
	}
	$url = "forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
	DB::query("UPDATE ".DB::table("forum_forum")." SET threads=$count WHERE fid=$fid");
	showmessage("导入完毕", $url);
}
?>