<?php
/*
 * create by qiaoyz
 */
 require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

/**
 *我要分享
 */
 function index(){
 	global $_G;
	$user=array("uid"=>$_G[uid],"regname"=>$_G[username],"realname"=>$realname=user_get_user_name($_G[uid]));
 	include template("sharesource:create/index");
	dexit();
 }

/**
 * 保存创建
 */
 function save(){
 	global $_G;
 	$share=array();
 	$share['uid']=$_G[gp_uid];
 	$share['regname']=$_G[gp_regname];
 	$share['realname']=$_G[gp_realname];
 	$share['contactor']=$_G[gp_contactor];
 	$share['contactway']=$_G[gp_contactway];
 	$share['province']=$_G[gp_province];
 	$share['dept']=$_G[gp_dept];
 	$share['companyname']=$_G[gp_companyname];
 	$share['companyway']=$_G[gp_companyway];
 	$share['coursename']=$_G[gp_coursename];
 	$share['category']=$_G[gp_category];
 	$share['time']=$_G[gp_time];
 	$share['trainobj']=$_G[gp_trainobj];
 	$share['lecturer']=$_G[gp_lecturer];
 	$share['lecturerinfo']=$_G[gp_lecturerinfo];
 	$start=$_G[gp_start];
 	$end=$_G[gp_end];
 	$traintime=$start;
 	if($end) $traintime.="~".$end;
 	$share['traintime']=$traintime;
 	$share['advantage']=$_G[gp_advantage];
 	$share['defect']=$_G[gp_defect];
 	$share['degree']=$_G[gp_degree];
 	$share['exp']=$_G[gp_exp];
	DB :: insert("sharesource", $share);
	DB::query("update ".DB::table("share_province")." set num=num+1 WHERE regname='".$_G[username]."'");
	showmessage("创建成功！","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=groupmenu");
 }

 /**
 * 保存修改
 */
 function alter(){
 	global $_G;
 	$share=array();
 	$share['uid']=$_G[gp_uid];
 	$share['regname']=$_G[gp_regname];
 	$share['realname']=$_G[gp_realname];
 	$share['contactor']=$_G[gp_contactor];
 	$share['contactway']=$_G[gp_contactway];
 	$share['province']=$_G[gp_province];
 	$share['dept']=$_G[gp_dept];
 	$share['companyname']=$_G[gp_companyname];
 	$share['companyway']=$_G[gp_companyway];
 	$share['coursename']=$_G[gp_coursename];
 	$share['category']=$_G[gp_category];
 	$share['time']=$_G[gp_time];
 	$share['trainobj']=$_G[gp_trainobj];
 	$share['lecturer']=$_G[gp_lecturer];
 	$share['lecturerinfo']=$_G[gp_lecturerinfo];
 	$start=$_G[gp_start];
 	$end=$_G[gp_end];
 	$traintime=$start;
 	if($end) $traintime.="~".$end;
 	$share['traintime']=$traintime;
 	$share['advantage']=$_G[gp_advantage];
 	$share['defect']=$_G[gp_defect];
 	$share['degree']=$_G[gp_degree];
 	$share['exp']=$_G[gp_exp];
 	$where=array("id"=>$_G[gp_id]);
	DB :: update("sharesource", $share,$where);
	showmessage("修改成功！","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=groupmenu");
 }

/**
 * 删除
 */
function delete(){
	global $_G;
	DB::query("delete FROM ".DB::table("sharesource")." WHERE id=".$_G[gp_id]);
	DB::query("update ".DB::table("share_province")." set num=num-1 WHERE regname=".$_G[uid]);
	showmessage("删除成功！","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=sharesource&plugin_op=groupmenu");
}

/**
 * 进入编辑
 */
  function edit(){
  	global $_G;
  	require_once (dirname(__FILE__)."/function/function_sharesource.php");
	$detail=getsharesource($_G[gp_id]);
	$time=explode("~",$detail[traintime]);
	$detail[start]=$time[0];
	$detail[end]=$time[1];
  	include template("sharesource:create/edit");
	dexit();
  }
?>
