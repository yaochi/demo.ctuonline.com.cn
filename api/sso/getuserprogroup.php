<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *
 *
 *      $Id: userapp.php 10075 2011-05-06 09:52:41Z qiaoyongzhi $
 */

define('APPTYPEID', 5);

require_once '../../source/class/class_core.php';
require_once '../../source/function/function_space.php';

$discuz = & discuz_core::instance();
$cachelist = array('grouptype', 'groupindex', 'blockclass');
$discuz->cachelist = $cachelist;
$discuz->init();
$progroup[groupname]="";
if($_GET[pro]=='group'){
	if($_GET[regname]){
		$regname=$_GET[regname];
	}else{
		$userarr=getuserbyuid($_GET[uid]);
		$regname=$userarr[username];
	}
	$progroup=getprogroup($regname);//获取用户所在省的信息
}

if($_GET[ptype]==1){
    $tpid=$_GET[tpid];
    $arraydata=array("s"=>$progroup[groupname],"regname"=>$regname,"tpid"=>$tpid);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
}else{
//$result=array("id"=>$progroup[groupid],"name"=>$progroup[groupname]);
//$json=json_encode($result);
echo json_encode($progroup);
}

?>