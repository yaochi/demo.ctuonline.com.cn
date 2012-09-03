<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function indexclass() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if($_GET[lecid]){
		$lecture=DB::fetch_first("select * from ".DB::Table("extra_lecture")." where id=".$_GET[lecid]);
	}
	if($_GET[orgid]){
		$org=DB::fetch_first("select * from ".DB::Table("extra_org")." where id=".$_GET[orgid]);
	}
	return array("lecture"=>$lecture,"org"=>$org);
}

function indexclass1() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if($_GET[lecid]){
		$lecture=DB::fetch_first("select * from ".DB::Table("extra_lecture")." where id=".$_GET[lecid]);
	}
	if($_GET[orgid]){
		$org=DB::fetch_first("select * from ".DB::Table("extra_org")." where id=".$_GET[orgid]);
	}
	include template("extraresource:create/indexclass1");
	dexit();
}

function indexlec() {
	global $_G;
	$fup = $_GET["fid"];

    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if($_GET[classid]){
		$class=DB::fetch_first("select * from ".DB::Table("extra_class")." where id=".$_GET[classid]);
	}
	if($_GET[orgid]){
		$org=DB::fetch_first("select * from ".DB::Table("extra_org")." where id=".$_GET[orgid]);
	}
	return array("class"=>$class,"org"=>$org);
}

function indexlec1() {
	global $_G;
	$fup = $_GET["fid"];

    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if($_GET[classid]){
		$class=DB::fetch_first("select * from ".DB::Table("extra_class")." where id=".$_GET[classid]);
	}
	if($_GET[orgid]){
		$org=DB::fetch_first("select * from ".DB::Table("extra_org")." where id=".$_GET[orgid]);
	}
	include template("extraresource:create/indexlec1");
	dexit();
}


function indexorg() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
}
function indexorg1() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	include template("extraresource:create/indexorg1");
	dexit();
}

function createorg(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("indexorg"));
    }

	$name=$_POST[orgname];
	$descr=$_POST[orgdescr];
	if(!$name){
		showmessage('请填写机构名称！', join_plugin_action("indexorg"));
	}
	if(!$descr){
		showmessage('请填写机构简介！', join_plugin_action("indexorg"));
	}
	$res=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where name='".$name."'");
	if($res){
		showmessage('该机构已存在，请重新填写！', join_plugin_action("indexorg"));
	}

	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	if($img["attachment"]){
		$uploadfile = "data/attachment/home/".$img["attachment"];
	}
	$sugestusername=user_get_user_name($_G['uid']);
	DB::insert("extra_org", array("name"=>$name,
	"descr"=>$descr,
	"uploadfile"=>$uploadfile,
	"totalstars"=>intval($_POST["orgtotalstars"]),
	"starsone"=>intval($_POST["orgstarsone"]),
	"starstwo"=>intval($_POST["orgstarstwo"]),
	"starsthree"=>intval($_POST["orgstarsthree"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));


	$orgid = DB::insert_id();
	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$orgid,
	"type"=>'org',
	"totalstars"=>intval($_POST["orgtotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	if($_POST["orgtotalstars"] || $_POST["orgstarsone"] || $_POST["orgstarstwo"] || $_POST["orgstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$orgid,
		"extratype"=>'org',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["orgtotalstars"]),
		"starsone"=>intval($_POST["orgstarsone"]),
		"starstwo"=>intval($_POST["orgstarstwo"]),
		"starsthree"=>intval($_POST["orgstarsthree"])));
	}
	//关联讲师
	$lecids=$_POST[lecids];

	foreach($lecids as $key=>$value){
		if(in_array($value,$lecidarr)){
		}else{
			$lecidarr[]=$value;
		}
	}
	//关联课程
	$classids=$_POST[classids];

	foreach($classids as $key=>$value){
		if(in_array($value,$classidarr)){
		}else{
			$classidarr[]=$value;
		}
	}


	if($lecidarr){
		foreach($lecidarr as $lecid){
			$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);
			DB::query("update ".DB::table("extra_lecture")." set relationorgname='".$lecture[relationorgname].",".$name."' where id=".$lecid);
			DB::query("update ".DB::table("extra_relationship")." set lecorg='".$lecture[relationorgname].",".$name."' where lecid=".$lecid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$name,
			"orglog"=>$uploadfile,
			"orgstars"=>intval($_POST["orgtotalstars"]),
			"lecid"=>$lecid,
			"lecname"=>$lecture[name],
			"lecorg"=>$lecture[relationorgname].",".$name,
			"lecstars"=>$lecture[totalstars],
			"dateline"=>time()));
		}
	}
	if($classidarr){
		foreach($classidarr as $claid){
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$name,
			"orglog"=>$uploadfile,
			"orgstars"=>intval($_POST["orgtotalstars"]),
			"classid"=>$claid,
			"classname"=>$class[name],
			"classstars"=>$class[totalstars],
			"dateline"=>time()));
			DB::query("update ".DB::table("extra_class")." set relationorgname='".$class[relationorgname].",".$name."' where id=".$claid);
		}
	}

		$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的机构，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$orgid.'&extraresource_action=vieworg">'.$name.'</a>'), 1);
			}
		}

	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=vieworg&id=".$orgid ;
	showmessage('创建成功！',$url);
}

