<?php
/*
 * Created on 2012-6-7
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
global $_G;
$action=$_G['gp_ac'];
if($action){
	$action();
}

function user(){
	global $_G;
	$status=-1;
	$user=array();

	$msg=md5("user_".$_G[gp_regname]);
	if($msg==$_G[gp_msg]){
		$uid=user_get_uid_by_username($_G[gp_regname]);
		if($uid){
			$user[uid]=$uid;
			$user[regname]=$_G[gp_regname];
			$user[realname]=user_get_user_name_by_username($user[regname]);
			$sql="select * from pre_forum_groupuser where fid=36 AND level=1 AND username='".$_G[gp_regname]."'";
			$value = DB :: fetch(DB :: query($sql));
			if($value) $user[isadmin]=1;
			else $user[isadmin]=0;
			$status=1;
		}else{
			$status=0;
		}
	}
	$arr=array("status"=>$status,"data"=>$user);
	$jsondata = json_encode ($arr);
	echo $jsondata;
}

function credit(){
	global $_G;
	$status=-1;
	$data=array();
	$msg=md5("credit_".$_G[gp_op]."_".$_G[gp_regname]);
	if($msg==$_G[gp_msg]){
		if($_G[gp_op]=='get'){
			$info=search($_G[gp_regname]);
			if($info[status]==0)
				$status=0;
			else{
				$status=1;
				$data=$info[value];
			}
		}
		if($_G[gp_op]=='delete')
			$status=delete($_G[gp_regname],$_G[gp_num]);

	}

	$arr=array("status"=>$status,"data"=>$data);
	$jsondata = json_encode ($arr);
	echo $jsondata;
}

function search($username){
	global $_G;
	$status=0;
	$sql="select uid,username as regname,realname,exchangecredit as credit from pre_learn_credit where username='".$username."'";
	$value = DB :: fetch(DB :: query($sql));
	if($value) $status=1;
	return array("status"=>$status,"value"=>$value);
}

function delete($username,$num){
	global $_G;
	$status=0;
	$sql="select uid,username as regname,realname,exchangecredit as credit from pre_learn_credit where username='".$username."'";
	$value = DB :: fetch(DB :: query($sql));
	if($value) {
		if($num>$value[credit])
			$status=2;
		else{
			op_learncredit($value[uid],$value[regname],$value[realname],5,5,1,$num);
			$status=1;
		}
	}
	return $status;
}


/**
 * 学习积分操作-兑换
 *用户信息 uid-用户ID,regname-用户网大帐号,realname-用户真实姓名
 *type 1-学习激励 2-意见箱 3-学习力评估 4-有奖问卷 5-积分兑换
 *mode 1-新建 2-审核通过 3-证明奖励 4-奖励 5-其他
 *credit 积分>0
 *company 所在公司
 *optionid 如学习激励ID
 *积分表pre_learn_credit,积分记录表pre_learncredit_record
 */
function op_learncredit($uid,$regname,$realname,$type,$mode,$optionid,$credit){
	$info=DB :: fetch(DB :: query("select count(*) as count from pre_learn_credit where username='".$regname."'"));
	//扣除积分
	$up_sql="update pre_learn_credit set exchangecredit=exchangecredit-".$credit.",dateline=".time()." where username='".$regname."'";
	DB :: query($up_sql);
	//更新积分记录
	$learncredit_record=array('uid' => $uid,'username' => $regname,'type' => $type,'mode' => $mode,'objectid' => $optionid,'credit' => $credit,'dateline'=>time());
	DB :: insert("learncredit_record", $learncredit_record);
}



?>
