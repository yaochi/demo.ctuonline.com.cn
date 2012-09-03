<?php
/* Function: 我收到的评论
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($uid){
	$query=DB::query("select f.*,c.*,f.idtype as idtype  from ".DB::table('home_feed')." f,".DB::table("home_comment")." c where c.idtype='feed' and c.id=f.feedid and c.uid=".$uid." ORDER BY c.dateline desc limit $num,$shownum");
		while($value=DB::fetch($query)){
			if($value[arlcid]){
				$orlvalue[$value[cid]]=DB::fetch_first("select * from ".DB::TABLE("home_comment")." where cid=".$value[arlcid]);
			}
			$value=mkfeed($value);
			$value[author]=user_get_user_name($value[authorid]);
			if($value[anonymity]){
			if($value[anonymity]==-1){
				$value[authorid]=-1;
				$value[author]='匿名';
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[anonymity]);
				$value[authorid]=$repeatsinfo[fid];
				$value[author]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
			}
		}else{
			$value[authorid]=$value[authorid];
			$value[author]=user_get_user_name($value[authorid]);
		}
		//$value[realname]=user_get_user_name($value[uid]);
		$newvalue[user][uid]=$value[authorid];
		$newvalue[user][username]=$value[author];
		if($value[anonymity]>0){
			$newvalue[user][iconImg]=$value['ficon'];
		}else{
			$newvalue[user][iconImg]=useravatar($value[authorid]);
		}
		$newvalue[commentDate]=$value[dateline];
		$newvalue[content]=$value[message];
		$newvalue[myContent]=$value[body_template];
		if($orlvalue[$value[cid]][message]){
			$value[body_data][summary]=$orlvalue[$value[cid]][message];
		}
		$newvalue[body_data]=$value[body_data];
		$newvalue[body_general]=$value[body_general];
		$newvalue[idtype]=$value[idtype];
		$newvalue[anonymity]=$value[anonymity];
		$newvalue[feedid]=$value[feedid];
		$newvalue[icon]=$value[icon];
		$newvalue[fid]=$value[fid];
		$newvalue[cid]=$value[cid];
//		$res[comments][]=$value;
		$res[comments][]=$newvalue;
	}
}
echo json_encode($res);
?>