function createorg1(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法');
    }

	$name=$_POST[orgname];
	$descr=$_POST[orgdescr];
	if(!$name){
		showmessage('请填写机构名称！');
	}
	if(!$descr){
		showmessage('请填写机构简介！');
	}
	$res=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where name='".$name."'");
	if($res){
		showmessage('该机构已存在，请重新填写！', join_plugin_action("indexorg"));
	}

	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	if($img["attachment"]){
		$uploadfile = "data/attachment/home/".$img["attachment"];
	}
	$sugestusername=user_get_user_name($_G['uid']);

	if(!$_POST["orgtotalstars"]||!$_POST["orgstarsone"]||!$_POST["orgstarstwo"]||!$_POST["orgstarsthree"]){
		showmessage('请完成全部打分后提交');
	}
	DB::insert("extra_org", array("name"=>$name,
	"descr"=>$descr,
	"uploadfile"=>$uploadfile,
	"totalstars"=>intval($_POST["orgtotalstars"]),
	"starsone"=>intval($_POST["orgstarsone"]),
	"starstwo"=>intval($_POST["orgstarstwo"]),
	"starsthree"=>intval($_POST["orgstarsthree"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));


	$orgid = DB::insert_id();
	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$orgid,
	"type"=>'org',
	"totalstars"=>intval($_POST["orgtotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	if($_POST["orgtotalstars"] || $_POST["orgstarsone"] || $_POST["orgstarstwo"] || $_POST["orgstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$orgid,
		"extratype"=>'org',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["orgtotalstars"]),
		"starsone"=>intval($_POST["orgstarsone"]),
		"starstwo"=>intval($_POST["orgstarstwo"]),
		"starsthree"=>intval($_POST["orgstarsthree"])));
	}
	//关联讲师
	$lecids=$_POST[lecids];

	foreach($lecids as $key=>$value){
		if(in_array($value,$lecidarr)){
		}else{
			$lecidarr[]=$value;
		}
	}
	//关联课程
	$classids=$_POST[classids];

	foreach($classids as $key=>$value){
		if(in_array($value,$classidarr)){
		}else{
			$classidarr[]=$value;
		}
	}


	if($lecidarr){
		foreach($lecidarr as $lecid){
			$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);
			DB::query("update ".DB::table("extra_lecture")." set relationorgname='".$lecture[relationorgname].",".$name."' where id=".$lecid);
			DB::query("update ".DB::table("extra_relationship")." set lecorg='".$lecture[relationorgname].",".$name."' where lecid=".$lecid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$name,
			"orglog"=>$uploadfile,
			"orgstars"=>intval($_POST["orgtotalstars"]),
			"lecid"=>$lecid,
			"lecname"=>$lecture[name],
			"lecorg"=>$lecture[relationorgname].",".$name,
			"lecstars"=>$lecture[totalstars],
			"dateline"=>time()));
		}
	}
	if($classidarr){
		foreach($classidarr as $claid){
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$name,
			"orglog"=>$uploadfile,
			"orgstars"=>intval($_POST["orgtotalstars"]),
			"classid"=>$claid,
			"classname"=>$class[name],
			"classstars"=>$class[totalstars],
			"dateline"=>time()));
			DB::query("update ".DB::table("extra_class")." set relationorgname='".$class[relationorgname].",".$name."' where id=".$claid);
		}
	}

		$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的机构，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$orgid.'&extraresource_action=vieworg">'.$name.'</a>'), 1);
			}
		}

	showmessage('do_success', dreferer(), array('orgid' => $orgid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
}


function createlec(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }

	$isinnerlec=$_POST["isinnerlec"];
	if($isinnerlec=='1'){
		$name=$_POST[firstman_names];
		$innerlecid=$_POST[firstman_names_uids];
		$res=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture")." where innerlecid='".$innerlecid."'");
		if($res){
			showmessage('该讲师已存在，请重新填写！', join_plugin_action("indexlec"));
		}
		$oldlecid=DB::result_first("select id from ".DB::TABLE("lecturer")." where lecid='".$innerlecid."'");
	}elseif($isinnerlec=='2'){
		$name=$_POST[lecname];
	}else{
		showmessage('请先选择讲师的类型！', join_plugin_action("indexlec"));
	}

	$descr=$_POST[lecdescr];
	if(!$name){
		showmessage('请填写讲师姓名！', join_plugin_action("indexlec"));
	}
	if(!$descr){
		showmessage('请填写讲师的背景介绍！', join_plugin_action("indexlec"));
	}
	//关联课程
	$classids=$_POST[classids];
	if(!$classids){
		showmessage('请选择讲师教授课程！', join_plugin_action("indexlec"));
	}

	foreach($classids as $key=>$value){
		if(in_array($value,$classidarr)){
		}else{
			$classidarr[]=$value;
		}
	}
	$orgids=$_POST[orgids];
	$orgnames=$_POST[orgnames];

	foreach($orgids as $key=>$value){
		if(in_array($value,$orgidarr)){
		}else{
			$orgidarr[]=$value;
			$orgnamearr[]=$orgnames[$key];
		}
	}
	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	if($img["attachment"]){
		$uploadfile = "data/attachment/home/".$img["attachment"];
	}else{
		showmessage('请上传讲师照片！', join_plugin_action("indexlec"));
	}
	$sugestusername=user_get_user_name($_G['uid']);
	DB::insert("extra_lecture", array("name"=>$name,
	"descr"=>$descr,
	"oldlecid"=>$oldlecid,
	"uploadfile"=>$uploadfile,
	"trainingexperince"=>$_POST["trainingexperince"],
	"trainingtrait"=>$_POST["trainingtrait"],
	"teachdirection"=>$_POST["teachdirection"],
	"telephone"=>$_POST["telephone"],
	"minfee"=>intval($_POST["minfee"]),
	"maxfee"=>intval($_POST["maxfee"]),
	"email"=>$_POST["email"],
	"isinnerlec"=>$isinnerlec,
	"innerlecid"=>$innerlecid,
	"gender"=>$_POST["gender"],
	"relationorgname"=>implode(',',$orgnamearr),
	"totalstars"=>intval($_POST["lectotalstars"]),
	"starsone"=>intval($_POST["lecstarsone"]),
	"starstwo"=>intval($_POST["lecstarstwo"]),
	"starsthree"=>intval($_POST["lecstarsthree"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	$lecid = DB::insert_id();


	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$lecid,
	"type"=>'lec',
	"totalstars"=>intval($_POST["lectotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	if($_POST["lectotalstars"] || $_POST["lecstarsone"] || $_POST["lecstarstwo"] || $_POST["lecstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$lecid,
		"extratype"=>'lec',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["lectotalstars"]),
		"starsone"=>intval($_POST["lecstarsone"]),
		"starstwo"=>intval($_POST["lecstarstwo"]),
		"starsthree"=>intval($_POST["lecstarsthree"])));
	}

	if($orgidarr){
		foreach($orgidarr as $orgid){
			$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orgid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$org[name],
			"orglog"=>$org[uploadfile],
			"orgstars"=>$org["totalstars"],
			"lecid"=>$lecid,
			"lecname"=>$name,
			"lecorg"=>implode(',',$orgnamearr),
			"lecstars"=>intval($_POST["lectotalstars"]),
			"dateline"=>time()));
		}
	}
	if($classidarr){
		foreach($classidarr as $claid){
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
			DB::insert("extra_relationship",array("lecid"=>$lecid,
			"lecname"=>$name,
			"lecorg"=>implode(',',$orgnamearr),
			"lecstars"=>intval($_POST["lectotalstars"]),
			"classid"=>$claid,
			"classname"=>$class[name],
			"classstars"=>$class[totalstars],
			"dateline"=>time()));
		}
	}


	$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的讲师，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$lecid.'&extraresource_action=viewlec">'.$name.'</a>'), 1);
			}
		}


	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewlec&id=".$lecid ;
	showmessage('创建成功！',$url);
}

