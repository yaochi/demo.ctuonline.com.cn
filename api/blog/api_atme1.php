<?php
/* Function: @提到我的
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
$feedid=$_G["gp_feedid"];
$pagetype=$_G["gp_pagetype"];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($feedid){
	if($pagetype=="up"){
		$wheresql=" and feedid<".$feedid;
	}elseif($pagetype=="down"){
		$wheresql=" and feedid>".$feedid;
	}
}else{
	$pagetype="up";
}
if($uid){
	$value=DB::fetch_first("select * from ".DB::TABLE("common_user_at")." where uid=".$uid);
	if($value){
		$ordersql=updownsql($pagetype,'home_feed','feedid',$feedid,$shownum,'feedid',"feedid in (".$value[feedids].") and icon!='comment' and idtype!='flash' and idtype!='music' and idtype!='video' and icon!='profile'  and icon!='click' and title_template!='{actor} 转发了一个视频' and title_template!='{actor} 转发了一个音乐'");
		$feedquery=DB::query("select * from ".DB::TABLE("home_feed")." where feedid in (".$value[feedids].")".$wheresql." and icon!='comment' and idtype!='flash' and idtype!='music' and idtype!='video' and icon!='profile'  and icon!='click' and title_template!='{actor} 转发了一个视频' and title_template!='{actor} 转发了一个音乐'".$ordersql['ordersql']);
		while($feedvalue=DB::fetch($feedquery)){
			$feedvalue=mkfeed($feedvalue);
			$nfeed=Array();
			$nfeed[feedid]=$feedvalue[feedid];
			$nfeed[icon]=$feedvalue[icon];
			$nfeed[content]=$feedvalue[body_data][summary];
			$nfeed[createDate]=$feedvalue[dateline];
			$nfeed[forwardCount]=$feedvalue[sharetimes];
			$nfeed[commentCount]=$feedvalue[commenttimes];
			if($feedvalue[fid]){
				$nfeed[forum][fid]=$feedvalue[fid];
				$nfeed[forum][fname]=DB::result_first("select name from ".DB::TABLE("forum_forum")." where fid=".$feedvalue[fid]);
			}else{
			}
			if($feedvalue[icon]=='thread' || $feedvalue[icon]=='poll' || $feedvalue[icon]=='reward'){
				$commentquery=DB::query("select *,anonymous as anonymity from ".DB::TABLE("forum_post")." where tid =".$feedvalue[id]." and first!='1' order by dateline desc limit 0,2");
			}elseif($value[icon]=='resourcelist'){
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id='".$feedvalue[id]."' order by dateline desc limit 0,2");
			}else{
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id='".$feedvalue[feedid]."' order by dateline desc limit 0,2");
			}
			while($commentvalue=DB::fetch($commentquery)){
				$newcommentvalue[anonymity]=$commentvalue[anonymity];
				$newcommentvalue[cid]=empty($commentvalue[pid])?$commentvalue[cid]:$commentvalue[pid];
				$newcommentvalue[id]=empty($commentvalue[tid])?$commentvalue[id]:$commentvalue[tid];
				$newcommentvalue[dateline]=$commentvalue[dateline];
				$newcommentvalue[ip]=empty($commentvalue[useip])?$commentvalue[ip]:$commentvalue[useip];
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
						$cvalue[user][uid]=-1;
						$cvalue[user][username]='匿名';
						$cvalue[user][iconImg]=useravatar(-1);
					}else{
						include_once libfile('function/repeats','plugin/repeats');
						$repeatsinfo=getforuminfo($commentvalue[anonymity]);
						if($repeatsinfo['icon']){
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
						}else{
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
						}
						$cvalue[user][uid]=$repeatsinfo[fid];
						$cvalue[user][username]=$repeatsinfo[name];
						$cvalue[user][iconImg]=$newcommentvalue['ficon'];
					}
				}else{
					$cvalue[user][uid]=$commentvalue[authorid];
					$cvalue[user][username]=user_get_user_name($cvalue[user][uid]);
					$cvalue[user][iconImg]=useravatar($cvalue[user][uid]);
				}
				$cvalue[commentDate]=$commentvalue[dateline];
				$cvalue[commentContent]=htmlspecialchars_decode($newcommentvalue[message]);
				$nfeed[comments][]=$cvalue;
			}
			if($feedvalue[image_1]){
				if(strrpos($feedvalue[image_1],'http://')===0||strrpos($feedvalue[image_1],'http://')){
				}else{
					$feedvalue[image_1]=$_G[config][image][url].'/'.$feedvalue[image_1];
				}
				$pic[pictureUrl]=$feedvalue[image_1];
				$nfeed[pics][]=$pic;
			}
			if($feedvalue[image_2]){
				if(strrpos($feedvalue[image_2],'http://')===0||strrpos($feedvalue[image_2],'http://')){
				}else{
					$feedvalue[image_2]=$_G[config][image][url].'/'.$feedvalue[image_2];
				}
				$pic[pictureUrl]=$feedvalue[image_2];
				$nfeed[pics][]=$pic;
			}
			if($feedvalue[image_3]){
				if(strrpos($feedvalue[image_3],'http://')===0||strrpos($feedvalue[image_3],'http://')){
				}else{
					$feedvalue[image_3]=$_G[config][image][url].'/'.$feedvalue[image_3];
				}
				$pic[pictureUrl]=$feedvalue[image_3];
				$nfeed[pics][]=$pic;
			}
			if($feedvalue[image_4]){
				if(strrpos($feedvalue[image_4],'http://')===0||strrpos($feedvalue[image_4],'http://')){
				}else{
					$feedvalue[image_4]=$_G[config][image][url].'/'.$feedvalue[image_4];
				}
				$pic[pictureUrl]=$feedvalue[image_4];
				$nfeed[pics][]=$pic;
			}
			if($feedvalue[image_5]){
				if(strrpos($feedvalue[image_5],'http://')===0||strrpos($feedvalue[image_5],'http://')){
				}else{
					$feedvalue[image_5]=$_G[config][image][url].'/'.$feedvalue[image_5];
				}
				$pic[pictureUrl]=$feedvalue[image_5];
				$nfeed[pics][]=$pic;
			}
			if($feedvalue['idtype']=='feed'){
				$oldfeedidarr[$feedvalue[feedid]]=$feedvalue[id];
				$nfeed[isOriginal]=2;
			}else{
				$nfeed[isOriginal]=1;
			}
			if($feedvalue[anonymity]){
				if($feedvalue[anonymity]==-1){
					$nfeed[user][uid]=-1;
					$nfeed[user][username]='匿名';
					$nfeed[user][iconImg]=useravatar(-1);
				}else{
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($feedvalue[anonymity]);
					$nfeed[user][uid]=$repeatsinfo[fid];
					$nfeed[user][username]=$repeatsinfo[name];
					if($repeatsinfo['icon']){
						$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
					}else{
						$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
					}
					$nfeed[user][iconImg]=$value['ficon'];
				}
			}else{
				$nfeed[user][uid]=$feedvalue[uid];
				$nfeed[user][iconImg]=useravatar($feedvalue[uid]);
				$nfeed[user][username]=user_get_user_name($feedvalue[uid]);
			}
			$feedidarr[]=$feedvalue[feedid];
			if(!$feedvalue['title_data']){
				$nfeed['title_data']=null;
			}
			if(!$feedvalue['body_data']){
				$nfeed['body_data']=array("message"=>"");;
			}
			if($feedvalue['icon']=='doc'){
				$nfeed['docarr']=getFile($feedvalue[id]);
				$nfeed['docarr']['context']=cutstr($feedvalue['docarr']['context'],150);
			}
			if($feedvalue['icon']=='album'||$feedvalue['icon']=='share'){
				$nfeed[content]=$feedvalue[body_general];
			}
			if($feedvalue['icon']=='thread'||$feedvalue['icon']=='notice'||$feedvalue['icon']=='poll'||$feedvalue['icon']=='activitys'){
				$nfeed[content]=$feedvalue[body_data][message];
			}
			if($feedvalue['icon']=='resourcelist'){
				$nfeed[content]=$feedvalue[title_data][resourcetitle];
			}
			if($feedvalue['icon']=='reward'||$feedvalue['icon']=='live'){
				$nfeed[content]='';
			}
			if($feedvalue['icon']=='profile'){
				$nfeed[content]=$feedvalue[title_template];
			}
			if( $feedvalue['idtype']=='flash'||$feedvalue['idtype']=='video'){
				$nfeed[videoUrl]=$feedvalue[body_data][data];
			}
			if($feedvalue['icon']=='share'&& $feedvalue['idtype']=='music'){
				$nfeed[soundUrl]=$feedvalue[body_data][data];
			}
			if($feedvalue['idtype']=='feed'){
				$nfeed[content]=$feedvalue[body_general];
			}
			$nfeed[content]=htmlspecialchars_decode($nfeed[content]);
			$feedarray[$feedvalue[feedid]]=$nfeed;

		}
		if($feedidarr&&$uid){
			$query=DB::query("select feedid from ".DB::TABLE("home_favorite")." where uid=".$uid." and feedid in (".implode(',',$feedidarr).")");
			while($value=DB::fetch($query)){
				$feedarray[$value[feedid]][isfavorite]="1";
			}
		}
		if($oldfeedidarr){
			$query=DB::query("select * from ".DB::TABLE("home_feed")." where feedid in (".implode(',',$oldfeedidarr).")");
			while($value=DB::fetch($query)){
				$value=mkfeed($value);
				if(!$value['title_data']){
					$value['title_data']=null;
				}
				if(!$value['body_data']){
					$value['body_data']=array("message"=>"");;
				}
				while(in_array($value[feedid],$oldfeedidarr)){
					$newid=array_search($value[feedid],$oldfeedidarr);
					$oldfeedidarr2[$newid]=$value[feedid];
					if(($feedarray[$newid][icon]=='share'||$feedarray[$newid][icon]=='album')&&$feedarray[$newid][idtype]=='feed'){
						$feedarray[$newid]['oldbody_general']=$value['body_general'];
					}
					$oldfeedidarr=array_diff_assoc($oldfeedidarr,$oldfeedidarr2);
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$feedarray[$newid][originalFeed][user][uid]=-1;
							$feedarray[$newid][originalFeed][user][username]='匿名';
							$feedarray[$newid][originalFeed][user][iconImg]=useravatar(-1);
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$feedarray[$newid][originalFeed][user][uid]=$repeatsinfo[fid];
							$feedarray[$newid][originalFeed][user][username]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
							$feedarray[$newid][originalFeed][user][iconImg]=$value['ficon'];
						}
					}else{
						$feedarray[$newid][originalFeed][user][uid]=$value[uid];
						$feedarray[$newid][originalFeed][user][iconImg]=useravatar($value[uid]);
						$feedarray[$newid][originalFeed][user][username]=user_get_user_name($value[uid]);
					}
					$feedarray[$newid][originalFeed][feedid]=$value[feedid];
					$feedarray[$newid][originalFeed][icon]=$value[icon];
					$feedarray[$newid][originalFeed][content]=$value[body_data][summary];
					$feedarray[$newid][originalFeed][createDate]=$value[dateline];
					$feedarray[$newid][originalFeed][forwardCount]=$value[sharetimes];
					$feedarray[$newid][originalFeed][commentCount]=$value[commenttimes];
					if($value['idtype']=='flash'||$value['idtype']=='video'){
						$feedarray[$newid][originalFeed][videoUrl]=$value[body_data][data];
					}
					if($value['icon']=='share'&& $value['idtype']=='music'){
						$feedarray[$newid][originalFeed][soundUrl]=$value[body_data][data];
					}
					if($value['icon']=='share'||$value['icon']=='album'){
						$feedarray[$newid][originalFeed][content]=$value[body_general];
					}
					if($value['icon']=='thread'||$value['icon']=='notice'||$value['icon']=='poll'||$value['icon']=='activitys'){
						$feedarray[$newid][originalFeed][content]=$value[body_data][message];
					}
					if($value['icon']=='resourcelist'){
						$feedarray[$newid][originalFeed][content]=$value[title_data][resourcetitle];
					}
					if($value['icon']=='reward'||$value['icon']=='live'){
						$feedarray[$newid][originalFeed][content]='';
					}
					if($value['icon']=='profile'){
						$feedarray[$newid][originalFeed][content]=$value[title_template];
					}
					$feedarray[$newid][originalFeed][content]=htmlspecialchars_decode($feedarray[$newid][originalFeed][content]);
					if($value[fid]){
						$feedarray[$newid][originalFeed][forum][fid]=$value[fid];
						$feedarray[$newid][originalFeed][forum][fname]=DB::result_first("select name from ".DB::TABLE("forum_forum")." where fid=".$value[fid]);
					}
					if($value[image_1]){
						if(strrpos($value[image_1],'http://')===0||strrpos($value[image_1],'http://')){
						}else{
							$value[image_1]=$_G[config][image][url].'/'.$value[image_1];
						}
						$pics[pictureUrl]=$value[image_1];
//						$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
//						$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
//						$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
						$feedarray[$newid][originalFeed][pics][]=$pics;
					}
					if($value[image_2]){
						if(strrpos($value[image_2],'http://')===0||strrpos($value[image_2],'http://')){
						}else{
							$value[image_2]=$_G[config][image][url].'/'.$value[image_2];
						}
						$pics[pictureUrl]=$value[image_2];
						$feedarray[$newid][originalFeed][pics][]=$pics;
					}
					if($value[image_3]){
						if(strrpos($value[image_3],'http://')===0||strrpos($value[image_3],'http://')){
						}else{
							$value[image_3]=$_G[config][image][url].'/'.$value[image_3];
						}
						$pics[pictureUrl]=$value[image_3];
						$feedarray[$newid][originalFeed][pics][]=$pics;
					}
					if($value[image_4]){
						if(strrpos($value[image_4],'http://')===0||strrpos($value[image_4],'http://')){
						}else{
							$value[image_4]=$_G[config][image][url].'/'.$value[image_4];
						}
						$pics[pictureUrl]=$value[image_4];
						$feedarray[$newid][originalFeed][pics][]=$pics;
					}
					if($value[image_5]){
						if(strrpos($value[image_5],'http://')===0||strrpos($value[image_5],'http://')){
						}else{
							$value[image_5]=$_G[config][image][url].'/'.$value[image_5];
						}
						$pics[pictureUrl]=$value[image_5];
						$feedarray[$newid][originalFeed][pics][]=$pics;
					}
				}
			}
		}
		for($i=0;$i<count($feedarray);$i++){
			$newfeedarray[]=$feedarray[$feedidarr[$i]];
		}
		$res['refresh']=$ordersql['refresh'];
		$res[feeds]=$newfeedarray;
	}else{
		$res['refresh']='0';
		$res['feeds']=null;
	}
	if($pagetype=='down'){
		DB::query("update ".DB::table('home_notification')." set new=0 where uid=".$uid." and new=1 and type='zq_at'");
	}
}

echo json_encode($res);
?>