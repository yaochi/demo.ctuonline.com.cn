<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");
 //require_once (dirname(__FILE__) . "/function/function_learning.php");

/**
 *进入创建有奖问卷试题页
 */
 function enter(){
 	global $_G;
 	include template("exam:create/enter");
	dexit();
 }

 function trans(){
 	global $_G;
 	require_once (dirname(__FILE__)."/function/function_exam.php");
 	$id=$_G['gp_id'];
 	$status=$_G['gp_status'];
 	update(array("status"=>$status),$id);
	showmessage("状态更改成功","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=createmenu");
}

 /**
 *进入创建有奖问卷编辑页
 */
 function edit(){
 	global $_G;
 	require_once (dirname(__FILE__)."/function/function_exam.php");
 	$eid=$_G['gp_id'];
	$base=getexam($eid);
 	include template("exam:create/edit");
	dexit();
 }

 /**
  *保存创建的有奖问卷
  */
  function save(){
  	global $_G;
  	require_once (dirname(__FILE__)."/function/function_exam.php");
  	$test=$_G['gp_title'].strtotime($_G['gp_status'])."==";
  	$initexam=array("title"=>$_G['gp_title'],"starttime"=>strtotime($_G['gp_starttime']),"status"=>$_G['gp_status'],"dateline"=>time(),"creator"=>$_G[uid]);
  	$id=create($initexam);
  	$filename=upload($id);
  	$num=import($id);
  	$exam=array("filename"=>$filename,"num"=>$num);
  	update($exam,$id);
  	showmessage("创建成功","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=groupmenu&id=".$id);

  }

/**
 *有奖问卷列表
 */
 function index(){
 	global $_G;
 	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=groupmenu&exam_action=exams";
   	header("Location:".$url);
 }

/**
 *管理员统计页面,包括相关统计和答题排名前列的学员名单
 */
 function stats(){
 	global $_G;
 	$id=$_G['gp_id'];
 	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=groupmenu&exam_action=stats&id=".$id;
   	header("Location:".$url);
 }

  function test(){
  	echo time();
  	/*
	require_once (dirname(__FILE__)."/function/function_exam.php");
	require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
	$realpath=dirname(dirname(dirname(dirname(__FILE__))));
	$filepath="/data/attachment/record.xls";
	$realpath.=$filepath;
	$data = new Spreadsheet_Excel_Reader(); //实例化
	$data->setOutputEncoding('gbk');      //编码
	$data->read($realpath);

	$len=$data->sheets[0]['numRows'];
 	for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++)
 	{    //循环输出查询结果，将数据值赋给指定的变量

		$optionid=$data->sheets[0]['cells'][$i][2];
		echo $optionid;

		$title=$data->sheets[0]['cells'][$i][4];
		$title=mb_convert_encoding($title,'UTF-8','GB2312');
		$question[title]=$title;
		$query = DB::query("SELECT cmp.uid, cmp.realname FROM ".DB::table("common_member_profile")." cmp, ".DB::table("common_member")." cm WHERE cm.uid=cmp.uid AND cm.username='".$title."'");
		$row = DB::fetch($query);
		if($row){
			$realname = $row["realname"];
			$uid=$row["uid"];
			print_r($row);
		}
		op_learncredit($uid,$title,$realname,3,1,$optionid,10,"无");
		unset($question);
 	}
	unset($data);
	*/
	exit();
  }


?>
