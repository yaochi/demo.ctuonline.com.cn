<?php
/**
 *      yangy 2011-10-24 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once './api/shpinx/sphinxapi.php';

$keywords=$_G['gp_keywords'];
if($_G[config][sphinx][used]){
	$cl = new SphinxClient();
	$cl->SetServer($_G[config][sphinx][hostname], $_G[config][sphinx][server_port]); //注意这里的主机
	$cl->SetMatchMode(SPH_MATCH_ANY);
	$sc->SetArrayResult(true);
	$result = $sc->Query ( $keywords, "hometag");
	if($_G[config][redis][used]){
		$redis = new Redis();
		$redis->connect($_G[config][redis][hostname], $_G[config][redis][server_port]);
		
		
	}else{
		$query=DB::query("select * from ".DB::TABLE("home_tagrelation")." where id in (".implode(",",$result[matches]).")");
		while($value=DB::fetch($query)){
			$contentlist[]=$value;
		}
	}
	
	

}else{
	$count=DB::result_first("select count(*) from ".DB::TABLE("home_tagrelation")." where tagname like '%".$keywords."%'");
	if($count){
		$query=DB::query("select * from ".DB::TABLE("home_tagrelation")." where tagname like '%".$keywords."%'");
		while($value=DB::fetch($query)){
			$contentlist[]=$value;
		}
	}

}

include template('home/space_task');


?>