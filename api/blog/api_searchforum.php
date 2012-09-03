<?php
/* Function:查找专区
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
	if($_G[config]['sphinx']['used'] && $_G[config]['memory']['redis']['on']){
		$result=sphinxdata($name,$num,$shownum);
		$forumcount=$result[total];
		if($result[matches]){
			$forums=redisdata($result[matches]);
		}
	}elseif($_G[config]['sphinx']['used']){
		$result=sphinxdata($name,$num,$shownum);
		$forumcount=$result[total];
		if($result[matches]){
			for($i=0; $i<count($result[matches]); $i++){
				$fidarr[]=$result[matches][$i]['id'];
			}
			$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.fid in (".implode(',',$fidarr).") and ff.type='sub' and ff.fid=fff.fid order by ff.fid asc limit 0,10");
			while($forumvalue=DB::fetch($query)){
				$forums[]=$forumvalue;
			}
		}
	}else{
		$forumcount=DB::result_first("select count(*) from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid");
		if($forumcount){
			$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid order by ff.fid asc limit 0,10");
			while($forumvalue=DB::fetch($query)){
				$forums[]=$forumvalue;
			}
		}
	}
	$res[forumcount]=$forumcount;
	$res[forum]=$forums;
}



echo json_encode($res);



function sphinxdata($name,$page,$pageSize){
	global $_G;
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "forum";
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
	$redis->select(1);
	for($i=0; $i<count($keysarr); $i++){
		$list[]=$redis->hmget($keysarr[$i]['id'],array('fid','name','description','icon','membernum'));
	}
	return $list;
}
?>