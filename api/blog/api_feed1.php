<?php
/* Function: 根据检索参数，获得相关动态
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_doc.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$feedid=$_G["gp_feedid"];
$pagetype=$_G["gp_pagetype"];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
$type=empty($_G['gp_type'])?'user':$_G['gp_type'];
$typeid=$_G['gp_typeid'];
$idtype=$_G['gp_idtype'];
$startdatetime=$_G['gp_startdatetime'];
$enddatetime=$_G['gp_enddatetime'];

$need_count=true;

if($type=='follow'){
	if($typeid==='0'||$typeid){
		if($typeid=='-1'){
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where (type=1 or type=3) and uid=".$uid." and gids=''");
		}elseif($typeid=='-2'){
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where type=3 and uid=".$uid);
		}else{
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where (type=1 or type=3) and uid=".$uid." and gids like '%,".$typeid.",%'");
		}
		while($value=DB::fetch($query)){
			$fuids[]=$value[fuid];
		}
		if(count($fuids)){
			$wheresql['uid'] = "uid IN ('0',".implode(',',$fuids).")";
		}else{
			$need_count=false;
		}
	}else{
		$result[uid]=$uid;
		space_merge($result, 'field_home');
		if($result[feedfollow]){
			$wheresql['uid'] = "uid IN ($uid,$result[feedfollow])";
		}else{
			$wheresql['uid'] = "uid IN ($uid)";
		}
	}
}elseif($type=='group'){
	if($typeid){
		$wheresql["fid"] = "( fid IN (".$typeid.") or sharetofids like '%,".$typeid.",%' )";
	}else{
		$fids = join_groups($uid);
		if($fids){
			$wheresql["fid"] = "( fid IN (".dimplode($fids).")";
			for($i=0;$i<count($fids);$i++){
				$wheresql["fid"]=$wheresql["fid"]." or sharetofids like '%,".$fids[$i].",%'";
			}
			$wheresql["fid"]=$wheresql["fid"]." )";
		}else{
			$need_count=false;
		}
	}
}elseif($type=='user'){
	$wheresql['uid'] = "uid =".$uid;
}elseif($type=='all'){
	$wheresql['all']=' 1=1 ';
}

if($feedid){
	if($pagetype=="up"){
		$wheresql['feedid']=" feedid<".$feedid;
	}elseif($pagetype=="down"){
		$wheresql['feedid']=" feedid>".$feedid;
	}
}else{
	$pagetype="up";
}
if($idtype){
	$wheresql['idtype']="idtype in ('".implode('\',\'',$idtype)."')";
}
if($startdatetime){
	$wheresql['startdatetime']="dateline >='".$startdatetime."'";
}
if($enddatetime){
	$wheresql['enddatetime']="dateline<'".$enddatetime."'";
}
if(!$wheresql){
	$wheresql['none']=' 1=0 ';
}
if($need_count){
	$ordersql=updownsql($pagetype,'home_feed','feedid',$feedid,$shownum,'feedid');
	$res['refresh']=$ordersql['refresh'];
	$query=DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(feedid) WHERE ".implode(' AND ', $wheresql)." and (icon!='comment' and icon!='forward' and icon!='sitefeed')".$ordersql['ordersql']);
	/*if($pagetype=="up"){
		$query=DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(feedid) WHERE ".implode(' AND ', $wheresql)." and (icon!='comment' and icon!='forward' and icon!='sitefeed') ORDER BY feedid desc LIMIT 0,$shownum");
	}elseif($pagetype=="down"){
		$query=DB::query("SELECT * from ".DB::table('home_feed')." USE INDEX(feedid) where feedid in (SELECT feedid FROM (select feedid from ".DB::table('home_feed')." USE INDEX(feedid) WHERE ".implode(' AND ', $wheresql)." and (icon!='comment' and icon!='forward' and icon!='sitefeed') order by feedid asc LIMIT 0,$shownum) as t ) order by feedid desc");
	}*/
		while($value=DB::fetch($query)){
			if($value[fid]){
				$value[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$value[fid]);
			}else{
				$value[gviewperm]=1;
			}
			if(($value[icon]=='thread' || $value[icon]=='poll' || $value[icon]=='reward')&&$value[idtype]!='feed'){
				$commentquery=DB::query("select * from ".DB::TABLE("forum_post")." where tid =".$value[id]." and first!='1' order by dateline desc limit 0,2");
			}elseif($value[icon]=='resourcelist'){
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id='".$value[id]."' order by dateline desc limit 0,2");
			}else{
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id='".$value[feedid]."' order by dateline desc limit 0,2");
			}
			while($commentvalue=DB::fetch($commentquery)){
				$newcommentvalue[cid]=empty($commentvalue[pid])?$commentvalue[cid]:$commentvalue[pid];
				$newcommentvalue[uid]=empty($commentvalue[uid])?$commentvalue[authorid]:$commentvalue[uid];
				$newcommentvalue[id]=empty($commentvalue[tid])?$commentvalue[id]:$commentvalue[tid];
				$newcommentvalue[dateline]=$commentvalue[dateline];
				$newcommentvalue[ip]=empty($commentvalue[useip])?$commentvalue[ip]:$commentvalue[useip];
				//$newcommentvalue[message]=$commentvalue[message];
				if(strripos($commentvalue[message],'</blockquote>')){
					$newcommentvalue[message]=substr($commentvalue[message],strripos($commentvalue[message],'</blockquote>')+29);
				}elseif(strripos($commentvalue[message],'[/i]')){
					$newcommentvalue[message]=substr($commentvalue[message],strripos($commentvalue[message],'[/i]')+16);
				}else{
					$newcommentvalue[message]=$commentvalue[message];
				}
				if(strripos($newcommentvalue[message],'<br />')){
					$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
				}
				if($commentvalue[anonymity]){
					if($commentvalue[anonymity]==-1){
						$newcommentvalue[authorid]=-1;
						$newcommentvalue[realname]='匿名';
					}else{
						include_once libfile('function/repeats','plugin/repeats');
						$repeatsinfo=getforuminfo($commentvalue[anonymity]);
						$newcommentvalue[authorid]=$repeatsinfo[fid];
						$newcommentvalue[realname]=$repeatsinfo[name];
						if($repeatsinfo['icon']){
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
						}else{
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
						}
					}
				}else{
					$newcommentvalue[authorid]=$commentvalue[authorid];
					$newcommentvalue[realname]=user_get_user_name($commentvalue[authorid]);
				}
				$value[comment][]=$newcommentvalue;
			}
			
			if($value[image_1]){
				if(strrpos($value[image_1],'http://')===0||strrpos($value[image_1],'http://')){
				}else{
					$value[image_1]=$_G[config][image][url].'/'.$value[image_1];
				}
				$value[imagearr][]=$value[image_1];
				$value[imageurlarr][]=$value[image_1_link];
			}
			if($value[image_2]){
				if(strrpos($value[image_2],'http://')===0||strrpos($value[image_2],'http://')){
				}else{
					$value[image_2]=$_G[config][image][url].'/'.$value[image_2];
				}
				$value[imagearr][]=$value[image_2];
				$value[imageurlarr][]=$value[image_2_link];
			}
			if($value[image_3]){
				if(strrpos($value[image_3],'http://')===0||strrpos($value[image_3],'http://')){
				}else{
					$value[image_3]=$_G[config][image][url].'/'.$value[image_3];
				}
				$value[imagearr][]=$value[image_3];
				$value[imageurlarr][]=$value[image_3_link];
			}
			if($value[image_4]){
				if(strrpos($value[image_4],'http://')===0||strrpos($value[image_4],'http://')){
				}else{
					$value[image_4]=$_G[config][image][url].'/'.$value[image_4];
				}
				$value[imagearr][]=$value[image_4];
				$value[imageurlarr][]=$value[image_4_link];
			}
			if($value[image_5]){
				if(strrpos($value[image_5],'http://')===0||strrpos($value[image_5],'http://')){
				}else{
					$value[image_5]=$_G[config][image][url].'/'.$value[image_5];
				}
				$value[imagearr][]=$value[image_5];
				$value[imageurlarr][]=$value[image_5_link];
			}
			if($value['idtype']=='feed'){
				$oldfeedidarr[$value[feedid]]=$value[id];
			}
			$value[userimgurl]=useravatar($value[uid]);
			$value[username]=user_get_user_name($value[uid]);
			$value[oldusername]=user_get_user_name($value[olduid]);
			$feedidarr[]=$value[feedid];
			$value=mkfeed($value);
			if(!$value['title_data']){
				$value['title_data']=null;
			}
			if(!$value['body_data']){
				$value['body_data']=null;
			}
			if($value['icon']=='doc'){
				$value['docarr']=getFile($value[id]);
				$value['docarr']['context']=cutstr($value['docarr']['context'],150);
			}
			if($value['icon']=='share' && $value['idtype']=='video'){
				if(strripos($value['body_data']['data'],'/data/attachment/flv')){
					$value['body_data']['data']=$_G[config][media][url].substr($value['body_data']['data'],strripos($value['body_data']['data'],'/data/attachment/flv'));
					$value['body_data']['flashvar']=IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($value['body_data']['data']);
				}
			
			}
		//	$value[body_data][subject]=parseat($value[body_data][subject]);
			//$value[body_data][summary]=parseat($value[body_data][summary]);
		//	$value[title_data][message]=parseat($value[title_data][message]);
		//	$value[body_general]=parseat($value[body_general]);
			$feedarray[$value[feedid]]=$value;
			
	}
	
	if($feedidarr){
		$query=DB::query("select feedid from ".DB::TABLE("home_favorite")." where uid=".$uid." and feedid in (".implode(',',$feedidarr).")");
		while($value=DB::fetch($query)){
			$feedarray[$value[feedid]][isfavorite]="1";
		}
	}
	if($oldfeedidarr){
		$query=DB::query("select * from ".DB::TABLE("home_feed")." where feedid in (".implode(',',$oldfeedidarr).")");
		while($value=DB::fetch($query)){
			while(in_array($value[feedid],$oldfeedidarr)){
				$newid=array_search($value[feedid],$oldfeedidarr);
				$oldfeedidarr2[$newid]=$value[feedid];
				$oldfeedidarr=array_diff_assoc($oldfeedidarr,$oldfeedidarr2);
				if(($feedarray[$newid][icon]=='share'||$feedarray[$newid][icon]=='album')&&$feedarray[$newid][idtype]=='feed'){
					$feedarray[$newid]['oldbody_general']=$value['body_general'];
				}
				$feedarray[$newid]['oldid']=$value['id'];
				$feedarray[$newid]['oldcommenttimes']=$value[commenttimes];
				$feedarray[$newid]['oldsharetimes']=$value[sharetimes];
			}
		}
	}
	for($i=0;$i<count($feedarray);$i++){
		$newfeedarray[]=$feedarray[$feedidarr[$i]];
	}
	$res[feed]=$newfeedarray;
}else{
	$res[feed]=null;
}
//print_r($res);
echo json_encode($res);
?>