function createlec1(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法');
    }

	$isinnerlec=$_POST["isinnerlec"];
	if($isinnerlec=='1'){
		$name=$_POST[firstman_names];
		$innerlecid=$_POST[firstman_names_uids];
		$res=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture")." where innerlecid='".$innerlecid."'");
		if($res){
			showmessage('该讲师已存在，请重新填写！', join_plugin_action("indexlec"));
		}
		$oldlecid=DB::result_first("select id from ".DB::TABLE("lecturer")." where lecid='".$innerlecid."'");
	}elseif($isinnerlec=='2'){
		$name=$_POST[lecname];
	}else{
		showmessage('请先选择讲师的类型！');
	}

	$descr=$_POST[lecdescr];
	if(!$name){
		showmessage('请填写讲师姓名！');
	}
	if(!$descr){
		showmessage('请填写讲师的背景介绍！');
	}
	//关联课程
	$classids=$_POST[classids];

	foreach($classids as $key=>$value){
		if(in_array($value,$classidarr)){
		}else{
			$classidarr[]=$value;
		}
	}
	$orgids=$_POST[orgids];
	$orgnames=$_POST[orgnames];

	foreach($orgids as $key=>$value){
		if(in_array($value,$orgidarr)){
		}else{
			$orgidarr[]=$value;
			$orgnamearr[]=$orgnames[$key];
		}
	}
	//获取图片url
	$img = null;
	if ($_POST["aid"]) {
		$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
		$img = DB::fetch($query);
	}
	if($img["attachment"]){
		$uploadfile = "data/attachment/home/".$img["attachment"];
	}else{
		showmessage('请上传讲师照片！');
	}
	if(!$_POST["minfee"]){
		showmessage('请填写授课费用下限！');
	}
	if(!$_POST["maxfee"]){
		showmessage('请填写授课费用上限！');
	}
	if($_POST["maxfee"]<$_POST["minfee"]){
		showmessage('授课费用上限不能低于下限!');
	}
	if(!$_POST["telephone"]){
		showmessage('请填写讲师联系电话！');
	}
	if(!$_POST["email"]){
		showmessage('请填写讲师email！');
	}
	if(!$_POST["lectotalstars"]||!$_POST["lecstarsone"]||!$_POST["lecstarstwo"]||!$_POST["lecstarsthree"]){
		showmessage('请完成全部打分后提交');
	}

	$sugestusername=user_get_user_name($_G['uid']);
	DB::insert("extra_lecture", array("name"=>$name,
	"descr"=>$descr,
	"oldlecid"=>$oldlecid,
	"uploadfile"=>$uploadfile,
	"trainingexperince"=>$_POST["trainingexperince"],
	"trainingtrait"=>$_POST["trainingtrait"],
	"teachdirection"=>$_POST["teachdirection"],
	"telephone"=>$_POST["telephone"],
	"minfee"=>intval($_POST["minfee"]),
	"maxfee"=>intval($_POST["maxfee"]),
	"email"=>$_POST["email"],
	"isinnerlec"=>$isinnerlec,
	"innerlecid"=>$innerlecid,
	"gender"=>$_POST["gender"],
	"relationorgname"=>implode(',',$orgnamearr),
	"totalstars"=>intval($_POST["lectotalstars"]),
	"starsone"=>intval($_POST["lecstarsone"]),
	"starstwo"=>intval($_POST["lecstarstwo"]),
	"starsthree"=>intval($_POST["lecstarsthree"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	$lecid = DB::insert_id();


	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$lecid,
	"type"=>'lec',
	"totalstars"=>intval($_POST["lectotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	if($_POST["lectotalstars"] || $_POST["lecstarsone"] || $_POST["lecstarstwo"] || $_POST["lecstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$lecid,
		"extratype"=>'lec',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["lectotalstars"]),
		"starsone"=>intval($_POST["lecstarsone"]),
		"starstwo"=>intval($_POST["lecstarstwo"]),
		"starsthree"=>intval($_POST["lecstarsthree"])));
	}

	if($orgidarr){
		foreach($orgidarr as $orgid){
			$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orgid);
			DB::insert("extra_relationship",array("orgid"=>$orgid,
			"orgname"=>$org[name],
			"orglog"=>$org[uploadfile],
			"orgstars"=>$org["totalstars"],
			"lecid"=>$lecid,
			"lecname"=>$name,
			"lecorg"=>implode(',',$orgnamearr),
			"lecstars"=>intval($_POST["lectotalstars"]),
			"dateline"=>time()));
		}
	}
	if($classidarr){
		foreach($classidarr as $claid){
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
			DB::insert("extra_relationship",array("lecid"=>$lecid,
			"lecname"=>$name,
			"lecorg"=>implode(',',$orgnamearr),
			"lecstars"=>intval($_POST["lectotalstars"]),
			"classid"=>$claid,
			"classname"=>$class[name],
			"classstars"=>$class[totalstars],
			"dateline"=>time()));
		}
	}


	$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的讲师，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$lecid.'&extraresource_action=viewlec">'.$name.'</a>'), 1);
			}
		}


	showmessage('do_success', dreferer(), array('lecid' => $lecid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
}


function createclass(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("indexclass"));
    }

	$name=$_POST[classname];
	$descr=$_POST[classdescr];
	if(!$name){
		showmessage('请填写课程名称！', join_plugin_action("indexclass"));
	}
	if(!$descr){
		showmessage('请填写课程简介！', join_plugin_action("indexclass"));
	}
	//$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$name."'");
	//if($res){
		//showmessage('该课程已存在，请重新填写！', join_plugin_action("indexclass"));
	//}
	//关联机构
	$orgids=$_POST[orgids];
	$orgnames=$_POST[orgnames];

	foreach($orgids as $key=>$value){
		if(in_array($value,$orgidarr)){
		}else{
			$orgidarr[]=$value;
			$orgnamearr[]=$orgnames[$key];
		}
	}
	//关联讲师
	$lecids=$_POST[lecids];
	if(!$lecids){
		showmessage('请选择讲师！', join_plugin_action("indexclass"));
	}

	foreach($lecids as $key=>$value){
		if(in_array($value,$lecidarr)){
		}else{
			$lecidarr[]=$value;
		}
	}

	$upload_path="data/attachment/plugin_extraresource";// 上传文件的存储路径
	$file_size_max=1024*1024*10; //10M限制文件上传最大容量(bytes)
	$store_dir=$upload_path."/"; // 上传文件的储存位置
   	$accept_overwrite=0;  //是否允许覆盖相同文件  1：允许 0:不允许

	//POST中name= "upload";
	$upload_file=$_FILES['classinfo']['tmp_name'];  //文件被上传后在服务端储存的临时文件名
	$upload_file_name=str_replace(" ","",$_FILES['classinfo']['name']); //文件名
	$upload_file_size=$_FILES['upload']['size'];  //文件大小
	$extarr=array(doc,docx,ppt,pptx,pdf,rtf);
     if($upload_file)
		{
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if(in_array($ext,$extarr)){
			}else{
     			showmessage("上传的文件类型错误，请重新上传！",$url);
			}

     		//检查文件大小
   			if($upload_file_size > $file_size_max)
   					showmessage("上传的文件超过10M，请重新上传！",$url);

          	//检查存储目录，如果存储目录不存在，则创建之
          	if(!is_dir($upload_path))
            		mkdir($upload_path);

   			//检查读写文件
  			if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
  					showmessage("文件已上传！",join_plugin_action("indexclass"));

   			//复制文件到指定目录
   			//$new_file_name=$_G[fid].".xls";//上传过后的保存的名称
   			if(!move_uploaded_file($upload_file,$store_dir.$upload_file_name))
   					showmessage("复制文件失败！",join_plugin_action("indexclass"));

   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名)
   			$info="你上传了文件:".$_FILES['upload']['name'].";文件大小："	.$_FILES['upload']['size']."B。<br/>"	;
   			//文件检查
   			$error=$_FILES['upload']['error'];
  			switch($error)
  			   {
    			case 0:
    				break;
    			case 1:
    				showmessage("上传的文件超过了系统配置中upload_max_filesize选项限制的值。",join_plugin_action("indexclass"));
    			case 2:
    				showmessage("上传文件的大小超过了 表单中 MAX_FILE_SIZE 选项指定的值。",join_plugin_action("indexclass"));
          		case 3:
          			showmessage("文件只有部分被上传！",join_plugin_action("indexclass"));
    			case 4:
    				showmessage("没有文件被上传！",join_plugin_action("indexclass"));
  			   }

		}
	$uploadfile=$store_dir.$upload_file_name;
	$uploadfilename=$upload_file_name;
	$sugestusername=user_get_user_name($_G['uid']);
	DB::insert("extra_class", array("name"=>$name,
	"descr"=>$descr,
	"uploadfile"=>$uploadfile,
	"classification"=>$_POST["classification"],
	"totalstars"=>intval($_POST["classtotalstars"]),
	"starsone"=>intval($_POST["classstarsone"]),
	"starstwo"=>intval($_POST["classstarstwo"]),
	"starsthree"=>intval($_POST["classstarsthree"]),
	"released"=>0,
	"uploadfilename"=>$uploadfilename,
	"relationorgname"=>implode(',',$orgnamearr),
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	$classid = DB::insert_id();

	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$classid,
	"type"=>'class',
	"totalstars"=>intval($_POST["classtotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));
	if($_POST["classtotalstars"] || $_POST["classstarsone"] || $_POST["classstarstwo"] || $_POST["classstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$classid,
		"extratype"=>'class',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["classtotalstars"]),
		"starsone"=>intval($_POST["classstarsone"]),
		"starstwo"=>intval($_POST["classstarstwo"]),
		"starsthree"=>intval($_POST["classstarsthree"])));
	}
	if($lecidarr){
		foreach($lecidarr as $lecid){
			$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);
			DB::insert("extra_relationship",array("classid"=>$classid,
			"classname"=>$name,
			"classstars"=>intval($_POST["classtotalstars"]),
			"lecid"=>$lecid,
			"lecname"=>$lecture[name],
			"lecorg"=>$lecture[relationorgname],
			"lecstars"=>$lecture[totalstars],
			"dateline"=>time()));
		}
	}

	if($orgidarr){
		foreach($orgidarr as $oid){
			$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$oid);
			DB::insert("extra_relationship",array("orgid"=>$oid,
			"orgname"=>$org[name],
			"orglog"=>$org[uploadfile],
			"orgstars"=>$org["totalstars"],
			"classid"=>$classid,
			"classname"=>$name,
			"classstars"=>intval($_POST["classtotalstars"]),
			"dateline"=>time()));
		}
	}

	$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的课程，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$classid.'&extraresource_action=viewclass">'.$name.'</a>'), 1);
			}
		}

	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id=".$classid ;
	showmessage('创建成功！',$url);
}

