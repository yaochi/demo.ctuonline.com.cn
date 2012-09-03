<?php
/* Function: 根据检索参数，获取日志的详情和日志的评论
 * Com.:
 * Author: yangyang
 * Date: 2011-10-26 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$blogid=$_G['gp_blogid'];
$feedid=$_G['gp_feedid'];

if($blogid&&$feedid){
	$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$blogid);
	$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feedid);
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
		$res['comment'][]=$value;
	}
}
$res['blog']=$blog;

echo json_encode($res);
?>