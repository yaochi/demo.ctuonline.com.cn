<?php


/*
 * Created on 2011-11-29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");


function index() {
require_once (dirname(__FILE__) . "/function/function_learning.php");
global $_G;
$usernews=getusenews();
return array("usernews"=>$usernews);
}

function change_username() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
    $witnessusername=$_GET[witnessusername];
	$username = $_G['gp_username'];
		$is = changeusername($witnessusername);
	$arraydate = array (
		"is" => $is
	);
	$jsondata = json_encode($arraydate);
	$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
	echo "$callback($jsondata)";
	exit ();
}
function reply() {
	$uid = $_GET[uid];
	$learid = $_GET[learid];
	$subrealname = $_GET[subrealname];
	$subusername = $_GET[subusername];
	$tap=$_GET[tap];
	$witnessusername=$_GET[witnessusername];
	$witnessrealname=$_GET[witnessrealname];
	$witnesscompanyname=$_GET[witnesscompanyname];
	$subcompanyname=$_GET[subcompanyname];
	$rewardIntegral=$_GET[rewardIntegral];
	return array (
		"learid" => $learid,
		"uid" => $uid,
		"subrealname" => $subrealname,
		"subusername" => $subusername,
		"tap" =>$tap,
		"witnessusername"=>$witnessusername,
		"witnessrealname"=>$witnessrealname,
		"witnesscompanyname"=>$witnesscompanyname,
		"subcompanyname"=>$subcompanyname,
		"rewardIntegral"=>$rewardIntegral
	);
}
function createreply() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$uid = $_GET[uid];
	$subusername=$_GET[subusername];
	$subrealname=$_GET[subrealname];
	$learid=$_GET[learid];
	$autherid=$_G[uid];
	$tap=intval($_G['gp_tap']) ? intval($_GET[tap]):2;
    $replymessage=$_GET[replymessage];
    $witnessusername = $_GET[witnessusername];
	$witnessrealname = $_GET[witnessrealname];
	$generalintegral = 100; //审核通过积分
	$rewardIntegral = $_GET[rewardIntegral]; //奖励积分
	$witnessintegral = 30; //审核通过证明人的积分
	$subcompanyname=$_GET[subcompanyname];
	$witnesscompanyname=$_GET[witnesscompanyname];
    $auther=getrealname($_G[uid]);
    $witnesuid = getwitnessuid($witnessusername);
    if($learid){
    	$examstatus=getlearstatus($learid);
    	if($examstatus==3){
            $arrdata=array("is"=>'1',"auther"=>$auther,"replymessage"=>$replymessage);
         $callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
         $jsondata=json_encode($arrdata);
         echo "$callback($jsondata)";
	      exit ();
    	}
    }
   if($learid)
   	DB::query("update pre_learning_excitation set examinestatus=3 ,examinedateline=".time()." where id=".$learid);//更改状态
   if($uid)
   op_learncredit($uid,$subusername,$subrealname,1,2,$learid,$generalintegral,$subcompanyname);//审核通过创建积分
   if($rewardIntegral!=0){
  	op_learncredit($uid,$subusername,$subrealname,1,4,$learid,$rewardIntegral,$subcompanyname);//奖励积分
    }
   if($witnesuid)
   op_learncredit($witnesuid,$witnessusername, $witnessrealname,1,3,$learid, $witnessintegral,$witnesscompanyname); //证明人奖励积分

   $is=createoptionreply($uid,$subusername,$subrealname,$learid,$autherid,$auther,1,$tap,$replymessage);
   if($is)
   $arrdata=array("is"=>$is,"auther"=>$auther,"replymessage"=>$replymessage);
   $callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
   $jsondata=json_encode($arrdata);
   echo "$callback($jsondata)";
	exit ();
}
function updatlearn() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$learid = $_GET[learid];
	$learnsource = $_GET[learnsource];
	$learnHarvest = $_GET[learnHarvest];
	$learnaction = $_GET[learnaction];
	$learnachievements = $_GET[learnachievements];
	if ($learid)
		$is = changelearn($learid, $learnsource, $learnHarvest, $learnaction, $learnachievements);
	$arraydate = array (
		"is" => $is
	);
	$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
	$jsondata = json_encode($arraydate);
	echo "$callback($jsondata)";
	exit ();
}

?>
