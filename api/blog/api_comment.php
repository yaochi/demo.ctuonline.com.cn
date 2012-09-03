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
if($feedid){
	$feedarr=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($feedarr[icon]=='thread'||$feedarr[icon]=='poll'||$feedarr[icon]=='reward'){
		$query=DB::query("select * from ".DB::TABLE("forum_post")." where tid =".$feedarr[id]." and first!='1' order by dateline asc ");
	}elseif($feedarr[icon]=='resourcelist'){
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id=".$feedarr[id]." order by dateline asc" );
	}else{
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feedid." order by dateline asc" );
	}
	while($value=DB::fetch($query)){
		$newvalue[anonymity]=$value[anonymity];
		$newvalue[cid]=empty($value[pid])?$value[cid]:$value[pid];
		$newvalue[uid]=empty($value[uid])?$value[authorid]:$value[uid];
		$newvalue[id]=empty($value[tid])?$value[id]:$value[tid];
		$newvalue[dateline]=$value[dateline];
		$newvalue[authorid]=$value[authorid];
		$newvalue[ip]=empty($value[useip])?$value[ip]:$value[useip];
		if(strripos($value[message],'</blockquote>')){
			$newvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
		}elseif(strripos($value[message],'[/i]')){
			$newvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
		}else{
			$newvalue[message]=$value[message];
		}
		
		if(strripos($newvalue[message],'<br />')){
			$newvalue[message]=substr($newvalue[message],0,strripos($newvalue[message],'<br />'));
		}
		if($value[anonymity]){
			if($value[anonymity]==-1){
				$newvalue[realuid]=$newvalue[authorid];
				$newvalue[authorid]=-1;
				$newvalue[realname]='匿名';
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$newvalue[authorid]=$repeatsinfo[fid];
				$newvalue[realname]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$newvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$newvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
			}
		}else{
			$newvalue[authorid]=$newvalue[authorid];
			$newvalue[realname]=user_get_user_name($newvalue[authorid]);
		}
		//$newvalue[realname]=user_get_user_name($value[authorid]);
		//$newvalue[userimgurl]=useravatar($value[authorid]);
		$res[comment][]=$newvalue;
	}
}
echo json_encode($res);
?>