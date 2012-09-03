<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
}

function index_outter() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
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
    
	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." WHERE id=".$_GET["lecid"]);
    
    $lecturer = array();
    $groupabouts = array();
    while ($row = DB::fetch($query)) {
    	$lecturer = $row;
    	$query1 = DB::query("SELECT * FROM ".DB::table("forum_forum_lecturer")." WHERE lecid=".$lecturer["id"]);
    	while ($r = DB::fetch($query1)) {
    		$groupabouts[] = $r;
    	}
    }
    
    require_once libfile('function/home_attachment');
	$attachlist = getattach($lecturer['id'],'lecid');
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];
    
    return array("attachlist"=>$attachlist, "attachs"=>$attachs, "imgattachs"=>$imgattachs, "lecturer"=>$lecturer, "groupabouts"=>$groupabouts);
    
}

function save_outter() {
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    //获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	$imgurl = "data/attachment/home/".$img["attachment"];
	
	DB::insert("shlecture", array("name"=>$_POST["name"],
			"gender"=>$_POST["gender"],
			"orgname"=>$_POST["orgname"],
			"rank"=>$_POST["rank"],
			"bginfo"=>$_POST["bginfo"],
			"coursexperience"=>$_POST["coursexperience"],
			"stars"=>floatval($_POST["stars"]),
    		"imgurl"=>$imgurl,    
    		"tel"=>$_POST["tel"],
    		"email"=>$_POST["email"],
    		"isinnerlec"=>2,
			"perspecial"=>$_POST["perspecial"],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));
	
	$lecturerid = DB::insert_id();
	
	$courses=$_POST[courses];
	$teachdirection=$_POST[teachdirection];
	for($i=0;$i<count($teachdirection)-1;$i++){
		DB::insert("shlecture_direct",array('lecid'=>$lecturerid,'teachdirection'=>$teachdirection[$i],'courses'=>$courses[$i]));
	}
	
	//附件
	require_once libfile('function/home_attachment');
    updateattach('', $lecturerid, 'shlecid', $_G['gp_attachnew'], $_G['gp_attachdel']);
	
    showmessage('创建成功', join_plugin_action('index'));
}

function save() {
	
	global $_G;
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	$imgurl = "data/attachment/home/".$img["attachment"];

	require_once libfile('function/org');
	$uid = $_POST["firstman_names_uids"];
	$userinfo = getmember($uid);
	//判断讲师是否存在
	checklecturer($userinfo[username], "index");
	//获取机构岗位信息
	$orgname = getOrgNameByUser($userinfo["username"]);
	
	
	
	DB::insert("shlecture", array("name"=>$_POST["firstman_names"],
			"tempusername"=>$userinfo[username],
			"gender"=>'0',
			"orgname"=>$orgname,
			"rank"=>$_POST["rank"],
			"bginfo"=>$_POST["bginfo"],
			"coursexperience"=>$_POST["coursexperience"],
			"stars"=>floatval($_POST["stars"]),
    		"imgurl"=>$imgurl,    
    		"tel"=>$_POST["tel"],
    		"email"=>$_POST["email"],
    		"isinnerlec"=>1,
			"perspecial"=>$_POST["perspecial"],
    		"dateline"=>time(),
			"uid"=>$_G['uid']));
	
	$lecturerid = DB::insert_id();
	
	$courses=$_POST[courses];
	$teachdirection=$_POST[teachdirection];
	for($i=0;$i<count($teachdirection)-1;$i++){
		DB::insert("shlecture_direct",array('lecid'=>$lecturerid,'teachdirection'=>$teachdirection[$i],'courses'=>$courses[$i]));
	}
	
	//附件
	require_once libfile('function/home_attachment');
    updateattach('', $lecturerid, 'shlecid', $_G['gp_attachnew'], $_G['gp_attachdel']);
	 
    showmessage('创建成功', join_plugin_action('index'));
}



function checklecturer($tempusername, $return_action) {
	if(DB::result(DB::query("SELECT id FROM ".DB::table('shlecture')." WHERE tempusername='".$tempusername."'"), 0)) {
        showmessage('该用户已经是讲师了!', join_plugin_action($return_action));
	}
}


function getmember($uid) {
	$userinfo = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid=".$uid);
	$userinfo = DB::fetch($query);
	return $userinfo;
}

function download(){
	$file_dir   =   "source/plugin/shlecturer/"; 
	$file_name   =   "model.xls"; 
	
	$file   =   fopen($file_dir   .   $file_name, "r ");   //   打开文件   
	//   输入文件标签 
	Header( "Content-type:   application/octet-stream "); 
	Header( "Accept-Ranges:   bytes "); 
	Header( "Accept-Length:   ".filesize($file_dir   .   $file_name)); 
	Header( "Content-Disposition:   attachment;   filename= "   .   $file_name); 
	//   输出文件内容 
	echo   fread($file,filesize($file_dir   .   $file_name)); 
	fclose($file); 
	exit; 
}

function upload(){
	require_once (dirname(__FILE__)."/upload.php");
	exit();
}
?>