function createclass1(){
	global $_G;
	require_once libfile('function/org');
	$fup = $_G["fid"];
    if(!$fup){
        showmessage('参数不合法');
    }

	$name=$_POST[classname];
	$descr=$_POST[classdescr];
	if(!$name){
		showmessage('请填写课程名称！');
	}
	if(!$descr){
		showmessage('请填写课程简介！');
	}
	$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$name."'");
	if($res){
		showmessage('该课程已存在，请重新填写！');
	}
	//关联机构
	$orgids=$_POST[orgids];
	$orgnames=$_POST[orgnames];

	foreach($orgids as $key=>$value){
		if(in_array($value,$orgidarr)){
		}else{
			$orgidarr[]=$value;
			$orgnamearr[]=$orgnames[$key];
		}
	}
	//关联讲师
	$lecids=$_POST[lecids];
	/*if(!$lecids){
		showmessage('请选择讲师！', join_plugin_action("indexclass"));
	}*/

	foreach($lecids as $key=>$value){
		if(in_array($value,$lecidarr)){
		}else{
			$lecidarr[]=$value;
		}
	}

	$upload_path="data/attachment/plugin_extraresource";// 上传文件的存储路径
	$file_size_max=1024*1024*10; //10M限制文件上传最大容量(bytes)
	$store_dir=$upload_path."/"; // 上传文件的储存位置
   	$accept_overwrite=0;  //是否允许覆盖相同文件  1：允许 0:不允许

	//POST中name= "upload";
	$upload_file=$_FILES['classinfo']['tmp_name'];  //文件被上传后在服务端储存的临时文件名
	$upload_file_name=str_replace(" ","",$_FILES['classinfo']['name']); //文件名
	if(!$upload_file_name){
		showmessage('请上传课程大纲！');
	}
	$upload_file_size=$_FILES['upload']['size'];  //文件大小
	$extarr=array(doc,docx,ppt,pptx,pdf,rtf);
     if($upload_file)
		{
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if(in_array($ext,$extarr)){
			}else{
     			showmessage("上传的文件类型错误，请重新上传！");
			}

     		//检查文件大小
   			if($upload_file_size > $file_size_max)
   					showmessage("上传的文件超过10M，请重新上传！");

          	//检查存储目录，如果存储目录不存在，则创建之
          	if(!is_dir($upload_path))
            		mkdir($upload_path);

   			//检查读写文件
  			if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
  					showmessage("文件已上传！");

   			//复制文件到指定目录
   			//$new_file_name=$_G[fid].".xls";//上传过后的保存的名称
   			if(!move_uploaded_file($upload_file,$store_dir.$upload_file_name))
   					showmessage("复制文件失败！");

   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名)
   			$info="你上传了文件:".$_FILES['upload']['name'].";文件大小："	.$_FILES['upload']['size']."B。<br/>"	;
   			//文件检查
   			$error=$_FILES['upload']['error'];
  			switch($error)
  			   {
    			case 0:
    				break;
    			case 1:
    				showmessage("上传的文件超过了系统配置中upload_max_filesize选项限制的值。");
    			case 2:
    				showmessage("上传文件的大小超过了 表单中 MAX_FILE_SIZE 选项指定的值。");
          		case 3:
          			showmessage("文件只有部分被上传！");
    			case 4:
    				showmessage("没有文件被上传！");
  			   }

		}
	$uploadfile=$store_dir.$upload_file_name;
	$uploadfilename=$upload_file_name;
	if(!$_POST["classtotalstars"]||!$_POST["classstarsone"]||!$_POST["classstarstwo"]||!$_POST["classstarsthree"]){
		showmessage('请完成全部打分后提交');
	}

	$sugestusername=user_get_user_name($_G['uid']);
	DB::insert("extra_class", array("name"=>$name,
	"descr"=>$descr,
	"uploadfile"=>$uploadfile,
	"classification"=>$_POST["classification"],
	"totalstars"=>intval($_POST["classtotalstars"]),
	"starsone"=>intval($_POST["classstarsone"]),
	"starstwo"=>intval($_POST["classstarstwo"]),
	"starsthree"=>intval($_POST["classstarsthree"]),
	"released"=>0,
	"uploadfilename"=>$uploadfilename,
	"relationorgname"=>implode(',',$orgnamearr),
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));

	$classid = DB::insert_id();

	DB::insert("extra_resource", array("name"=>$name,
	"resourceid"=>$classid,
	"type"=>'class',
	"totalstars"=>intval($_POST["classtotalstars"]),
	"released"=>0,
	"sugestuid"=>$_G['uid'],
	"sugestusername"=>$sugestusername,
	"sugestorgid"=>get_org_id_by_user($_G['username']),
	"sugestorgname"=>getOrgNameByUser($_G['username']),
	"sugestdateline"=>time(),
	"fid"=>$fup));
	if($_POST["classtotalstars"] || $_POST["classstarsone"] || $_POST["classstarstwo"] || $_POST["classstarsthree"]){
		DB::insert('extrastar',array("extraid"=>$classid,
		"extratype"=>'class',
		"uid"=>$_G['uid'],
		"dateline"=>time(),
		"totalstars"=>intval($_POST["classtotalstars"]),
		"starsone"=>intval($_POST["classstarsone"]),
		"starstwo"=>intval($_POST["classstarstwo"]),
		"starsthree"=>intval($_POST["classstarsthree"])));
	}
	if($lecidarr){
		foreach($lecidarr as $lecid){
			$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);
			DB::insert("extra_relationship",array("classid"=>$classid,
			"classname"=>$name,
			"classstars"=>intval($_POST["classtotalstars"]),
			"lecid"=>$lecid,
			"lecname"=>$lecture[name],
			"lecorg"=>$lecture[relationorgname],
			"lecstars"=>$lecture[totalstars],
			"dateline"=>time()));
		}
	}

	if($orgidarr){
		foreach($orgidarr as $oid){
			$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$oid);
			DB::insert("extra_relationship",array("orgid"=>$oid,
			"orgname"=>$org[name],
			"orglog"=>$org[uploadfile],
			"orgstars"=>$org["totalstars"],
			"classid"=>$classid,
			"classname"=>$name,
			"classstars"=>intval($_POST["classtotalstars"]),
			"dateline"=>time()));
		}
	}

	$checkusers = array();
		$query = DB::query("SELECT uid FROM ".DB::table('forum_groupuser')." WHERE fid='$fup' and level='1'");
		while($row = DB::fetch($query)) {
			$checkusers[] = $row['uid'];
		}
		if($checkusers) {
			foreach($checkusers as $uid) {
				notification_add($uid, '外部培训资源', '[外部培训资源]“{username}”在{groupname}推荐了“{extratitle}”的课程，赶快去审核发布吧', array('username'=>'<a href="home.php?mod=space&uid='.$_G[uid].'">'.$sugestusername.'</a>','groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&diy=&id='.$classid.'&extraresource_action=viewclass">'.$name.'</a>'), 1);
			}
		}

	showmessage('do_success', dreferer(), array('classid' => $classid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
}



function errorMsg($errorcode){
    $lang = array(
	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。'
    );
    if(array_key_exists($errorcode,$lang)){
        $msg = $lang[$errorcode];
    }else{
        $msg = "上传失败！未知原因，请重新尝试或者联系服务中心。";
    }
    return $msg;
}

function search(){
	global  $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("search", array("ac"=>'search')));
	}
	if ($_GET["ac"]==search) {
		return;
	}

	$name = $_REQUEST[name];
	$iscollegelec = $_REQUEST[iscollegelec];
	$orgname = $_REQUEST[orgname_input];
	$orgid = $_REQUEST[orgname_input_id];
	$includechild = $_REQUEST[includechild];
	$rank = $_REQUEST[rank];
	$courses = $_REQUEST[courses];
	$teachdirection = $_REQUEST[teachdirection];
	//机构
	if ($orgid) {
		$orgidurl = "&orgname_input_id=".$orgid;
		if ($includechild) {
			$includechildurl = "&includechild=".$includechild;
			//获取子机构
			require_once libfile('function/org');
	    	$orgidlist = getSubOrg($orgid);
	    	$orgids = implode(",", $orgidlist);
	    	if ($orgids) {
	    		$orgidssql = " AND orgid in(".$orgids.",".$orgid.")";
	    	} else {
	    		$orgidssql = " AND orgid=".$orgid;
	    	}
		} else {
			$orgidssql = " AND orgid=".$orgid;
		}
	}
	if ($orgname) {
		$orgnameurl = "&orgname_input=".$orgname;
	}
	//是否学院讲师
	if ($iscollegelec) {
		$iscollegelecsql = " AND iscollegelec=1";
		$iscollegelecurl = "&iscollegelec=".$iscollegelec;
	} else {
		$iscollegelecsql = "";
	}
	//讲师级别
	if ($rank) {
		$ranksql = " AND rank=".$rank;
		$rankurl = "&rank=".$rank;
	} else {
		$ranksql = "";
	}

	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	} else {
		$teachdirectionsql = "";
	}
	//主要培训课程
	if ($courses) {
		$coursessql = " AND courses like '%".$courses."%'";
		$coursesurl = "&coursesurl".$courses;
	} else {
		$coursessql = "";
	}
	//end
	$namesql = " AND name like '%".$name."%'";
	$nameurl = "&name=".$name;

	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;

	$query = DB::query("SELECT * FROM ".DB::table("lecturer")." lec WHERE isinnerlec=1 ".$namesql.$orgidssql.$iscollegelecsql.$ranksql.$teachdirectionsql.$coursessql." LIMIT $start,$perpage");

	while($row = DB::fetch($query)){
		//过滤专区讲师
		if (DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('forum_forum_lecturer')." WHERE lecid=".$row["id"]." AND fid=".$_G['fid']), 0)) {
			continue;
		}
		$lecturers[] = $row;
	}

	$addsql = $namesql.$iscollegelecsql.$orgidsql.$ranksql.$coursessql.$teachdirectionsql;
	$addurl = $nameurl.$iscollegelecurl.$orgnameurl.$orgidurl.$includechildurl.$rankurl.$coursesurl.$teachdirectionurl;
	$getcount = getlecturercount(1, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=extraresource&plugin_op=createmenu&extraresource_action=search".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}

	return array("select"=>"search", "multipage"=>$multipage, "_G"=>$_G, "lecturers"=>$lecturers, "total"=>$getcount, "name"=>$name, "firstman_names_uids"=>"", "orgname_input"=>$orgname, "orgname_input_id"=>$orgid, "includechild"=>$includechild, "coursexperience"=>$coursexperience, "rank"=>$rank, "teachdirection"=>$teachdirection, "iscollegelec"=>$iscollegelec);
}

