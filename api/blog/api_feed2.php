<?php
/* Function: 根据检索参数，获得相关动态
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_doc.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$feedid=$_G["gp_feedid"];
$pagetype=$_G["gp_pagetype"];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
$type=empty($_G['gp_type'])?'user':$_G['gp_type'];
$typeid=$_G['gp_typeid'];
$idtype=$_G['gp_idtype'];
$startdatetime=$_G['gp_startdatetime'];
$enddatetime=$_G['gp_enddatetime'];

$need_count=true;

if($type=='follow'){
	if($typeid==='0'||$typeid){
		if($typeid=='-1'){
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where (type=1 or type=3) and uid=".$uid." and gids=''");
		}elseif($typeid=='-2'){
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where type=3 and uid=".$uid);
		}else{
			$query=DB::query("select fuid from ".DB::TABLE("home_friend")." where (type=1 or type=3) and uid=".$uid." and gids like '%,".$typeid.",%'");
		}
		while($value=DB::fetch($query)){
			$fuids[]=$value[fuid];
		}
		if(count($fuids)){
			$wheresql['uid'] = "uid IN ('0',".implode(',',$fuids).")";
		}else{
			$need_count=false;
		}
	}else{
		$result[uid]=$uid;
		space_merge($result, 'field_home');
		if($result[feedfollow]){
			$wheresql['uid'] = "uid IN ($uid,$result[feedfollow])";
		}else{
			$wheresql['uid'] = "uid IN ($uid)";
		}
	}
}elseif($type=='group'){
	if($typeid){
		$wheresql["fid"] = "( fid IN (".$typeid.") or sharetofids like '%,".$typeid.",%' )";
	}else{
		$fids = join_groups($uid);
		if($fids){
			$wheresql["fid"] = "( fid IN (".dimplode($fids).")";
			for($i=0;$i<count($fids);$i++){
				$wheresql["fid"]=$wheresql["fid"]." or sharetofids like '%,".$fids[$i].",%'";
			}
			$wheresql["fid"]=$wheresql["fid"]." )";
		}else{
			$need_count=false;
		}
	}
}elseif($type=='user'){
	$wheresql['uid'] = "uid =".$uid;
}elseif($type=='all'){
	$wheresql['all']=' 1=1 ';
}

if($feedid){
	if($pagetype=="up"){
		$wheresql['feedid']=" feedid<".$feedid;
	}elseif($pagetype=="down"){
		$wheresql['feedid']=" feedid>".$feedid;
	}
}else{
	$pagetype="up";
}
if($idtype){
	$wheresql['idtype']="idtype in ('".implode('\',\'',$idtype)."')";
}
if($startdatetime){
	$wheresql['startdatetime']="dateline >='".$startdatetime."'";
}
if($enddatetime){
	$wheresql['enddatetime']="dateline<'".$enddatetime."'";
}
if(!$wheresql){
	$wheresql['none']=' 1=0 ';
}
if($need_count){
	$ordersql=updownsql($pagetype,'home_feed','feedid',$feedid,$shownum,'feedid',implode(' AND ', $wheresql)." and (idtype!='flash' and idtype!='music' and idtype!='video' and icon!='comment' and icon!='forward' and icon!='sitefeed' and icon!='profile' and icon!='click' and title_template!='{actor} 转发了一个视频' and title_template!='{actor} 转发了一个音乐')");
	$res['refresh']=$ordersql['refresh'];
	$query=DB::query("SELECT * FROM ".DB::table('home_feed')." USE INDEX(feedid) WHERE ".implode(' AND ', $wheresql)." and (idtype!='flash' and idtype!='music' and idtype!='video' and icon!='comment' and icon!='forward' and icon!='sitefeed' and icon!='profile' and icon!='click' and title_template!='{actor} 转发了一个视频' and title_template!='{actor} 转发了一个音乐')".$ordersql['ordersql']);
		while($value=DB::fetch($query)){
			if($value[fid]){
				$value[forum][fid]=$value[fid];
				$value[forum][fname]=DB::result_first("select name from ".DB::TABLE("forum_forum")." where fid=".$value[fid]);
				$value[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$value[fid]);
			}else{
				$value[gviewperm]=1;
			}
			if($value[icon]=='thread' || $value[icon]=='poll' || $value[icon]=='reward'){
				$commentquery=DB::query("select *,anonymous as anonimity from ".DB::TABLE("forum_post")." where tid =".$value[id]." and first!='1' order by dateline desc limit 0,2");
			}elseif($value[icon]=='resourcelist'){
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id='".$value[id]."' order by dateline desc limit 0,2");
			}else{
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id='".$value[feedid]."' order by dateline desc limit 0,2");
			}
			while($commentvalue=DB::fetch($commentquery)){
				$cvalue=array();
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
				$cvalue[cid]=$newcommentvalue[cid];
				$value[comments][]=$cvalue;
			}

			if($value[image_1]){
				if(strrpos($value[image_1],'http://')===0||strrpos($value[image_1],'http://')){
				}else{
					$value[image_1]=$_G[config][image][url].'/'.$value[image_1];
				}
				$pic[pictureUrl]=$value[image_1];
				$value[pics][]=$pic;
			}
			if($value[image_2]){
				if(strrpos($value[image_2],'http://')===0||strrpos($value[image_2],'http://')){
				}else{
					$value[image_2]=$_G[config][image][url].'/'.$value[image_2];
				}
				$pic[pictureUrl]=$value[image_2];
				$value[pics][]=$pic;
			}
			if($value[image_3]){
				if(strrpos($value[image_3],'http://')===0||strrpos($value[image_3],'http://')){
				}else{
					$value[image_3]=$_G[config][image][url].'/'.$value[image_3];
				}
				$pic[pictureUrl]=$value[image_3];
				$value[pics][]=$pic;
			}
			if($value[image_4]){
				if(strrpos($value[image_4],'http://')===0||strrpos($value[image_4],'http://')){
				}else{
					$value[image_4]=$_G[config][image][url].'/'.$value[image_4];
				}
				$pic[pictureUrl]=$value[image_4];
				$value[pics][]=$pic;
			}
			if($value[image_5]){
				if(strrpos($value[image_5],'http://')===0||strrpos($value[image_5],'http://')){
				}else{
					$value[image_5]=$_G[config][image][url].'/'.$value[image_5];
				}
				$pic[pictureUrl]=$value[image_5];
				$value[pics][]=$pic;
			}
			if($value['idtype']=='feed'){
				$oldfeedidarr[$value[feedid]]=$value[id];
				$value[isOriginal]=2;
			}else{
				$value[isOriginal]=1;
			}
			if($value[anonymity]){
				if($value[anonymity]==-1){
					$value[user][uid]=-1;
					$value[user][username]='匿名';
					$value[user][iconImg]=useravatar(-1);
				}else{
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($value[anonymity]);
					$value[user][uid]=$repeatsinfo[fid];
					$value[user][username]=$repeatsinfo[name];
					if($repeatsinfo['icon']){
						$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
					}else{
						$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
					}
					$value[user][iconImg]=$value['ficon'];
				}
			}else{
				$value[user][uid]=$value[uid];
				$value[user][iconImg]=useravatar($value[uid]);
				$value[user][username]=user_get_user_name($value[uid]);
			}
			$feedidarr[]=$value[feedid];
			$value=mkfeed($value);
			$value[content]=$value[body_data][summary];
			$value[createDate]=$value[dateline];
			$value[forwardCount]=$value[sharetimes];
			$value[commentCount]=$value[commenttimes];
			$value[videoUrl]="";
			$value[soundUrl]="";
			if(!$value['title_data']){
				$value['title_data']=null;
			}
			if(!$value['body_data']){
				$value['body_data']=array("message"=>"");
			}
			if($value['icon']=='album'||$value['icon']=='share'){
				$value[content]=$value[body_general];
			}
			if($value['icon']=='thread'||$value['icon']=='notice'||$value['icon']=='poll'||$value['icon']=='activitys'){
				$value[content]=$value[body_data][message];
			}
			if($value['icon']=='resourcelist'){
				$value[content]=$value[title_data][resourcetitle];
			}
			if($value['icon']=='reward'||$value['icon']=='live'){
				$value[content]='';
			}
			if($value['icon']=='profile'){
				$value[content]=$value[title_template];
			}

			if($value['icon']=='doc'){
				$value['docarr']=getFile($value[id]);
				$value['docarr']['context']=cutstr($value['docarr']['context'],150);
			}
			if( $value['idtype']=='flash'||$value['idtype']=='video'){
				$value[videoUrl]=$value[body_data][data];

			}
			if($value['icon']=='share'&& $value['idtype']=='music'){
				$value[soundUrl]=$value[body_data][data];
			}
			if($value['idtype']=='feed'){
				$value[content]=$value[body_general];
			}
			$value[content]=htmlspecialchars_decode($value[content]);
			$feedarray[$value[feedid]]=$value;

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
				$oldfeedidarr=array_diff_assoc($oldfeedidarr,$oldfeedidarr2);
				if(($feedarray[$newid][icon]=='share'||$feedarray[$newid][icon]=='album')&&$feedarray[$newid][idtype]=='feed'){
					$feedarray[$newid]['oldbody_general']=$value['body_general'];
				}
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
				$feedarray[$newid][originalFeed][videoUrl]="";
				$feedarray[$newid][originalFeed][soundUrl]="";
				$feedarray[$newid][originalFeed][body_data]=$value[body_data];

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
	$res[feeds]=$newfeedarray;
}else{
	$res[feeds]=null;
}

echo json_encode($res);
?>