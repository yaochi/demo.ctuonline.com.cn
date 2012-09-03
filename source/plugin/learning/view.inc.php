<?php
/*
 * Created on 2011-11-29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

 function index() {
	global $_G;
	$planid=$_G['gp_learnid'];
	require_once (dirname(__FILE__)."/function/function_learning.php");
	$learnmv=getlearnmv($planid);
	return array("learnmv"=>$learnmv);
 }




function save(){
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$id=$_POST[learnid];
	$type=$_POST[savetype];
	$subrealname=$_POST['subrealname'];
	$subusername=$_POST['subusername'];
	$subdeptname=$_POST['subdept'];
	$subcompanyname=$_POST['subcompany'];
	$subtel=$_POST['subtel'];
	$subPost=$_POST['subpost'];
	$learnsource=$_POST['learnsource'];
	$learnHarvest= $_POST['learnHarvest'];
	$learnaction=$_POST['learnaction'];
	$learnachievements=$_POST['learnachievements'];
	$learnname =$_POST['learnname'];
	$Witnessrealname=$_POST['witnessrealname'];
	$Witnessusername =$_POST['witnessusername'];
	$Witnessdeptname=$_POST['witnessdeptname'];
	$WitnessPost=$_POST['witnesspost'];
	$Witnesscompanyname =$_POST['witnesscompanyname'];
	$Witnesstel =$_POST['witnesstel'];
	$confidenceindex=$_POST['confidenceindex'];
	if($type=="savepost"){
     $savestatus=1;//保存状态
	 $info="保存成功，您可以随时完善后，提交审核，谢谢！";
	 $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=learning&plugin_op=groupmenu&learning_action=create";
	savelearning($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus,$id);
	}if($type=="releasepost"){
     $savestatus=2;//发布状态 3 已审核
      savelearning($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus,$id);
//	 $createcredit=10;
//   op_learncredit($_G['uid'],$subusername,$subrealname,1,1,$id,$createcredit,$subcompanyname);//奖励积分
     $info="提交成功，点击意见回复查看审核意见，谢谢！";
     $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=learning&plugin_op=groupmenu&learning_action=optionreply";
	}
	showmessage($info, $url);
}



?>