function search_outter(){
	global  $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("search_outter", array("ac"=>'search_out')));
	}
	if ($_GET["ac"]=='search_out') {
		return;
	}

	if ($_POST[name]) {
		$name = $_POST[name];
		$rank = $_POST[rank];
		$teachdirection = $_POST[teachdirection];
	} elseif ($_GET[name]) {
		$name = $_GET[name];
		$rank = $_GET[rank];
		$teachdirection = $_GET[teachdirection];
	}

	//讲师级别
	if ($rank) {
		$ranksql = " AND rank=".$rank;
		$rankurl = "&rank=".$rank;
	} else {
		$ranksql = "";
	}
	//授课方向
	if ($teachdirection) {
		$teachdirectionsql = " AND teachdirection=".$teachdirection;
		$teachdirectionurl = "&teachdirection=".$teachdirection;
	} else {
		$teachdirectionsql = "";
	}

	$namesql = " AND name like '%".$name."%'";
	$nameurl = "&name=".$name;

	//	分页
    $perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;

	$sql = "SELECT * FROM ".DB::table("lecturer")." lec WHERE isinnerlec=2 ".$namesql.$ranksql.$teachdirectionsql." LIMIT $start,$perpage";
	$query = DB::query($sql);
	$lecturers = array();
	while($row = DB::fetch($query)){
		//过滤专区讲师
		if (DB::result(DB::query("SELECT lecid, fid FROM ".DB::table('forum_forum_lecturer')." WHERE lecid=".$row["id"]." AND fid=".$_G['fid']), 0)) {
			continue;
		}
		$lecturers[] = $row;
	}

	$addsql = $namesql.$ranksql.$teachdirectionsql;
	$addurl = $nameurl.$rankurl.$teachdirectionurl;
	$getcount = getlecturercount(2, $addsql);
	$url = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=extraresource&plugin_op=createmenu&extraresource_action=search_outter".$addurl;
	if($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}

	return array("multipage"=>$multipage, "_G"=>$_G, "lecturers"=>$lecturers, "total"=>$getcount, "name"=>$name, "rank"=>$rank, "teachdirection"=>$teachdirection);
}

