<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
	global $_G;
	
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    $query = DB::query("SELECT flec.id id,lec.uid uid, lec.name name, flec.imgurl imgurl, lec.gender gender, lec.orgname orgname, lec.tel tel, lec.email email, lec.isinnerlec isinnerlec, flec.about about, lec.teachdirection teachdirection, lec.rank rank, lec.courses courses,lec.tempusername tempusername FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec 
    		WHERE lec.id=flec.lecid AND flec.id=".$_GET["lecid"]." AND flec.fid=".$_G["fid"]);
    
    $lecturer = array();
    while ($row = DB::fetch($query)) {
    	$lecturer['id'] = $row['id'];
    	$lecturer['uid']=$row['uid'];
    	$lecturer['name'] = $row['name'];
    	$lecturer['imgurl'] = $row['imgurl'];
    	$lecturer['gender'] = $row['gender'];
    	$lecturer['orgname'] = $row['orgname'];
    	$lecturer['isinnerlec'] = $row['isinnerlec'];
    	$lecturer['about'] = $row['about'];
    	$lecturer['tel'] = $row['tel'];
    	$lecturer['email'] = $row['email'];
    	$lecturer['teachdirection'] = $row['teachdirection'];
    	$lecturer['rank'] = $row['rank'];
    	$lecturer['courses'] = unserialize($row['courses']);
    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]." AND fid=".$_G["fid"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    	}
    }
    $lectureruid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$lecturer[tempusername 	]."'");
    require_once libfile('function/home_attachment');
	$attachments = getattachs($lecturer['id'], 'lecid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];
    
    return array("attachlist"=>$attachlist, "groupabouts"=>$groupabouts,"lectureruid"=>$lectureruid, "lecturer"=>$lecturer);
}

function edit() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    $query = DB::query("SELECT flec.id id,lec.uid,uid, lec.name name, flec.imgurl imgurl, lec.gender gender, lec.orgname orgname, lec.iscollegelec iscollegelec, lec.isinnerlec isinnerlec, flec.about about, lec.teachdirection teachdirection, lec.rank rank, lec.courses courses FROM ".DB::table("lecturer")." lec, ".DB::table("forum_forum_lecturer")." flec 
    		WHERE lec.id=flec.lecid AND flec.id=".$_GET["lecid"]);
    
    $lecturer = array();
    while ($row = DB::fetch($query)) {
    	$lecturer['id'] = $row['id'];
    	
    	$lecturer['name'] = $row['name'];
    	$lecturer['uid']=$row['uid'];
    	$lecturer['imgurl'] = $row['imgurl'];
    	$lecturer['gender'] = $row['gender'];
    	$lecturer['orgname'] = $row['orgname'];
    	$lecturer['iscollegelec'] = $row['iscollegelec'];
    	$lecturer['isinnerlec'] = $row['isinnerlec'];
    	$lecturer['about'] = $row['about'];
    	$lecturer['teachdirection'] = $row['teachdirection'];
    	$lecturer['rank'] = $row['rank'];
    	$lecturer['courses'] = unserialize($row['courses']);
    }
    
    return array("lecturer"=>$lecturer);
    
}

function modify() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_POST["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
//	获取图片url
	if ($_POST["aid"]) {
	    $imgurl = null;
	    
	    $query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
	    $img = DB::fetch($query);
	    $imgurl = ", imgurl='data/attachment/home/".$img["attachment"]."'";
	}
    
    DB::query("UPDATE ".DB::table("forum_forum_lecturer")." SET about='".$_POST["about"]."'".$imgurl." WHERE id=".$_POST["lecid"]." AND fid=".$_G['fid']);
    $params = array (
		'lecid' => $_POST["lecid"],
	);
    showmessage('更新成功', join_plugin_action2('index', $params));
}

function delete() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
    
    DB::query("DELETE FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$_GET['lecid']." AND fid=".$_G['fid']);
    showmessage('删除成功', join_plugin_action('index'));
}

function getlecturercount($groupid, $addsql) {
	$query = DB::query("SELECT count(*) FROM ".DB::table('forum_forum_lecturer')." WHERE fid=".$groupid.$addsql);
	return DB::result($query, 0);
}

?>
