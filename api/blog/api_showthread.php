<?php
/* Function: 根据检索参数，获取日志的详情和日志的评论
 * Com.:
 * Author: yangyang
 * Date: 2011-10-26 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
require_once libfile('function/discuzcode');
$uid=$_G['gp_uid'];
$tid=$_G['gp_tid'];
$feedid=$_G['gp_feedid'];
$fid=$_G['gp_fid'];

if($tid&&$feedid){
	//$thread=DB::fetch_first("SELECT fth.* ,bf.message FROM ".DB::table('forum_thread')." fth left join ".DB::table('forum_post')." fp on fth.tid = fp.tid WHERE fth.tid =".$tid." and fth.fid=".$fid);
	if($fid){
		$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$tid." and fid=".$fid);
	}else{
		$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$tid);
	}
	while($value=DB::fetch($query)){
		$value['message'] = discuzcode($value['message'],0, 0, 1, 0 , 1, 1, 1, 0, 0, $value['authorid'], 1, $value['pid']);
		if($value[first]=='1'){
			$value[author]=user_get_user_name($value[authorid]);
			$res['thread']=$value;
		}else{
			if($value[anonymity]){
				if($value[anonymity]==-1){
					$value[realuid]=$value[authorid];
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
			$res['comment'][]=$value;
		}
	}
}

echo json_encode($res);
?>