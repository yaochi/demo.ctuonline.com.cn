<?php
/* Function: 根据检索参数，获得整个图片
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G['gp_feedid'];
$pid=$_G['gp_pid'];
$type=$_G['gp_type'];
if($type=="home"){
	$pic=DB::fetch_first("select * from ".DB::TABLE("home_pic")." where picid =".$pid);
}elseif($type=="group"){
	$pic=DB::fetch_first("select * from ".DB::TABLE("group_pic")." where picid =".$pid);
}
$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='picid' and id=".$pid);
while($value=DB::fetch($query)){
	if($value[anonymity]){
		if($value[anonymity]==-1){
			$value[realuid]=$value[authorid];
			$value[authorid]=-1;
			$value[realname]='匿名';
		}else{
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value[anonymity]);
			$value[authorid]=$repeatsinfo[fid];
			$value[realname]=$repeatsinfo[name];
			if($repeatsinfo['icon']){
				$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
			}else{
				$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
			}
		}
	}else{
		$value[authorid]=$value[authorid];
		$value[realname]=user_get_user_name($value[authorid]);
	}
	$res[comment][]=$value;
}

$res['picture']=$pic;
echo json_encode($res);
?>