<?php
/* Function:查找人员
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8 
 */
require dirname(dirname(dirname(__FILE__))).'/api/sphinx/sphinxapi.php';
include dirname(dirname(dirname(__FILE__))).'/config/config_global.php';

$name=$_GET['name'];
$num=empty($_GET['num'])?0:$_GET['num'];
$shownum=empty($_GET['shownum'])?10:$_GET['shownum'];
if($name){
	if($_config['sphinx']['used'] && $_config['memory']['redis']['on']){
		$result=sphinxdata($_config,$name,$num,$shownum);
		$membercount=$result[total];
		if($result[matches]){
			$member=redisdata($_config,$result[matches]);
		}
	}else{
		require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
		$discuz = & discuz_core::instance();
		
		$discuz->init();
		
		$name=$_GET['name'];
		$num=empty($_GET['num'])?0:$_GET['num'];
		$shownum=empty($_GET['shownum'])?10:$_GET['shownum'];
		$membercount=DB::result_first("SELECT count(*) FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$name."%'");
		if($membercount){
			$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and profile.realname LIKE '%".$name."%' ORDER BY cms.uid asc limit $num,$shownum");
			while($uservalue=DB::fetch($query)){
				$member[]=$uservalue;
			}
		}
		
	}
	$res[membercount]=$membercount;
	$res[member]=$member;
}

echo json_encode($res);

function sphinxdata($_config,$name,$page,$pageSize){
	$host =$_config['sphinx']['hostname'];
	$port = $_config['sphinx']['server_port'];
	$index = "article";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	$sc->SetArrayResult ( true );
	$result = $sc->Query ( $name, $index );

	return $result;

}

function redisdata($_config,$keysarr=array()){
	$redis = new Redis();
	$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
	for($i=0; $i<count($keysarr); $i++){
		$list[]=$redis->hmget($keysarr[$i]['id'],array('uid','username','realname','follow','fans','blogs','friends'));
	}
	return $list;
}
?>