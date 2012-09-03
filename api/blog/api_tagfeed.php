<?php
/* Function: 根据标签名获取该标签下的动态
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_doc.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$tag=$_G['gp_tag'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
$idtype=$_G['gp_idtype'];
$startdatetime=$_G['gp_startdatetime'];
$enddatetime=$_G['gp_enddatetime'];

$need_count=true;
$wheresql['1=1']='1=1';
if($idtype){
	$wheresql['idtype']="ht.contenttype in ('".implode('\',\'',$idtype)."')";
}
if($startdatetime){
	$wheresql['startdatetime']="ht.dateline >='".$startdatetime."'";
}
if($enddatetime){
	$wheresql['enddatetime']="ht.dateline<'".$enddatetime."'";
}
if($tag){
	$tag=urldecode($tag);
	$feedquery=DB::query("select * from ".DB::Table("home_tagrelation")." ht,".DB::TABLE("home_feed")." hf where ht.contentid=hf.feedid and ht.tagname='".$tag."' and ".implode(' AND ', $wheresql)." and hf.icon!='comment' ORDER BY ht.dateline DESC LIMIT $num,$shownum");
		//$feedquery=DB::query("select * from ".DB::TABLE("home_feed")." where feedid in (".implode(',',$feedids).") and icon!='comment' order by dateline desc");
		while($feedvalue=DB::fetch($feedquery)){
			if($feedvalue[fid]){
				$feedvalue[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$feedvalue[fid]);
			}else{
				$feedvalue[gviewperm]=1;
			}
			if($feedvalue[icon]=='thread' || $feedvalue[icon]=='poll' || $feedvalue[icon]=='reward'){
				$commentquery=DB::query("select * from ".DB::TABLE("forum_post")." where tid =".$feedvalue[id]." and first!='1' order by dateline desc limit 0,2");
			}elseif($value[icon]=='resourcelist'){
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id='".$feedvalue[id]."' order by dateline desc limit 0,2");
			}else{
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id='".$feedvalue[feedid]."' order by dateline desc limit 0,2");
			}
			while($commentvalue=DB::fetch($commentquery)){
				$newcommentvalue[anonymity]=$commentvalue[anonymity];
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
				$feedvalue[comment][]=$newcommentvalue;
			}
			if($feedvalue[image_1]){
				$feedvalue[imagearr][]=$feedvalue[image_1];
				$feedvalue[imageurlarr][]=$feedvalue[image_1_link];
			}
			if($feedvalue[image_2]){
				$feedvalue[imagearr][]=$feedvalue[image_2];
				$feedvalue[imageurlarr][]=$feedvalue[image_2_link];
			}
			if($feedvalue[image_3]){
				$feedvalue[imagearr][]=$feedvalue[image_3];
				$feedvalue[imageurlarr][]=$feedvalue[image_3_link];
			}
			if($feedvalue[image_4]){
				$feedvalue[imagearr][]=$feedvalue[image_4];
				$feedvalue[imageurlarr][]=$feedvalue[image_4_link];
			}
			if($feedvalue[image_5]){
				$feedvalue[imagearr][]=$feedvalue[image_5];
				$feedvalue[imageurlarr][]=$feedvalue[image_5_link];
			}
			if($feedvalue['idtype']=='feed'){
				$oldfeedidarr[$feedvalue[feedid]]=$feedvalue[id];
			}
			if($feedvalue[anonymity]){
				if($feedvalue[anonymity]==-1){
					$feedvalue[uid]=-1;
					$feedvalue[username]='匿名';
				}else{
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($feedvalue[anonymity]);
					$feedvalue[uid]=$repeatsinfo[fid];
					$feedvalue[username]=$repeatsinfo[name];
					if($repeatsinfo['icon']){
						$feedvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
					}else{
						$feedvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
					}
				}
			}else{
				$feedvalue[userimgurl]=useravatar($feedvalue[uid]);
				$feedvalue[username]=user_get_user_name($feedvalue[uid]);
			}
			//$feedvalue[oldusername]=user_get_user_name($feedvalue[olduid]);
			$feedidarr[]=$feedvalue[feedid];
			$feedvalue=mkfeed($feedvalue);
			if(!$feedvalue['title_data']){
				$feedvalue['title_data']=null;
			}
			if(!$feedvalue['body_data']){
				$feedvalue['body_data']=null;
			}
			if($feedvalue['icon']=='doc'){
				$feedvalue['docarr']=getFile($feedvalue[id]);
				$feedvalue['docarr']['context']=cutstr($feedvalue['docarr']['context'],150);
			}
			if($feedvalue['icon']=='share' && $feedvalue['idtype']=='video'){
				if(strripos($feedvalue['body_data']['data'],'/data/attachment/flv')){
					$feedvalue['body_data']['data']=$_G[config][media][url].substr($feedvalue['body_data']['data'],strripos($feedvalue['body_data']['data'],'/data/attachment/flv'));
					$feedvalue['body_data']['flashvar']=IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($feedvalue['body_data']['data']);
				}
			
			}
			//$feedvalue[body_data][subject]=parseat($feedvalue[body_data][subject]);
			//$feedvalue[body_data][summary]=parseat($feedvalue[body_data][summary]);
			//$feedvalue[title_data][message]=parseat($feedvalue[title_data][message]);
			//$feedvalue[body_general]=parseat($feedvalue[body_general]);
			$feedarray[$feedvalue[feedid]]=$feedvalue;
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
					if(($feedarray[$newid][icon]=='share'||$feedarray[$newid][icon]=='album')&&$feedarray[$newid][idtype]=='feed'){
						$feedarray[$newid]['oldbody_general']=$value['body_general'];
					}
					$feedarray[$newid]['oldusername']=user_get_user_name($feedarray[$newid]['olduid']);
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$feedarray[$newid]['oldusername']='匿名';
							$feedarray[$newid]['oldanonymity']='-1';
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$feedarray[$newid]['oldusername']=$repeatsinfo[name];
							$feedarray[$newid]['oldanonymity']=$repeatsinfo['fid'];
						}
					}
					$oldfeedidarr=array_diff_assoc($oldfeedidarr,$oldfeedidarr2);
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


echo json_encode($res);
?>