function getlecturercount($isIO, $addsql) {
	if ($isIO == 1) {
		$isinnerlecsql = " isinnerlec=1";
	} else {
		$isinnerlecsql = " isinnerlec=2";
	}
	$query = DB::query("SELECT count(*) FROM ".DB::table('lecturer')." WHERE ".$isinnerlecsql.$addsql);
	return DB::result($query, 0);
}

function step2() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("indexlec"));
	}

	if (!empty($_POST["lecturercheckbox"])) {
		$lecids = "".join(",", $_POST["lecturercheckbox"])."";
		$query = DB::query("SELECT * FROM ".DB::table("lecturer")."
    		WHERE id IN (".$lecids.")");
		while($row = DB::fetch($query)){
			$lecturers[] = $row;
		}
	}
	$total = count($lecturers);

	return array("_G"=>$_G, "lecturers"=>$lecturers, "total"=>$total);
}

function step2_save() {
	global $_G;
	$fup = $_GET["fid"];
	if(!$fup){
		showmessage('参数不合法', join_plugin_action("indexlec"));
	}
	require_once libfile('function/org');
	if ($_POST["total"]) {
		for ($i=0; $i<$_POST["total"]; $i++) {
			$res=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture")." where oldlecid='".$_POST["lecid".$i]."'");
			if(!$res){
				DB::insert("extra_lecture", array("oldlecid"=>$_POST["lecid".$i],
					"name"=>$_POST["lecname".$i],
					"innerlecid"=>$_POST["innerlecid".$i],
					"descr"=>$_POST["about".$i],
					"uploadfile"=>$_POST["lecimgurl".$i],
					"trainingexperince"=>$_POST["trainingexperince".$i],
					"trainingtrait"=>$_POST["trainingtrait".$i],
					"telephone"=>$_POST["telephone".$i],
					"email"=>$_POST["email".$i],
					"isinnerlec"=>$_POST["isinnerlec".$i],
					"gender"=>$_POST["gender".$i],
					"released"=>0,
					"sugestuid"=>$_G['uid'],
					"sugestusername"=>user_get_user_name($_G['uid']),
					"sugestorgid"=>get_org_id_by_user($_G['username']),
					"sugestorgname"=>getOrgNameByUser($_G['username']),
					"sugestdateline"=>time()));
				$lecid = DB::insert_id();
				DB::insert("extra_resource", array("resourceid"=>$lecid,
					"name"=>$_POST["lecname".$i],
					"type"=>"lec",
					"released"=>0,
					"sugestuid"=>$_G['uid'],
					"sugestusername"=>user_get_user_name($_G['uid']),
					"sugestorgid"=>get_org_id_by_user($_G['username']),
					"sugestorgname"=>getOrgNameByUser($_G['username']),
					"sugestdateline"=>time(),
					"fid"=>'197'));
				}
		}
		showmessage('保存专区讲师成功', 'forum.php?mod=group&action=plugin&fid='.$fup.'&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=indexlec');
	} else {
		showmessage('请先选择一个讲师', join_plugin_action('search'));
	}
}

