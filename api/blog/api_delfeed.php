<?php
/* Function: 删除动态
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$feedid=$_G['gp_feedid'];
$code=$_G['gp_code'];

if($code=md5('esn'.$uid.$feedid)){
	$feed=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($uid==$feed[uid]||$feed[anonymity]>0){
		if($feed[idtype]=='tid'){
			$tablearray = array('forum_threadmod', 'forum_relatedthread', 'forum_thread', 'forum_debate', 'forum_debatepost', 'forum_polloption', 'forum_poll', 'forum_typeoptionvar');
			foreach ($tablearray as $table) {
				DB::query("DELETE FROM ".DB::table($table)." WHERE tid='$feed[id]'", 'UNBUFFERED');
			}
		}elseif($feed[idtype]=='albumid'){
			if($feed[id]){
				DB::query("delete from ".DB::TABLE("home_album")." where albumid=".$feed[id]);
				DB::query("delete from ".DB::TABLE("home_pic")." where albumid=".$feed[id]);
			}elseif($feed[target_ids]){
				DB::query("delete from ".DB::TABLE("home_pic")." where picid in ('".$feed[target_ids]."')");
			}
		}elseif($feed[idtype]=='picid'){
			DB::query("delete from ".DB::TABLE("home_pic")." where picid=".$feed[id]);
		}elseif($feed[idtype]=='gpicid'){
			DB::query("delete from ".DB::TABLE("group_pic")." where picid=".$feed[id]);
		}elseif($feed[idtype]=='doid'){
			DB::query("delete from ".DB::TABLE("home_doing")." where doid=".$feed[id]);
		}elseif($feed[idtype]=='blogid'){
			DB::query("delete from ".DB::TABLE("home_blog")." where blogid =".$feed[id]);
			DB::query("delete from ".DB::TABLE("home_blogfield")." where blogid =".$feed[id]);
		}elseif($feed[idtype]=='link'||$feed[idtype]=='flash'||$feed[idtype]=='music'){
			DB::query("delete from ".DB::TABLE("home_share")." where sid=".$feed[id]);
		}elseif($feed[idtype]=='docid'){
		}
		DB::query("update ".DB::TABLE("home_feed")." set title_template='',title_data='',body_template='',body_data='' where idtype='feed' and id=".$feedid);
		$result=DB::query("delete from ".DB::TABLE("home_feed")." where feedid=".$feedid);

		if($result){
			if($feed[icon]=='profile'){
			}else{
				if($feed[anonymity]=='0'){
					DB::query("update pre_common_member_status set blogs=blogs-1 where uid=".$uid);
				}
			}
			$res[success]='Y';
			$res[message]='删除成功！';
		}else{
			$res[success]='N';
			$res[message]='删除失败！';
		}
	}else{
		$res[success]='N';
		$res[message]='只有作者才能删除！';
	}
}else{
	$res[success]='N';
	$res[message]='加密code错误！';
}

echo json_encode($res);
?>