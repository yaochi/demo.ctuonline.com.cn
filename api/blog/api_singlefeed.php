<?php
/* Function: 根据检索参数，获得相关动态
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_doc.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G[gp_feedid];

if($feedid){
	$feedarr=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid='".$feedid."'");
	if($feedarr['icon']=='blog'&&$feedarr['idtype']!='feed'){
		$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$feedarr[id]);	
	}elseif($feedarr['icon']=='album'&&$feedarr['idtype']!='feed'){
		$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$feedarr[target_ids].")");
		while($value=DB::fetch($query)){
			$album[]=$value;
		}
	}elseif($feedarr['icon']=='share'&& ($feedarr['idtype']=='link'||$feedarr['idtype']=='music'||$feedarr['idtype']=='flash')){
		
	}
	$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feedid." order by dateline desc");
	while($commentvalue=DB::fetch($commentquery)){
		if($commentvalue[anonymity]){
			if($commentvalue[anonymity]==-1){
				$commentvalue[authorid]=-1;
				$commentvalue[realname]='匿名';
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($commentvalue[anonymity]);
				$commentvalue[authorid]=$repeatsinfo[fid];
				$commentvalue[realname]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$commentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$commentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
			}
		}else{
			$commentvalue[authorid]=$commentvalue[authorid];
			$commentvalue[realname]=user_get_user_name($commentvalue[authorid]);
		}
		$res['comment'][]=$commentvalue;
	}
	$res['feed']=mkfeed($feedarr);
	$res['blog']=$blog;
	$res['album']=$album;
	
}else{
	$res=null;
}

echo json_encode($res);
?>