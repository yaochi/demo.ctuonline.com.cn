<?php
/* Function: 我评论过的动态
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
	$query=DB::query("select f.*,c.*,f.anonymity as fanonymity,f.idtype as idtype from ".DB::table('home_feed')." f,".DB::table("home_comment")." c where c.anonymity='0' and c.idtype='feed' and c.id=f.feedid and c.authorid=".$uid." ORDER BY c.dateline desc limit $num,$shownum");
	while($value=DB::fetch($query)){
		$value=mkfeed($value);
		if($value[arlcid]){
			$orlvalue[$value[cid]]=DB::fetch_first("select * from ".DB::TABLE("home_comment")." where cid=".$value[arlcid]);
		}
		if($value[fanonymity]){
			if($value[fanonymity]==-1){
				$value[uid]=-1;
				$value[realname]='匿名';
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($value[fanonymity]);
				$value[uid]=$repeatsinfo[fid];
				$value[realname]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
			}
		}else{
			$value[realname]=user_get_user_name($value[uid]);
		}
		if($orlvalue[$value[cid]][message]){
			$value[body_data][summary]=$orlvalue[$value[cid]][message];
		}
		//$value[author]=user_get_user_name($value[authorid]);
		
		$res[comment][]=$value;
	}
}
echo json_encode($res);
?>