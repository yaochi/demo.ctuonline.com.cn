<?php
/* Function: 根据检索参数，获得该动态的评论信息
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G['gp_feedid'];
$cid=$_G['gp_cid'];
$pagetype=$_G['gp_pagetype'];
$shownum=empty($_G['gp_shownum'])?20:$_G['gp_shownum'];

if($feedid){
	$feedarr=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	$wheresql['1=1']=" 1=1 ";
	if($cid){
		if($pagetype=="up"){
			$wheresql['cid']=" cid<".$cid;
			$postsql['pid']=" and  pid<".$cid;
		}elseif($pagetype=="down"){
			$wheresql['cid']=" cid>".$cid;
			$postsql['pid']=" and pid>".$cid;
		}
	}else{
		$pagetype='up';
	}
	if($feedarr[icon]=='thread'||$feedarr[icon]=='poll'||$feedarr[icon]=='reward'){
		$ordersql=updownsql($pagetype,'forum_post','dateline',$cid,$shownum,'pid'," tid=".$feedarr[id]." and first!='1' and authorid!=0 ");
		$res['refresh']=$ordersql['refresh'];
		$query=DB::query("select *,anonymous as anonymity from ".DB::TABLE("forum_post")." where tid =".$feedarr[id]." and first!='1' and authorid!=0 ".$postsql['pid'].$ordersql['ordersql']);
	}elseif($feedarr[icon]=='resourcelist'){
		$ordersql=updownsql($pagetype,'home_comment','cid',$cid,$shownum,'cid'," idtype='docid' and authorid!=0 and id=".$feedarr[id]);
		$res['refresh']=$ordersql['refresh'];
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where ".implode(' AND ', $wheresql)." and idtype ='docid' and authorid!=0 and id=".$feedarr[id].$ordersql['ordersql'] );
	}else{
		$ordersql=updownsql($pagetype,'home_comment','cid',$cid,$shownum,'cid'," idtype='feed' and authorid!=0 and id=".$feedid);
		$res['refresh']=$ordersql['refresh'];
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where ".implode(' AND ', $wheresql)." and idtype ='feed' and authorid!=0 and id=".$feedid.$ordersql['ordersql'] );
	}
	while($value=DB::fetch($query)){
		$newvalue[cid]=empty($value[pid])?$value[cid]:$value[pid];
		$newvalue[commentDate]=$value[dateline];
		if(strripos($value[message],'</blockquote>')){
			$newvalue[content]=substr($value[message],strripos($value[message],'</blockquote>')+29);
		}elseif(strripos($value[message],'[/i]')){
			$newvalue[content]=substr($value[message],strripos($value[message],'[/i]')+16);
		}else{
			$newvalue[content]=$value[message];
		}
		if(strripos($newvalue[content],'<br />')){
			$newvalue[content]=substr($newvalue[content],0,strripos($newvalue[content],'<br />'));
		}
		$newvalue[content]=htmlspecialchars_decode($newvalue[content]);
		if($value[anonymity]){
			if($value[anonymity]==-1){
				$newvalue[user][uid]=-1;
				$newvalue[user][username]='匿名';
				$newvalue[user][iconImg]=useravatar(-1);
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$newvalue[user][uid]=$repeatsinfo[fid];
				$newvalue[user][username]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
				$newvalue[user][iconImg]=$value['ficon'];
			}
		}else{
			$newvalue[user][uid]=$value[authorid];
			$newvalue[user][iconImg]=useravatar($newvalue[user][uid]);
			$newvalue[user][username]=user_get_user_name($newvalue[user][uid]);
		}
		$newvalue[myContent]="";
		$res[comments][]=$newvalue;
	}
}
echo json_encode($res);
?>