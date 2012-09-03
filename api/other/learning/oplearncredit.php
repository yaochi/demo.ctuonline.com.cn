<?php
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	global $_G;
	$action=$_G['gp_ac'];
	if($action){
		$action();
	}

/**
 * 获取学习力积分排行榜
 */
function toplist(){
	global $_G;
	$key=$_G['gp_key'];
	$sys=$_G['gp_sys'];
    $mykey=md5("api_learning_top_".$sys);

    if($mykey==$key){
    	$message="success";
		$per=$_G['gp_per'];
		$info = DB :: query("select username,realname,company,exchangecredit from pre_learn_credit  order by exchangecredit desc,dateline limit 0,".$per);
		if ($info == False)  $obj = "";
		else while ($value = DB :: fetch($info))  $obj[] = $value;
    }else{
		$message="fail";
		$obj="";
    }
	$arr=array("message"=>$message,"data"=>$obj);

	$jsondata = json_encode ($arr);
    echo $jsondata;
    exit();
}

/**
 * 直播评估积分
 *
 */
function addcredit(){
	global $_G;
	$key=$_G['gp_key'];
	$sys=$_G['gp_sys'];
    $mykey=md5("api_credit_pinggu_".$sys);

    if($mykey==$key){
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/source/plugin/learning/function/function_learning.php';
		op_learncredit($_G['gp_uid'],$_G['gp_regname'],$_G['gp_realname'],3,1,$_G['gp_liveid'],10,"无");
		$message="success";
    }else{
		$message="fail";
    }
	$arr=array("message"=>$message);

	$jsondata = json_encode ($arr);
    echo $jsondata;
    exit();
}


?>