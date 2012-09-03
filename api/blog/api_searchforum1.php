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
$searchfid=$_G['gp_searchfid'];
$pagetype=$_G['gp_pagetype'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($name){
	if($_G[config]['sphinx']['used'] && $_G[config]['memory']['redis']['on'] && '1'=='0'){
		$result=sphinxdata($name,$shownum,$searchfid,$pagetype);
		$forumcount=$result[total];
		if($result[matches]){
			$forums=redisdata($result[matches]);
		}
	}elseif($_G[config]['sphinx']['used']){
		$result=sphinxdata($name,$shownum,$searchfid,$pagetype);
		$forumcount=$result[total];
		if($result[matches]){
			for($i=0; $i<count($result[matches]); $i++){
				$fidarr[]=$result[matches][$i]['id'];
			}
			if(!$pagetype){
				$pagetype='up';
			}
			$ordersql=updownsql($pagetype,'forum_forum','ff.fid',$searchfid,$shownum,'fid'," name like '%".$name."%' and type='sub'");
			$res['refresh']=$ordersql['refresh'];
			$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.fid in (".implode(',',$fidarr).") and ff.type='sub' and ff.fid=fff.fid order by ff.fid desc limit 0,$shownum");
			while($forumvalue=DB::fetch($query)){
				$forumvalue[type]='forum';
				$forum[fid]=$forumvalue[fid];
				$forum[ficonImg]=$forumvalue[icon];
				$forum[fname]=$forumvalue[name];
				$forum[memberNum]=$forumvalue[membernum];
				$forum[description]=$forumvalue[description];
				$forums[]=$forum;
			}
		}
	}else{
		$forumcount=DB::result_first("select count(*) from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid");
		if($forumcount){
			$wheresql['1=1']=" 1=1 ";
			if($searchfid){
				if($pagetype=='up'){
					$wheresql['uid']=" ff.fid<".$searchfid;
				}elseif($pagetype=='down'){
					$wheresql['uid']=" ff.fid>".$searchfid;
				}
			}else{
				$pagetype='up';
			}
			$ordersql=updownsql($pagetype,'forum_forum','ff.fid',$searchfid,$shownum,'fid'," name like '%".$name."%' and type='sub'");
			$res['refresh']=$ordersql['refresh'];
			$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum,fff.gviewperm from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ".implode(' AND ', $wheresql)." and ff.name like '%".$name."%' and ff.type='sub' and fff.jointype='0' and ff.fid=fff.fid ".$ordersql['ordersql']);
			/*if($pagetype=='up'){
				$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum,fff.gviewperm from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ".implode(' AND ', $wheresql)." and ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid order by ff.fid desc limit 0,$shownum");
			}elseif($pagetype=='down'){
				$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum,fff.gviewperm from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ".implode(' AND ', $wheresql)." and ff.name like '%".$name."%' and ff.type='sub' and ff.fid=fff.fid order  by ff.fid asc limit 0,$shownum");
			}*/
			while($forumvalue=DB::fetch($query)){
				$forumvalue[type]='forum';
				$forum[fid]=$forumvalue[fid];
				$forum[ficonImg]=$forumvalue[icon];
				$forum[fname]=$forumvalue[name];
				$forum[memberNum]=$forumvalue[membernum];
				$forum[description]=$forumvalue[description];
				$forums[]=$forum;
	//			$forums[]=$forumvalue;
			}
		}
	}
	$res[forumcount]=$forumcount;
	$res[forums]=$forums;
}

echo json_encode($res);


function sphinxdata($name,$shownum,$searchfid,$pagetype){
	global $_G;
	
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "forum";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;
	
	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	if($searchfid){
		$searchfid=$searchfid-1;
		if($pagetype=='up'){
			$sc->SetIDRange(0,$searchfid);
		}elseif($pagetype=='down'){
			$sc->SetIDRange($searchfid,10000000000);
		}
	}
	
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_BOOLEAN );
	$sc->SetSortMode(SPH_SORT_EXTENDED,"@id DESC");
	$sc->SetArrayResult ( true );
	//$sc->SetFilter( 'jointype', array(0) );
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
}
?>