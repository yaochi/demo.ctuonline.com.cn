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
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];
$type=empty($_G['gp_type'])?'user':$_G['gp_type'];
$typeid=$_G['gp_typeid'];
$idtype=$_G['gp_idtype'];
$startdatetime=$_G['gp_startdatetime'];
$enddatetime=$_G['gp_enddatetime'];
$feedid=$_G['gp_feedid'];

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
			$wheresql['uid'] = "uid IN (0,".implode(',',$fuids).")";
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
	$wheresql['fid'] = "( fid=0  OR fid IN (select  f1.fid FROM ".DB::table("forum_forumfield")." as  f1 LEFT JOIN " . DB::table('forum_forum') . " as f ON f.fid=f1.fid , ".DB::table("forum_forumfield")." as f2 WHERE f.status=3 AND f1.gviewperm=1 ) )";
	$wheresql['anonymity']=" anonymity=0 ";
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
	if($typeid=='self'){
		$wheresql['anonymity']=" anonymity<=0 ";
	}else{
		$wheresql['anonymity']=" anonymity=0 ";
	}
	if($feedid){
		$wheresql['feedid']=" feedid=".$feedid;
	}
}elseif($type=='all'){
	$wheresql['all']=' 1=1 ';
	$wheresql['fid'] = "( fid=0  OR fid IN (select  f1.fid FROM ".DB::table("forum_forumfield")." as  f1 LEFT JOIN " . DB::table('forum_forum') . " as f ON f.fid=f1.fid , ".DB::table("forum_forumfield")." as f2 WHERE f.status=3 AND f1.gviewperm=1 ) )";
}
if($idtype){
	$idtype=explode(',',$idtype);
	$wheresql['idtype']=' 1=1 ';
	for($i=0;$i<count($idtype);$i++){
		if($idtype[$i]=='blogid'){
			$wheresql['idtype']=$wheresql['idtype']." and ( icon='blog' ";
		}
		if($idtype[$i]=='link'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='share' and title_template like '%网址%'  ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='share' and title_template like '%网址%'  ";
			}
		}
		if($idtype[$i]=='albumid'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='album' ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='album' ";
			}
		}
		if($idtype[$i]=='flash'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='share' and (title_template like '%视频%' or title_template like '%flash%') ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='share' and (title_template like '%视频%' or title_template like '%flash%')  ";
			}
		}
		if($idtype[$i]=='music'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='share' and title_template like '%音乐%'  ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='share' and title_template like '%音乐%'  ";
			}
		}
		if($idtype[$i]=='class'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='class'  ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='class'  ";
			}
		}
		if($idtype[$i]=='doc'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and (icon='doc' ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or icon='doc' ";
			}
		}
		if($idtype[$i]=='case'){
			if($i==0){
				$wheresql['idtype']=$wheresql['idtype']." and ( icon='case'  ";
			}else{
				$wheresql['idtype']=$wheresql['idtype']." or  icon='case'  ";
			}
		}
	}
	$wheresql['idtype']=$wheresql['idtype']." ) ";
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
	$query=DB::query("SELECT * FROM ".DB::table('home_feed')." WHERE ".implode(' AND ', $wheresql)." and icon!='comment' and idtype!='picid' and idtype!='gpicid' and idtype!='galbumid' and uid!='0' ORDER BY dateline DESC LIMIT $num,$shownum");
		while($value=DB::fetch($query)){
			if($value[fid]){
				$value[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$value[fid]);
			}else{
				$value[gviewperm]=1;
			}
			if(($value[icon]=='thread' || $value[icon]=='poll' || $value[icon]=='reward')&&$value[idtype]!='feed'){
				$commentquery=DB::query("select * from ".DB::TABLE("forum_post")." where tid =".$value[id]." and first!='1' order by dateline desc limit 0,2");
			}elseif($value[icon]=='resourcelist'){
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='docid' and id='".$value[id]."' order by dateline desc limit 0,2");
			}else{
				$commentquery=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id='".$value[feedid]."' order by dateline desc limit 0,2");
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
						$newcommentvalue[realuid]=$commentvalue[authorid];
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
				$value[comment][$newcommentvalue[cid]]=$newcommentvalue;
				ksort($value[comment]);
			}
			if($value[image_1]){
				if(strrpos($value[image_1],'http://')===0||strrpos($value[image_1],'http://')){
				}else{
					$value[image_1]=$_G[config][image][url].'/'.$value[image_1];
					$imageurlarr[image_1]=explode('.thumb.',$value[image_1]);
					if(count($imageurlarr[image_1])>1){
						if(count(explode(".jpg",$imageurlarr[image_1][0]))>1){
							$value[image_1]=$imageurlarr[image_1][0];
						}else{
							$value[image_1]=$imageurlarr[image_1][0].'.'.$imageurlarr[image_1][1];
						}
					}
				}
				$value[imagearr][]=$value[image_1];
				$value[imageurlarr][]=$value[image_1_link];
			}
			if($value[image_2]){
				if(strrpos($value[image_2],'http://')===0||strrpos($value[image_2],'http://')){
				}else{
					$value[image_2]=$_G[config][image][url].'/'.$value[image_2];
					$imageurlarr[image_2]=explode('.thumb.',$value[image_2]);
					if(count($imageurlarr[image_2])>1){
						if(count(explode(".jpg",$imageurlarr[image_2][0]))>1){
							$value[image_2]=$imageurlarr[image_2][0];
						}else{
							$value[image_2]=$imageurlarr[image_2][0].'.'.$imageurlarr[image_2][1];
						}
					}
				}
				$value[imagearr][]=$value[image_2];
				$value[imageurlarr][]=$value[image_2_link];
			}
			if($value[image_3]){
				if(strrpos($value[image_3],'http://')===0||strrpos($value[image_3],'http://')){
				}else{
					$value[image_3]=$_G[config][image][url].'/'.$value[image_3];
					$imageurlarr[image_3]=explode('.thumb.',$value[image_3]);
					if(count($imageurlarr[image_3])>1){
						if(count(explode(".jpg",$imageurlarr[image_3][0]))>1){
							$value[image_3]=$imageurlarr[image_3][0];
						}else{
							$value[image_3]=$imageurlarr[image_3][0].'.'.$imageurlarr[image_3][1];
						}
					}
				}
				$value[imagearr][]=$value[image_3];
				$value[imageurlarr][]=$value[image_3_link];
			}
			if($value[image_4]){
				if(strrpos($value[image_4],'http://')===0||strrpos($value[image_4],'http://')){
				}else{
					$value[image_4]=$_G[config][image][url].'/'.$value[image_4];
					$imageurlarr[image_4]=explode('.thumb.',$value[image_4]);
					if(count($imageurlarr[image_4])>1){
						if(count(explode(".jpg",$imageurlarr[image_4][0]))>1){
							$value[image_4]=$imageurlarr[image_4][0];
						}else{
							$value[image_4]=$imageurlarr[image_4][0].'.'.$imageurlarr[image_4][1];
						}
					}
				}
				$value[imagearr][]=$value[image_4];
				$value[imageurlarr][]=$value[image_4_link];
			}
			if($value[image_5]){
				if(strrpos($value[image_5],'http://')===0||strrpos($value[image_5],'http://')){
				}else{
					$value[image_5]=$_G[config][image][url].'/'.$value[image_5];
					$imageurlarr[image_5]=explode('.thumb.',$value[image_5]);
					if(count($imageurlarr[image_5])>1){
						if(count(explode(".jpg",$imageurlarr[image_5][0]))>1){
							$value[image_5]=$imageurlarr[image_5][0];
						}else{
							$value[image_5]=$imageurlarr[image_5][0].'.'.$imageurlarr[image_5][1];
						}
					}
				}
				$value[imagearr][]=$value[image_5];
				$value[imageurlarr][]=$value[image_5_link];
			}
			if($value['idtype']=='feed'){
				$oldfeedidarr[$value[feedid]]=$value[id];
			}
			if($value[anonymity]){
				if($value[anonymity]==-1){
					$value[realuid]=$value[uid];
					$value[uid]=-1;
					$value[username]='匿名';
				}else{
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($value[anonymity]);
					$value[uid]=$repeatsinfo[fid];
					$value[username]=$repeatsinfo[name];
					if($repeatsinfo['icon']){
						$value['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
					}else{
						$value['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
					}
				}
			}else{
				$value[userimgurl]=useravatar($value[uid]);
				$value[username]=user_get_user_name($value[uid]);
			}
			//$value[oldusername]=user_get_user_name($value[olduid]);
			$feedidarr[]=$value[feedid];
			$value=mkfeed($value);
			if(!$value['title_data']){
				$value['title_data']=null;
			}
			if(!$value['body_data']){
				$value['body_data']=null;
			}
			if($value['icon']=='doc'){
				///print_r($value['docarr']);
				if($value['idtype']=='docid'){
					$value['docarr']=getFile($value[id]);
					$value['docarr']['context']=cutstr($value['docarr']['context'],150);
				}else{
					//echo(substr($value[hash_data],5));
					$value['docarr']=getFile(substr($value[hash_data],5));
					$value['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
					$value['body_data'] = array(
						'subject' => "<a href=\"".$value[docarr][titlelink]."\">".$value[docarr][title]."</a>",
						'author' => "未知",
						'message' => cutstr($value['docarr']['context'],150)
					);
					$value[image_1]=$value['docarr']['imglink'];
					$value[image_1_link]=$value['docarr']['titlelink'];
				}
			}
			if($value['icon']=='share' && $value['idtype']=='video'){
				if(strripos($value['body_data']['data'],'/data/attachment/flv')){
					$value['body_data']['data']=$_G[config][media][url].substr($value['body_data']['data'],strripos($value['body_data']['data'],'/data/attachment/flv'));
					$value['body_data']['flashvar']=IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($value['body_data']['data']);
				}
			
			}
		//	$value[body_data][subject]=parseat($value[body_data][subject]);
			//$value[body_data][summary]=parseat($value[body_data][summary]);
		//	$value[title_data][message]=parseat($value[title_data][message]);
		//	$value[body_general]=parseat($value[body_general]);
			$feedarray[$value[feedid]]=$value;
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
				$oldfeedidarr=array_diff_assoc($oldfeedidarr,$oldfeedidarr2);
				if(($feedarray[$newid][icon]=='share'||$feedarray[$newid][icon]=='album')&&$feedarray[$newid][idtype]=='feed'){
					$feedarray[$newid]['oldbody_general']=$value['body_general'];
				}
				$feedarray[$newid]['olduid']=$value['uid'];
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
				$feedarray[$newid]['oldid']=$value['id'];
				$feedarray[$newid]['oldfid']=$value['fid'];
				$feedarray[$newid]['oldcommenttimes']=$value[commenttimes];
				$feedarray[$newid]['oldsharetimes']=$value[sharetimes];
				$feedarray[$newid]['oldfromwhere']=$value[fromwhere];
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
//print_r($res);
echo json_encode($res);
?>