function downloadextraresource(){
	$file_dir   =   "source/plugin/extraresource/";
	$file_name   =   "modelall.xls";

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
function downloadorg(){
	$file_dir   =   "source/plugin/extraresource/";
	$file_name   =   "modelorg.xls";

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

function downloadlec(){
	$file_dir   =   "source/plugin/extraresource/";
	$file_name   =   "modellec.xls";

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

function downloadclass(){
	$file_dir   =   "source/plugin/extraresource/";
	$file_name   =   "modelclass.xls";

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

function uploadall(){
	require_once (dirname(__FILE__)."/uploadall.php");
	exit();
}
function uploadorg(){
	require_once (dirname(__FILE__)."/uploadorg.php");
	exit();
}

function uploadclass(){
	require_once (dirname(__FILE__)."/uploadclass.php");
	exit();
}

function uploadlec(){
	require_once (dirname(__FILE__)."/uploadlec.php");
	exit();
}

function checkclass(){
	global $_G;
	$fup = $_GET["fid"];
	$name=$_G[gp_name];
	$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$name."'");

	$arraydata=array("s"=>$res);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	dexit();
}


function checkorg(){
	global $_G;
	$fup = $_GET["fid"];
	$name=$_G[gp_name];
	$res=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where name='".$name."'");

	$arraydata=array("s"=>$res);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	dexit();
}

function checklec(){
	global $_G;
	$fup = $_GET["fid"];
	$name=$_G[gp_name];
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}

	if($_G['forum']['ismoderator']){
		$query=DB::query("select * from ".DB::TABLE("extra_lecture")." where name='".$name."'");
	}else{
		$query=DB::query("select * from ".DB::TABLE("extra_lecture")." where released='1' or sugestuid='".$_G[uid]."' and name='".$name."'");
	}


	while($value=DB::fetch($query)){
		$res[]=$value;
	}

	$arraydata=array("s"=>$res);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	dexit();
}

function extraexpt(){
require_once (dirname(__FILE__) . "/function/function_extraresource.php");
$filename='';
$type=$_GET['type'];
if($type=='indexclass'){
$filename=mb_convert_encoding("课程",'GB2312','UTF-8');
}
if($type=='indexlec'){
$filename=mb_convert_encoding("讲师",'GB2312','UTF-8');
}
if($type=='indexorg'){
	$filename=mb_convert_encoding("培训机构",'GB2312','UTF-8');
}
header("Content-type:application/vnd.ms-excel");
header("Content-Disposition:attachment;filename=".$filename.".xls");
	if($type=='indexclass'){
	echo   mb_convert_encoding("课程名称",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("机构名称",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("推荐部门",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("教授本课程的讲师",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("课程级别",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("课程简介",'GB2312','UTF-8')."\t";
	$list=getAllextra($type);
    foreach ( $list as $value){
	$lecarr=getclasslec($value[id]);
	$lecname='';
	foreach($lecarr as $lec){
			$lecname=$lecname." ".mb_convert_encoding($lec[lecname],'GB2312','UTF-8');
	}
			echo   "\n";
			$class[name]=mb_convert_encoding($value[name],'GB2312','UTF-8');
			$class[relationorgname]=mb_convert_encoding($value[relationorgname],'GB2312','UTF-8');
			$class[sugestorgname]=mb_convert_encoding($value[sugestorgname],'GB2312','UTF-8');
			$class[totalstars]=mb_convert_encoding($value[totalstars]."分",'GB2312','UTF-8');
			$class[descr]=str_replace("\n","",mb_convert_encoding($value[descr],'GB2312','UTF-8'));
			echo $class[name]."\t";
			echo $class[relationorgname]."\t";
			echo $class[sugestorgname]."\t";
			echo $lecname."\t";
			echo $class[totalstars]."\t";
			echo $class[descr]."\t";

    }
		dexit();
	}
	if($type=='indexlec'){
	echo   mb_convert_encoding("讲师",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("授课方向",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("工作单位",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("培训经历",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("培训特点",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("授课费用",'GB2312','UTF-8')."\t";
		$list=getAllextra($type);
		foreach ( $list as $value){
			if($value[teachdirection]=='1'){
				$teachdirname="领导力发展及管理类";
			}if($value[teachdirection]=='2'){
				$teachdirname="营销类";
			}if($value[teachdirection]=='3'){
				$teachdirname="技术类";
			}
			echo   "\n";
			$lec[name]=mb_convert_encoding($value[name],'GB2312','UTF-8');
			$teachdirname=mb_convert_encoding($teachdirname,'GB2312','UTF-8');
			$lec[relationorgname]=mb_convert_encoding($value[relationorgname],'GB2312','UTF-8');
			$lec[trainingexperince]=str_replace("\n"," ",mb_convert_encoding($value[trainingexperince],'GB2312','UTF-8'));
			$lec[trainingtrait]=str_replace("\n"," ",mb_convert_encoding($value[trainingtrait],'GB2312','UTF-8'));
			$lec[free]=mb_convert_encoding($value[minfee]."-".$value[maxfee]."元/天",'GB2312','UTF-8');
			echo $lec[name]."\t";
			echo $teachdirname."\t";
			echo $lec[relationorgname]."\t";
			echo $lec[trainingexperince]."\t";
			echo $lec[trainingtrait]."\t";
			echo $lec[free]."\t";
		}
		dexit();
	}
	if($type=='indexorg'){
	echo   mb_convert_encoding("机构名称",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("推荐部门",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("机构级别",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("本机构拥有讲师",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("机构描述",'GB2312','UTF-8')."\t";
	$list=getAllextra($type);
		foreach ( $list as $value){
			$orgarr=getorglec($value[id]);
			$lecname='';
			foreach($orgarr as $org){
				$lecname=$lecname." ".mb_convert_encoding($org[lecname],'GB2312','UTF-8');
			}
			echo   "\n";
			$org[name]=mb_convert_encoding($value[name],'GB2312','UTF-8');
			$org[sugestorgname]=mb_convert_encoding($value[sugestorgname],'GB2312','UTF-8');
			$org[totalstars]=mb_convert_encoding($value[totalstars]."分",'GB2312','UTF-8');
			$org[descr]=str_replace("\n"," ",mb_convert_encoding($value[descr],'GB2312','UTF-8'));
			echo $org[name]."\t";
			echo $org[sugestorgname]."\t";
			echo $org[totalstars]."\t";
			echo $lecname."\t";
			echo $org[descr]."\t";

		}
		dexit();
	}
}

?>
