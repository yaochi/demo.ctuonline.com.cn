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

    $query = DB::query("SELECT * from ".DB::TABLE("shlecture")." where id=".$_GET["lecid"]);
    
   	$lecturer= DB::fetch($query);
	//获得讲师uid
	$lectureruid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$lecturer[tempusername]."'");
	$query =DB::query("SELECT * from ".DB::TABLE("shlecture_direct")." where lecid=".$_GET['lecid']);
	while($row=DB::fetch($query)){
		$lecturer[teachdirection][]=$row[teachdirection];
		$lecturer[courses][]=explode(',',$row[courses]);
	}
	//喜欢程度
	$lecturer['fonders']=DB::result_first("SELECT count(*) from ".DB::TABLE("shlecture_stars")." where lecid=".$_GET['lecid']);
	//是否表态
	$isfondered=DB::result_first("SELECT count(*) from ".DB::TABLE("shlecture_stars")." where uid=".$_G[uid]." and lecid=".$_GET['lecid']);
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
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$lecturer[id]' AND idtype='shlecturerid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$lecturer[id]' AND idtype='shlecturerid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$commentlist[] = $value;
		}
	}
    require_once libfile('function/home_attachment');
	$attachments = getattachs($lecturer['id'], 'shlecid');
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];
    
    return array("attachlist"=>$attachlist,"lectureruid"=>$lectureruid, "lecturer"=>$lecturer, "commentreplynum"=>$count, "_G"=>$_G, "commentlist"=>$commentlist,"isfondered"=>$isfondered);
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
       $query = DB::query("SELECT * from ".DB::TABLE("shlecture")." where id=".$_GET["lecid"]);
    
   	$lecturer= DB::fetch($query);
	$query =DB::query("SELECT * from ".DB::TABLE("shlecture_direct")." where lecid=".$_GET['lecid']);
	while($row=DB::fetch($query)){
		$lecturer[teachdirectionid][]=$row[id];
		$lecturer[teachdirection][]=$row[teachdirection];
		$lecturer[courses][]=$row[courses];
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
    
   if ($_POST["orgname"]) {
    	$orgname = ", orgname='".$_POST["orgname"]."'";
    }
    
    if ($_POST["bginfo"]) {
    	$bginfo = ", bginfo='".$_POST["bginfo"]."'";
    }
	if ($_POST["perspecial"]) {
    	$perspecial = ", perspecial='".$_POST["perspecial"]."'";
    }
	if ($_POST["coursexperience"]) {
    	$coursexperience = ", coursexperience='".$_POST["coursexperience"]."'";
    }
	if ($_POST["rank"]) {
    	$rank = ", rank='".$_POST["rank"]."'";
    }
	if ($_POST["stars"]) {
    	$stars = ", stars='".$_POST["stars"]."'";
    }
	if ($_POST["tel"]) {
    	$tel = ", tel='".$_POST["tel"]."'";
    }
	if ($_POST["email"]) {
    	$email = ", email='".$_POST["email"]."'";
    }
    $dateline=$_POST['$datelien'];
    require_once libfile('function/home_attachment');

    updateattach('', $_POST['lecid'], 'shlecid', $_G['gp_attachnew'], $_G['gp_attachdel']);
    
    DB::query("UPDATE ".DB::table("shlecture")." SET dateline='".$dateline."' ".$imgurl.$rank.$bginfo.$belongto.$perspecial.$coursexperience.$stars.$tel.$email." WHERE id=".$_POST['lecid']);
	
	$oldlectdid=$_POST["oldlectdid"];
	$teachdirection=$_POST["teachdirection"];
	$courses=$_POST["courses"];
	$lectdid=$_POST["lectdid"];
	if($teachdirection){
		for($i=0;$i<count($teachdirection)-1;$i++){
			if($lectdid[$i]){
				DB::query("update ".DB::table("shlecture_direct")." SET courses='".$courses[$i]."', teachdirection='".$teachdirection[$i]."' where id=".$lectdid[$i]);
			}else{
				DB::insert("shlecture_direct",array('lecid'=>$_POST['lecid'],'teachdirection'=>$teachdirection[$i],'courses'=>$courses[$i]));
			}
		}
		
	}
	foreach($oldlectdid as $value){
		if(!in_array($value,$lectdid)){
			DB::query("DELETE FROM ".DB::table(shlecture_direct)." where id=".$value);
		}
	}
    
    showmessage('更新成功', join_plugin_action('index'));
}

function fonder(){
	global $_G;
	
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	 if (!$_GET["lecid"]) {
    	showmessage('参数不合法', join_plugin_action("index"));
    }
	//是否表态
	$isfondered=DB::result_first("SELECT count(*) from ".DB::TABLE("shlecture_stars")." where uid=".$_G[uid]." and lecid=".$_GET['lecid']);
	if($isfondered){

	}else{
		DB::query("insert into ".DB::Table("shlecture_stars")."(lecid,uid) values (".$_GET['lecid'].",".$_G['uid'].")");
	
		//喜欢程度
		$lecturer['fonders']=DB::result_first("SELECT count(*) from ".DB::TABLE("shlecture_stars")." where lecid=".$_GET['lecid']);
		$isfondered=DB::result_first("SELECT count(*) from ".DB::TABLE("shlecture_stars")." where uid=".$_G[uid]." and lecid=".$_GET['lecid']);
	}
	include template("shlecturer:view/fonder");
	exit;
}

?>
