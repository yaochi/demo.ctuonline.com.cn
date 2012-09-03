<?php
/* Function:查找人员
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/api/sphinx/sphinxapi.php';
$discuz = & discuz_core::instance();

$discuz->init();
$name=$_G['gp_name'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
if($name){
	if($_config['sphinx']['used'] && $_config['memory']['redis']['on']){
		$result=sphinxdata($name,$num,$shownum);
		$membercount=$result[total];
		if($result[matches]){
			$member=redisdata($result[matches]);
		}
	}elseif($_G[config]['sphinx']['used']){
		$result=sphinxdata($name,$num,$shownum);
		$membercount=$result[total];
		if($result[matches]){
			for($i=0; $i<count($result[matches]); $i++){
				$uidarr[]=$result[matches][$i]['id'];
			}
			$query=DB::query("SELECT * FROM ".DB::table("common_member_status")." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and cms.uid in (".implode(',',$uidarr).") ORDER BY cms.uid asc limit $num,$shownum");
			while($uservalue=DB::fetch($query)){
				$member[]=$uservalue;
			}
		}
	}else{
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


function sphinxdata($name,$page,$pageSize){
	global $_G;
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
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

function redisdata($keysarr=array()){
	global $_G;
	$redis = new Redis();
	$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
	for($i=0; $i<count($keysarr); $i++){
		$list[]=$redis->hmget($keysarr[$i]['id'],array('uid','username','realname','follow','fans','blogs','friends','userprovince'));
	}
	return $list;
}
?>