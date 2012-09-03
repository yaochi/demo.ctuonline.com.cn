<?php
/* Function: 根据检索参数，获取日志的详情和日志的评论
 * Com.:
 * Author: yangyang
 * Date: 2011-10-26
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$feedid=$_G['gp_feedid'];
$siteurl=substr($_G['siteurl'],0,strripos($_G['siteurl'],'api/blog/'));
if($feedid){
	$feed=DB::fetch_first("SELECT *  FROM ".DB::table('home_feed')." WHERE feedid =".$feedid);
	if($feed[image_1]){
		if(strrpos($feed[image_1],'http://')===0||strrpos($feed[image_1],'http://')){
		}else{
			$feed[image_1]=$_G[config][image][url].'/'.$feed[image_1];
		}
		$pics[pictureUrl]=$feed[image_1];
//		$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
//		$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
//		$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
		$feed[pics][]=$pics;
	}
	if($feed[image_2]){
		if(strrpos($feed[image_2],'http://')===0||strrpos($feed[image_2],'http://')){
		}else{
			$feed[image_2]=$_G[config][image][url].'/'.$feed[image_2];
		}
		$pics[pictureUrl]=$feed[image_2];
		$feed[pics][]=$pics;
	}
	if($feed[image_3]){
		if(strrpos($feed[image_3],'http://')===0||strrpos($feed[image_3],'http://')){
		}else{
			$feed[image_3]=$_G[config][image][url].'/'.$feed[image_3];
		}
		$pics[pictureUrl]=$feed[image_3];
		$feed[pics][]=$pics;
	}
	if($feed[image_4]){
		if(strrpos($feed[image_4],'http://')===0||strrpos($feed[image_4],'http://')){
		}else{
			$feed[image_4]=$_G[config][image][url].'/'.$feed[image_4];
		}
		$pics[pictureUrl]=$feed[image_4];
		$feed[pics][]=$pics;
	}
	if($feed[image_5]){
		if(strrpos($feed[image_5],'http://')===0||strrpos($feed[image_5],'http://')){
		}else{
			$feed[image_5]=$_G[config][image][url].'/'.$feed[image_5];
		}
		$pics[pictureUrl]=$feed[image_5];
		$feed[pics][]=$pics;
	}
	$feed=mkfeed($feed);
	if($feed[fid]){
		$feed[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$feed[fid]);
	}else{
		$feed[gviewperm]=1;
	}
	if($feed[anonymity]){
		if($feed[anonymity]==-1){
			$feed[user][uid]=-1;
			$feed[user][username]='匿名';
			$feed[user][iconImg]=useravatar(-1);
		}else{
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($feed[anonymity]);
			$feed[user][uid]=$repeatsinfo[fid];
			$feed[user][username]=$repeatsinfo[name];
			if($repeatsinfo['icon']){
				$feed['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
			}else{
				$feed['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
			}
			$feed[user][iconImg]=$feed['ficon'];
		}
	}else{
		$feed[user][uid]=$feed[uid];
		$feed[user][iconImg]=useravatar($feed[uid]);
		$feed[user][username]=user_get_user_name($feed[uid]);
	}
	$feed[content]=$feed[body_general];
	$feed[createDate]=$feed[dateline];
	$feed[commentCount]=$feed[commenttimes];
	$feed[forwardCount]=$feed[sharetimes];

	if($feed[body_data]){
	}else{
		$feed[body_data]=array("message"=>"");
	}
	if($feed[idtype]=='feed'){
		$feed[isOriginal]=2;
		$feed[content]=$feed[body_general];
		$oldfeed=DB::fetch_first("SELECT *  FROM ".DB::table('home_feed')." WHERE feedid =".$feed[id]);
		$oldfeed=mkfeed($oldfeed);
		if($oldfeed[body_data]){
		}else{
			$oldfeed[body_data]=array("message"=>"");
		}
		if($oldfeed[anonymity]){
			if($oldfeed[anonymity]==-1){
				$oldfeed[user][uid]=-1;
				$oldfeed[user][username]='匿名';
				$oldfeed[user][iconImg]=useravatar(-1);
			}else{
				include_once libfile('function/repeats','plugin/repeats');
				$repeatsinfo=getforuminfo($oldfeed[anonymity]);
				$oldfeed[user][uid]=$repeatsinfo[fid];
				$oldfeed[user][username]=$repeatsinfo[name];
				if($repeatsinfo['icon']){
					$oldfeed['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
				}else{
					$oldfeed['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
				}
				$oldfeed[user][iconImg]=$oldfeed['ficon'];
			}
		}else{
			$oldfeed[user][uid]=$oldfeed[uid];
			$oldfeed[user][iconImg]=useravatar($oldfeed[uid]);
			$oldfeed[user][username]=user_get_user_name($oldfeed[uid]);
		}
		$oldfeed[content]=$oldfeed[body_general];
		$oldfeed[createDate]=$oldfeed[dateline];
		$oldfeed[commentCount]=$oldfeed[commenttimes];
		$oldfeed[forwardCount]=$oldfeed[sharetimes];
		if($oldfeed[icon]=='blog'){
			$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$oldfeed[id]);
			$message=dhtmlspecialchars($blog[message]);
			if(strlen($message)!=strlen($blog[message])){
				$res['isrich']='1';
			}else{
				$res['isrich']='0';
			}
			$oldfeed[content]=str_replace("\r\n","",$blog[message]);
		}elseif($oldfeed[icon]=='album'){
			$res['isrich']='1';
			$oldfeed['body_general']="<div>".$oldfeed['body_general']."</div>";
//			$pic="<style>img { width: 92px; height: 92px; float: left; margin: 0 5px 5px 0; }</style>";
//			$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$oldfeed[target_ids].")");
//			while($value=DB::fetch($query)){
//				$pic=$pic."<img src='".$_G['config']['image']['url'].'/'.$value[filepath]."'>";//端口需要修改
//			}
			$oldfeed[content]=$oldfeed['body_general'];
		}elseif($oldfeed[icon]=='thread'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$oldfeed[id]." order by first desc,dateline desc limit 0,6");
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isrich']='1';
					}else{
						$res['isrich']='0';
					}
					$res['thread']=$value;
					$oldfeed[content]=str_replace("\r\n","",$value[message]);
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymous]){
						if($value[anonymous]==-1){
							$newcommentvalue[user][uid]=-1;
							$newcommentvalue[user][username]='匿名';
							$newcommentvalue[user][iconImg]=useravatar(-1);
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymous]);
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
							$newcommentvalue[user][uid]=$repeatsinfo[fid];
							$newcommentvalue[user][username]=$repeatsinfo[name];
							$newcommentvalue[user][iconImg]=$newcommentvalue['ficon'];
						}
					}else{
						$newcommentvalue[user][uid]=$value[authorid];
						$newcommentvalue[user][username]=user_get_user_name($value[authorid]);
						$newcommentvalue[user][iconImg]=useravatar($value[authorid]);
					}
					$newcommentvalue[commentDate]=$value[dateline];
					$newcommentvalue[commentContent]=htmlspecialchars_decode($value[message]);
					$feed['comments'][]=$newcommentvalue;
				}
			}
		}elseif($oldfeed[icon]=='reward'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$oldfeed[id]." order by first desc,dateline desc limit 0,6");
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isrich']='1';
					}else{
						$res['isrich']='0';
					}
					$res['reward']=$value;
					$oldfeed[content]=str_replace("\r\n","",$value[message]);
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymous]){
						if($value[anonymous]==-1){
							$newcommentvalue[user][uid]=-1;
							$newcommentvalue[user][username]='匿名';
							$newcommentvalue[user][iconImg]=useravatar(-1);
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymous]);
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
							$newcommentvalue[user][uid]=$repeatsinfo[fid];
							$newcommentvalue[user][username]=$repeatsinfo[name];
							$newcommentvalue[user][iconImg]=$newcommentvalue['ficon'];
						}
					}else{
						$newcommentvalue[user][uid]=$value[authorid];
						$newcommentvalue[user][username]=user_get_user_name($value[authorid]);
						$newcommentvalue[user][iconImg]=useravatar($value[authorid]);
					}
					$newcommentvalue[commentDate]=$value[dateline];
					$newcommentvalue[commentContent]=htmlspecialchars_decode($value[message]);
					$feed['comments'][]=$newcommentvalue;
				}
			}
		}elseif($oldfeed[icon]=='notice'){
			$notice=DB::fetch_first("SELECT * FROM ".DB::table('notice')." WHERE id =".$oldfeed[id]);
			$message=dhtmlspecialchars($notice[content]);
			if(strlen($message)!=strlen($notice[content])){
				$res['isrich']='1';
			}else{
				$res['isrich']='0';
			}
			$res['notice']=$notice;
			$oldfeed[content]=str_replace("\r\n","",$notice[content]);
			$oldfeed[content]=$oldfeed[content];
		}elseif($oldfeed[icon]=='profile'){
			$res['isrich']='0';
			$oldfeed[content]=$oldfeed[title_template];
		}elseif($oldfeed[icon]=='resourcelist'){
			$res['isrich']='0';
			$oldfeed[content]=$oldfeed[title_data][resourcetitle];
		}else{
			$res['isrich']='0';
		}
		if($oldfeed[idtype]=='music'){
			$oldfeed[soundUrl]=$oldfeed[body_data][data];
			$res['isrich']='1';
		}elseif($oldfeed[idtype]=='flash'||$oldfeed[idtype]=='video'){
			$oldfeed[videoUrl]=$oldfeed[body_data][data];
			$res['isrich']='1';
		}
		$oldfeed[content]=htmlspecialchars_decode($oldfeed[content]);
		$oldfeed[richContent]=$oldfeed[content];
		$feed[originalFeed]=$oldfeed;
		if($oldfeed[image_1]){
			if(strrpos($oldfeed[image_1],'http://')===0||strrpos($oldfeed[image_1],'http://')){
			}else{
			$oldfeed[image_1]=$_G[config][image][url].'/'.$oldfeed[image_1];
			}
			$pics[pictureUrl]=$oldfeed[image_1];
			$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
			$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
			$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
			$feed[originalFeed][pics][]=$pics;
		}
		if($oldfeed[image_2]){
			if(strrpos($oldfeed[image_2],'http://')===0||strrpos($oldfeed[image_2],'http://')){
			}else{
			$oldfeed[image_2]=$_G[config][image][url].'/'.$oldfeed[image_2];
			}
			$pics[pictureUrl]=$oldfeed[image_2];
			$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
			$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
			$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
			$feed[originalFeed][pics][]=$pics;
		}
		if($oldfeed[image_3]){
			if(strrpos($oldfeed[image_3],'http://')===0||strrpos($oldfeed[image_3],'http://')){
			}else{
			$oldfeed[image_3]=$_G[config][image][url].'/'.$oldfeed[image_3];
			}
			$pics[pictureUrl]=$oldfeed[image_3];
			$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
			$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
			$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
			$feed[originalFeed][pics][]=$pics;
		}
		if($oldfeed[image_4]){
			if(strrpos($oldfeed[image_4],'http://')===0||strrpos($oldfeed[image_4],'http://')){
			}else{
			$oldfeed[image_4]=$_G[config][image][url].'/'.$oldfeed[image_4];
			}
			$pics[pictureUrl]=$oldfeed[image_4];
			$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
			$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
			$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
			$feed[originalFeed][pics][]=$pics;
		}
		if($oldfeed[image_5]){
			if(strrpos($oldfeed[image_5],'http://')===0||strrpos($oldfeed[image_5],'http://')){
			}else{
			$oldfeed[image_5]=$_G[config][image][url].'/'.$oldfeed[image_5];
			}
			$pics[pictureUrl]=$oldfeed[image_5];
			$pics[reducePictureUrl]=str_replace(".jpg",".thumb.jpg",$pics[pictureUrl]);
			$pics[reducePictureUrl]=str_replace(".png",".thumb.png",$pics[reducePictureUrl]);
			$pics[reducePictureUrl]=str_replace(".gif",".thumb.gif",$pics[reducePictureUrl]);
			$feed[originalFeed][pics][]=$pics;
		}
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feed[feedid]." order by dateline desc limit 0,5");
		while($value=DB::fetch($query)){
			$cvalue[cid]=$value[cid];
			if($value[anonymity]){
				if($value[anonymity]==-1){
					$cvalue[user][uid]=-1;
					$cvalue[user][username]='匿名';
					$cvalue[user][iconImg]=useravatar(-1);
				}else{
					include_once libfile('function/repeats','plugin/repeats');
					$repeatsinfo=getforuminfo($value[anonymity]);
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
				$cvalue[user][uid]=$value[authorid];
				$cvalue[user][username]=user_get_user_name($value[authorid]);
				$cvalue[user][iconImg]=useravatar($value[authorid]);
			}
			$cvalue[commentDate]=$value[dateline];
			$cvalue[commentContent]=htmlspecialchars_decode($value[message]);
			$feed['comments'][]=$cvalue;
		}
	}else{
		$feed[isOriginal]=1;
		$feed[oldfeed]='null';
		if($feed[icon]=='blog'){
			$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$feed[id]);
			$message=dhtmlspecialchars();
			if(strlen($message)!=strlen($blog[message])){
				$res['isrich']='1';
			}else{
				$res['isrich']='0';
			}
//			$feed[content]=$blog[message];
			$feed[content]=str_replace("\r\n","<br>",$blog[message]);
		}elseif($feed[icon]=='album'){
			$res['isrich']='1';
			$feed['body_general']="<div>".$feed['body_general']."</div>";
//			$pic="<style>img { width: 92px; height: 92px; float: left; margin: 0 5px 5px 0; }</style>";
//			$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$feed[target_ids].")");
//			while($value=DB::fetch($query)){
//				$pic=$pic."<img src='".$_G['config']['image']['url'].'/'.$value[filepath]."'>";
//			}
			$feed[content]=$feed[body_general];
		}elseif($feed[icon]=='thread'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$feed[id]." order by first desc,dateline desc limit 0,6");
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isrich']='1';
					}else{
						$res['isrich']='0';
					}
					$res['thread']=$value;
					$feed[content]=str_replace("\r\n","<br>",$value[message]);
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[iconImg]=useravatar($value[authorid]);
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymous]){
						if($value[anonymous]==-1){
							$cvalue[user][uid]=-1;
							$cvalue[user][username]='匿名';
							$cvalue[user][iconImg]=useravatar(-1);
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymous]);
							$cvalue[user][uid]=$repeatsinfo[fid];
							$cvalue[user][username]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
							$cvalue[user][iconImg]=$newcommentvalue['ficon'];
						}
					}else{
						$cvalue[user][uid]=$value[authorid];
						$cvalue[user][username]=user_get_user_name($value[authorid]);
						$cvalue[user][iconImg]=useravatar($value[authorid]);
					}
					$cvalue[cid]=$newcommentvalue[cid];
					$cvalue[commentDate]=$newcommentvalue[dateline];
					$cvalue[commentContent]=htmlspecialchars_decode($newcommentvalue[message]);
					$feed['comments'][]=$cvalue;
				}
			}
		}elseif($feed[icon]=='reward'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$feed[id]." order by first desc,dateline desc limit 0,6");
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isrich']='1';
					}else{
						$res['isrich']='0';
					}
					$res['reward']=$value;
					$feed[content]=str_replace("\r\n","<br>",$value[message]);
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[iconImg]=useravatar($value[authorid]);
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymous]){
						if($value[anonymous]==-1){
							$cvalue[user][uid]=-1;
							$cvalue[user][username]='匿名';
							$cvalue[user][iconImg]=useravatar(-1);
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymous]);
							$cvalue[user][uid]=$repeatsinfo[fid];
							$cvalue[user][username]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
							$cvalue[user][iconImg]=$newcommentvalue['ficon'];
						}
					}else{
						$cvalue[user][uid]=$value[authorid];
						$cvalue[user][username]=user_get_user_name($value[authorid]);
						$cvalue[user][iconImg]=useravatar($value[authorid]);
					}
					$cvalue[cid]=$newcommentvalue[cid];
					$cvalue[commentDate]=$newcommentvalue[dateline];
					$cvalue[commentContent]=htmlspecialchars_decode($newcommentvalue[message]);
					$feed['comments'][]=$cvalue;
				}
			}
		}elseif($feed[icon]=='notice'){
			$notice=DB::fetch_first("SELECT * FROM ".DB::table('notice')." WHERE id =".$feed[id]);
			$message=dhtmlspecialchars($notice[content]);
			if(strlen($message)!=strlen($notice[content])){
				$res['isrich']='1';
			}else{
				$res['isrich']='0';
			}
			$res['notice']=$notice;
			$feed[content]=str_replace("\r\n","<br>",$notice[content]);
		}elseif($feed[icon]=='profile'){
			$res['isrich']='0';
			$feed[content]=$value[title_template];
		}elseif($feed[icon]=='resourcelist'){
			$res['isrich']='0';
			$feed[content]=$feed[title_data][resourcetitle];
		}else{
			$res['isrich']='0';
		}
		$feed[content]=htmlspecialchars_decode($feed[content]);
		if($feed[idtype]=='music'){
			$feed[soundUrl]=$feed[body_data][data];
			$res['isrich']='1';
		}elseif($feed[idtype]=='flash'||$feed[idtype]=='video'){
			$feed[videoUrl]=$feed[body_data][data];
			$res['isrich']='1';
		}
		if($feed[comments]){
		}else{
			$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feedid." order by dateline desc limit 0,5");
			while($value=DB::fetch($query)){
				$cvalue[cid]=$value[cid];
				if($value[anonymity]){
					if($value[anonymity]==-1){
						$cvalue[user][uid]=-1;
						$cvalue[user][username]='匿名';
						$cvalue[user][iconImg]=useravatar(-1);
					}else{
						include_once libfile('function/repeats','plugin/repeats');
						$repeatsinfo=getforuminfo($value[anonymity]);
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
					$cvalue[user][uid]=$value[authorid];
					$cvalue[user][username]=user_get_user_name($value[authorid]);
					$cvalue[user][iconImg]=useravatar($value[authorid]);
				}
				$cvalue[commentDate]=$value[dateline];
				$cvalue[commentContent]=htmlspecialchars_decode($value[message]);
				$feed['comments'][]=$cvalue;
			}
		}
	}

}
$res['blog']=$blog;
$res['pic']=$pic;
$res['feed']=$feed;
$feed[isrich]=$res[isrich];
$feed[richContent]=$feed[content];


echo json_encode($feed);

?>